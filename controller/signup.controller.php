<?php
session_start();
require('../model/congif.model.php');
session_unset();
$errors = [];
$dbname = 'timespand';
$conn->select_db($dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION = [];

    // First Name Validation
    if (empty($_POST['firstname'])) {
        $_SESSION['fnameErr'] = 'First name is required';
        $errors[] = 'fnameErr';
    } else {
        $fname = htmlspecialchars($_POST['firstname']);
        $_SESSION['fname'] = $fname;
    }

    // Last Name Validation
    if (empty($_POST['lastname'])) {
        $_SESSION['lnameErr'] = 'Last name is required';
        $errors[] = 'lnameErr';
    } else {
        $lname = htmlspecialchars($_POST['lastname']);
        $_SESSION['lname'] = $lname;
    }

    // Username Validation
    if (empty($_POST['username'])) {
        $_SESSION['usernameErr'] = 'Username is required';
        $errors[] = 'usernameErr';
    } else {

        $username = htmlspecialchars($_POST['username']);
        $stmt = $conn->prepare("SELECT username from users where username = ?");

        // Bind Parameters
        $stmt->bind_param("s", $username);

        // Execute
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['usernameErr'] = 'Username Already Exists';
            $errors[] = 'usernameErr';
        } else {
            $_SESSION['username'] = $username;
        }
        $stmt->close();
    }

    // Email Validation
    if (empty($_POST['email'])) {
        $_SESSION['signUpemailErr'] = 'Email is required';
        $errors[] = 'emailErr';
    } else {
        $email = htmlspecialchars($_POST['email']);
        $stmt = $conn->prepare("SELECT * from users where email = ?");

        // Bind Parameters
        $stmt->bind_param("s", $email);

        // Execute
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['signUpemailErr'] = 'email Already Exists';
            $errors[] = 'emailErr';
        } else {
            $_SESSION['email'] = $email;
        }
        $stmt->close();
    }

    // Profile Picture Validation


    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != 0) {
        $_SESSION['profileErr'] = 'Please upload a profile picture';
        $errors[] = 'profileErr';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['avatar']['type'];
        $fileSize = $_FILES['avatar']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['profileErr'] = 'Only JPG, PNG, and GIF files are allowed';
            $errors[] = 'profileErr';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $_SESSION['profileErr'] = 'File size must be less than 2MB';
            $errors[] = 'profileErr';
        } else {
            $uploadDir = 'avatar/'; // Directory where images will be stored
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if not exists
            }

            // Generate a unique filename
            $fileExt = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $uniqueFilename = uniqid('avatar_', true) . '.' . $fileExt;

            // Move the uploaded file to the avatar directory
            $destination = $uploadDir . $uniqueFilename;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $_SESSION['profileSuccess'] = 'Profile picture uploaded successfully';
                // You can store `$uniqueFilename` in your database for reference
            } else {
                $_SESSION['profileErr'] = 'Failed to save uploaded file';
                $errors[] = 'profileErr';
            }
        }
    }



    // Password Validation
    if (empty($_POST['password'])) {
        $_SESSION['signUppasswordErr'] = 'Password is required';
        $errors[] = 'passwordErr';
    } else {
        $password = $_POST['password'];
        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W]/", $password)) {
            $_SESSION['passwordErr'] = 'Password must be at least 8 characters, contain upper/lowercase letters, a number, and a special character';
            $errors[] = 'passwordErr';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $_SESSION['password'] = $password;
        }
    }
    //confirm password
    if (empty($_POST['confirmpassword'])) {
        $_SESSION['signUpconfirmpasswordErr'] = 'Confirm Password is required';
        $errors[] = 'passwordErr';
    } elseif ($_POST['confirmpassword'] !== $_POST['password']) {
        $_SESSION['signUpconfirmpasswordErr'] = 'Passwords do not match';
        $errors[] = 'passwordErr';
    }

    // Gender Validation
    if (empty($_POST['gender'])) {
        $_SESSION['genderErr'] = 'Gender is required';
        $errors[] = 'genderErr';
    } else {
        $gender = $_POST['gender'];
        $_SESSION['gender'] = $gender;
    }

    // DOB Validation
    if (empty($_POST['dob'])) {
        $_SESSION['dobErr'] = 'Date of birth is required';
        $errors[] = 'dobErr';
    } else {
        $dob = $_POST['dob'];
        $_SESSION['dob'] = $dob;
    }

    // Qualification Validation
    if (empty($_POST['qualification'])) {
        $_SESSION['qualificationErr'] = 'Qualification is required';
        $errors[] = 'qualificationErr';
    } else {
        $qualification = $_POST['qualification'];
        $_SESSION['qualification'] = $qualification;
    }

    // Address Validation
    if (empty($_POST['address'])) {
        $_SESSION['addressErr'] = 'Address is required';
        $errors[] = 'addressErr';
    } else {
        $address = $_POST['address'];
        $_SESSION['address'] = $address;
    }

    // Hobbies Validation
    if (empty($_POST['hobbies'])) {
        $_SESSION['hobbiesErr'] = 'At least one hobby is required';
        $errors[] = 'hobbiesErr';
    } else {
        $hobbies = $_POST['hobbies'];
        $_SESSION['hobbies'] = $hobbies;
        $hobbies = implode(",", $hobbies);
    }

    // Redirect back if there are errors
    if (!empty($errors)) {
        // print_r($errors);
        header('Location: ../signup/signup.php');
        exit();
    } else {
        session_unset();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO users (fName, lName, userName, email, avatar, password, dob, gender, qualification, address, hobbies,accountStatus) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");

        // Bind Parameters
        $stmt->bind_param("ssssssssssss", $fname, $lname, $username, $email, $uniqueFilename, $hashedPassword, $dob, $gender, $qualification, $address, $hobbies, "public");

        // Execute
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
        }
        $stmt->close();
        //store id in session
        $_SESSION['user_id'] = $user_id;
        

        // // Redirect to home
        header('Location: ../home/home.php');
        exit();
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
} else {
    header('Location: ../signup/signup.php');
    exit();
}
