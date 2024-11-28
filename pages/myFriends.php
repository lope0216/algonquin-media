<?php
require_once '../db/DBconnection.php'; // Include database connection

// Initialize variables
$pdo = getPDOConnection();
$user = ['name' => 'Gio']; //$_SESSION['student'];
$userId = 'U0001'; // Replace with the logged-in user's ID
$friends = [];
$friendRequests = [];
$errorMessage = '';

// Fetch friends
try {
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN f.Friend_RequesterId = :userId THEN u2.Name
                ELSE u1.Name
            END AS FriendName,
            CASE 
                WHEN f.Friend_RequesterId = :userId THEN f.Friend_RequesteeId
                ELSE f.Friend_RequesterId
            END AS FriendId,
            (SELECT COUNT(*) FROM cst8257project.album WHERE Owner_Id = f.Friend_RequesterId OR Owner_Id = f.Friend_RequesteeId) AS SharedAlbums
        FROM cst8257project.friendship f
        LEFT JOIN cst8257project.user u1 ON f.Friend_RequesterId = u1.UserId
        LEFT JOIN cst8257project.user u2 ON f.Friend_RequesteeId = u2.UserId
        WHERE (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) AND f.Status = 'accepted'
    ");
    $stmt->execute([':userId' => $userId]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching friends: " . $e->getMessage();
}

// Fetch friend requests
try {
    $stmt = $pdo->prepare("
        SELECT f.Friend_RequesterId AS RequesterId, u.Name AS RequesterName
        FROM cst8257project.friendship f
        LEFT JOIN cst8257project.user u ON f.Friend_RequesterId = u.UserId
        WHERE f.Friend_RequesteeId = :userId AND f.Status = 'pending'
    ");
    $stmt->execute([':userId' => $userId]);
    $friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching friend requests: " . $e->getMessage();
}

// Handle friend request actions (Accept/Deny)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_requests'])) {
    $action = $_POST['action']; // 'accept' or 'deny'
    $requesterIds = $_POST['friend_request_ids'] ?? [];

    if (!empty($requesterIds)) {
        try {
            $status = ($action === 'accept') ? 'accepted' : 'denied';
            $stmt = $pdo->prepare("
                UPDATE cst8257project.friendship 
                SET Status = :status 
                WHERE Friend_RequesteeId = :userId AND Friend_RequesterId IN (" . implode(',', array_fill(0, count($requesterIds), '?')) . ")
            ");
            $stmt->execute(array_merge([$status, $userId], $requesterIds));
            header("Location: myFriends.php");
            exit();
        } catch (Exception $e) {
            $errorMessage = "Error processing friend requests: " . $e->getMessage();
        }
    }
}

// Handle defriend action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['defriend'])) {
    if (!empty($_POST['friend_ids'])) {
        $friendIds = $_POST['friend_ids'];
        try {
            $stmt = $pdo->prepare("
                UPDATE cst8257project.friendship 
                SET Status = 'removed' 
                WHERE (Friend_RequesterId = :userId AND Friend_RequesteeId IN (" . implode(',', array_fill(0, count($friendIds), '?')) . "))
                   OR (Friend_RequesteeId = :userId AND Friend_RequesterId IN (" . implode(',', array_fill(0, count($friendIds), '?')) . "))
            ");
            $stmt->execute(array_merge([$userId], $friendIds, [$userId], $friendIds));
            header("Location: myFriends.php");
            exit();
        } catch (Exception $e) {
            $errorMessage = "Error removing friends: " . $e->getMessage();
        }
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
        <p>Welcome <b><?= $user['name'] ?></b>! (not you? change user <a href="/pages/logout.php">here</a>)</p>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <!-- Friends List -->
         <tr>
            <th><h2> Friends: </h2></th>
            <th><p><a href="addFriend.php">Add Friends</a></p></th>
        </tr>
        
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
                            <td><a href="myPictures.php?friendId=<?= htmlspecialchars($friend['Friend_Id']) ?>"><?= htmlspecialchars($friend['Name']) ?></a></td>
                            <td><?= htmlspecialchars($friend['SharedAlbums']) ?></td>
                            <td><input type="checkbox" name="friend_ids[]" value="<?= htmlspecialchars($friend['Friend_Id']) ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="defriend" class="btn btn-danger">Defriend Selected</button>
        </form>

        <!-- Friend Requests -->
        <h2>Friend Requests:</h2>
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
                            <td><?= htmlspecialchars($request['Name']) ?></td>
                            <td><input type="checkbox" name="friend_request_ids[]" value="<?= htmlspecialchars($request['Requester_Id']) ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="action" value="accept" class="btn btn-primary">Accept Selected</button>
            <button type="submit" name="action" value="deny" class="btn btn-secondary">Deny Selected</button>
        </form>
        
    </div>
    <?php include("../common/footer.php"); ?>
</body>
</html>
