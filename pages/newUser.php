<?php
include_once('../db/DBconnection.php');

include_once __DIR__ . '/../common/utils.php';

startSession();

if (isLoggedIn()) {
  redirectToHome();
}

$UserId = $name = $phone = $password = $confirmPassword = "";
$errors = [
    "UserId" => "",
    "name" => "",
    "phone" => "",
    "password" => "",
    "confirmPassword" => ""
];
$conn = getPDOConnection();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $UserId = trim($_POST["UserId"]);
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);
    if (empty($UserId)) {
        $errors["UserId"] = "User ID cannot be blank.";
    } elseif (checkUserIdExists($UserId, $conn)) {
        $errors["UserId"] = "This user ID already exists.";
    }

    // Validate Name
    if (empty($name)) {
        $errors["name"] = "Name cannot be blank.";
    }

    // Validate Phone
    if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $phone)) {
        $errors["phone"] = "Phone number must be in the format nnn-nnn-nnnn.";
    }

    // Validate Password
    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/\d/", $password)) {
        $errors["password"] = "Password must be at least 6 characters long, contain one uppercase letter, one lowercase letter, and one digit.";
    }

    // Validate Confirm Password if not match
    if ($password !== $confirmPassword) {
        $errors["confirmPassword"] = "Passwords do not match.";
    }

    // Check if there are no errors before inserting into the database
    if (array_filter($errors) === []) { // If no errors exist in the array
        $insertSuccess = insertUser($UserId, $name, $phone, $password, $conn);

        if ($insertSuccess) {
            // Redirect or show a success message
            header("Location: login.php"); // Redirect to a success page
            exit;
        } else {
            echo "<div class='text-danger'>An error occurred while saving your data. Please try again later.</div>";
        }
    }
}


function checkUserIdExists($userId, $conn) {
    $stmt = $conn->prepare("SELECT 1 FROM User WHERE UserId = ? LIMIT 1");
    $stmt->execute([$userId]);
    return $stmt->fetch() ? true : false;
}

function insertUser($UserId, $name, $phone, $password, $conn) {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO User (UserId, Name, Phone, Password) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$UserId, $name, $phone, $hashedPassword]);
    } catch (PDOException $e) {
        error_log("Error inserting User: " . $e->getMessage());
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Algonquin Social Media Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/newUser.css' ?>">
</head>
<body>
    <?php include(dirname(__FILE__) . "/../common/header.php"); ?>
    <div class="container">
        <div class="form-container">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Sign Up</h2>
                    <p>All fields are required</p>
                    <form method="post" action="NewUser.php">
                        <div class="mb-3">
                            <label for="UserId" class="form-label">User ID:</label>
                            <input type="text" class="form-control" id="UserId" name="UserId" value="<?php echo htmlspecialchars($UserId); ?>">
                            <div class="text-danger"><?php echo $errors["UserId"]; ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                            <div class="text-danger"><?php echo $errors["name"]; ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number:</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                            <div class="text-danger"><?php echo $errors["phone"]; ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>">
                            <div class="text-danger"><?php echo $errors["password"]; ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password:</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" value="<?php echo htmlspecialchars($confirmPassword); ?>">
                            <div class="text-danger"><?php echo $errors["confirmPassword"]; ?></div>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary">Clear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        <?php include(dirname(__FILE__) . "/../common/footer.php"); ?>
</body>
</html>
