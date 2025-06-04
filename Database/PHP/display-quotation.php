<?php
// display_quotation.php
require_once 'config.php';

$quotation = null;
$items = [];
$quotation_id = $_GET['id'] ?? null;

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

mysqli_close($link);

if (!$quotation) {
    echo "<p>Quotation not found.</p>";
    echo "<a href='index.php'>Back to list</a>";
    exit();
}

// --- Custom Function to convert number to words (replaces NumberFormatter) ---
function numberToWordsCustom($number) {
    $hyphen      = ' ';
    $conjunction = ' AND ';
    $separator   = ', ';
    $negative    = 'NEGATIVE ';
    $decimal_word = ' SEN'; // For 'RINGGIT MALAYSIA: ... AND ... SEN'

    $dictionary = array(
        0                   => 'ZERO',
        1                   => 'ONE',
        2                   => 'TWO',
        3                   => 'THREE',
        4                   => 'FOUR',
        5                   => 'FIVE',
        6                   => 'SIX',
        7                   => 'SEVEN',
        8                   => 'EIGHT',
        9                   => 'NINE',
        10                  => 'TEN',
        11                  => 'ELEVEN',
        12                  => 'TWELVE',
        13                  => 'THIRTEEN',
        14                  => 'FOURTEEN',
        15                  => 'FIFTEEN',
        16                  => 'SIXTEEN',
        17                  => 'SEVENTEEN',
        18                  => 'EIGHTEEN',
        19                  => 'NINETEEN',
        20                  => 'TWENTY',
        30                  => 'THIRTY',
        40                  => 'FORTY',
        50                  => 'FIFTY',
        60                  => 'SIXTY',
        70                  => 'SEVENTY',
        80                  => 'EIGHTY',
        90                  => 'NINETY'
    );

    $thousands = array(
        '',
        'THOUSAND',
        'MILLION',
        'BILLION',
        'TRILLION',
        'QUADRILLION',
        'QUINTILLION',
    );

    $number = (string) $number; // Ensure working with string for decimal handling

    // Handle negative numbers
    if (strpos($number, '-') === 0) {
        return $negative . numberToWordsCustom(abs($number));
    }

    $parts = explode('.', $number);
    $whole = (string) $parts[0];
    $decimal_part = isset($parts[1]) ? (string) $parts[1] : '';

    $return = '';

    // Process the whole number part
    if ($whole === '0') {
        $return = $dictionary[0];
    } else {
        // Pad the whole number with leading zeros so its length is a multiple of 3
        $whole = str_pad($whole, ceil(strlen($whole) / 3) * 3, '0', STR_PAD_LEFT);

        for ($i = 0; $i < strlen($whole); $i += 3) {
            $chunk = substr($whole, $i, 3);

            $hundreds = (int) $chunk[0];
            $tens = (int) $chunk[1];
            $ones = (int) $chunk[2];

            $word = '';

            if ($hundreds > 0) {
                $word .= $dictionary[$hundreds] . ' HUNDRED';
                if ($tens > 0 || $ones > 0) {
                    $word .= $conjunction;
                }
            }

            if ($tens > 0 || $ones > 0) {
                if ($tens < 2) { // 1-19
                    $word .= $dictionary[($tens * 10) + $ones];
                } else { // 20-99
                    $word .= $dictionary[$tens * 10];
                    if ($ones > 0) {
                        $word .= $hyphen . $dictionary[$ones];
                    }
                }
            }

            if ($word !== '') {
                $return .= $word . ' ' . $thousands[floor((strlen($whole) - $i - 1) / 3)] . $separator;
            }
        }
    }

    $return = trim($return, $separator . ' ');

    // Add decimal part (sen)
    if ($decimal_part !== '') {
        // Pad to two decimal places
        $decimal_part = str_pad($decimal_part, 2, '0', STR_PAD_RIGHT);
        $decimal_value = (int) $decimal_part; // Treat as a whole number for words

        if ($decimal_value > 0) {
            $decimal_words = '';
            $tens = (int) $decimal_part[0];
            $ones = (int) $decimal_part[1];

            $decimal_words .= ' AND '; // ' AND ' for Ringgit Malaysia convention

            if ($tens < 2) { // 01-19
                $decimal_words .= $dictionary[($tens * 10) + $ones];
            } else { // 20-99
                $decimal_words .= $dictionary[$tens * 10];
                if ($ones > 0) {
                    $decimal_words .= $hyphen . $dictionary[$ones];
                }
            }
            $return .= $decimal_words . $decimal_word;
        }
    }

    return 'RINGGIT MALAYSIA: ' . trim($return) . ' ONLY';
}
// --- End of Custom Function ---

$amount_in_words = numberToWordsCustom($quotation['total_amount']);

// Use the current date for invoice header display
$display_date = date('d/m/Y');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Quotation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .invoice-box {
            max-width: 900px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            line-height: 24px;
            color: #555;
            background-color: #fff;
        }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .logo img { max-width: 150px; height: auto; } /* Adjust as needed */
        .company-details { text-align: right; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .company-info { font-size: 12px; line-height: 1.5; }
        .invoice-title { text-align: center; margin: 20px 0; }
        .invoice-title h3 { font-size: 28px; margin: 0; color: #333; }
        .divider { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-section .col { flex: 1; margin-right: 20px; }
        .info-section .col:last-child { margin-right: 0; }
        .details-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .invoice-table th, .invoice-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        .invoice-table th { background-color: #f9f9f9; font-weight: bold; color: #333; }
        .invoice-table .total-row td { font-weight: bold; background-color: #f2f2f2; }
        .invoice-table .label-left { text-align: right; padding-right: 15px; }
        .amount-in-words { margin-top: 20px; font-weight: bold; }
        .notes { margin-top: 20px; font-size: 12px; line-height: 1.5; }
        .signatures { display: flex; justify-content: space-around; margin-top: 50px; text-align: center; }
        .signatures div { flex: 1; }
        .signatures p { margin-bottom: 50px; } /* Space for actual signature */
        .signatures hr { border: 0; border-top: 1px solid #ccc; margin: 0 auto; width: 80%; }
        .footer-space { height: 50px; } /* Add some space at the bottom */

        /* Styles for the buttons */
        .button-container {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .button-container button,
        .button-container a {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }

        .button-container button.print-btn {
            background-color: #007bff; /* Blue */
            color: white;
        }

        .button-container a.back-btn {
            background-color: #6c757d; /* Gray */
            color: white;
        }

        /* Hide buttons when printing */
        @media print {
            .button-container {
                display: none;
            }
        }
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
        <div class="invoice-box">
    <div class="header">
        <div class="logo">
            <img src="logo1.png" alt="Logo" />
        </div>
        <div class="company-details">
            <div class="company-name">HIGHBRIDGE CREATIVE</div>
            <div class="company-info">
                AMIZAR IDHAM BIN BAHARI (RA0048607-P)<br />
                NO. 53, TINGKAT ATAS, JALAN SARAWAK<br />
                TAMAN BUKIT KUBU JAYA,<br />
                02000, KUALA PERLIS, PERLIS<br />
                TEL: 0175123135 / 0189486561
            </div>
        </div>
    </div>

    <div class="invoice-title">
        <h3>QUOTATION</h3>
        <hr class="divider" />
    </div>

    <div class="info-section">
        <div class="col" >
            <strong>TO:</strong><br />
            <?php echo nl2br(htmlspecialchars($quotation['customer_name'])) . "<br />"; ?>
            <?php echo nl2br(htmlspecialchars($quotation['customer_address'])) . "<br />"; ?>
            TEL: <?php echo htmlspecialchars($quotation['customer_phone']); ?>
        </div>
        <div class="col" style="margin-left: 10%;">
            <strong>DELIVERY TO:</strong><br />
            <?php echo nl2br(htmlspecialchars($quotation['delivery_address'])); ?>
        </div>
    </div>

    <hr class="divider" />

    <div class="details-grid">
        <div><strong>QUOTATION NO:</strong> <?php echo htmlspecialchars($quotation['invoice_no']); ?></div>
        <div><strong>DATE:</strong> <?php echo date('d/m/Y', strtotime($quotation['quote_date'])); ?></div>
        <div><strong>TERMS:</strong> <?php echo htmlspecialchars($quotation['terms']); ?></div>
        <div><strong>P.O NO:</strong> <?php echo htmlspecialchars($quotation['po_no'] ?: '-'); ?></div>
        <div><strong>D.O NO:</strong> <?php echo htmlspecialchars($quotation['do_no'] ?: '-'); ?></div>
        <div><strong>QUO NO:</strong> <?php echo htmlspecialchars($quotation['quo_no'] ?: '-'); ?></div>
        <div><strong>ATTN:</strong> <?php echo htmlspecialchars($quotation['attn'] ?: '-'); ?></div>
        <div><strong>PAGE NO:</strong> 1 / 1</div>
    </div>

    <hr class="divider" />

    <table class="invoice-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO.</th>
                <th style="width: 13%;">ITEM CODE</th>
                <th style="width: 40%;">DESCRIPTION</th>
                <th style="width: 7%;">QTY</th>
                <th style="width: 10%;">PRICE (RM)</th>
                <th style="width: 10%;">DISCOUNT(RM)</th>
                <th style="width: 15%;">TOTAL(RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php $row_num = 1; foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $row_num++; ?></td>
                        <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['discount'], 2); ?></td>
                        <td><?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No items for this quotation.</td></tr>
            <?php endif; ?>
            <?php for ($i = count($items); $i < 5; $i++): ?>
                <tr><td colspan="7" style="height: 30px"></td></tr>
            <?php endfor; ?>

            <tr class="total-row">
                <td colspan="6" class="label-left">TOTAL (RM)</td>
                <td><strong><?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="amount-in-words">
        <strong>RINGGIT MALAYSIA:</strong> <?php echo htmlspecialchars($amount_in_words); ?>
    </div>

    <hr class="divider" />

    <div class="notes">
        <p><em>*Goods sold are not returnable</em></p>
        <p>
            <strong>Payment by cheque should be made payable to:</strong><br />
            HIGHBRIDGE CREATIVE<br />
            MAYBANK, Account No: 559021608934
        </p>
    </div>

    <hr class="divider" />

    <div class="signatures">
        <div>
            <p>Authorised Signature</p>
            <br /><br /><br />
            ___________________________
        </div>
        <div>
            <p>Customer Signature & Stamp</p>
            <br /><br /><br />
            ___________________________
        </div>
    </div>

    <div class="footer-space"></div>
</div>

<div class="button-container">
    <button type="button" class="print-btn" onclick="window.print()">Print Quotation</button>
    <a href="quotation.php" class="back-btn">Back to List</a>
</div>

    </div>
    <script src="../JS/script.js"></script>
</body>

</html>