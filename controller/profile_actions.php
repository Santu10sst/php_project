
<?php

session_start();
require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);



    if($_GET['action'] == 'update_profile'){
        header('location: ../home/updateProfile.php');
        exit();
    }else if($_GET['action'] == 'back_to_home_page'){
        header('location: ../home/home.php');
        exit();
    }else if($_GET['action'] =='change_password'){
        header('location:./passwordChange.controller.php');
        exit();
    }


?>
