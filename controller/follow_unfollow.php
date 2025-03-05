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
    header("Location: ../home/profilePage.php?profileCheck=$followingId");
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
    header("Location: ../home/profilePage.php?profileCheck=$followingId");
    exit();
}

?>