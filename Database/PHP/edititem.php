<?php
// database config
$host = 'localhost';
$dbname = 'highbridgecreative';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// check connection
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
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    die("No item ID provided.");
}

//  form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $unit = $conn->real_escape_string($_POST['unit']);

    $stmt = $conn->prepare("UPDATE items SET item_name=?, description=?, price=?, stock_quantity=?, unit=? WHERE item_id=?");
    $stmt->bind_param("ssdisi", $item_name, $description, $price, $stock_quantity, $unit, $item_id);

    $showPopup = false;

    if ($stmt->execute()) {
        $showPopup = true;
    } else {
        $message = "Error updating item: " . $stmt->error;
    }



    $stmt->close();
}

// Fetch item to edit
$stmt = $conn->prepare("SELECT * FROM items WHERE item_id=?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

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
        <div class="form-container">
            <h2>Edit Item</h2>
            <?php if (!empty($message)) echo "<p style='color: green; text-align:center;'>$message</p>"; ?>
            <form action="" method="POST">
                <label for="item_name">Item Name:</label>
                <input type="text" name="item_name" id="item_name" required value="<?= htmlspecialchars($item['item_name']) ?>"><br>

                <label for="description">Item Description:</label>
                <textarea name="description" id="description" required><?= htmlspecialchars($item['description']) ?></textarea><br>

                <label for="price">Price (RM):</label>
                <input type="number" name="price" id="price" step="0.01" required value="<?= $item['price'] ?>"><br>

                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" name="stock_quantity" id="stock_quantity" required value="<?= $item['stock_quantity'] ?>"><br>

                <label for="unit">Unit (e.g. pcs, kg):</label>
                <select name="unit" id="unit" required>
                    <option value="">Select Unit</option>
                    <?php foreach ($unit_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= (isset($item['unit']) && $item['unit'] == $key) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($value) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <button type="submit" class="button1">Update</button>
                <button type="button" class="button2" onclick="window.location.href='item.php'">Back</button>
            </form>
            <div id="successModal" class="modal">
                <div class="modal-content">
                    <p>✅ Item updated successfully!</p>
                    <button onclick="closePopup()">OK</button>
                </div>
            </div>

        </div>
    </div>
    <script>
        function closePopup() {
        document.getElementById('successModal').style.display = 'none';
        window.location.href = 'item.php';
}
        const showPopup = <?= $showPopup ? 'true' : 'false' ?>;
    </script>
    <script src="../JS/script.js"></script>
</body>

</html>