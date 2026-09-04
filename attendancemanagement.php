<?php

include 'connection.php';
$dates = $dates ?? [];
$students = $students ?? [];
$attendanceMap = $attendanceMap ?? [];
$period_type = $period_type ?? [];

$reportSuccess = $_SESSION['reportSuccess'] ?? '';
unset($_SESSION['reportSuccess']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style6.css">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supervisor</title>
<style>
.table-scroll {
    width: 100%;
    overflow-x: auto;
}

.table-two {
   width: 100%;
   table-layout: auto; /* важно */
   border-collapse: collapse;
}

.table-two th,
.table-two td {
    text-align: center;
    padding: 8px;
}


.table-two td:first-child,
.table-two th:first-child {
    width: 200px;
    position: sticky;
    left: 0;
    background: white;
}


th:last-child {
    width: 100px;
}

th:nth-last-child(2) {
    width: 100px;
}

.side-panel {
    width: 260px;
    min-width: 260px;
}
.dashboard {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      width: 100%; /* ВАЖНО */

}
.ai {
  font-family: 'Quicksand', sans-serif;
  background-color: #4a2fa5;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
}
</style>
</head>

<body>
<div class = "container">
  <h1>Supervisor | Attendance Management Dashboard</h1>
  <?php if ($reportSuccess): ?>
    <p style="color: green; font-weight: bold;">
        <?= $reportSuccess ?>
    </p>
<?php endif; ?>

  <div class="filters-attendance">
  <form method="POST" action="attendance.php" class = "filters-form">

  <select name="lesson_id">
      <option value="">Select lesson</option>
      <option value="4">Robotics</option>
      <option value="2">Debate club</option>
  </select>

  <select name="period_type">
      <option value="month">Month</option>
      <option value="sem1">Semester 1</option>
      <option value="sem2">Semester 2</option>
  </select>

  <input type="month" name="month">
  <input type="submit" value="Load Attendance">

  </form>
  </div>

  <?php if (!empty($students)): ?>

<!-- REPORT FORM -->
<form method="POST" action="report.php">

<input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
<input type="hidden" name="period_type" value="<?= $period_type ?>">
<input type="hidden" name="month" value="<?= $month ?>">

<div class="dashboard">

<div class="table-scroll">
<table class="table-two">

<tr>
    <th>Student</th>

    <?php foreach ($dates as $d): ?>
        <th><?= date('d.m', strtotime($d)) ?></th>
    <?php endforeach; ?>

    <?php if ($period_type != 'month'): ?>
       <th>Attendance   </th>
       <th>Attested</th>
    <?php endif; ?>
</tr>

<?php foreach ($students as $st): ?>
<tr>

    <td><?= $st['StudentName'] ?></td>

    <?php foreach ($dates as $d): ?>
        <td>
            <?php
            $enrollID = $st['EnrollmentID'];

            if (isset($attendanceMap[$enrollID][$d])) {
                            echo ($attendanceMap[$enrollID][$d] == 'Present')
                                ? "<span style='color:green'>✔️</span>"
                                : "<span style='color:red'>✖️</span>";
                        } else {
                            echo "-";
                        }
            ?>
        </td>
    <?php endforeach; ?>

    <?php if ($period_type != 'month'): ?>
      <?php
       $enrollID = $st['EnrollmentID'];

       $total = $attendanceStats[$enrollID]['total'] ?? 0;
       $present = $attendanceStats[$enrollID]['present'] ?? 0;

       $percent = ($total > 0) ? round(($present / $total) * 100) : 0;
       ?>

       <td><b><?= $percent ?>%</b></td>
       <td>
         <input type="checkbox"
    name="attested[]"
    value="<?= $st['EnrollmentID'] ?>"
    <?= in_array($st['EnrollmentID'], $checkedStudents ?? []) ? 'checked' : '' ?>>
               </td>

    <?php endif; ?>

</tr>
<input type="hidden" name="total_students" value="<?= count($students) ?>">
<input type="hidden" name="total_lessons" value="<?= count($dates) ?>">
<?php endforeach; ?>

</table>
</div>

<!-- RIGHT PANEL -->
<div class="side-panel">

<div class="summary-box">
<h3>Summary</h3>

<p>Total Students: <?= count($students) ?></p>
<p>Total Lessons: <?= count($dates) ?></p>

</div>

<div class="comment-box">
<label>Supervisor Comment</label>
<textarea id="commentBox" name="comment" placeholder="Write conclusion..."></textarea>
<br><br>
<button type="button" class = "ai" onclick="generateAIComment()">Generate AI Comment</button>
<br><br>
<script>
function generateAIComment() {
    let studentsData = <?= json_encode($attendanceStats) ?>;

  fetch('/www/ai_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(studentsData)
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('commentBox').value = data;
    });
}
</script>
<input type="submit" name = "report" value="Send Report to Admin">
</div>

</div>

</div>

</form>

<?php endif; ?>

<div class="footer">
<a href="main.html">← Back to Main</a>
</div>
</div>
</body>
</html>
