<?php
session_start();
require_once '../db/DBconnection.php'; // Include database connection

$conn = getPDOConnection(); // Assuming this function returns a PDO instance

// Initialize variables
$user = $_SESSION['UserName'] ?? 'Guest';
$userId = $_SESSION['UserId'] ?? null;
$friends = [];
$friendRequests = [];
$errorMessage = '';

// Fetch friends
try {
    $stmt = $conn->prepare("
        SELECT f.Friend_RequesterId AS FriendId, u.Name AS FriendName, COUNT(DISTINCT a.Album_Id) AS SharedAlbums
        FROM cst8257project.friendship f
        JOIN cst8257project.user u ON f.Friend_RequesterId = u.UserId OR f.Friend_RequesteeId = u.UserId
        LEFT JOIN cst8257project.album a ON f.Friend_RequesteeId = a.Owner_id
        WHERE (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) AND f.Status = 'accepted'
        GROUP BY f.Friend_RequesterId, u.Name
    ");
    $stmt->execute([':userId' => $userId]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching friends: " . $e->getMessage();
}

// Fetch friend requests
try {
    $stmt = $conn->prepare("
        SELECT f.Friend_RequesterId AS RequesterId, u.Name AS RequesterName
        FROM cst8257project.friendship f
        LEFT JOIN cst8257project.user u ON f.Friend_RequesterId = u.UserId
        WHERE f.Friend_RequesteeId = :userId AND f.Status = 'request'
    ");
    $stmt->execute([':userId' => $userId]);
    $friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $errorMessage = "Error fetching friend requests: " . $e->getMessage();
}

// Handle friend request actions (Accept/Deny)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $acceptedIds = $_POST['friend_request_ids'] ?? [];
    $deniedIds = $_POST['deny_request_ids'] ?? [];

    if (!empty($acceptedIds) || !empty($deniedIds)) {
        try {
            if (!empty($acceptedIds)) {
                $placeholders = implode(',', array_fill(0, count($acceptedIds), '?'));
                $stmt = $conn->prepare("
                    UPDATE cst8257project.friendship 
                    SET Status = 'accepted' 
                    WHERE Friend_RequesteeId = ? AND Friend_RequesterId IN ($placeholders)
                ");
                $stmt->execute(array_merge([$userId], $acceptedIds));
            }

            if (!empty($deniedIds)) {
                $placeholders = implode(',', array_fill(0, count($deniedIds), '?'));
                $stmt = $conn->prepare("
                    UPDATE cst8257project.friendship 
                    SET Status = 'denied' 
                    WHERE Friend_RequesteeId = ? AND Friend_RequesterId IN ($placeholders)
                ");
                $stmt->execute(array_merge([$userId], $deniedIds));
            }

            header("Location: myFriends.php");
            exit();
        } catch (Exception $e) {
            $errorMessage = "Error processing friend requests: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please select friend requests to process.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Friends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/myFriends.css' ?>">
</head>
<body>

<?php include("../common/header.php"); ?>
<div class="container mt-4">
    <h1 class="text-center mb-4">My Friends</h1>
    <p>Welcome <b><?= htmlspecialchars($user) ?></b>! (not you? change user <a href="/pages/logout.php">here</a>)</p>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <!-- Friends List -->
    <h2>Friends:</h2>
    <p><a href="addFriend.php">Add Friends</a></p>

    <form action="" method="POST">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Shared Albums</th>
                    <th>Defriend</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($friends as $friend): ?>
                    <tr>
                        <td><a href="myPictures.php?friendId=<?= htmlspecialchars($friend['FriendId']) ?>"><?= htmlspecialchars($friend['FriendName']) ?></a></td>
                        <td><?= htmlspecialchars($friend['SharedAlbums']) ?></td>
                        <td><input type="checkbox" name="friend_ids[]" value="<?= htmlspecialchars($friend['FriendId']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="defriend" class="btn btn-danger">Defriend Selected</button>
    </form>

    <!-- Friend Requests -->
    <h2>Friend Requests:</h2>
    <?php if (empty($friendRequests)): ?>
        <p>No friend requests found.</p>
    <?php else: ?>
        <form action="" method="POST">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Accept or Deny</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($friendRequests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['RequesterName']) ?></td>
                            <td>
                                <input type="checkbox" name="friend_request_ids[]" value="<?= htmlspecialchars($request['RequesterId']) ?>"> Accept
                                <input type="checkbox" name="deny_request_ids[]" value="<?= htmlspecialchars($request['RequesterId']) ?>"> Deny
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="action" value="process" class="btn btn-primary">Submit</button>
        </form>
    <?php endif; ?>

</div>
<?php include("../common/footer.php"); ?>
</body>
</html>
