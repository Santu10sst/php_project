<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require('../model/congif.model.php');
$conn->select_db('timespand');

$myId = $_SESSION['user_id'];
$seenStatus = 'unseen';
$query = 'SELECT m.sender_id, count(m.id) as total_message,u.avatar,u.fName, u.lName FROM messages m INNER JOIN users u ON m.sender_id = u.id WHERE receiver_id = ? AND seen_status = ? GROUP BY sender_id';

$stmt = $conn->prepare($query);
$stmt->bind_param('is', $myId, $seenStatus);
$stmt->execute();
$result = $stmt->get_result();
$messagesList = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messagesList[] = $row;
    }
}

$myId = $_SESSION['user_id'];
$seenStatus = 'seen';
$query = 'SELECT m.sender_id, count(m.id) as total_message,u.avatar,u.fName, u.lName FROM messages m INNER JOIN users u ON m.sender_id = u.id WHERE receiver_id = ? AND seen_status = ? GROUP BY sender_id';

$stmt = $conn->prepare($query);
$stmt->bind_param('is', $myId, $seenStatus);
$stmt->execute();
$result = $stmt->get_result();
$messagesSeenList = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messagesSeenList[] = $row;
    }
}

$unreadUserIds = array_column($messagesList, 'sender_id');

$messagesSeenList2 = array_filter($messagesSeenList, function ($currlist) use ($unreadUserIds) {
    return !in_array($currlist['sender_id'], $unreadUserIds);
});

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="messageListPage.css?v=<?php echo time(); ?>">
    <title>Time Spend</title>

</head>

<body>
    <?php require('./navbar.php'); ?>
    <div class="message-container">
        <h1 style="text-align:center">Message List</h1>
        <?php if(empty($messagesList)):?>
            <!-- <p style="text-align:center; margin-top:2rem">no messages unread</p> -->
        <?php endif;?>
        <?php foreach ($messagesList as $message): ?>
            <a href="./messagePage.php?user_id=<?php echo $message['sender_id'] ?>" style="text-decoration:none; display:flex; gap:1rem ;width:100%">
            <div class="user-message">
                    <img src="../controller/avatar/<?= $message['avatar'] ?>" alt="avatar">
                    <div class="message-info">
                        <p class="user-name"><?php echo ucfirst($message['fName']) ." ".  ucfirst($message['lName']) ?></p>
                        <p class="total-message"><?= $message['total_message'] ?> unread messages</p>
                    </div>
                </a>
                </div>
        <?php endforeach; ?>

        <?php foreach ($messagesSeenList2 as $message): ?>
            <a href="./messagePage.php?user_id=<?php echo $message['sender_id'] ?>" style="text-decoration:none; display:flex; gap:1rem ;width:100%">
            <div class="user-message">
                    <img src="../controller/avatar/<?= $message['avatar'] ?>" alt="avatar">
                    <div class="message-info">
                        <p class="user-name"><?php echo ucfirst($message['fName']) ." ". ucfirst($message['lName']) ?></p>
                    </div>
                </a>
                </div>
        <?php endforeach; ?>

    </div>
    <?php require('./footer.php'); ?>
</body>

</html>