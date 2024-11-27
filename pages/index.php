<?php
session_start();
$iniFilePath = dirname(__FILE__) . "/../config/db.ini";
if (!file_exists($iniFilePath)) {
    die("Database configuration file not found.");
}
$dbConfig = parse_ini_file($iniFilePath, true);
include(dirname(__FILE__) . "/../db/DBConnection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/index.css' ?>" />
    <title>Algonquin Social Media Website</title>
</head>
<body class="bg-light">
    <div class="body-layout">
        <?php include(dirname(__FILE__) . "/../common/header.php"); ?>
        <div class="container mt-5 content">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header text-center">
                            <h2>Welcome to Algonquin Social Media Website</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-center">If you have never used this before, you have to <a href="NewUser.php">sign up</a> first.</p>
                            <p class="text-center">If you have already signed up, you can <a href="Login.php">log in</a> now.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include(dirname(__FILE__) . "/../common/footer.php"); ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
