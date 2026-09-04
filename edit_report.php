<?php
include 'connection.php';

$id = $_GET['id'];

$query = mysqli_query($connect, "SELECT * FROM reports WHERE ReportID='$id'");
$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style3.css">
    <title></title>
  </head>
  <style>
  input[type="text"],
  input[type="number"] {
     font-family: Quicksand;
      width: 228px;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
  }
  button {
      font-family: Quicksand;
      background-color: #4a2fa5;
      text-align: center;
      width: 70px;
      padding: 7px;
      margin: 10px;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      white-space: normal;
  }

  button:hover {
      background: #5730cc;
  }

</style>
  <body>

<form method="POST" action="update_report.php">
    <input type="hidden" name="id" value="<?= $data['ReportID'] ?>">

    Attested:
    <input type="number" name="attested" value="<?= $data['Attested'] ?>"><br>

    Not Attested:
    <input type="number" name="not_attested" value="<?= $data['NotAttested'] ?>"><br>

    Comment:
    <input type="text" name="comment" value="<?= $data['Comment'] ?>"><br>

   <button type="submit">Save</button>
</form>
</body>
