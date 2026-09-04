<?php
include 'connection.php';

$id = $_GET['id'];

mysqli_query($connect, "DELETE FROM reports WHERE ReportID='$id'");

header("Location: admin.php");
?>
