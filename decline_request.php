<?php
session_start();
require "config.php";

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM request WHERE request_id='$id'");

echo "<script>
alert('Request Declined.');
window.location='view_requests.php';
</script>";
?>
