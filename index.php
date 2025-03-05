<?php 
// var_dump($_SESSION);
session_start();
if(isset($_SESSION["user_id"])){
    header('location: home/home.php');
    exit();
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Time Spend</title>
</head>

<body>

    <div class="container">
        <form action="<?php echo htmlspecialchars('./controller/login.controller.php')?>" method="post" >

            <div class="box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['emailErr']) ? $_SESSION['emailErr'] : ""; ?></span>
            </div>

            <div class="box">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"  placeholder="********">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['passwordErr']) ? $_SESSION['passwordErr'] : ""; ?></span>
            </div>

            <div class="submit">
                <button type="submit">Log In</button>
            </div>
            <div class="another-page">
                <p>Don't have an account?<a href="./signup/signup.php">Sign Up</a></p>
            </div>
        </form>
    </div>
</body>

</html>