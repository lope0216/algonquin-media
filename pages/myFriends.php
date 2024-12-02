<?php
require_once '../db/DBconnection.php';
include_once __DIR__ . '/../common/utils.php';

startSession();

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    unauthorizedAccess();
}

$conn = getPDOConnection();
$user = $_SESSION['UserName'];
$userId = $_SESSION['UserId'];
$friends = [];
$friendRequests = [];
$errorMessage = '';

// Fetch Friends
try {
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN f.Friend_RequesterId = :userId THEN f.Friend_RequesteeId
                ELSE f.Friend_RequesterId
            END AS FriendId,
            u.Name AS FriendName,
            COUNT(a.Album_Id) AS SharedAlbums
        FROM cst8257project.friendship f
        JOIN cst8257project.user u ON 
            (u.UserId = f.Friend_RequesterId AND f.Friend_RequesterId != :userId) OR 
            (u.UserId = f.Friend_RequesteeId AND f.Friend_RequesteeId != :userId)
        LEFT JOIN cst8257project.album a ON 
            (a.Owner_Id = f.Friend_RequesterId OR a.Owner_Id = f.Friend_RequesteeId)
        WHERE 
            (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) AND 
            f.Status = 'accepted'
        GROUP BY FriendId, u.Name
    ");
    $stmt->execute([':userId' => $userId]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching friends: " . $e->getMessage();
}

// Fetch Friend Requests
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

// Handle Actions (Accept/Deny/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['accept_requests'])) {
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
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/global.css' ?>">
    <script>
        function confirmDefriend() {
            const checkboxes = document.querySelectorAll('input[name="friend_ids[]"]:checked');
            if (checkboxes.length === 0) {
                alert("Please select at least one friend to defriend.");
                return false;
            }
            return confirm("Are you sure you want to defriend the selected friends?");
        }

    </script>
</head>
<body class="body-layout">

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

    <form method="POST" onsubmit="return confirmDefriend();">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Shared Albums</th>
                    <th>Defriend</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($friendRequests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['RequesterName']) ?></td>
                            <td>
                                <input type="checkbox" name="friend_request_ids[]" value="<?= htmlspecialchars($request['RequesterId']) ?>">
                            </td>
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
        <form method="POST" onsubmit="return confirmDenyRequests();">
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
        <td>
            <a href="friendPictures.php?userId=<?= htmlspecialchars($request['RequesterId']) ?>">
                <?= htmlspecialchars($request['RequesterName']) ?>
            </a>
        </td>
        <td>
            <input type="checkbox" name="friend_request_ids[]" value="<?= htmlspecialchars($request['RequesterId']) ?>">
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


