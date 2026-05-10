<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home - Blood Bank</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-bg">
    
 <!-- NAVBAR -->
<nav class="navbar">
    <ul>
        <!-- DONOR MAIN MENU -->
        <li class="dropdown">
            <a href="#">Donor ▾</a>
            <div class="dropdown-menu">
                <a href="add_donor.php">➕ Add New Donor</a>
                <a href="update_donor.php">✏ Update Details</a>
                <a href="all_donor.php">📋 All Donor Details</a>
            </div>
        </li>

        <li><a href="search_donor.php">Search Donor</a></li>

        <!-- STOCK -->
        <li class="dropdown">
            <a href="#">Stock Management ▾</a>
            <div class="dropdown-menu">
                <a href="stock_increase.php">Increase Stock</a>
                <a href="stock_decrease.php">Decrease Stock</a>
                <a href="details.php">Stock Details</a>
            </div>
        </li>

        <!-- View Requests -->
        <li><a href="view_requests.php">🩸 View Requests</a></li>
       

        <li><a href="request_blood.php">Blood Requests</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
  
</nav>



</body>
</html>
