<?php
include 'connection.php';

$id = $_POST['id'];
$attested = $_POST['attested'];
$not_attested = $_POST['not_attested'];
$comment = $_POST['comment'];

mysqli_query($connect, "
    UPDATE reports
    SET Attested='$attested',
        NotAttested='$not_attested',
        Comment='$comment'
    WHERE ReportID='$id'
");

header("Location: admin.php");
?>
