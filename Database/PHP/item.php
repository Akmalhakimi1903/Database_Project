<?php
// database
$host = 'localhost';
$dbname = 'highbridgecreative';
$username = 'root';
$password = '';

// connection
$conn = new mysqli($host, $username, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$search = $_GET['search'] ?? '';

$unit_options = [
    1 => "pcs",
    2 => "kg",
    3 => "box",
    4 => "dozen"
];

$message = "";
// Initialize popup flag
$showPopup = false;
$popupMessage = "";

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM items WHERE item_id = $id") === TRUE) {
        $showPopup = true;
        $popupMessage = "Item deleted successfully!";
    } else {
        $showPopup = true;
        $popupMessage = "Error deleting item: " . $conn->error;
    }
}

// Get items
$sql = "SELECT item_id, item_name, description, price, stock_quantity, unit FROM items";
$result = $conn->query($sql);
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
        <div class="search-container">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by item name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div>
            <button class="button" onclick="window.location.href='additem.php'">Add Item</button>
            <table border="1" cellpadding="10" class="table"
                <tr>
                    <th>Item Name</th>
                    <th>Item Description</th>
                    <th>Price</th>
                    <th>Stock Quantity</th>
                    <th>Unit</th>
                    <th>Action</th>
                </tr>

                <?php

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $unit_name = isset($unit_options[$row['unit']]) ? $unit_options[$row['unit']] : 'Unknown';

                        echo "<tr>
                        <td>{$row['item_name']}</td>
                        <td>{$row['description']}</td>
                        <td>RM " . number_format($row['price'], 2) . "</td>
                        <td>{$row['stock_quantity']}</td>
                        <td>{$unit_name}</td>
                        <td>
                            <a href='edititem.php?id={$row['item_id']}' class='btn-edit'>Edit</a>
                            <a href='#' class='btn-delete' data-id='{$row['item_id']}'>Delete</a>
                        </td>
                    </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No items found</td></tr>";
                }
                ?>


            </table>
            <div id="popup-message" class="popup-message"></div>
        </div>
    </div>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <p id="popup-text">✅ Success</p>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to delete this item?</p>
            <button id="confirmDelete">Yes, Delete</button>
            <button id="cancelDelete" class="btn-cancel">Cancel</button>
        </div>
    </div>


    <script>
        const showPopup = <?= $showPopup ? 'true' : 'false' ?>;
        const popupMessage = <?= json_encode($popupMessage) ?>;
    </script>

    <script src="../JS/script.js"></script>
</body>

</html>