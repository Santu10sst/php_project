<?php
session_start();
require('../model/congif.model.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$errors = [];
$dbname = 'timespand';
$conn->select_db($dbname);
foreach ($_SESSION as $key => $value) {
    if ($key !== 'user_id') {
        unset($_SESSION[$key]);
    }
}
$currentPassword = $hashedPassword = $newPassword = $confirmPassword = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate Current Password
    if (empty($_POST['currentPassword'])) {
        $_SESSION['currentPasswordErr'] = "Enter your current password";
        $errors[] = 'currentPasswordErr';
    } else {
        $currentPassword = $_POST['currentPassword'];
    }

    // Validate New Password
    if (empty($_POST['newPassword'])) {
        $_SESSION['newPasswordErr'] = "Enter your new password";
        $errors[] = 'newPasswordErr';
    } else {
        $newPassword = $_POST['newPassword'];
        if ($currentPassword == $newPassword) {
            $_SESSION['passwordErr'] = "Current password and new password can't be the same.";
            $errors[] = 'newPasswordErr';
        } else if (strlen($newPassword) < 8 || !preg_match("/[A-Z]/", $newPassword) || !preg_match("/[a-z]/", $newPassword) || !preg_match("/[0-9]/", $newPassword) || !preg_match("/[\W]/", $newPassword)) {
            $_SESSION['passwordErr'] = 'Password must be at least 8 characters, contain upper/lowercase letters, a number, and a special character';
            $errors[] = 'passwordErr';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $_SESSION['password'] = $newPassword;
        }
    }

    // Validate Confirm Password
    if (empty($_POST['confirmPassword'])) {
        $_SESSION['confirmPasswordErr'] = "Confirm your new password";
        $errors[] = 'confirmPasswordErr';
    } elseif ($_POST['newPassword'] !== $_POST['confirmPassword']) {
        $_SESSION['confirmPasswordErr'] = "Passwords do not match";
        $errors[] = 'confirmPasswordMismatch';
    } else {
        $confirmPassword = $_POST['confirmPassword'];
    }

    if (empty($errors)) {
        try {
            // Fetch stored hashed password
            $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashedPassword = $row['password'];

                // Verify current password
                if (password_verify($currentPassword, $hashedPassword)) {
                    // Hash new password
                    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Update password in database
                    $updateStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $updateStmt->bind_param('si', $newHashedPassword, $_SESSION['user_id']);
                    if ($updateStmt->execute()) {
                        $_SESSION['successMsg'] = "Password updated successfully";
                        header('Location: ../home/home.php');
                        exit();
                    } else {
                        $_SESSION['dbErr'] = "Something went wrong. Please try again.";
                    }
                } else {
                    $_SESSION['currentPasswordErr'] = "Incorrect current password";
                }
            } else {
                $_SESSION['dbErr'] = "User not found.";
            }
        } catch (\Throwable $th) {
            $_SESSION['dbErr'] = "Database error occurred";
        }
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Change Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #5A189A, #000000);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        .box {
            position: relative;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            padding-right: 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        .error {
            color: red;
            font-size: 0.8rem;
            display: block;
            margin-top: 5px;
        }

        .toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .submit-btn {
            display: block;
            width: 95%;
            margin: 0 auto;
            padding: .5rem 0rem;
            background-color: rgb(35, 21, 191);
            outline: none;
            border: none;
            color: #ddd;
            border-radius: .5rem;
        }

        .back-btn {
            position: absolute;
            left: 1rem;
            top: 1rem;
            cursor: pointer;
        }

        .back-btn form {
            background-color: transparent;
        }

        .back-btn button {
            border-radius: 50%;
            background-color: transparent;
            outline: none;
            border: none;
            cursor: pointer;
        }

        .back-btn img {
            width: 3rem;
        }
    </style>
</head>

<body>
    <div class="back-btn">

        <a href="../controller/profile_actions.php?action=back_to_home_page">
            <button type="submit"><img src="../home/assests/93634.png" alt=""></button>
        </a>


    </div>
    <div class="container">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <!-- Current Password -->
            <div class="box">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" placeholder="********">
                <span class="error"><?= $_SESSION['currentPasswordErr'] ?? ''; ?></span>
                <button type="button" class="toggle-btn" onclick="togglePassword('currentPassword', this)">👁️</button>
            </div>

            <!-- New Password -->
            <div class="box">
                <label for="newPassword">New Password</label>
                <input type="password" id="newPassword" name="newPassword" placeholder="********">
                <span class="error"><?= $_SESSION['passwordErr'] ?? ''; ?></span>
                <button type="button" class="toggle-btn" onclick="togglePassword('newPassword', this)">👁️</button>
            </div>

            <!-- Confirm Password -->
            <div class="box">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="********">
                <span class="error"><?= $_SESSION['confirmPasswordErr'] ?? ''; ?></span>
                <button type="button" class="toggle-btn" onclick="togglePassword('confirmPassword', this)">👁️</button>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Update Password</button>
        </form>
    </div>

    <script>
        function togglePassword(fieldId, btn) {
            var field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
                btn.textContent = "🙈";
            } else {
                field.type = "password";
                btn.textContent = "👁️";
            }
        }
    </script>
</body>