<?php
//database
$host = 'localhost';
$dbname = 'highbridgecreative';
$username = 'root';
$password = '';

//connection
$conn = new mysqli($host, $username, $password, $dbname);

//check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>High Bridge Creative</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'sidebar.html'; ?>
    <!-- Main Content -->
    <div class="content" id="content">
        <input class="icon" type="image" src="../PNG/Icon/sidebarIcon.png" onclick="toggleSidebar()" />
        <div class="scrolling-text">
            <span>𝓗𝓲𝓰𝓱 𝓑𝓻𝓲𝓭𝓰𝓮 𝓒𝓻𝓮𝓪𝓽𝓲𝓿𝓮</span>
        </div>
    </div>
    <script src="../JS/script.js"></script>
</body>

</html>