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
    <title>Quotation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: auto; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a { margin-right: 5px; text-decoration: none; padding: 3px 8px; border: 1px solid #ccc; border-radius: 3px; }
        .actions a.add { background-color: #4CAF50; color: white; border: none; }
        .actions a.view { background-color: #008CBA; color: white; border: none; }
        .actions a.edit { background-color: #f44336; color: white; border: none; }
        .actions a.delete { background-color: #555555; color: white; border: none; }
        .add-button { text-align: right; margin-bottom: 15px; }
    </style>
</head>

<body>
    <?php include 'sidebar.html'; ?>
    <!-- Main Content -->
    <div class="content" id="content">
        <input class="icon" type="image" src="../PNG/Icon/sidebarIcon.png" onclick="toggleSidebar()" />
        <div class="scrolling-text">
            <span>𝓗𝓲𝓰𝓱 𝓑𝓻𝓲𝓭𝓰𝓮 𝓒𝓻𝓮𝓪𝓽𝓲𝓿𝓮</span>
        </div>
        <div class="container">
        <h2>Manage Quotations</h2>
        <div class="add-button">
            <a href="create-quotation.php" class="add">Add New Quotation</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Total (RM)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // index.php
                require_once 'config.php';

                $sql = "SELECT id, invoice_no, quote_date, customer_name, total_amount FROM quotations ORDER BY quote_date DESC";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['invoice_no'] . "</td>";
                            echo "<td>" . $row['quote_date'] . "</td>";
                            echo "<td>" . $row['customer_name'] . "</td>";
                            echo "<td>" . number_format($row['total_amount'], 2) . "</td>";
                            echo "<td class='actions'>";
                            echo "<a href='display-quotation.php?id=" . $row['id'] . "' class='view'>View</a>";
                            echo "<a href='edit-quotation.php?id=" . $row['id'] . "' class='edit'>Edit</a>";
                            echo "<a href='delete-quotation.php?id=" . $row['id'] . "' class='delete' onclick=\"return confirm('Are you sure you want to delete this quotation?');\">Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        mysqli_free_result($result);
                    } else {
                        echo "<tr><td colspan='6'>No quotations found.</td></tr>";
                    }
                } else {
                    echo "ERROR: Could not execute $sql. " . mysqli_error($link);
                }

                mysqli_close($link);
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <script src="../JS/script.js"></script>
</body>

</html>
