<head>
    <style>
        footer {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            position: fixed;
            bottom: 0;
            border: 1px solid black;
            width: 100%;
            padding: 1rem 0rem;
            background: #2c3e50;
            color: white;
            z-index: 10;
        }

        footer a {
            color: aliceblue;
            text-decoration: none;
        }
        a img{
            height: 30px;
            border-radius: 20px;
        }
    </style>
</head>

<footer>
    <div class="left-side">
        <a href="home.php">Home</a>
    </div>
    <div class="middle">
        <a href="searchPage.php">
            <img src="./assests/search-btn.png" alt="">
        </a>
    </div>
    <div class="middle">
        <a href="CreatePost.php">create Post</a>
    </div>
    <div class="right-side">
        <a href="profilePage.php">profile</a>
    </div>
</footer>