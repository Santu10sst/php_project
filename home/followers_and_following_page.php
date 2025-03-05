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
$status = 'accepted';
$followDatas = [];
if (isset($_GET['followers_id'])) {
    $query = 'SELECT  u.id, u.fName, u.lName, u.userName, u.avatar, f.status FROM users u INNER JOIN follows f ON u.id = f.followers_id WHERE f.following_id = ? AND f.status = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $_GET['followers_id'],$status);
    $stmt->execute();
    $result = $stmt->get_result();
    $followDatas = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $followDatas[] = $row;
        }
    }
    // header("Location: " . $_SERVER['PHP_SELF']);
    // exit();
}

if (isset($_GET['following_id'])) {
    $query = 'SELECT u.id, u.fName, u.lName, u.userName, u.avatar, m.status FROM users u INNER JOIN follows f ON u.id = f.following_id WHERE f.followers_id = ? AND f.status = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $_GET['following_id'],$status);
    $stmt->execute();
    $result = $stmt->get_result();
    $followDatas = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $followDatas[] = $row;
        }
    }
    // header("Location: " . $_SERVER['PHP_SELF']);
    // exit();
    
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
    <title>Time Spend</title>
    <link rel="stylesheet" href="followers_and_following_page.css?v=<?php echo time(); ?>">
</head>

<body>
<?php require('./navbar.php'); ?>
    <div class="back-btn">
        <?php if (isset($_GET['following_id'])) :?>
        <a href="./profilePage.php?profileCheck=<?=$_GET['following_id']?>">
            <button type="submit"><img src="./assests/93634.png" alt=""></button>
        </a>
        <?php elseif (isset($_GET['followers_id'])):?>
            <a href="./profilePage.php?profileCheck=<?=$_GET['followers_id']?>">
            <button type="submit"><img src="./assests/93634.png" alt=""></button>
        </a>
        <?php endif;?>
    </div>
    <div class="container">
        <?php
        if (isset($_GET['followers_id'])) {
            echo "<h1>followers</h1>";
        }
        if (isset($_GET['following_id'])) {
            echo "<h1>following</h1>";
        }
        ?>


        <!-- followers List -->
        <div class="followers-list">
            <?php foreach ($followDatas as $followData): ?>
                
                <div class="follower-card">
                    <img src="../controller/avatar/<?= $followData['avatar'] ?>" alt="User Avatar">
                    <a href="./profilePage.php?profileCheck=<?= $followData['id']?>" style="text-decoration: none; color:black">
                    <div class="follower-info">
                        <h2><?php echo ucfirst($followData['fName'] . " " . $followData['lName']) ?></h2>
                        <p><?= $followData['userName'] ?></p>
                    </a>
                    </div>
                    <?php
                    // print_r($followData);
                    if(empty($_GET['followers_id']) ){
                        if($followData['status'] === 'pending' && $_GET['following_id'] == $_SESSION['user_id'] ){
                            echo "<button class='unfollow-btn'>Request</button>";
                            
                        }
                        else if($_GET['following_id'] == $_SESSION['user_id']){
                            echo '<button class="unfollow-btn">Unfollow</button>';
                        }
                    }
                    else{
                        if($followData['status'] === 'pending' && $_GET['followers_id'] == $_SESSION['user_id'] ){
                            echo "<button class='unfollow-btn'>Request</button>";
                            
                        }
                        else if($_GET['followers_id'] == $_SESSION['user_id']){
                            echo '<button class="unfollow-btn">Unfollow</button>';
                        }
                    }
                    ?>
                    
                </div>
            <?php endforeach; ?>

        </div>
    </div>
    <?php require('./footer.php'); ?>
</body>

</html>