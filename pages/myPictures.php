<?php
require_once '../db/DBconnection.php';
include_once __DIR__ . '/../common/utils.php';

startSession();

if (!isLoggedIn()) {
  unauthorizedAccess();
}

$pdo = getPDOConnection();
$albums = [];
$comments = [];
$pictures = [];
$selectedPicture = null;
$successMessage = '';
$errorMessage = '';

// Fetch albums owned by the user
try {
    $userid = $_SESSION['UserId']; 
    $stmt = $pdo->prepare("SELECT Album_Id as AlbumId, Title as AlbumName FROM cst8257project.album WHERE Owner_Id = :userId");
    $stmt->bindParam(':userId', $userid);
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching albums: " . $e->getMessage();
}

// Handle album selection and fetch pictures
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['album'])) {
    $albumId = htmlspecialchars($_GET['album']);
    try {
        $stmt = $pdo->prepare("SELECT Picture_Id, File_Name, Title, Description FROM cst8257project.picture WHERE Album_Id = :albumId");
        $stmt->bindParam(':albumId', $albumId);
        $stmt->execute();
        $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set the first picture as selected by default
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
    $comment = htmlspecialchars($_POST['comment']);
    $pictureId = htmlspecialchars($_POST['pictureId']);

    try {
        $stmt = $pdo->prepare("INSERT INTO cst8257project.comment (Picture_Id, Comment_Text, Author_Id) VALUES (:pictureId, :commentText, :authorId)");
        $stmt->execute([
            ':pictureId' => $pictureId,
            ':commentText' => $comment,
            ':authorId' => $userid,
        ]);
        $successMessage = "Comment added successfully!";
        
        // Redirect to prevent form resubmission and reload updated comments
        header("Location: myPictures.php?album=" . urlencode($_GET['album']) . "&picture=" . urlencode($pictureId));
        exit;
    } catch (Exception $e) {
        $errorMessage = "Error adding comment: " . $e->getMessage();
    }
}

// Fetch comments for the selected picture
if ($selectedPicture) {
    try {
        $stmt = $pdo->prepare("SELECT Comment_Text FROM cst8257project.comment WHERE Picture_Id = :pictureId");
        $stmt->bindParam(':pictureId', $selectedPicture['Picture_Id']);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $errorMessage = "Error fetching comments: " . $e->getMessage();
    }
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
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="body-layout">
    <?php include("../common/header.php"); ?>

    <div class="container">
        <h1 class="text-center my-4">My Pictures</h1>

        <!-- Display Success and Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <!-- Album Selection -->
        <form method="GET" action="myPictures.php" class="mb-4">
            <div class="mb-3">
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
                <!-- Indicators -->
                <div class="carousel-indicators">
                    <?php foreach ($pictures as $index => $picture): ?>
                        <button type="button" data-bs-target="#pictureCarousel" data-bs-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Items -->
                <div class="carousel-inner">
                    <?php foreach ($pictures as $index => $picture): ?>
                        <div class="carousel-item <?= $picture['Picture_Id'] == $selectedPicture['Picture_Id'] ? 'active' : '' 
                        ?>"
                        data-picture-id="<?=$picture['Picture_Id']?>" data-test="<?=$selectedPicture['Picture_Id']?>">
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

                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No pictures found in this album.</p>
        <?php endif; ?>

        <!-- Comments Section -->
        <?php if ($selectedPicture): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Comments</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($comments as $comment): ?>
                            <li class="list-group-item"><?= htmlspecialchars($comment['Comment_Text']) ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($comments)): ?>
                            <li class="list-group-item">No comments yet.</li>
                        <?php endif; ?>
                    </ul>
                    <!-- Add Comment -->
                    <form method="POST" action="myPictures.php?album=<?= htmlspecialchars($_GET['album']) ?>">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Write a comment..." required></textarea>
                        </div>
                        <input type="hidden" name="pictureId" value="<?= htmlspecialchars($selectedPicture['Picture_Id']) ?>">
                        <button type="submit" class="btn btn-primary mb-3">Add Comment</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include("../common/footer.php"); ?>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="../public/js/myPictures.js"></script>
</body>
</html>
