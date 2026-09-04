<?php
session_start();
include 'connection.php';

// Получаем фильтр
$period_type = $_POST['period_type'] ?? 'month';
$month = $_POST['month'] ?? '';

// Условие
$where = "1";

if ($period_type == 'month' && !empty($month)) {
    $where = "r.PeriodType = 'month' AND r.PeriodValue = '$month'";
}
elseif ($period_type == 'sem1') {
    $where = "r.PeriodType = 'sem1'";
}
elseif ($period_type == 'sem2') {
    $where = "r.PeriodType = 'sem2'";
}

// СНАЧАЛА создаём query
$query = mysqli_query($connect, "
    SELECT r.*,
           s.supervisor_name,
           l.LessonName
    FROM reports r
    JOIN supervisors s ON r.supervisor_id = s.supervisor_id
    JOIN extracurricular_lessons l ON r.LessonID = l.LessonID
    WHERE $where
    ORDER BY r.CreatedAt DESC
");

// проверка
if (!$query) {
    die("SQL ERROR: " . mysqli_error($connect));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style5.css">
<meta charset="utf-8">
<title>Admin Dashboard</title>
</head>

<body>

<h1>Admin | Reports Dashboard</h1>

<form method="POST">
    <select name="period_type">
        <option value="month" <?= ($period_type=='month')?'selected':'' ?>>Month</option>
        <option value="sem1" <?= ($period_type=='sem1')?'selected':'' ?>>Semester 1</option>
        <option value="sem2" <?= ($period_type=='sem2')?'selected':'' ?>>Semester 2</option>
    </select>

    <input type="month" name="month" value="<?= $month ?>">
    <input type="submit" value="Load">
</form>

<br>

<table>
<tr>
    <th>Lesson</th>
    <th>Supervisor</th>
    <th>Period</th>
    <th>Attested</th>
    <th>Not Attested</th>
    <th>Comment</th>
    <th>Date</th>
    <th>Actions</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($query)): ?>
<tr>

<td><?= $row['LessonName'] ?></td>
<td><?= $row['supervisor_name'] ?></td>

<td>
<?php
if ($row['PeriodType'] == 'month') {
    echo $row['PeriodValue'];
} else {
    echo strtoupper($row['PeriodType']);
}
?>
</td>

<td><?= ($row['PeriodType'] != 'month') ? $row['Attested'] : '-' ?></td>
<td><?= ($row['PeriodType'] != 'month') ? $row['NotAttested'] : '-' ?></td>

<td><?= $row['Comment'] ?></td>
<td><?= date('d.m.Y', strtotime($row['CreatedAt'])) ?></td>

<td>
    <a href="delete_report.php?id=<?= $row['ReportID'] ?>">Delete</a>
    |
    <a href="edit_report.php?id=<?= $row['ReportID'] ?>">Edit</a>
</td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
