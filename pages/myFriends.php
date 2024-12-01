<?php
session_start();
require_once '../db/DBconnection.php'; // Include database connection

// Redirect to login if user is not logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: /pages/login.php");
    exit();
}

$conn = getPDOConnection(); // Assuming this function returns a PDO instance

// Initialize variables
$user = $_SESSION['UserName'] ?? 'Guest';
$userId = $_SESSION['UserId'] ?? null;
$friends = [];
$friendRequests = [];
$errorMessage = '';

// Fetch friends
try {
    // Fetch all accepted friendships where the current user is involved
    $stmt1 = $conn->prepare("
        SELECT Friend_RequesteeId AS FriendId 
        FROM cst8257project.friendship 
        WHERE Friend_RequesterId = :userId AND Status = 'accepted'
    ");
    $stmt1->execute([':userId' => $userId]);
    $friends1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->prepare("
        SELECT Friend_RequesterId AS FriendId 
        FROM cst8257project.friendship 
        WHERE Friend_RequesteeId = :userId AND Status = 'accepted'
    ");
    $stmt2->execute([':userId' => $userId]);
    $friends2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $friends = array_merge($friends1, $friends2);

    // Fetch friend names and shared albums
    $friendIds = array_column($friends, 'FriendId');
    if (!empty($friendIds)) {
        $placeholders = implode(',', array_fill(0, count($friendIds), '?'));
        $stmt = $conn->prepare("
            SELECT u.UserId AS FriendId, u.Name AS FriendName, COUNT(a.Album_Id) AS SharedAlbums
            FROM cst8257project.user u
            LEFT JOIN cst8257project.album a ON a.Owner_Id = u.UserId
            WHERE u.UserId IN ($placeholders)
            GROUP BY u.UserId
        ");
        $stmt->execute($friendIds);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $friends = [];
    }
} catch (Exception $e) {
    $errorMessage = "Error fetching friends: " . $e->getMessage();
}

// Fetch friend requests
try {
    $stmt = $conn->prepare("
        SELECT Friend_RequesterId AS RequesterId, u.Name AS RequesterName
        FROM cst8257project.friendship f
        LEFT JOIN cst8257project.user u ON f.Friend_RequesterId = u.UserId
        WHERE Friend_RequesteeId = :userId AND Status = 'request'
    ");
    $stmt->execute([':userId' => $userId]);
    $friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching friend requests: " . $e->getMessage();
}

// Handle friendship actions (Accept/Deny/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['accept_requests'])) {
            // Accept selected friend requests
            $requesterIds = $_POST['friend_request_ids'] ?? [];
            if (!empty($requesterIds)) {
                $placeholders = implode(',', array_fill(0, count($requesterIds), '?'));
                $stmt = $conn->prepare("
                    UPDATE cst8257project.friendship 
                    SET Status = 'accepted' 
                    WHERE Friend_RequesteeId = ? AND Friend_RequesterId IN ($placeholders)
                ");
                $stmt->execute(array_merge([$userId], $requesterIds));
            }
        } elseif (isset($_POST['deny_requests'])) {
            // Deny selected friend requests
            $requesterIds = $_POST['friend_request_ids'] ?? [];
            if (!empty($requesterIds)) {
                $placeholders = implode(',', array_fill(0, count($requesterIds), '?'));
                $stmt = $conn->prepare("
                    DELETE FROM cst8257project.friendship 
                    WHERE Friend_RequesteeId = ? AND Friend_RequesterId IN ($placeholders) AND Status = 'request'
                ");
                $stmt->execute(array_merge([$userId], $requesterIds));
            }
        } elseif (isset($_POST['defriend'])) {
            // Delete selected friends
            $friendIds = $_POST['friend_ids'] ?? [];
            if (!empty($friendIds)) {
                $placeholders = implode(',', array_fill(0, count($friendIds), '?'));
                $stmt = $conn->prepare("
                    DELETE FROM cst8257project.friendship 
                    WHERE ((Friend_RequesterId = ? AND Friend_RequesteeId IN ($placeholders)) OR 
                          (Friend_RequesteeId = ? AND Friend_RequesterId IN ($placeholders))) 
                          AND Status = 'accepted'
                ");
                $stmt->execute(array_merge([$userId], $friendIds, [$userId], $friendIds));
            }
        }
        header("Location: myFriends.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = "Error processing requests: " . $e->getMessage();
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

    <form method="POST">
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
        <form method="POST">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($friendRequests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request['RequesterName']) ?></td>
                    <td>
                        <input type="checkbox" name="friend_request_ids[]" value="<?= htmlspecialchars($request['RequesterId']) ?>"> Select
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="submit" name="accept_requests" class="btn btn-primary">Accept</button>
    <button type="submit" name="deny_requests" class="btn btn-secondary">Deny</button>
    </form>

    <?php endif; ?>

</div>
<?php include("../common/footer.php"); ?>
</body>
</html>
