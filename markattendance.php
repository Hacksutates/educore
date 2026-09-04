<?php
include 'mark.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="style6.css">
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>School Management System</title>
  </head>
  <style>


  .table-scroll {
      width: 100%;
      overflow-x: auto;
  }
  input[type="text"]  {
   font-family: Quicksand, sans-serif;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  .success {
   color: #2e8b57;
   font-weight: 500;
   margin-bottom: 10px;
  }

  </style>
    <body>

<form method = "post">
  <div class="header-row">
        <h1>Teacher |</h1>
        <a href="scheduleteacher.php" class="enroll-link">My Schedule</a>
        <h1>| Mark Attendance</h1>
</div>
        <?php if ($enrollSuccess): ?>
          <p class="success"><?= $enrollSuccess ?></p>
      <?php endif; ?>
    </form>

<!-- FILTER FORM -->
<div class="filters">
<form method="post" action="">

    <select name="lesson_id">
        <option value="">Select lesson</option>
        <option value="4">Robotics</option>
        <option value="1">Dance</option>
        <option value = "2">Debate club</option>
        <option value = "3">Chess club</option>
    </select>

    <input type="date" name="datemark" value="2026-02-02">
    <input type="text" name="studentname" placeholder = "Student's Name">
    <input type="submit" name="lessonsearch" value="Find a Lesson">
    <input type="hidden" name="enrollmentID">


</form>
</div>

<!-- STUDENT LIST -->
<?php if ($queryMark) { ?>

<form method="post" action="">

<input type="hidden" name="lesson_id" value="<?= $idlesson ?>">
<input type="hidden" name="datemark" value="<?= $date ?>">
<input type="hidden" name="enrollmentID" value="<?= $enrollmentID ?>">

<div class = "table-scroll">
<table border="1">
<tr>
  <th>Student Name</th>
  <th>Student ID</th>
  <th>Lesson Name</th>
  <th>Date</th>
  <th>Attendance</th>
</tr>

<?php if (!empty($arr_for_sort)) { ?>

  <?php foreach ($result as $row) { ?>
<tr>
  <td><?= $row['StudentName'] ?></td>
  <td><?= $row['StudentID'] ?></td>
  <td><?= $row['LessonName'] ?></td>
  <td><?= $date ?></td>
  <td>
    <input type="checkbox"
           name="attendance[<?= $row['EnrollmentID'] ?>]"
           value="1">
  </td>
</tr>
<?php } ?>
<?php } ?>

</table>
</div>
<br>
<input type="submit" name="save_attendance" value="Submit Attendance">
<input type="hidden" name="enrollmentID" value="<?= $enrollmentID ?>">
<input type="hidden" name="scheduleID" value="<?= $scheduleID ?>">
</form>

<?php } ?>

</body>
</html>
