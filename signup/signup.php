<?php 
error_reporting(E_ERROR);
session_start();
if(isset($_SESSION["user_id"])){
    header('location: ../home/home.php');
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Time Spend</title>
</head>

<body>

    <div class="container">
        <form action="<?php echo htmlspecialchars('../controller/signup.controller.php') ?>" method="post" enctype="multipart/form-data">
            <div class="box">
                <label for="firstname">Firstname</label>
                <input type="text" id="firstname" name="firstname" placeholder="Enter firstname" value="<?= isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset( $_SESSION['fnameErr']) ?  $_SESSION['fnameErr'] : "" ; ?></span>
                
            </div>
            <div class="box">
                <label for="lastname">Lastname</label>
                <input type="text" id="lastname" name="lastname" placeholder="Enter lastname"  value="<?= isset($_SESSION['lname']) ? htmlspecialchars($_SESSION['lname']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['lnameErr']) ? $_SESSION['lnameErr'] : ""; ?></span>
            </div>
            <div class="box">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username"  value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['usernameErr']) ? $_SESSION['usernameErr'] : ""; ?></span>
            </div>
            <div class="box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email" value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['signUpemailErr']) ? $_SESSION['signUpemailErr'] : ""; ?></span>
            </div>
            <div class="box">
                <label for="avatar">Profile Photo</label>
                <input type="file" accept="image/*" id="avatar" name="avatar" >
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['profileErr']) ? $_SESSION['profileErr'] : ""; ?></span>
            </div>
            <div class="box">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="********" value="<?= isset($_SESSION['password']) ? htmlspecialchars($_SESSION['password']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['signUppasswordErr']) ? $_SESSION['signUppasswordErr'] : ""; ?></span>
            </div>
            <div class="box">
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="********" value="<?= isset($_SESSION['confirmpassword']) ? htmlspecialchars($_SESSION['confirmpassword']) : '' ?>">
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['signUpconfirmpasswordErr']) ? $_SESSION['signUpconfirmpasswordErr'] : ""; ?></span>
            </div>
            <div class="box-gender">
                <span>Gender</span>
                <div class="gender-list">
                    <label for="male" class="gender">Male</label>                            
                    <input type="radio" id="male" name="gender" value="male" <?= (isset($_SESSION['gender']) && $_SESSION['gender'] == 'male') ? 'checked' : '' ?>>
                    <label for="female" class="gender">Female</label>
                    <input type="radio" id="female" name="gender" value="female" <?= (isset($_SESSION['gender']) && $_SESSION['gender'] == 'female') ? 'checked' : '' ?>>
                    <label for="others" class="gender">Others</label>
                    <input type="radio" id="others" name="gender" value="others" <?= (isset($_SESSION['gender']) && $_SESSION['gender'] == 'others') ? 'checked' : '' ?>>
                </div>
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['genderErr']) ? $_SESSION['genderErr'] : ""; ?></span>
            </div>
            <div class="box dob-qualification">
                <div class="dob">
                    <label for="dob">DOB</label>
                    <input type="date" id="dob" name="dob" max="<?= date('Y-m-d', strtotime('today')) ?>" min="<?= date('Y-m-d', strtotime('-80 year')) ?>" >
                    <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['dobErr']) ? $_SESSION['dobErr'] : ""; ?></span>
                </div>
                <div class="qualification">
                    <label for="qualification">Qualification</label>
                    <select name="qualification" id="qualification">
                        <option value="Secondary">Secondary</option>
                        <option value="Higher Secondary">Higher Secondary</option>
                        <option value="Graduation">Graduation</option>
                        <option value="PhD">PhD</option>
                    </select>
                    <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['qualificationErr']) ? $_SESSION['qualificationErr'] : ""; ?></span>
                </div>
            </div>
            <div class="box">
                <label for="address">Address</label>
                <textarea name="address" placeholder="Enter your permanent address" rows="5"><?php echo isset($_SESSION['address']) ? $_SESSION['address'] : ""; ?></textarea>
                <span style="color:red; font-size:.8rem"><?php echo isset($_SESSION['addressErr']) ? $_SESSION['addressErr'] : ""; ?></span>
            </div>
            <div class="hobbies">
                <p>Hobbies</p>
                
                <div class="hobbies-container">
                    <div class="hobbies-item">
                        <input type="checkbox" id="sports" name="hobbies[]" value="Sports">
                        <label for="sports">Sports</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="reading" name="hobbies[]" value="Reading">
                        <label for="reading">Reading</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="writing" name="hobbies[]" value="Writing">
                        <label for="writing">Writing</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="cooking" name="hobbies[]" value="Cooking">
                        <label for="cooking">Cooking</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="photography" name="hobbies[]" value="Photography">
                        <label for="photography">Photography</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="gardening" name="hobbies[]" value="Gardening">
                        <label for="gardening">Gardening</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="shopping" name="hobbies[]" value="Shopping">
                        <label for="shopping">Shopping</label>
                    </div>
                    <div class="hobbies-item">
                        <input type="checkbox" id="arts-crafts" name="hobbies[]" value="Arts & Crafts">
                        <label for="arts-crafts">Arts & Crafts</label>
                    </div>
                </div>
                
            </div>
            <span style="color:red; font-size:.8rem "><?php echo isset($_SESSION['hobbiesErr']) ? $_SESSION['hobbiesErr'] : ""; ?></span>
            <div class="submit">
                <button type="submit">Sign Up</button>
            </div>
            <div class="another-page">
                <p>Already have an account? <a href="../index.php">Log In</a></p>
            </div>
        </form>
    </div>
</body>

</html>
