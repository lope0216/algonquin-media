<?php

include_once __DIR__ . '/../common/utils.php';

startSession();

if (!isLoggedIn()) {
  unauthorizedAccess();
}

require_once("../model/album.php");

$albumModel = new AlbumModel();
$accessibilityOptions = $albumModel->getAlbumAccesibillityOptions();
$user = $_SESSION['UserName'];
$userId = $_SESSION['UserId'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $accessibility = $_POST['accessibility'] ?? '';
  $description = $_POST['description'] ?? '';

  // Call the createAlbum function
  $message = $albumModel->createAlbum($title, $accessibility, $description, $userId);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/addAlbum.css' ?>" />
      <!-- Google Fonts -->
      <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <title>Algonquin Media</title>

    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #D4D4D4; /* Light Gray Background */
            color: #343a40; /* Bootstrap Default Dark Text */
        }
    </style>
</head>

<body>
    <?php include("../common/header.php"); ?>

    <div class="container mt-5 mb-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">Create a New Album</h1>
                <p class="lead text-center">Welcome <b><?= $user ?></b>! (Not you? Change user <a href="logout.php" class="text-decoration-none">here</a>)</p>

                <?php if (isset($message)): ?>
                    <div class="alert <?= strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="addAlbum.php">
                    <!-- Title Field -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" id="title" placeholder="Enter album title" required>
                    </div>

                    <!-- Accessibility Dropdown -->
                    <div class="mb-3">
                        <label for="accessibility" class="form-label">Accessibility</label>
                        <select id="accessibility" name="accessibility" class="form-select" required>
                            <option value="" disabled selected>Choose accessibility</option>
                            <?php foreach ($accessibilityOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['Accessibility_Code']) ?>"><?= htmlspecialchars($option['Description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Description Field -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Enter your description here..." required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("../common/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
