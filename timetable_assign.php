<?php
include 'timetable_common.php';

$timetableID = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

$stmt = mysqli_prepare($connect, "SELECT * FROM timetables WHERE TimetableID = ?");
mysqli_stmt_bind_param($stmt, 'i', $timetableID);
mysqli_stmt_execute($stmt);
$timetable = mysqli_stmt_get_result($stmt)->fetch_assoc();
if (!$timetable) {
    flash('ttError', 'Schedule not found.');
    header('Location: timetables.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'assign') {
    $type = $_POST['type'] ?? '';
    $ids  = $_POST['ids'] ?? [];
    if (!in_array($type, ['student', 'teacher'], true) || empty($ids)) {
        flash('ttError', 'Pick at least one ' . ($type === 'teacher' ? 'teacher' : 'student') . ' to assign.');
    } else {
        $stmt = mysqli_prepare($connect, "
            INSERT INTO timetable_assignments (TimetableID, AssigneeType, AssigneeID)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE TimetableID = VALUES(TimetableID)
        ");
        $count = 0;
        foreach ($ids as $rawID) {
            $id = (int) $rawID;
            mysqli_stmt_bind_param($stmt, 'isi', $timetableID, $type, $id);
            if (mysqli_stmt_execute($stmt)) {
                $count++;
            }
        }
        flash('ttNotice', 'This schedule is now active for ' . $count . ' ' . ($type === 'teacher' ? 'teacher(s)' : 'student(s)') . '.');
    }
    header('Location: timetable_assign.php?id=' . $timetableID);
    exit;
}

if ($action === 'unassign') {
    $assignmentID = (int) ($_POST['assignment_id'] ?? 0);
    $stmt = mysqli_prepare($connect, "DELETE FROM timetable_assignments WHERE AssignmentID = ? AND TimetableID = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $assignmentID, $timetableID);
    mysqli_stmt_execute($stmt);
    header('Location: timetable_assign.php?id=' . $timetableID);
    exit;
}

$notice = takeFlash('ttNotice');
$error  = takeFlash('ttError');

$assignedStudents = mysqli_query($connect, "
    SELECT ta.AssignmentID, st.StudentID, st.StudentName
    FROM timetable_assignments ta
    JOIN students st ON ta.AssigneeID = st.StudentID
    WHERE ta.TimetableID = $timetableID AND ta.AssigneeType = 'student'
    ORDER BY st.StudentName
");
$assignedTeachers = mysqli_query($connect, "
    SELECT ta.AssignmentID, t.TeacherID, t.TeacherName
    FROM timetable_assignments ta
    JOIN teachers t ON ta.AssigneeID = t.TeacherID
    WHERE ta.TimetableID = $timetableID AND ta.AssigneeType = 'teacher'
    ORDER BY t.TeacherName
");
$availableStudents = mysqli_query($connect, "
    SELECT StudentID, StudentName FROM students
    WHERE StudentID NOT IN (
        SELECT AssigneeID FROM timetable_assignments WHERE TimetableID = $timetableID AND AssigneeType = 'student'
    )
    ORDER BY StudentName
");
$availableTeachers = mysqli_query($connect, "
    SELECT TeacherID, TeacherName FROM teachers
    WHERE TeacherID NOT IN (
        SELECT AssigneeID FROM timetable_assignments WHERE TimetableID = $timetableID AND AssigneeType = 'teacher'
    )
    ORDER BY TeacherName
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign | <?= htmlspecialchars($timetable['Name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style_timetable.css">
</head>
<body>

<div class="header-row">
  <h1>Assign: <?= htmlspecialchars($timetable['Name']) ?></h1>
  <a href="timetable_builder.php?id=<?= $timetableID ?>" class="enroll-link">← Edit classes</a>
</div>
<p class="muted">Assigning replaces a person's current active timetable — each student or teacher only ever has one at a time.</p>

<?php if ($notice): ?><p class="notice"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<div class="two-col">

  <div class="panel">
    <h2>Students</h2>
    <h3 class="muted-h">Currently assigned</h3>
    <?php if (mysqli_num_rows($assignedStudents) === 0): ?>
      <p class="muted small">No students yet.</p>
    <?php else: ?>
      <ul class="assign-list">
        <?php while ($s = mysqli_fetch_assoc($assignedStudents)): ?>
        <li><?= htmlspecialchars($s['StudentName']) ?>
          <form method="POST" class="inline">
            <input type="hidden" name="action" value="unassign">
            <input type="hidden" name="id" value="<?= $timetableID ?>">
            <input type="hidden" name="assignment_id" value="<?= $s['AssignmentID'] ?>">
            <button type="submit" class="link-btn danger">Remove</button>
          </form>
        </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>

    <h3 class="muted-h">Add students</h3>
    <input type="text" class="filter-box" placeholder="Search students…" onkeyup="filterList(this,'stu-list')">
    <form method="POST">
      <input type="hidden" name="action" value="assign">
      <input type="hidden" name="id" value="<?= $timetableID ?>">
      <input type="hidden" name="type" value="student">
      <ul class="pick-list" id="stu-list">
        <?php while ($s = mysqli_fetch_assoc($availableStudents)): ?>
        <li><label><input type="checkbox" name="ids[]" value="<?= $s['StudentID'] ?>"> <?= htmlspecialchars($s['StudentName']) ?></label></li>
        <?php endwhile; ?>
      </ul>
      <button type="submit">Assign selected students</button>
    </form>
  </div>

  <div class="panel">
    <h2>Teachers</h2>
    <h3 class="muted-h">Currently assigned</h3>
    <?php if (mysqli_num_rows($assignedTeachers) === 0): ?>
      <p class="muted small">No teachers yet.</p>
    <?php else: ?>
      <ul class="assign-list">
        <?php while ($t = mysqli_fetch_assoc($assignedTeachers)): ?>
        <li><?= htmlspecialchars($t['TeacherName']) ?>
          <form method="POST" class="inline">
            <input type="hidden" name="action" value="unassign">
            <input type="hidden" name="id" value="<?= $timetableID ?>">
            <input type="hidden" name="assignment_id" value="<?= $t['AssignmentID'] ?>">
            <button type="submit" class="link-btn danger">Remove</button>
          </form>
        </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>

    <h3 class="muted-h">Add teachers</h3>
    <input type="text" class="filter-box" placeholder="Search teachers…" onkeyup="filterList(this,'tea-list')">
    <form method="POST">
      <input type="hidden" name="action" value="assign">
      <input type="hidden" name="id" value="<?= $timetableID ?>">
      <input type="hidden" name="type" value="teacher">
      <ul class="pick-list" id="tea-list">
        <?php while ($t = mysqli_fetch_assoc($availableTeachers)): ?>
        <li><label><input type="checkbox" name="ids[]" value="<?= $t['TeacherID'] ?>"> <?= htmlspecialchars($t['TeacherName']) ?></label></li>
        <?php endwhile; ?>
      </ul>
      <button type="submit">Assign selected teachers</button>
    </form>
  </div>

</div>

<script>
function filterList(input, listId) {
  const filter = input.value.toLowerCase();
  document.querySelectorAll('#' + listId + ' li').forEach(function (li) {
    li.style.display = li.textContent.toLowerCase().includes(filter) ? '' : 'none';
  });
}
</script>
</body>
</html>
