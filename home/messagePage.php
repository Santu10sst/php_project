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

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    die("Error: User ID is missing.");
}
$myId = $_SESSION['user_id'];
//get user details
$query = 'SELECT fName,lName, avatar FROM users WHERE id = ?';
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
//update unseen to seen message
$query = 'UPDATE messages SET seen_status = ? WHERE receiver_id = ? AND sender_id = ?';
$stmt = $conn->prepare($query);
$status = 'seen';
$stmt->bind_param('sii', $status, $myId, $userId);
$stmt->execute();

//get all data 
$query = 'SELECT m.message, m.sender_id, m.receiver_id, m.seen_status, u.avatar 
          FROM messages m 
          INNER JOIN users u ON m.sender_id = u.id 
          WHERE (m.sender_id = ? AND m.receiver_id = ?) 
             OR (m.sender_id = ? AND m.receiver_id = ?)
          ORDER BY m.sent_at DESC'; 
$stmt = $conn->prepare($query);
$stmt->bind_param('iiii', $myId, $userId, $userId, $myId);

$stmt->execute();
$result = $stmt->get_result();
$messageArr = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messageArr[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="messagePage.css?v=<?php echo time(); ?>">
    <title>Time Spend</title>
</head>

<body>
    <?php require('./navbar.php'); ?>

    <div class="chat-container">

        <!-- Chat window -->
        <div class="chat-window">
            <div class="chat-header">
                <img src="../controller/avatar/<?= $userData['avatar']?>" alt="">
                <h3><?php echo ucfirst($userData['fName'])." ".ucfirst($userData['lName'])?></h3>
            </div>

            <div class="chat-messages">
            <?php foreach ($messageArr as $message): ?>
                    <?php if ($message['sender_id'] != $myId): ?>
                        <div class="message received">
                        <img src="../controller/avatar/<?= htmlspecialchars($message['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="avatar">
                            <p><?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <?php else: ?>
                            <div class="message sent">
                               
                                <p><?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($message['seen_status'] == 'seen'): ?>
                                <img src="./assests/green_tick.png" alt="Seen" class="green_tick">
                                <?php endif; ?>
                            </div>
                            <?php endif ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Message Input -->
            <div class="chat-input">
                <form action="../controller/message.controller.php" method="POST">
                    <input type="text" name="message" placeholder="Type a message..." required>
                    <input type="hidden" name="id" value="<?= $userId ?>">
                    <button type="submit">Send</button>
                </form>

            </div>
        </div>
    </div>


    <?php require('./footer.php'); ?>
</body>

</html>