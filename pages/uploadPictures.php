<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $album = htmlspecialchars($_POST['album'] ?? '');
    $title = htmlspecialchars($_POST['title'] ?? 'Untitled');
    $description = htmlspecialchars($_POST['description'] ?? '');

    // Directory to store uploaded files
    $uploadDir = 'uploads/' . $album . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];
    $uploadErrors = [];

    // Process each uploaded file
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedFiles[] = $fileName;
            } else {
                $uploadErrors[] = "Failed to upload file: $fileName";
            }
        }
    } else {
        $uploadErrors[] = "No files selected for upload.";
    }

    // Redirect if at least one file was uploaded successfully
    if (count($uploadedFiles) > 0) {
        header("Location: myPictures.php?album=" . urlencode($album));
        exit();
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
    <title>Upload Pictures</title>
</head>

<body class="body-layout">
    <?php include("../common/header.php"); ?>

    <div class="container">
        <h1 class="text-center text-primary mb-2">Upload Pictures</h1>

        <!-- Form for uploading pictures -->
        <div class="form-container mx-auto p-4 border shadow-sm rounded" style="max-width: 500px; background-color: white;">
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Album Selection -->
                <div class="mb-3">
                    <label for="album" class="form-label fw-bold">Upload to Album:</label>
                    <select id="album" name="album" class="form-select" required>
                      
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
