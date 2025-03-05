<?php
session_start();
require('../model/congif.model.php');
// session_unset();
$errors = [];
$dbname = 'timespand';
$conn->select_db($dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //email validation
    if (empty($_POST['email'])) {
        $_SESSION['emailErr'] = 'Please provide Email';
        $errors[] = 'email';
    } else {
        $email = $_POST['email'];
    }

    //password validation
    if (empty($_POST['password'])) {
        $_session['passwordErr'] = "Enter Password";
        $errors[] = 'password';
    } else {
        $password = $_POST['password'];
    }



    if (!empty($errors)) {
        header('Location: ../index.php');
        exit();
    }

    //database validation
    try {
        $stmt = $conn->prepare("SELECT id,username, password, avatar FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashed_password = $user['password'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Store User Data in session
                $user_id = $user['id'];
                $_SESSION['user_id'] = $user_id;

                //delete all session expect user_id
                foreach ($_SESSION as $key => $value) {
                    if ($key !== 'user_id') {
                        unset($_SESSION[$key]);
                    }
                }

                // // Redirect to Home
                header('Location: ../home/home.php');
                exit();
            } else {
                echo "sdfosdifhdfui";
                $_SESSION['passwordErr'] = "Incorrect password";
                header('Location: ../index.php');
                exit();
            }
        } else {
            $_SESSION['emailErr'] = "User with this email does not exist";
            header('Location: ../index.php');
            exit();
        }
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
} else {

    header('Location: ../index.php');
    exit();
}
