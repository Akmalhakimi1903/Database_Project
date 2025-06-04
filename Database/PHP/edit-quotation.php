<?php
// edit.php
require_once 'config.php';

$quotation_id = $_GET['id'] ?? null;
$quotation = null;
$items = [];

if ($quotation_id) {
    // Fetch quotation details
    $sql_quotation = "SELECT * FROM quotations WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql_quotation)) {
        mysqli_stmt_bind_param($stmt, "i", $quotation_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quotation = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    // Fetch quotation items
    $sql_items = "SELECT * FROM quotation_items WHERE quotation_id = ?";
    if ($stmt_items = mysqli_prepare($link, $sql_items)) {
        mysqli_stmt_bind_param($stmt_items, "i", $quotation_id);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        while ($row = mysqli_fetch_assoc($result_items)) {
            $items[] = $row;
        }
        mysqli_stmt_close($stmt_items);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $quotation_id) {
    // Collect updated quotation data
    $invoice_no = mysqli_real_escape_string($link, $_POST['invoice_no']);
    $quote_date = mysqli_real_escape_string($link, $_POST['quote_date']);
    $customer_name = mysqli_real_escape_string($link, $_POST['customer_name']);
    $customer_address = mysqli_real_escape_string($link, $_POST['customer_address']);
    $customer_phone = mysqli_real_escape_string($link, $_POST['customer_phone']);
    $delivery_address = mysqli_real_escape_string($link, $_POST['delivery_address']);
    $terms = mysqli_real_escape_string($link, $_POST['terms']);
    $po_no = mysqli_real_escape_string($link, $_POST['po_no']);
    $do_no = mysqli_real_escape_string($link, $_POST['do_no']);
    $quo_no = mysqli_real_escape_string($link, $_POST['quo_no']);
    $attn = mysqli_real_escape_string($link, $_POST['attn']);
    $total_amount = 0; // Recalculate from items

    // Start transaction
    mysqli_begin_transaction($link);
    $success = true;

    try {
        // Update quotations table
        $sql_update_quotation = "UPDATE quotations SET invoice_no=?, quote_date=?, customer_name=?, customer_address=?, customer_phone=?, delivery_address=?, terms=?, po_no=?, do_no=?, quo_no=?, attn=?, total_amount=? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql_update_quotation)) {
            mysqli_stmt_bind_param($stmt, "sssssssssssdi", $invoice_no, $quote_date, $customer_name, $customer_address, $customer_phone, $delivery_address, $terms, $po_no, $do_no, $quo_no, $attn, $total_amount, $quotation_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $success = false;
            throw new Exception("Error preparing update quotation statement: " . mysqli_error($link));
        }

        // Delete existing items and re-insert (simpler for this example)
        $sql_delete_items = "DELETE FROM quotation_items WHERE quotation_id = ?";
        if ($stmt_delete = mysqli_prepare($link, $sql_delete_items)) {
            mysqli_stmt_bind_param($stmt_delete, "i", $quotation_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
        } else {
            $success = false;
            throw new Exception("Error preparing delete items statement: " . mysqli_error($link));
        }

        // Insert updated quotation items
        $item_descriptions = $_POST['item_description'] ?? [];
        $item_qtys = $_POST['item_qty'] ?? [];
        $item_prices = $_POST['item_price'] ?? [];
        $item_discounts = $_POST['item_discount'] ?? [];
        $item_codes = $_POST['item_code'] ?? array_fill(0, count($item_descriptions), '');

        $calculated_total_amount = 0;

        $sql_insert_item = "INSERT INTO quotation_items (quotation_id, item_code, description, quantity, price, discount, total) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt_insert_item = mysqli_prepare($link, $sql_insert_item)) {
            for ($i = 0; $i < count($item_descriptions); $i++) {
                $description = mysqli_real_escape_string($link, $item_descriptions[$i]);
                $qty = mysqli_real_escape_string($link, $item_qtys[$i]);
                $price = floatval($item_prices[$i]);
                $discount = floatval($item_discounts[$i]);
                $item_code = mysqli_real_escape_string($link, $item_codes[$i]);

                $item_total = ($price * floatval(preg_replace('/[^0-9.]/', '', $qty))) - $discount;
                $calculated_total_amount += $item_total;

                mysqli_stmt_bind_param($stmt_insert_item, "isssddd", $quotation_id, $item_code, $description, $qty, $price, $discount, $item_total);
                mysqli_stmt_execute($stmt_insert_item);
            }
            mysqli_stmt_close($stmt_insert_item);
        } else {
            $success = false;
            throw new Exception("Error preparing insert item statement: " . mysqli_error($link));
        }

        // Update total_amount in quotations table again
        $sql_update_total = "UPDATE quotations SET total_amount = ? WHERE id = ?";
        if ($stmt_update_total = mysqli_prepare($link, $sql_update_total)) {
            mysqli_stmt_bind_param($stmt_update_total, "di", $calculated_total_amount, $quotation_id);
            mysqli_stmt_execute($stmt_update_total);
            mysqli_stmt_close($stmt_update_total);
        } else {
            $success = false;
            throw new Exception("Error preparing final total update statement: " . mysqli_error($link));
        }

        if ($success) {
            mysqli_commit($link);
            header("location: index.php");
            exit();
        } else {
            mysqli_rollback($link);
            echo "Something went wrong. Please try again.";
        }
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo "Error: " . $e->getMessage();
    } finally {
        mysqli_close($link);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Quotation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 900px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group textarea { width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .form-row { display: flex; justify-content: space-between; gap: 15px; }
        .form-row .form-group { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .button-group { text-align: center; margin-top: 20px; }
        .button-group button, .button-group a { padding: 10px 20px; margin: 0 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .button-group button.submit { background-color: #4CAF50; color: white; }
        .button-group a.back { background-color: #f44336; color: white; }
        .add-item-btn { background-color: #008CBA; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
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
        <h2>Edit Quotation</h2>
        <?php if ($quotation): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $quotation_id; ?>" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Invoice No.:</label>
                    <input type="text" name="invoice_no" value="<?php echo htmlspecialchars($quotation['invoice_no']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="quote_date" value="<?php echo htmlspecialchars($quotation['quote_date']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Customer Name:</label>
                <input type="text" name="customer_name" value="<?php echo htmlspecialchars($quotation['customer_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Customer Address:</label>
                <textarea name="customer_address" rows="3"><?php echo htmlspecialchars($quotation['customer_address']); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Customer Phone:</label>
                    <input type="text" name="customer_phone" value="<?php echo htmlspecialchars($quotation['customer_phone']); ?>">
                </div>
                <div class="form-group">
                    <label>Delivery Address:</label>
                    <input type="text" name="delivery_address" value="<?php echo htmlspecialchars($quotation['delivery_address']); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Terms:</label>
                    <input type="text" name="terms" value="<?php echo htmlspecialchars($quotation['terms']); ?>">
                </div>
                <div class="form-group">
                    <label>P.O No:</label>
                    <input type="text" name="po_no" value="<?php echo htmlspecialchars($quotation['po_no']); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>D.O No:</label>
                    <input type="text" name="do_no" value="<?php echo htmlspecialchars($quotation['do_no']); ?>">
                </div>
                <div class="form-group">
                    <label>Quotation No:</label>
                    <input type="text" name="quo_no" value="<?php echo htmlspecialchars($quotation['quo_no'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>ATTN:</label>
                <input type="text" name="attn" value="<?php echo htmlspecialchars($quotation['attn']); ?>">
            </div>

            <h3>Items:</h3>
            <table id="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 15%;">Item Code</th>
                        <th style="width: 30%;">Description</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 15%;">Price (RM)</th>
                        <th style="width: 15%;">Discount(RM)</th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php $item_num = 1; foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $item_num++; ?></td>
                                <td><input type="text" name="item_code[]" value="<?php echo htmlspecialchars($item['item_code']); ?>" style="width: 90%;"></td>
                                <td><input type="text" name="item_description[]" value="<?php echo htmlspecialchars($item['description']); ?>" required style="width: 90%;"></td>
                                <td><input type="text" name="item_qty[]" value="<?php echo htmlspecialchars($item['quantity']); ?>" required style="width: 90%;"></td>
                                <td><input type="number" step="0.01" name="item_price[]" value="<?php echo htmlspecialchars($item['price']); ?>" required style="width: 90%;"></td>
                                <td><input type="number" step="0.01" name="item_discount[]" value="<?php echo htmlspecialchars($item['discount']); ?>" style="width: 90%;"></td>
                                <td><button type="button" onclick="removeItem(this)">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>1</td>
                            <td><input type="text" name="item_code[]" style="width: 90%;"></td>
                            <td><input type="text" name="item_description[]" required style="width: 90%;"></td>
                            <td><input type="text" name="item_qty[]" required style="width: 90%;"></td>
                            <td><input type="number" step="0.01" name="item_price[]" value="0.00" required style="width: 90%;"></td>
                            <td><input type="number" step="0.01" name="item_discount[]" value="0.00" style="width: 90%;"></td>
                            <td><button type="button" onclick="removeItem(this)">Remove</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="add-item-btn" onclick="addItem()">Add Item</button>

            <div class="button-group">
                <button type="submit" class="submit">Update Quotation</button>
                <a href="quotation.php" class="back">Cancel</a>
            </div>
        </form>
        <?php else: ?>
            <p>Quotation not found.</p>
            <a href="quotation.php" class="back">Back to list</a>
        <?php endif; ?>
    </div>

    <script>
        let itemCounter = <?php echo count($items) > 0 ? count($items) : 1; ?>;
        function addItem() {
            itemCounter++;
            const table = document.getElementById('items-table').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            newRow.innerHTML = `
                <td>${itemCounter}</td>
                <td><input type="text" name="item_code[]" style="width: 90%;"></td>
                <td><input type="text" name="item_description[]" required style="width: 90%;"></td>
                <td><input type="text" name="item_qty[]" required style="width: 90%;"></td>
                <td><input type="number" step="0.01" name="item_price[]" value="0.00" required style="width: 90%;"></td>
                <td><input type="number" step="0.01" name="item_discount[]" value="0.00" style="width: 90%;"></td>
                <td><button type="button" onclick="removeItem(this)">Remove</button></td>
            `;
        }

        function removeItem(button) {
            const row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
            const rows = document.getElementById('items-table').getElementsByTagName('tbody')[0].rows;
            for (let i = 0; i < rows.length; i++) {
                rows[i].cells[0].innerText = i + 1;
            }
            itemCounter = rows.length;
        }
    </script>
    </div>
    <script src="../JS/script.js"></script>
</body>

</html>