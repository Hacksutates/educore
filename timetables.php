<?php
include 'timetable_common.php';

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        flash('ttError', 'Please give the schedule a name.');
        header('Location: timetables.php');
        exit;
    }
    $stmt = mysqli_prepare($connect, "INSERT INTO timetables (Name, Status, CreatedBy) VALUES (?, 'draft', ?)");
    mysqli_stmt_bind_param($stmt, 'si', $name, $supervisorID);
    mysqli_stmt_execute($stmt);
    $newID = mysqli_insert_id($connect);
    header('Location: timetable_builder.php?id=' . $newID);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($connect, "DELETE FROM timetables WHERE TimetableID = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    flash('ttNotice', 'Schedule deleted.');
    header('Location: timetables.php');
    exit;
}

if ($action === 'toggle_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($connect, "UPDATE timetables SET Status = IF(Status='draft','published','draft') WHERE TimetableID = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: timetables.php');
    exit;
}

$notice = takeFlash('ttNotice');
$error  = takeFlash('ttError');

$list = mysqli_query($connect, "
    SELECT t.TimetableID, t.Name, t.Status, t.UpdatedAt,
           (SELECT COUNT(*) FROM timetable_slots ts WHERE ts.TimetableID = t.TimetableID) AS SlotCount,
           (SELECT COUNT(*) FROM timetable_assignments ta WHERE ta.TimetableID = t.TimetableID AND ta.AssigneeType = 'student') AS StudentCount,
           (SELECT COUNT(*) FROM timetable_assignments ta WHERE ta.TimetableID = t.TimetableID AND ta.AssigneeType = 'teacher') AS TeacherCount
    FROM timetables t
    ORDER BY t.UpdatedAt DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedules | Supervisor</title>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style_timetable.css">
</head>
<body>

<div class="header-row">
  <h1>Supervisor | Schedules</h1>
  <a href="main.html" class="enroll-link">← Back to Main</a>
</div>

<?php if ($notice): ?><p class="notice"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<div class="panel">
  <h2>Create a new schedule</h2>
  <form method="POST" class="inline-form">
    <input type="hidden" name="action" value="create">
    <input type="text" name="name" placeholder="e.g. Grade 10A – Term 1" required>
    <button type="submit">Create &amp; build</button>
  </form>
</div>

<div class="panel">
  <h2>Existing schedules</h2>
  <?php if (mysqli_num_rows($list) === 0): ?>
    <p class="muted">No schedules yet — create your first one above.</p>
  <?php else: ?>
  <table class="tt-table">
    <tr>
      <th>Name</th><th>Status</th><th>Classes</th><th>Students</th><th>Teachers</th><th>Last updated</th><th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($list)): ?>
    <tr>
      <td><?= htmlspecialchars($row['Name']) ?></td>
      <td><span class="badge badge-<?= $row['Status'] ?>"><?= ucfirst($row['Status']) ?></span></td>
      <td><?= (int) $row['SlotCount'] ?></td>
      <td><?= (int) $row['StudentCount'] ?></td>
      <td><?= (int) $row['TeacherCount'] ?></td>
      <td><?= date('d M Y, H:i', strtotime($row['UpdatedAt'])) ?></td>
      <td class="actions">
        <a href="timetable_builder.php?id=<?= (int) $row['TimetableID'] ?>">Edit</a>
        <a href="timetable_generate.php?id=<?= (int) $row['TimetableID'] ?>">Generate</a>
        <a href="timetable_assign.php?id=<?= (int) $row['TimetableID'] ?>">Assign</a>
        <form method="POST" class="inline">
          <input type="hidden" name="action" value="toggle_status">
          <input type="hidden" name="id" value="<?= (int) $row['TimetableID'] ?>">
          <button type="submit" class="link-btn"><?= $row['Status'] === 'draft' ? 'Publish' : 'Unpublish' ?></button>
        </form>
        <form method="POST" class="inline" onsubmit="return confirm('Delete this schedule? This also removes it from everyone it is assigned to.');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $row['TimetableID'] ?>">
          <button type="submit" class="link-btn danger">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php endif; ?>
</div>

</body>
</html>
