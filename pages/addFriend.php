<?php
session_start();
include_once('../db/DBconnection.php');

$conn = getPDOConnection(); // Ensure this function sets up the database connection

if (isset($_SESSION['UserId'])) {
    $current_user_id = $_SESSION['UserId'];
} else {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $friend_user_id = isset($_POST['friend_user_id']) ? trim($_POST['friend_user_id']) : '';

    if (empty($friend_user_id)) {
        $error_message = "Please enter a valid User ID.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT UserId FROM `user` WHERE UserId = ?");
            $stmt->execute([$friend_user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $normalized_current_user_id = strtolower($current_user_id);
                $normalized_friend_user_id = strtolower($friend_user_id);

                $stmt = $conn->prepare(
                    "SELECT * FROM `friendship` 
                     WHERE (LOWER(Friend_RequesterId) = ? AND LOWER(Friend_RequesteeId) = ?) 
                     OR (LOWER(Friend_RequesterId) = ? AND LOWER(Friend_RequesteeId) = ?)"
                );
                $stmt->execute([$normalized_current_user_id, $normalized_friend_user_id, $normalized_friend_user_id, $normalized_current_user_id]);
                $existing_request = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing_request) {
                    $error_message = "A friendship request already exists between these users.";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO `friendship` (Friend_RequesterId, Friend_RequesteeId, Status)
                        VALUES (?, ?, 'Request')"
                    );

                    if ($stmt->execute([$current_user_id, $friend_user_id])) {
                        $success_message = "The friend request has been sent.";
                    } else {
                        $error_message = "An error occurred while sending the friend request.";
                    }
                }
            } else {
                $error_message = "User ID not found.";
            }
        } catch (PDOException $e) {
            $error_message = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Friend</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/../public/css/login.css"> <!-- Your custom CSS if needed -->
</head>
<body>
    <?php include(dirname(__FILE__) . "/../common/header.php"); ?>

    <div class="container my-5">
        <h1 class="mb-4">Add Friend</h1>
        <p class="lead mb-4">Welcome, <?php echo htmlspecialchars($current_user_id); ?>! (Not you? <a href="logout.php">Change user</a>)</p>

        <form method="POST" action="" class="mb-4">
            <div class="mb-3">
                <label for="friend_user_id" class="form-label">Enter User ID:</label>
                <input type="text" class="form-control" id="friend_user_id" name="friend_user_id" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Friend Request</button>
        </form>

        <?php
        if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        }
        ?>
    </div>

    <?php include(dirname(__FILE__) . "/../common/footer.php"); ?>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
