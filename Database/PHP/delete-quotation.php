<?php
// delete.php
require_once 'config.php';

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $quotation_id = trim($_GET["id"]);

    // Prepare a delete statement
    $sql = "DELETE FROM quotations WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Set parameters
        $param_id = $quotation_id;

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Records deleted successfully. Redirect to landing page
            header("location: quotation.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);

    // Close connection
    mysqli_close($link);
} else {
    // URL doesn't contain id parameter. Redirect to error page or index.
    header("location: quotation.php"); // Or a specific error page
    exit();
}
?>