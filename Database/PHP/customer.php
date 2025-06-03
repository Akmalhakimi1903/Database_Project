<?php
// Database configuration
$host = 'localhost';
$dbname = 'highbridgecreative';
$username = 'root';
$password = '';

$showPopup = false;
$popupMessage = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Add new customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO customers (name, address, phone) VALUES (:name, :address, :phone)");
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->execute();
        header("Location:/Database/PHP/customer.php");
        echo "<script>alert('Customer added successfully!'); window.location.href='customers.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Update existing customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    try {
        $stmt = $conn->prepare("UPDATE customers SET name=:name, address=:address, phone=:phone WHERE customer_id=:id");
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $showPopup = true;
        $popupMessage = "✅ Customer updated successfully!";
    } catch (PDOException $e) {
        $popupMessage = "Error updating record: " . $e->getMessage();
    }
}

// Delete customer
if ($action == 'delete' && $id > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();


        $showPopup = true;
        $popupMessage = "Customer deleted successfully!";
    } catch (PDOException $e) {
        $showPopup = true;
        $popupMessage = "Error deleting customer: " . $e->getMessage();
    }
}


// Fetch current customer for editing
$currentCustomer = null;
if ($action == 'edit' && $id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $currentCustomer = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Get all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Customer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .form-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .form-container input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-container input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .customer-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .customer-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: calc(33.333% - 20px);
            box-sizing: border-box;
            transition: transform 0.2s;
        }

        .customer-card:hover {
            transform: translateY(-5px);
        }

        .customer-card h4 {
            margin-top: 0;
            color: #333;
        }

        .customer-card p {
            margin: 10px 0;
            color: #555;
        }

        .actions {
            margin-top: 15px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin-right: 10px;
            display: inline-block;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media screen and (max-width: 1000px) {
            .customer-card {
                width: calc(50% - 20px);
            }
        }

        @media screen and (max-width: 600px) {
            .customer-card {
                width: 100%;
            }
        }

        .cancel-btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #ccc;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn:hover {
            opacity: 0.9;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.html'; ?>

    <div class="content" id="content">
        <input class="icon" type="image" src="../PNG/Icon/sidebarIcon.png" onclick="toggleSidebar()" />
        <div class="scrolling-text">
            <span>𝓗𝓲𝓰𝓱 𝓑𝓻𝓲𝓭𝓰𝓮 𝓒𝓻𝓮𝓪𝓽𝓲𝓿𝓮</span>
        </div>

        <?php if ($action == 'edit' && $currentCustomer): ?>
            <h2>Edit Customer</h2>
            <form method="POST" action="?action=edit&id=<?= $id ?>">
                <label>Name:</label><br>
                <input type="text" name="name" value="<?= htmlspecialchars($currentCustomer['name']) ?>" required><br><br>

                <label>Address:</label><br>
                <input type="text" name="address" value="<?= htmlspecialchars($currentCustomer['address']) ?>" required><br><br>

                <label>Phone Number:</label><br>
                <input type="text" name="phone" value="<?= htmlspecialchars($currentCustomer['phone']) ?>" required><br><br>

                <button type="submit" name="update" class="submit-btn">Update</button>
                <a href="customer.php" class="cancel-btn">Cancel</a>
            </form>
        <?php else: ?>
            <h2>Add New Customer</h2>
            <div class="form-container">
                <form method="POST" action="">
                    <label>Name:</label>
                    <input type="text" name="name" required>

                    <label>Address:</label>
                    <input type="text" name="address" required>

                    <label>Phone Number:</label>
                    <input type="text" name="phone" required>

                    <input type="submit" name="submit" value="Add Customer">
                </form>
            </div>

            <h3>Customer List</h3>
            <div class="customer-grid">
                <?php foreach ($customers as $customer): ?>
                    <div class="customer-card">
                        <h4><?= htmlspecialchars($customer['name']) ?></h4>
                        <p><strong>Address:</strong> <?= htmlspecialchars($customer['address']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
                        <div class="actions">
                            <a href="?action=edit&id=<?= $customer['customer_id'] ?>" class="btn-edit">Edit</a>
                            <a href='?action=delete&id=<?= $customer['customer_id'] ?>' class='btn-delete'>Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Success Modal -->
        <div id="successModal" class="modal">
            <div class="modal-content">
                <p id="modalMessage">Customer updated successfully!</p>
                <button onclick="closePopupCustomer()">OK</button>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <p>Are you sure you want to delete this customer?</p>
                <button id="confirmDelete">Yes, Delete</button>
                <button id="cancelDelete" class="btn-cancel">Cancel</button>
            </div>
        </div>

    </div>

    <script>
        const showPopup = <?= json_encode($showPopup) ?>;
        const popupMessage = <?= json_encode($popupMessage) ?>;

        function closePopupCustomer() {
            document.getElementById('successModal').style.display = 'none';
            window.location.href = 'customer.php';
        }

        if (showPopup) {
            const modal = document.getElementById('successModal');
            const message = document.getElementById('modalMessage');
            message.textContent = popupMessage;
            modal.style.display = 'block';
        }
    </script>
    <script src="../JS/script.js"></script>
</body>

</html>

<?php $conn = null; ?>