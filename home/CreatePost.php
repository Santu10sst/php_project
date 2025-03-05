<?php
session_start();
require('../model/congif.model.php');
$dbname = 'timespand';
$conn->select_db($dbname);
$errors = [];
$titleErr = $fileErr = $descriptionErr = "";
$title = $description = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (empty($_POST['title'])) {
        $titleErr = "Title is required";
        $errors[] = 'titleError';
    } else {
        $title = $_POST['title'];
    }

    //media upload in file
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        $fileErr = 'Please upload a file';
        $errors[] = 'fileErr';
    } else {
        // Allowed file types (Images, Videos, Audio)
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif', // Images
            'image/avif', // Images
            'video/mp4',
            'video/webm',
            'video/ogg', // Videos
            'audio/mpeg',
            'audio/wav',
            'audio/ogg'  // Audio
        ];

        $fileType = $_FILES['file']['type'];
        $type = explode('/', $fileType, 2);
        $fileSize = $_FILES['file']['size'];

        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            $fileErr = 'Only images, videos, and audio files are allowed';
            $errors[] = 'fileErr';
        } elseif ($fileSize > 20 * 1024 * 1024) { // 20MB max file size
            $fileErr = 'File size must be less than 10MB';
            $errors[] = 'fileErr';
        } else {
            $uploadDir = 'uploads/'; // Directory for storing files
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if not exists
            }

            // Generate a unique filename
            $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $uniqueFilename = uniqid('file_', true) . '.' . $fileExt;

            // Move uploaded file to uploads directory
            $destination = $uploadDir . $uniqueFilename;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                $fileErr = 'Failed to save uploaded file';
                $errors[] = 'fileErr';
            }
        }
    }
    //description validation
    if (empty($_POST['description'])) {
        $descriptionErr = "Description is Required";
        $errors[] = 'descriptionErr';
    } else {
        $destination = $_POST['description'];
    }

    //check errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO posts(title, mediaUrl, description,fileType, user_id) VALUES (?,?,?,?,?)");
            $stmt->bind_param('ssssi', $title, $uniqueFilename, $destination, $type[0], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();;
            header('location:./home.php');
            // header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (\Throwable $th) {
            echo $th->getMessage();
            header('location:./CreatePost.php');
            exit();
        }
    } else {
        // header('location:./CreatePost.php');
        // exit();

    }
} else {
    // header('location:./CreatePost.php');
    //     exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CreatePost.css?v=<?php echo time(); ?>">
    <title>Time Spend</title>
</head>

<body>
    <?php require('./navbar.php'); ?>
    <div class="body">
        <div class="container">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                <h2>Upload Your File</h2>

                <div class="post-box">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter a title" value="<?= $title ?>">
                    <span style="color:red; font-size:.8rem"><?php echo isset($titleErr) ? $titleErr : ""; ?></span>
                </div>

                <div class="post-box">
                    <label for="file">File Upload</label>
                    <input type="file" id="file" name="file" accept="image/*, video/*">
                    <span style="color:red; font-size:.8rem"><?php echo isset($fileErr) ? $fileErr : ""; ?></span>
                </div>

                <div class="preview-container">
                    <p id="file-message">No file selected</p>
                    <img id="imagePreview" class="hidden img" alt="Image Preview">
                    <video id="videoPreview" class="hidden video" controls></video>

                    <!-- <audio id="audioPreview" class="hidden audio" controls></audio> -->
                </div>

                <div class="post-box">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter a description" rows="4"><?= $description ?></textarea>
                    <span style="color:red; font-size:.8rem"><?php echo !empty($descriptionErr) ? $descriptionErr : ""; ?></span>
                </div>

                <button type="submit">Upload</button>
            </form>
        </div>
    </div>

    <?php require('./footer.php'); ?>
</body>
<script>
    document.getElementById("file").addEventListener("change", function(event) {
        const file = event.target.files[0];
        const fileMessage = document.getElementById("file-message");
        const imagePreview = document.getElementById("imagePreview");
        const videoPreview = document.getElementById("videoPreview");
        // const audioPreview = document.getElementById("audioPreview");

        // Reset previous preview
        imagePreview.classList.add("hidden");
        videoPreview.classList.add("hidden");
        // audioPreview.classList.add("hidden");
        fileMessage.innerText = "file selected";

        if (file) {
            const fileURL = URL.createObjectURL(file);
            const fileType = file.type;

            if (fileType.startsWith("image/")) {
                imagePreview.src = fileURL;
                imagePreview.classList.remove("hidden");
            } else if (fileType.startsWith("video/")) {
                videoPreview.src = fileURL;
                videoPreview.classList.remove("hidden");
            }
            // else if (fileType.startsWith("audio/")) {
            //     audioPreview.src = fileURL;
            //     audioPreview.classList.remove("hidden");
            // }
            else {
                fileMessage.innerText = "Unsupported file type!";
            }
        }
    });
</script>

</html>