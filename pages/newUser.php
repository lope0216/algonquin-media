<?php
include_once('../db/DBconnection.php');
$studentId = $name = $phone = $password = $confirmPassword = "";
$errors = [
    "studentId" => "",
    "name" => "",
    "phone" => "",
    "password" => "",
    "confirmPassword" => ""
];
$conn = getPDOConnection();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = trim($_POST["studentId"]);
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);
    if (!array_filter($errors)) {
        insertStudent($studentId, $name, $phone, $password, $conn);
        echo "<p class='alert alert-success mt-4'>Registration successful! You can now <a href='Login.php' class='alert-link'>log in</a>.</p>";
    }
}
function checkStudentIdExists($studentId, $conn) {
    $stmt = $conn->prepare("SELECT 1 FROM Student WHERE StudentId = ? LIMIT 1");
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->store_result(); 
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return true; 
    } else {
        $stmt->close();
        return false; 
    }
}
function insertStudent($studentId, $name, $phone, $password, $conn) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO User (UserId, Name, Phone, Password) VALUES (?, ?, ?, ?)");
    $stmt->bindParam(1, $studentId);
    $stmt->bindParam(2, $name);
    $stmt->bindParam(3, $phone);
    $stmt->bindParam(4, $hashedPassword);

    if ($stmt->execute()) {
        echo "<p class='alert alert-success mt-4'>Registration successful! You can now <a href='Login.php'>log in</a>.</p>";
        return true;
    } else {
        echo "<p class='alert alert-danger mt-4'>Error: " . $stmt->errorInfo()[2] . "</p>";
        return false;
    }
    $stmt->close();
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
                    <form method="post" action="NewUser.php">
                        <div class="mb-3">
                            <label for="studentId" class="form-label">Student ID:</label>
                            <input type="text" class="form-control" id="studentId" name="studentId" value="<?php echo htmlspecialchars($studentId); ?>">
                            <div class="text-danger"><?php echo $errors["studentId"]; ?></div>
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
