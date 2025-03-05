<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);



if (!function_exists('callDatabase')) {
    function callDatabase($conn)
    {
        $status = 'pending';
        $following_id = $_SESSION['user_id'];
        $query = 'SELECT * FROM users WHERE id IN (SELECT followers_id FROM follows WHERE following_id = ? AND status = ?)';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $following_id, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        }
        return $requests;
    }
}

if (!function_exists('FriendShipStatus')) {
    function FriendShipStatus($followerId, $followingId, $conn)
    {
        $checkQuery = "SELECT status FROM follows WHERE followers_id = ? AND following_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc()['status'] : null;
    }
}

$requests = callDatabase($conn);

// print_r($requests);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_accept'])) {


    $friendId = $_POST['request_accept'];
    $myId = $_SESSION['user_id'];
    $status = 'accepted';
    $query = 'UPDATE follows SET status = ? WHERE followers_id = ? AND following_id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $friendId, $myId);
    $stmt->execute();
    $requests = callDatabase($conn);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_reject'])) {
    $friendId = $_POST['request_reject'];
    $myId = $_SESSION['user_id'];

    $query = 'DELETE FROM follows WHERE followers_id = ? AND following_id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $friendId, $myId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        // echo "Friend request rejected successfully.";
    } else {
        // echo "No matching record found or deletion failed.";
    }
    $requests = callDatabase($conn);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Spend</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .friendList-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: start;
            gap: 1rem;
            align-items: center;
            height: 89vh;
            overflow: scroll;
            background-color: #f4f4f4;
            padding-top: 1rem;
        }

        .request {
            width: 70%;
            /* background-color: red; */
            display: flex;
            flex-direction: column;
            /* justify-content: center; */
            align-items: center;

        }

        .request .search-listed {
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            padding: .1rem 1rem;
            background-color: rgb(254, 254, 250);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* .request:hover .search-listed {
            display: block;
        } */

        .request .user-data {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            background-color: #f4f4f4;
            border: 1px solid black;
            width: 100%;
            /* max-width: 20rem; */
        }

        .request .user-data img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid #007bff;
        }

        .user-data div {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-right: 2rem;
        }

        .request .user-data span {
            font-size: 16px;
            font-weight: bold;
        }

        .request .follow-btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            position: absolute;
            right: 1rem;
            top: 1rem;

        }

        .fire-action {
            width: 10px;

        }
    </style>
</head>

<body>
    <?php require('./navbar.php'); ?>
    <div class="friendList-container">
        <div class="request">

            <?php if (count($requests) > 0): ?>
                <h1 style="text-align: center;">Request list</h1>
                <div class="search-listed">
                    <?php foreach ($requests as $friend): ?>
                        <?php
                        if ($friend['id'] == $_SESSION['user_id']) continue;
                        // $friendStatus = FriendshipStatus($_SESSION['user_id'], $friend['id'], $conn);
                        ?>
                        <div class="user-data">
                            <a href="./profilePage.php?profileCheck=<?= $friend['id'] ?>" style="text-decoration: none;">

                                <button type="submit" style='display:flex; align-items:center; background:transparent; border:none; outline:none; cursor:pointer; margin-right:1rem;'>
                                    <img src="../controller/avatar/<?= $friend['avatar'] ?>" alt="avatar">
                                    <span style="color:black"><?= "$friend[fName] $friend[lName]" ?></span>
                                </button>
                            </a>
                            <div>
                                <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                                    <input type="hidden" name="request_accept" value="<?= "$friend[id]" ?>">
                                    <button type="submit" style='display:flex; align-items:center; background:transparent; border:none; outline:none; cursor:pointer;margin-right:1rem;'>
                                        <img src="./assests/istockphoto.jpg" alt="" class="fire-action" style='width:30px; height:30px; border:none;'>
                                    </button>
                                </form>
                                <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST" class="fire-action" style='border:none'>
                                    <input type="hidden" name="request_reject" value="<?= "$friend[id]" ?>">
                                    <button style='display:flex; align-items:center; background:transparent; border:none; outline:none; cursor:pointer' type="submit">
                                        <img src="./assests/cross2.png" alt="" style='width:30px; height:30px; border:none;'>
                                    </button>
                                </form>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <strong>no friend request exists</strong>
            <?php endif ?>
        </div>
    </div>
    <?php require('./footer.php'); ?>
</body>
<script>
    // if (window.history.replaceState) {
    //     window.history.replaceState(null, null, window.location.href);
    // }
</script>

</html>