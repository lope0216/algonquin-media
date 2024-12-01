<?php
require_once '../db/DBconnection.php'; // Correct path to DBconnection.php
include_once __DIR__ . '/../common/utils.php';

startSession();

if (!isLoggedIn()) {
  unauthorizedAccess();
}

// Fetch albums from database
$pdo = getPDOConnection();
$albums = [];
try {
    $userid = 'U0001'; // Replace with actual UserId
    $stmt = $pdo->prepare("SELECT Album_Id as AlbumId, Title as AlbumName FROM cst8257project.album WHERE Owner_Id = :userId");
    $stmt->bindParam(':userId', $userid);
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error fetching albums: " . $e->getMessage();
}

// Initialize variables
$uploadedFiles = [];
$uploadErrors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $album = htmlspecialchars($_POST['album'] ?? '');
    $title = htmlspecialchars($_POST['title'] ?? 'Untitled');
    $description = htmlspecialchars($_POST['description'] ?? '');

    // Fetch AlbumName from the database based on Album_Id
    $albumName = '';
    try {
        $stmt = $pdo->prepare("SELECT Title as AlbumName FROM cst8257project.album WHERE Album_Id = :albumId");
        $stmt->bindParam(':albumId', $album);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $albumName = $result['AlbumName'] ?? 'Unknown Album'; // Default if no title found
    } catch (Exception $e) {
        $uploadErrors[] = "Failed to retrieve album name: " . $e->getMessage();
    }

    // Directory to store uploaded files
    $uploadDir = 'uploads/' . $album . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Process each uploaded file
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                // Save file metadata to database
                try {
                    $stmt = $pdo->prepare("INSERT INTO cst8257project.picture (Album_Id, File_Name, Title, Description) VALUES (:albumId, :fileName, :title, :description)");
                    $stmt->execute([
                        ':albumId' => $album,
                        ':fileName' => $fileName,
                        ':title' => $title,
                        ':description' => $description
                    ]);
                    $uploadedFiles[] = $fileName;
                } catch (Exception $e) {
                    $uploadErrors[] = "Failed to save file metadata: $fileName (" . $e->getMessage() . ")";
                }
            } else {
                $uploadErrors[] = "Failed to upload file: $fileName";
            }
        }
    } else {
        $uploadErrors[] = "No files selected for upload.";
    }

    // Set success message if files were uploaded successfully
    if (count($uploadedFiles) > 0) {
        $successMessage = "Successfully uploaded " . count($uploadedFiles) . " file(s) to the album '$albumName'.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="/../public/css/uploadPictures.css" />
    <link rel="stylesheet" href="/../public/css/global.css" />
    
    <title>Upload Pictures</title>
</head>

<body class="body-layout">
    <?php include("../common/header.php"); ?>

    <div class="container">
        <h1 class="text-center text-primary mb-2">Upload Pictures</h1>

        <!-- Display Success Message -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Display Error Messages -->
        <?php if (!empty($uploadErrors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($uploadErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form for uploading pictures -->
        <div class="form-container mx-auto p-4 border shadow-sm rounded" style="max-width: 500px; background-color: white;">
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Album Selection -->
                <div class="mb-3">
                    <label for="album" class="form-label fw-bold">Upload to Album:</label>
                    <select id="album" name="album" class="form-select" required>
                        <option value="">Select an album</option>
                        <?php foreach ($albums as $album): ?>
                            <option value="<?= htmlspecialchars($album['AlbumId']) ?>">
                                <?= htmlspecialchars($album['AlbumName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- File Upload -->
                <div class="mb-3">
                    <label for="file" class="form-label fw-bold">File to Upload:</label>
                    <input type="file" id="file" name="files[]" class="form-control" multiple required>
                </div>

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">Title:</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter a title (optional)">
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Description:</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter a description (optional)"></textarea>
                </div>

                <!-- Submit and Clear Buttons -->
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>
            </form>
        </div>
    </div>

    <?php include("../common/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></script>
</body>

</html>