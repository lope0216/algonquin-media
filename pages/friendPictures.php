<?php
require_once '../db/DBconnection.php';
include_once __DIR__ . '/../common/utils.php';

startSession();

if (!isLoggedIn()) {
    unauthorizedAccess();
}

$pdo = getPDOConnection();
$albums = [];
$errorMessage = '';

// Check if friendId is provided in the URL
$friendId = isset($_GET['friendId']) ? $_GET['friendId'] : null;

if ($friendId) {
    try {
        // Fetch albums owned by the friend with the given friendId
        $stmt = $pdo->prepare("SELECT Album_Id as AlbumId, Title as AlbumName FROM cst8257project.album WHERE Owner_Id = :friendId");
        $stmt->bindParam(':friendId', $friendId);
        $stmt->execute();
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $errorMessage = "Error fetching albums: " . $e->getMessage();
    }
} else {
    $errorMessage = "No friend ID provided.";
}

// Redirect to 'friendPictures.php' if an album is selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selectedAlbum'])) {
    $albumId = htmlspecialchars($_POST['selectedAlbum']);
    header("Location: friendPictures.php?friendId=" . urlencode($friendId) . "&album=" . urlencode($albumId));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/global.css' ?>" />
    <title>Albums for Friend</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="body-layout">
    <?php include("../common/header.php"); ?>

    <div class="container">
        <!-- Display Error Message if friendId is not provided -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php else: ?>
            <h1>Albums for Friend ID: <?= htmlspecialchars($friendId) ?></h1>

            <!-- Display the Albums as a Dropdown -->
            <?php if (!empty($albums)): ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="albumSelect" class="form-label">Select an Album:</label>
                        <select id="albumSelect" name="selectedAlbum" class="form-select" required>
                            <option value="">-- Choose an album --</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?= htmlspecialchars($album['AlbumId']) ?>"><?= htmlspecialchars($album['AlbumName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">View Album</button>
                </form>
            <?php else: ?>
                <p class="text-center text-muted">No albums found for this friend.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
