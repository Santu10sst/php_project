<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);
$stmt = $conn->prepare("select * from users where id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['userName'];
    $avatar = $user['avatar'];

    // Get the image MIME type

}
$stmt->close();


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

$myId = $_SESSION['user_id'];
$seenStatus = 'unseen';
// $query = 'SELECT sender_id, count(id) as total_message FROM messages WHERE receiver_id = ? AND seen_status = ? GROUP BY sender_id';
$query = 'SELECT COUNT(*) as total_message FROM messages WHERE receiver_id = ? AND seen_status = ?';

$stmt = $conn->prepare($query);
$stmt->bind_param('is',$myId,$seenStatus);

$stmt->execute();
$countMessage = $stmt->get_result()->fetch_assoc();
?>

<head>
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            /* Dark blue-gray */
            padding: 15px 30px;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            height: 70px;
            border-bottom: 2px solid black;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Left side (Profile Image & Username) */
        .left-side {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Profile Image */
        .logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            /* Makes it circular */
            object-fit: cover;
            /* Ensures proper scaling */
            border: 2px solid white;
        }

        /* Username Text */
        .left-side span {
            font-size: 18px;
            font-weight: bold;
        }

        .right-side {
            display: flex;
            justify-content: space-around;
            align-items: center;
            gap: 2rem;
        }

        .right-side .profile {
            position: relative;
        }

        .right-side .profile-details {
            position: absolute;
            color: #2c3e50;
            padding: 1rem 0rem;
            left: -100px;
            top: 2rem;
            border-radius: .5rem;
            background-color: rgb(239, 243, 252);
            display: none;
            min-width: 150px;
            z-index: 10;
            cursor: pointer;

        }

        .right-side .profile-details button {
            width: 100%;
            padding: .5rem 0rem;
            margin-bottom: .5rem;
            outline: none;
            border-left: none;
            border-right: none;
            cursor: pointer;
            font-weight: 500;
        }

        .right-side .profile-details button:hover {
            background-color: #4e5b67;
            color: white;
        }

        .right-side .profile:hover .profile-details {
            display: block;
        }

        .right-side .profile img {
            width: 3rem;
            border-radius: 50%;
            cursor: pointer;
        }

        /* Right side (Logout Button) */
        .right-side .logout-btn {
            background: #e74c3c;
            /* Red color */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s ease;
        }

        .right-side .logout-btn:hover {
            background: #c0392b;
            /* Darker red on hover */
        }

        .request img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
        }

        .request {
            position: relative;
        }
    </style>
</head>
<nav class="navbar">
    <div class="left-side">
        <img src="../controller/avatar/<?php echo $avatar; ?>" alt="profile-img" class="logo">
        <span><?php echo $username; ?></span>
    </div>

    <div class="right-side">
    <div class="request">
            <?php if (count($requests) >= 0): ?>
                <a href="./messageListPage.php" style="text-decoration: none;">
                    <button style="background-color: transparent; outline:none;border:none;">

                            <?php echo $countMessage['total_message'] != 0 ?  "<p style='position: absolute; right:0; color:white; background-color:red; border-radius:50%;padding:.3rem'>  $countMessage[total_message]</p> " : null ?>
                        
                        <img src="./assests/messagebox.png" alt="icon image" style="width:40px; border-radius:5%">
                    </button>
                </a>


            <?php endif; ?>
        </div>
        <div class="request">
            <?php if (count($requests) >= 0): ?>
                <a href="./friendRequestPage.php" style="text-decoration: none;">
                    <button style="background-color: transparent; outline:none;border:none;">
                        <p style="position: absolute; right:0; color:white; background-color:red; border-radius:50%;padding:.3rem"><?= count($requests) > 9 ? '9+' : count($requests); ?></p>
                        <img src="./assests/friend_request1.png" alt="icon image">
                    </button>
                </a>


            <?php endif; ?>
        </div>
        <div class="profile">

            <img src="./assests/download.png" alt="icon image">
            <div class="profile-details">
                <a href="../controller/profile_actions.php?action=update_profile">
                <button>Update Profile</button>
                </a>

                <a href="../controller/profile_actions.php?action=change_password">
                <button>Password Change</button>
                </a>
            </div>

        </div>

        <form action="<?php echo htmlspecialchars('../controller/logout.controller.php') ?>">
            <button class="logout-btn">Logout</button>
        </form>
    </div>
</nav>