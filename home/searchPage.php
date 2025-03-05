<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);

$users = [];
$searchErr = "";
$errors = [];
$searchValue = "";

// ✅ Function to check if follow request already exists
function getFriendshipStatus($followerId, $followingId, $conn)
{
    $checkQuery = "SELECT status FROM follows WHERE followers_id = ? AND following_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['status']; // Return latest status
    } else {
        return null; // No relationship exists
    }
}

// ✅ Handling search request
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['query'])) {
    if (empty($_GET['query'])) {
        $searchErr = "Enter user Name";
        $errors[] = 'searchErr';
    } else {
        try {
            $query = $_GET['query'];
            $searchValue = $query;
            $arr = explode(' ', $query, 2);
            $fName = $arr[0];
            $lName = isset($arr[1]) ? $arr[1] : '';

            $sql = "SELECT u.id, u.fName, u.lName, u.userName, u.accountStatus, u.avatar, 
                           f.followers_id, f.following_id, f.status
                    FROM users u 
                    LEFT JOIN follows f ON u.id = f.following_id 
                    WHERE (u.fName LIKE ? OR u.lName LIKE ? OR u.userName LIKE ?) GROUP BY u.id";

            $stmt = $conn->prepare($sql);
            $param = "%$fName%";
            $stmt->bind_param('sss', $param, $param, $param);
            $stmt->execute();
            $results = $stmt->get_result();

            while ($row = $results->fetch_assoc()) {
                $users[] = $row;
            }

            if (empty($users) || $users[0]['id'] == $_SESSION['user_id']) {

                $query = "SELECT u.id, u.fName, u.lName, u.userName, u.accountStatus, u.avatar, 
                f.followers_id, f.following_id, f.status
                FROM users u 
                LEFT JOIN follows f ON u.id = f.following_id GROUP BY u.id";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $results = $stmt->get_result();
                $users = [];
                while ($row = $results->fetch_assoc()) {
                    $users[] = $row;
                }
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
} else {
    // ✅ Fetch all users
    try {
        $query = "SELECT u.id, u.fName, u.lName, u.userName, u.accountStatus, u.avatar, 
                         f.followers_id, f.following_id, f.status
                  FROM users u 
                  LEFT JOIN follows f ON u.id = f.following_id GROUP BY u.id";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->get_result();
        $users = [];
        while ($row = $results->fetch_assoc()) {
            $users[] = $row;
        }
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
}

// ✅ Follow/Request logic (use POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['private']) || isset($_POST['public']))) {

    $followerId = $_SESSION['user_id'];
    $followingId =  $_POST['private'] ?? $_POST['public'];
    $status = isset($_POST['private']) ? 'pending' : 'accepted';
    if (!getFriendshipStatus($followerId, $followingId, $conn)) {
        $insertQuery = "INSERT INTO follows (followers_id, following_id, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iis", $followerId, $followingId, $status);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
// ✅ unfollow logic (use POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['unfollow']))) {
    $followerId = $_SESSION['user_id'];
    $followingId = $_POST['unfollow'];

    // echo getFriendshipStatus($followerId, $followingId, $conn);
    if (getFriendshipStatus($followerId, $followingId, $conn)) {
        $insertQuery = "DELETE FROM follows WHERE followers_id = ? AND following_id = ?";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--  -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="searchPage.css?v=<?php echo time(); ?>">
    <title>Time Spand</title>
</head>

<body>
    <?php require('./navbar.php'); ?>

    <div class="main-container">
        <div class="search-container">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="GET">
                <input type="text" name="query" placeholder="Search for users..." value=<?php echo $searchValue ?>>
                <button type="submit">🔍</button>
            </form>
            <span style="color:red; font-size:.8rem"><?php echo $searchErr; ?></span>
        </div>

        <div class="search-list">
            <?php foreach ($users as $friend): ?>
                <?php
                if ($friend['id'] == $_SESSION['user_id']) continue;
                $friendStatus = getFriendshipStatus($_SESSION['user_id'], $friend['id'], $conn);
                ?>
                <div class="user-data">
                    <a href="./profilePage.php?profileCheck=<?= $friend['id'] ?>" style="text-decoration: none;">
                        <button style='display:flex; align-items:center; background:transparent; border:none; outline:none; cursor:pointer'>
                            <img src="../controller/avatar/<?= $friend['avatar'] ?>" alt="avatar">
                            <span><?= "$friend[fName] $friend[lName]" ?></span>
                        </button>
                    </a>

                    <?php if ($friend['accountStatus'] == 'private' && $friendStatus !== 'pending' && $friendStatus !== 'accepted'): ?>
                        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                            <input type="hidden" name="private" value="<?= $friend['id'] ?>">
                            <button class="follow-btn">Follow Request</button>
                        </form>
                    <?php elseif ($friendStatus == 'pending'): ?>
                        <button class="follow-btn" style="background-color:orange;">Requested</button>
                    <?php elseif ($friendStatus == 'accepted'): ?>
                        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                            <input type="hidden" name="unfollow" value="<?= $friend['id'] ?>">
                            <button class="follow-btn" style="background-color:green;">Unfollow</button>
                        </form>
                    <?php elseif ($friend['accountStatus'] == 'public' && !$friendStatus): ?>
                        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                            <input type="hidden" name="public" value="<?= $friend['id'] ?>">
                            <button class="follow-btn">Follow</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require('./footer.php'); ?>
</body>

</html>