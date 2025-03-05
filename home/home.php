<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!$_SESSION) {
    header('Location: ../index.php');
    exit();
}

require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);


$results = [];
try {
    $query = 'SELECT u.userName, u.avatar, m.id, m.title, m.description, m.mediaUrl, 
    u.id, m.fileType,u.id, u.accountStatus
    FROM users u 
    INNER JOIN posts m ON u.id = m.user_id 
    LEFT JOIN follows f ON u.id = f.following_id AND f.followers_id = ? 
    WHERE u.accountStatus = ? 
    OR u.id = ? 
    OR (u.accountStatus = ? AND f.status = ?) 
    GROUP BY m.mediaUrl';

    $stmt = $conn->prepare($query);
    $accStatusPublic = 'public';
    $accStatusPrivate = 'private';
    $status = 'accepted';

    $stmt->bind_param('issss', $_SESSION['user_id'], $accStatusPublic, $_SESSION['user_id'], $accStatusPrivate, $status);
    $stmt->execute();

    $results = $stmt->get_result();
    if ($results->num_rows > 0) {
        $results = $results->fetch_all();
        $results = array_reverse($results);
    }
} catch (\Throwable $th) {
    echo $th->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">

    <title>Time Spend</title>
</head>

<body>
    <?php require('./navbar.php'); ?>
    <div class="main-container">
        <?php foreach ($results as $result) : ?>
            <div class="home-container">
                <div class="user-media">
                    <div class="profile">
                        <a href="./profilePage.php?profileCheck=<?= $result[8] ?>" style="text-decoration: none;">
                            <button style="border:none; outline:none;background:transparent;cursor:pointer; display:flex;align-items:center;gap:1rem; font-size:1.1rem ">
                                <img src="../controller/avatar/<?php echo $result[1] ?>" alt="User Avatar" class="avatar">
                                <span class="username"><?php echo $result[0] ?></span>
                            </button>
                        </a>

                    </div>
                    <div class="description">
                        <h2 class="title">
                            <?php
                            $title = htmlspecialchars($result[3]); // Prevent XSS
                            echo (strlen($title) > 50) ? substr($title, 0, 50) . "..." : $title;
                            ?></h2>
                        <p class="desc">
                            <?php
                            $desc = htmlspecialchars($result[4]); // Prevent XSS
                            echo (strlen($desc) > 50) ? substr($desc, 0, 50) . "..." : $desc;
                            ?>
                        </p>
                    </div>
                    <div class="media">
                        <?php
                        $mediaUrl = htmlspecialchars($result[5]);
                        $fileExtension = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));

                        if ($result[7] == 'image') {
                            // Show Image
                            echo '<img src="./uploads/' . $mediaUrl . '" alt="Media Image" class="media-content" >';
                        } elseif ($result[7] == 'video') {
                            // Show and Auto-Play Video
                            echo '<video controls  loop class="media-content"  >
                                    <source src="./uploads/' . $mediaUrl . '" type="video/' . $fileExtension . '" >
                                    Your browser does not support the video tag.
                                  </video>';
                        }
                         
                        else {
                            // Unsupported File Type
                            echo "<p>Unsupported media type</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require('./footer.php'); ?>

</body>

</html>