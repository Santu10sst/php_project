<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = 'timespand';

try {
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        die("unable to connet in database");
    } else {
        // echo "connection successfully <br/>";
    }

    //create db

    $sql = "CREATE DATABASE IF NOT EXISTS timespand";

    if ($conn->query($sql)) {
        // echo "create database successfully<br/>";
    } else {
        echo $conn->error;
    }
    //create user table
    $conn->select_db($dbname);
    $sql = "CREATE TABLE IF NOT EXISTS users(
            id INT AUTO_INCREMENT PRIMARY KEY,
            fName VARCHAR(50) NOT NULL,
            lName VARCHAR(50) NOT NULL,
            userName VARCHAR(50) UNIQUE,
            email VARCHAR(50) UNIQUE,
            avatar VARCHAR(200) NOT NULL ,
            password VARCHAR(100) NOT NULL,
            dob DATE,
            gender ENUM('male','female','others'),
            qualification  ENUM('Secondary','Higher Secondary','Graduation','PhD'),
            address TEXT,
            hobbies SET('Sports','Reading','Writing','Cooking','Photography','Gardening','Shopping','Arts & Crafts'),
            accountStatus ENUM('public','private')
    )";
    if ($conn->query($sql)) {
        // echo "you have successfully create table<br/>";
    }
    $sql = "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title TEXT NOT NULL,
            mediaUrl VARCHAR(200) NOT NULL ,
            description TEXT,
            user_id INT,
            fileType VARCHAR(20),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        // echo "you have successfully create table<br/>";
    }
    $sql = "CREATE TABLE IF NOT EXISTS follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    followers_id INT NOT NULL,    -- Sender of the friend request
    following_id INT NOT NULL,    -- Receiver of the friend request
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (followers_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        // echo "you have successfully create table<br/>";
    }


    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        seen_status ENUM('seen','unseen') DEFAULT unseen,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        // echo "you have successfully create table<br/>";
    }
} catch (\Throwable $th) {
    echo $th->getMessage();
}
