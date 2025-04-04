<?php
require_once '../db/DBconnection.php';
include_once __DIR__ . '/../common/utils.php';

startSession();

if (!isLoggedIn()) {
    unauthorizedAccess();
}
$friendId = isset($_GET['friendId']) ? $_GET['friendId'] : null;
$friendName = isset($_GET['friendName']) ? $_GET['friendName'] : null;

$pdo = getPDOConnection();
$albums = [];
$comments = [];
$pictures = [];
$selectedPicture = null;
$successMessage = '';
$errorMessage = '';

if ($friendId) {
    try {
        // Fetch albums owned by the friend with the given friendId
        $albums = getAlbum($pdo, $friendId); 
    } catch (Exception $e) {
        $errorMessage = "Error fetching albums: " . $e->getMessage();
    }
} else {
    $errorMessage = "No friend ID provided.";
}


try {
    $friendId = isset($_GET['friendId']) ? htmlspecialchars($_GET['friendId']) : null;
    if (!$friendId) {
        throw new Exception("Friend ID is required.");
    }

    
    if (empty($albums)) {
        $errorMessage = "No albums found for the specified friend.";
    }
} catch (Exception $e) {
    $errorMessage = "Error fetching albums: " . $e->getMessage();
}

// Handle album selection and fetch pictures
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['album'])) {
    $albumId = htmlspecialchars($_GET['album']);
    try {
        $stmt = $pdo->prepare("SELECT Picture_Id, File_Name, Title, Description FROM cst8257project.picture WHERE Album_Id = :albumId");
        $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
        $stmt->execute();
        $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($pictures)) {
            $selectedPicture = $pictures[0];
        }

        // If a specific picture is requested, update the selected picture
        if (!empty($_GET['picture'])) {
            $selectedPictureId = htmlspecialchars($_GET['picture']);
            foreach ($pictures as $picture) {
                if ($picture['Picture_Id'] == $selectedPictureId) {
                    $selectedPicture = $picture;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        $errorMessage = "Error fetching pictures: " . $e->getMessage();
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment']) && !empty($_POST['pictureId'])) {
    $friendId = isset($_POST['friendId']) ? $_POST['friendId'] : null;
    $friendName = isset($_POST['friendName']) ? $_POST['friendName'] : null;
    $albums = getAlbum($pdo, $friendId);
    $comment = htmlspecialchars($_POST['comment']);
    $pictureId = htmlspecialchars($_POST['pictureId']);
    $userId = $_SESSION['UserId']; 

    if (!$userId) {
        $errorMessage = "User not logged in.";
    } else {
        try { 
            $stmt = $pdo->prepare("INSERT INTO cst8257project.comment (Picture_Id, Comment_Text, Author_Id) VALUES (:pictureId, :commentText, :authorId)");
            $stmt->execute([
                ':pictureId' => $pictureId,
                ':commentText' => $comment,
                ':authorId' => $userId,
            ]);
            $successMessage = "Comment added successfully!";
            
            // Redirect to prevent form resubmission
            header("Location: friendPictures.php?album=" . urlencode($_GET['album']) 
            . "&picture=" . urlencode($pictureId) 
            . "&friendId=" . urlencode($friendId) 
            . "&friendName=" . urlencode($friendName));
            exit;
        } catch (Exception $e) {
            $errorMessage = "Error adding comment: " . $e->getMessage();
        }
    }
}

// Fetch comments for the selected picture
if ($selectedPicture) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.Comment_Text, u.Name as UserName
            FROM cst8257project.comment c
            INNER JOIN cst8257project.user u ON c.Author_Id = u.UserId
            WHERE Picture_Id = :pictureId;
        ");
        $stmt->bindParam(':pictureId', $selectedPicture['Picture_Id'], PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $errorMessage = "Error fetching comments: " . $e->getMessage();
    }
}
function getAlbum($pdo, $friendId){
    $stmt = $pdo->prepare("SELECT Album_Id as AlbumId, Title as AlbumName FROM cst8257project.album WHERE Owner_Id = :friendId and Accessibility_Code ='shared'" );
        $stmt->bindParam(':friendId', $friendId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/global.css' ?>" />
    <title>My Pictures</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #D4D4D4; /* Light Gray Background */
            color: #343a40; /* Bootstrap Default Dark Text */
        }
    </style>
</head>
<body class="body-layout">
    <?php include("../common/header.php"); ?>

    <div class="container">
    <h1><?= htmlspecialchars($friendName) ?>'s pictures</h1>
        <!-- Display Success and Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <!-- Album Selection -->
        <form method="GET" action="friendPictures.php" class="mb-4">
            <div class="mb-3">
            <input type="hidden" name="friendId" value="<?=  $friendId?>">
            <input type="hidden" name="friendName" value="<?=  $friendName?>">
                <label for="album" class="form-label">Select an Album</label>
                <select id="album" name="album" class="form-select" onchange="this.form.submit()" required>
                    <option value="">Choose an album</option>
                    <?php foreach ($albums as $album): ?>
                        <option value="<?= htmlspecialchars($album['AlbumId']) ?>" <?= isset($_GET['album']) && $_GET['album'] == $album['AlbumId'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($album['AlbumName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- Carousel -->
        <?php if (!empty($pictures)): ?>
            <div id="pictureCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($pictures as $index => $picture): ?>
                        <button type="button" data-bs-target="#pictureCarousel" data-bs-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($pictures as $index => $picture): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="uploads/<?= htmlspecialchars($albumId) ?>/<?= htmlspecialchars($picture['File_Name']) ?>" 
                                 class="d-block w-100" 
                                 alt="<?= htmlspecialchars($picture['Title']) ?>">
                            <div class="carousel-caption d-none d-md-block">
                                <h5><?= htmlspecialchars($picture['Title']) ?></h5>
                                <p><?= htmlspecialchars($picture['Description'] ?? 'No description available') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted <?= empty($_GET['album']) ? 'd-none' : '' ?>">No pictures found in this album.</p>
        <?php endif; ?>

        <!-- Comments Section -->
        <?php if ($selectedPicture): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Comments</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($comments as $comment): ?>
                            <li class="list-group-item">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?= htmlspecialchars($comment['UserName']) ?></div>
                                    <?= htmlspecialchars($comment['Comment_Text']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($comments)): ?>
                            <li class="list-group-item">No comments yet.</li>
                        <?php endif; ?>
                    </ul>
                    <form method="POST" action="friendPictures.php?album=<?= htmlspecialchars($_GET['album']) ?>">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Write a comment..." required></textarea>
                        </div>
                        <input type="hidden" name="pictureId" value="<?= htmlspecialchars($selectedPicture['Picture_Id']) ?>">
                        <input type="hidden" name="friendId" value="<?=  $friendId?>">
                        <input type="hidden" name="friendName" value="<?=  $friendName?>">
                        <button type="submit" class="btn btn-primary mb-3">Add Comment</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include("../common/footer.php"); ?>
    <script src="../public/js/myPictures.js"></script>
</body>
</html>
