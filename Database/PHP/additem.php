<?php
$host = 'localhost';
$dbname = 'highbridgecreative';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$unit_options = [
    1 => "pcs",
    2 => "kg",
    3 => "box",
    4 => "dozen"
];

$message = "";

$showPopup = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $unit = $conn->real_escape_string($_POST['unit']);

    $stmt = $conn->prepare("INSERT INTO items (item_name, description, price, stock_quantity, unit) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $item_name, $description, $price, $stock_quantity, $unit);

    if ($stmt->execute()) {
        $showPopup = true; // trigger popup
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>
    <?php include 'sidebar.html'; ?>
    <!-- Main Content -->
    <div class="content" id="content">
        <input class="icon" type="image" src="../PNG/Icon/sidebarIcon.png" onclick="toggleSidebar()" />
        <div class="scrolling-text">
            <span>𝓗𝓲𝓰𝓱 𝓑𝓻𝓲𝓭𝓰𝓮 𝓒𝓻𝓮𝓪𝓽𝓲𝓿𝓮</span>
        </div>
        <div>
            <div class="form-container">
                <h2>Add New Item</h2>
                <?php if (!empty($message)) {
                    echo "<p style='text-align:center; color: green;'>$message</p>";
                } ?>
                <form action="" method="POST">
                    <label for="item_name">Item Name:</label>
                    <input type="text" name="item_name" id="item_name" required>

                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required></textarea>

                    <label for="price">Price (RM):</label>
                    <input type="number" step="0.01" name="price" id="price" required>

                    <label for="stock_quantity">Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" required>

                    <label for="unit">Unit:</label>
                    <select name="unit" id="unit" required>
                        <option value="">--Select Unit--</option>
                        <?php foreach ($unit_options as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="button1">Submit</button>
                    <button type="button" onclick="window.location.href='item.php'" class="button2">Back</button>
                </form>
            </div>
        </div>
    </div>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <p>✅ Item added successfully!</p>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>
    <script>
        const showPopup = <?= $showPopup ? 'true' : 'false' ?>;
    </script>
    <script src="../JS/script.js"></script>
</body>

</html>