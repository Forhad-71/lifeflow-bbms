<?php
require "includes/auth.php";
require_admin();
require "config.php";

$id = $_GET['id'];

$sql = "SELECT * FROM request WHERE request_id='$id'";
$result = mysqli_query($conn, $sql);
$request = mysqli_fetch_assoc($result);

$group = $request['blood_group'];
$units_needed = $request['units_needed'];

/* Check current stock */
$stock_sql = mysqli_query($conn, "SELECT units FROM stock WHERE blood_group='$group'");
$stock_row = mysqli_fetch_assoc($stock_sql);
$current_units = $stock_row['units'];

if($units_needed <= $current_units){
    // Deduct stock
    mysqli_query($conn, "UPDATE stock SET units = units - $units_needed WHERE blood_group='$group'");
    
    // Delete request
    mysqli_query($conn, "DELETE FROM request WHERE request_id='$id'");

    $msg = "✔ Request approved. Stock updated.";
} else {
    $msg = "❌ Not enough stock!";
}

echo "<script>alert('$msg'); window.location='view_requests.php';</script>";
?>
