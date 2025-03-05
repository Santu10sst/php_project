<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!$_SESSION) {
    header('Location: ../index.php');
    exit();
}
require('../model/congif.model.php');
$conn->select_db('timespand');

if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])){
    $message = $_POST['message'];
    $userId = $_POST['id'];
    $myId = $_SESSION['user_id'];
    $status ='unseen';
    try {
        $query = 'INSERT INTO messages(sender_id,receiver_id,message,seen_status) VALUE(?,?,?,?)';
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiss',$myId,$userId,$message,$status);
        $stmt->execute();
        header("location:../home/messagePage.php?user_id=$userId");
        exit();
    } catch (\Throwable $th) {
        header("location:../home/messagePage.php?user_id=$userId");
        exit();
        echo "error";
    }
   

}else{
    header("location:../home/messagePage.php?user_id=$userId");
    exit();
    echo "error";
}


?>