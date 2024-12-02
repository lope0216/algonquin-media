<?php
include_once('../db/DBconnection.php');
include_once __DIR__ . '/../common/utils.php';

startSession();
$errorMessage = "";
if (isLoggedIn()) {
  redirectToHome();
}

$conn = getPDOConnection(); 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['UserId'];
    $userPassword = $_POST['password'];
    $sql = $conn->prepare("SELECT * FROM User WHERE UserId = :userId");
    $sql->bindParam(':userId', $userId, PDO::PARAM_STR); 
    $sql->execute();
    $User = $sql->fetch(PDO::FETCH_ASSOC);
    if ($User) {
        if (password_verify($userPassword, $User['Password'])) {
            $_SESSION['UserId'] = $User['UserId'];
            $_SESSION['UserName'] = $User['Name'];
            header("Location: addAlbum.php");
            exit();
        } else {
            $errorMessage = "Invalid User ID or password!";
        }
    } else {
$errorMessage = "User not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Course Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="/../public/css/login.css">
</head>
<body>
    <?php include(dirname(__FILE__) . "/../common/header.php"); ?>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Login</h2>
                        <form method="POST" action="Login.php">
                            <div class="mb-3">
                                <label for="UserId" class="form-label">User ID:</label>
                                <input type="text" class="form-control" id="UserId" name="UserId" placeholder="Enter your User ID" >
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" >
                            </div>
                            <div class='alert alert-danger text-center <?= empty($errorMessage)? "d-none" : "" ?>'><?= $errorMessage ?></div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Log In</button>
                            </div>
                            <div class="d-grid mt-2">
                                <button type="reset" class="btn btn-secondary">Clear</button>
                            </div>
                            
                        </form>
                        <div class="text-center mt-3">
                            <p>Don't have an account? <a href="NewUser.php" class="text-decoration-none">Sign up</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include(dirname(__FILE__) . "/../common/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
