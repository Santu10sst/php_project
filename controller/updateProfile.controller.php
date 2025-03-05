<?php
session_start();
require('../model/congif.model.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
$errors=[];
$dbname = 'timespand';
$conn->select_db($dbname);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // First Name Validation
    if (empty($_POST['fname'])) {
        $_SESSION['fnameErr'] = 'First name is required';
        $errors[] = 'fnameErr';
    } else {
        $fname = htmlspecialchars($_POST['fname']);
        $_SESSION['fname'] = $fname;
    }

    // Last Name Validation
    if (empty($_POST['lname'])) {
        $_SESSION['lnameErr'] = 'Last name is required';
        $errors[] = 'lnameErr';
    } else {
        $lname = htmlspecialchars($_POST['lname']);
        $_SESSION['lname'] = $lname;
    }

    
    if(!empty($_POST['username'])){
        
        $username = htmlspecialchars($_POST['username']);
        $stmt = $conn->prepare('SELECT * FROM users WHERE userName = ? And id != ?');
        $stmt->bind_param('si',$username,$_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $_SESSION['usernameErr'] = "username alreay use";
            $errors[] = 'usernameErr';
        }
    }else{
        $_SESSION['usernameErr'] = 'Username is required';
        $errors[] = 'usernameErr';
    }

    if(!empty($_POST['email'])){
        $email = htmlspecialchars($_POST['email']);
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? And id != ?');
        $stmt->bind_param('si',$email,$_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $_SESSION['emailErr'] = "email alreay use";
            $errors[] = 'emailErr';
        }
    }else{
        $_SESSION['signUpemailErr'] = 'Email is required';
        $errors[] = 'emailErr';
    }
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
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
                // Delete the old file if it exists and is not a default avatar
                $query = "SELECT avatar FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $oldAvatar = $row['avatar']; // Get old filename
                $stmt->close();

                if (!empty($oldAvatar) && file_exists($uploadDir . $oldAvatar) && $oldAvatar !== 'default-avatar.png') {
                    unlink($uploadDir . $oldAvatar);
                }
    
                // // Update the database with the new filename
                // $updateQuery = "UPDATE users SET avatar = ? WHERE id = ?";
                // $updateStmt = $conn->prepare($updateQuery);
                // $updateStmt->bind_param("si", $uniqueFilename, $userId);
                // if ($updateStmt->execute()) {
                //     $_SESSION['profileSuccess'] = 'Profile picture updated successfully';
                // } else {
                //     $_SESSION['profileErr'] = 'Database update failed';
                //     $errors[] = 'profileErr';
                // }
                // $updateStmt->close();
            } else {
                $_SESSION['profileErr'] = 'Failed to save uploaded file';
                $errors[] = 'profileErr';
            }
        }
    }else{
        $uniqueFilename ="";
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
     // acountStatus Validation
     if (empty($_POST['accountStatus'])) {
        $_SESSION['acountStatus'] = 'At least one hobby is required';
        
    } else {
        $acountStatus = $_POST['accountStatus'];
        $_SESSION['accountStatus'] = $acountStatus;
        
    }

    //if any error ossure then return
    if(!empty($errors)){
        header('location:../home/updateProfile.php');
        exit();
        // var_dump($errors);
    }
    //delete all session expect user_id
    foreach ($_SESSION as $key => $value) {
        if ($key !== 'user_id') {
            unset($_SESSION[$key]);
        }
    }
    if($uniqueFilename == ""){
        $query = "UPDATE users SET fName = ?, lName = ?, userName = ?, email = ?, dob = ?, qualification = ?, address = ?, gender = ?, hobbies = ?, accountStatus = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssi", $fname, $lname, $username, $email, $dob, $qualification, $address, $gender, $hobbies,$acountStatus, $_SESSION['user_id']);
        if ($stmt->execute()) {
            header('location:../home/home.php');
            exit();
        } else {
            die("Execution failed: " . $stmt->error);
        }
        }else{
            $query = "UPDATE users SET avatar = ?, fName = ?, lName = ?, userName = ?, email = ?, dob = ?, qualification = ?, address = ?, gender = ?, hobbies = ?, accountStatus = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
    
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
    
            $stmt->bind_param("sssssssssssi",$uniqueFilename, $fname, $lname, $username, $email, $dob, $qualification, $address, $gender, $hobbies,$acountStatus, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                header('location:../home/home.php');
                exit();
            } else {
                die("Execution failed: " . $stmt->error);
            }
        }
}else{
    header('location:../home/updateProfile.php');
    exit();
}
?>