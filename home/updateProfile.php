<?php
session_start();
require('../model/congif.model.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$dbname = 'timespand';
$conn->select_db($dbname);

try {
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?'); // Fix: "FROM users" instead of "Form users"
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fname = $user['fName'];
        $lname = $user['lName'];
        $username = $user['userName'];
        $email = $user['email'];
        $avatar = $user['avatar'];
        $dob = $user['dob'];
        $address = $user['address'];
        $qualification = $user['qualification'];
        $gender = $user['gender'];
        $hobbies = explode(",", $user['hobbies']); // Convert stored hobbies string to array
        $accountStatus = $user['accountStatus'];
    }
} catch (\Throwable $th) {
    echo "Error: " . $th->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="updateProfile.css?v=<?php echo time(); ?>">
    <title>Update Profile</title>
</head>

<body>

    <div class="container">
        <div class="back-btn">
            <a href="../controller/profile_actions.php?action=back_to_home_page">
                <button><img src="./assests/93634.png" alt=""></button>
            </a>
        </div>
        <div class="text">Update your Profile</div>

        <form action="../controller/updateProfile.controller.php" method="POST" enctype="multipart/form-data">
            <div class="box">
                <label for="fname">Firstname</label>
                <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($fname) ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['fnameErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="lname">Lastname</label>
                <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($lname) ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['lnameErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['usernameErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['emailErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="avatar">Profile Photo</label>
                <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(event)">
                <input type="hidden" name="oldImage" value="<?php echo  $avatar ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['profileErr'] ?? ''; ?></span>

                <div id="image-preview">
                    <?php if (!empty($avatar)) : ?>
                        <img id="current-avatar" src="../controller/avatar/<?php echo  $avatar ?>" width="100">
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-gender">
                <span>Account Status</span>
                <input type="radio" name="accountStatus" value="public" <?= $accountStatus == 'public' ? "checked" : "" ?> style="cursor:pointer"> public
                <input type="radio" name="accountStatus" value="private" <?= $accountStatus == 'private' ? "checked" : "" ?> style="cursor:pointer">private
            </div>
            <div class="box">
                <label for="dob">DOB</label>
                <input type="date" id="dob" name="dob" value="<?= $dob ?>">
                <span style="color:red; font-size:.8rem"><?= $_SESSION['dobErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="qualification">Qualification</label>
                <select name="qualification" id="qualification">
                    <option value="Secondary" <?= $qualification == "Secondary" ? "selected" : "" ?>>Secondary</option>
                    <option value="Higher Secondary" <?= $qualification == "Higher Secondary" ? "selected" : "" ?>>Higher Secondary</option>
                    <option value="Graduation" <?= $qualification == "Graduation" ? "selected" : "" ?>>Graduation</option>
                    <option value="PhD" <?= $qualification == "PhD" ? "selected" : "" ?>>PhD</option>
                </select>
                <span style="color:red; font-size:.8rem"><?= $_SESSION['qualificationErr'] ?? ''; ?></span>
            </div>
            <div class="box">
                <label for="address">Address</label>
                <textarea name="address"><?= htmlspecialchars($address) ?></textarea>
                <span style="color:red; font-size:.8rem"><?= $_SESSION['addressErr'] ?? ''; ?></span>
            </div>
            <div class="box-gender">
                <span>Gender</span>
                <input type="radio" name="gender" value="male" <?= $gender == "male" ? "checked" : "" ?>> Male
                <input type="radio" name="gender" value="female" <?= $gender == "female" ? "checked" : "" ?>> Female
                <input type="radio" name="gender" value="others" <?= $gender == "others" ? "checked" : "" ?>> Others
                <span style="color:red; font-size:.8rem"><?= $_SESSION['genderErr'] ?? ''; ?></span>
            </div>
            <div class="hobbies">
                <p>Hobbies</p>
                <input type="checkbox" name="hobbies[]" value="Sports" <?= in_array("Sports", $hobbies) ? "checked" : "" ?>> Sports
                <input type="checkbox" name="hobbies[]" value="Reading" <?= in_array("Reading", $hobbies) ? "checked" : "" ?>> Reading
                <input type="checkbox" name="hobbies[]" value="Writing" <?= in_array("Writing", $hobbies) ? "checked" : "" ?>> Writing
                <input type="checkbox" name="hobbies[]" value="Cooking" <?= in_array("Cooking", $hobbies) ? "checked" : "" ?>> Cooking
                <input type="checkbox" name="hobbies[]" value="Photography" <?= in_array("Photography", $hobbies) ? "checked" : "" ?>> Photography <br />
                <input type="checkbox" name="hobbies[]" value="Gardening" <?= in_array("Gardening", $hobbies) ? "checked" : "" ?>> Gardening
                <input type="checkbox" name="hobbies[]" value="shopping" <?= in_array("shopping", $hobbies) ? "checked" : "" ?>> shopping
                <input type="checkbox" name="hobbies[]" value="Arts & Crafts" <?= in_array("Arts & Crafts", $hobbies) ? "checked" : "" ?>> Arts & Crafts
                <span style="color:red; font-size:.8rem"><?= $_SESSION['hobbiesErr'] ?? ''; ?></span>
            </div>
            <div class="submit">
                <button type="submit">Update Profile</button>
            </div>
        </form>
        <?php
        foreach ($_SESSION as $key => $value) {
            if ($key !== 'user_id') {
                unset($_SESSION[$key]);
            }
        }
        ?>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0]; // Get selected file
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Show the new image preview
                    document.getElementById('image-preview').innerHTML = `<img src="${e.target.result}" width="100">`;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>