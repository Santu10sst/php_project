<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION)) {
    header('Location: ../index.php');
    exit();
}

require('../model/congif.model.php');
$conn->select_db('timespand');
$accStatus = "";
$userData = [];
$usersMediaData = [];
$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['mediaDelete'])) {
    $mediaID = $_POST['mediaDelete'];
    $query = 'DELETE FROM posts WHERE id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $mediaID);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
    // $uploadDir = "uploads/";
    // if (!empty($arr[1]) && file_exists($uploadDir . $arr[1]) && $arr[1] !== 'default-avatar.png') {
    //     unlink($uploadDir . $arr[1]);
    // }

}
function countFollowers($userId,  $conn)
{
    //count followers_id and following_id
    $status = 'accepted';
    try {
        //following_id
        $query = 'SELECT count(followers_id) as following_id
                  FROM follows WHERE followers_id = ? AND status = ? GROUP BY followers_id';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $userId, $status);
        $stmt->execute();
        $following = $stmt->get_result()->fetch_assoc() ?? [];
        //followers_id
        $query = 'SELECT count(following_id) as followers_id
                  FROM follows WHERE following_id = ? AND status = ? GROUP BY following_id';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $userId, $status);
        $stmt->execute();
        $followers = $stmt->get_result()->fetch_assoc() ?? [];

        return [$following, $followers];
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
};

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


if (!empty($_GET['profileCheck'])) {

    $userId = $_GET['profileCheck'];

    try {
        // Fetch user data
        $query = 'SELECT avatar, fName, lName, userName, hobbies, id,accountStatus FROM users WHERE id = ?';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $userData = $stmt->get_result()->fetch_assoc() ?? [];

        $accStatus = $userData['accountStatus'];
        if ($accStatus == 'public') {
            $status = 'accepted';
        } else {
            //fetch request status
            $query = 'SELECT status FROM follows WHERE following_id = ? AND followers_id = ?';
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $userId, $_SESSION['user_id']);
            $stmt->execute();
            $follows = $stmt->get_result()->fetch_assoc();
            $status = !empty($follows) ? $follows['status'] : "";
        }
        if (($accStatus === 'public' || ($accStatus === 'private' && $status === 'accepted')) || $userId == $_SESSION['user_id']) {

            // Fetch user media data
            $query = 'SELECT m.id, m.mediaUrl, m.title, m.fileType 
                        FROM users u 
                        INNER JOIN posts m ON u.id = m.User_id 
                        WHERE u.id = ? AND u.accountStatus = ?';
            $stmt = $conn->prepare($query);
            $stmt->bind_param('is', $userId, $accStatus);
            $stmt->execute();
            $results = $stmt->get_result();
            if ($results->num_rows > 0) {
                while ($row = $results->fetch_assoc()) {
                    $usersMediaData[] = $row;
                }
            }
        }
        list($following, $followers) = countFollowers($userId, $conn);

        //check friend are follow or not
        $query = "SELECT u.id, u.fName, u.lName, u.userName, u.accountStatus, u.avatar, 
                            f.followers_id, f.following_id, f.status
                            FROM users u 
                            LEFT JOIN follows f ON u.id = f.followers_id WHERE f.followers_id = ? AND f.following_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $_SESSION['user_id'], $userId);
        $stmt->execute();
        $friend = $stmt->get_result()->fetch_assoc() ?? [];

        $friendStatus = getFriendshipStatus($_SESSION['user_id'], $userId, $conn);
        if (empty($friend)) {
            $status = "";
        }
    } catch (Throwable $th) {
        echo $th->getMessage();
    }
} else {
    try {
        // Fetch logged-in user data
        $query = 'SELECT avatar, fName, lName, userName, hobbies, id,accountStatus FROM users WHERE id = ?';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $userData = $stmt->get_result()->fetch_assoc() ?? [];
        $accStatus = $userData['accountStatus'];
        $userId = $userData['id'];
        //follower and following_id count
        list($following, $followers) = countFollowers($_SESSION['user_id'],  $conn);

        // Fetch media data for logged-in user
        $query = 'SELECT id, mediaUrl, title, fileType FROM posts WHERE user_id = ?';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $results = $stmt->get_result();
        while ($row = $results->fetch_assoc()) {
            $usersMediaData[] = $row;
        }
    } catch (Throwable $th) {
        echo $th->getMessage();
    }
}
$usersMediaData = array_reverse($usersMediaData);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="profilePage.css?v=<?= time(); ?>">
    <title>Time Spend</title>
</head>

<body>
    <?php require('./navbar.php'); ?>
    <div class="main-container">
        <div class="profile_and_followers">
            <div class="profile-details">
                <?php if (!empty($userData)): ?>
                    <img src="../controller/avatar/<?php echo $userData['avatar'] ?>" alt="Profile Photo">
                    <p><?= ucfirst(htmlspecialchars($userData['fName'])) . ' ' . ucfirst(htmlspecialchars($userData['lName'])) ?></p>
                    <p><?= htmlspecialchars($userData['userName']) ?></p>
                    <p><?= htmlspecialchars($userData['hobbies']) ?></p>
                <?php endif; ?>
            </div>
            <div class="two-container">
                <div class="followers-container">
                    <div class="posts">
                        <p style="font-size: 1.5rem;"> <?= count($usersMediaData); ?> </p>
                        <p>posts</p>
                    </div>
                    <div class="followers">
                        <?php if (($accStatus === 'private' && $status === 'accepted') || $accStatus === 'public' || $userData['id'] === $_SESSION['user_id']): ?>
                            <a href="./followers_and_following_page.php?followers_id=<?php echo $userData['id'] ?>" style="text-decoration: none;">
                                <button type="submit">
                                    <p style="font-size: 1.5rem;"><?= empty($followers) ? 0 : $followers['followers_id'] ?></p>
                                    <p>followers</p>
                                </button>
                            </a>
                        <?php else : ?>
                            <p style="font-size: 1.5rem;"><?= empty($followers) ? 0 : $followers['followers_id'] ?></p>
                            <p>followers</p>
                        <?php endif; ?>
                    </div>
                    <div class="following_id">
                        <?php if (($accStatus === 'private' && $status === 'accepted') || $accStatus === 'public' || $userData['id'] === $_SESSION['user_id']): ?>
                            <a href="./followers_and_following_page.php?following_id=<?php echo $userData['id'] ?>" style="text-decoration: none;">
                                <button type="submit">
                                    <p style="font-size: 1.5rem;"><?= empty($following) ? 0 : $following['following_id'] ?></p>
                                    <p>following</p>
                                </button>
                        </a>
                        <?php else : ?>
                            <p style="font-size: 1.5rem;"><?= empty($following) ? 0 : $following['following_id'] ?></p>
                            <p>following</p>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="message-follow">
                    <div class="follow-btn">
                        <?php if ($accStatus == 'private' && $status !== 'pending' && $status !== 'accepted' && $userId != $_SESSION['user_id']): ?>
                            <form action="../controller/follow_unfollow.php" method="POST">
                                <input type="hidden" name="private" value="<?= $userData['id'] ?>">
                                <button class="follow-btn">Follow Request</button>
                            </form>
                        <?php elseif ($status == 'pending' && $userId != $_SESSION['user_id']): ?>
                            <button class="follow-btn" style="background-color:orange;">Requested</button>
                        <?php elseif ($status == 'accepted' && $userId != $_SESSION['user_id']): ?>
                            <form action="../controller/follow_unfollow.php" method="POST">
                                <input type="hidden" name="unfollow" value="<?= $userData['id'] ?>">
                                <button class="follow-btn" style="background-color:green;">Unfollow</button>
                            </form>
                        <?php elseif ($accStatus == 'public' && $status == "" && $userId != $_SESSION['user_id']): ?>
                            <form action="../controller/follow_unfollow.php" method="POST">
                                <input type="hidden" name="public" value="<?= $userData['id'] ?>">
                                <button class="follow-btn">Follow</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php if ($status === 'accepted'): ?>
                        <div class="message-btn">

                            <button class="follow-btn"> <a href="./messagePage.php?user_id=<?= $userData['id'] ?>" >message</a></button>

                        </div>
                    <?php endif; ?>


                </div>
            </div>
        </div>

        <div class="media-container">
            <?php foreach ($usersMediaData as $userMedia): ?>
                <div class="media">
                    <?php if ($userData['id'] == $_SESSION['user_id']): ?>
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                            <input type="hidden" name="mediaDelete" value="<?= htmlspecialchars($userMedia['id']) ?>">
                            <button class="delete-btn" type="submit"><img src="./assests/delete.png" alt="Delete"></button>
                        </form>
                    <?php endif; ?>
                    <?php
                    $mediaUrl = htmlspecialchars($userMedia['mediaUrl']);
                    if ($userMedia['fileType'] === 'image') {
                        echo '<img src="./uploads/' . $mediaUrl . '" alt="Media Image" class="media-content">';
                    } elseif ($userMedia['fileType'] === 'video') {
                        echo '<video controls loop class="media-content">
                                <source src="./uploads/' . $mediaUrl . '" type="video/' . pathinfo($mediaUrl, PATHINFO_EXTENSION) . '">
                                Your browser does not support the video tag.
                              </video>';
                    } else {
                        echo "<p>Unsupported media type</p>";
                    }
                    ?>
                </div>
            <?php endforeach; ?>
            <strong>
                <?php
                if ($accStatus === 'private' && $status == 'pending') {
                    echo "Private account";
                } elseif ($accStatus === 'private' && $status == 'accepted' && empty($usersMediaData)) {
                    echo "No content uploaded";
                } elseif ($accStatus === 'private' && $status == "" && $userId != $_SESSION['user_id']) {
                    echo "Private Account";
                } elseif (empty($usersMediaData)) {
                    echo "No content uploaded";
                } else {
                    echo "";
                }
                ?>


            </strong>
        </div>
    </div>
    <?php require('./footer.php'); ?>
</body>

</html>