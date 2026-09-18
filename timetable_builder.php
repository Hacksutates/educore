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

if ($action === 'add_slot') {
    $day       = $_POST['day'] ?? '';
    $start     = $_POST['time_start'] ?? '';
    $end       = $_POST['time_end'] ?? '';
    $subjectID = (int) ($_POST['subject_id'] ?? 0);
    $teacherID = (int) ($_POST['teacher_id'] ?? 0);
    $roomID    = (int) ($_POST['classroom_id'] ?? 0);

    $subjectMismatch = null;
    if ($subjectID && $teacherID) {
        $teacherSubjectMap = tt_teacher_subject_map($connect);
        if (isset($teacherSubjectMap[$teacherID]) && $teacherSubjectMap[$teacherID] !== $subjectID) {
            $stmt = mysqli_prepare($connect, "SELECT TeacherName FROM teachers WHERE TeacherID = ?");
            mysqli_stmt_bind_param($stmt, 'i', $teacherID);
            mysqli_stmt_execute($stmt);
            $teacherRow = mysqli_stmt_get_result($stmt)->fetch_assoc();

            $stmt = mysqli_prepare($connect, "SELECT SubjectName FROM subjects WHERE SubjectID = ?");
            mysqli_stmt_bind_param($stmt, 'i', $teacherSubjectMap[$teacherID]);
            mysqli_stmt_execute($stmt);
            $subjectRow = mysqli_stmt_get_result($stmt)->fetch_assoc();

            $subjectMismatch = ($teacherRow['TeacherName'] ?? 'That teacher') . ' teaches '
                . ($subjectRow['SubjectName'] ?? 'a different subject') . ', not this subject.';
        }
    }

    if (!in_array($day, $days, true) || !$start || !$end || !$subjectID || !$teacherID || !$roomID) {
        flash('ttError', 'Please fill in every field to add a class.');
    } elseif ($start >= $end) {
        flash('ttError', 'The end time must be after the start time.');
    } elseif ($subjectMismatch) {
        flash('ttError', $subjectMismatch);
    } else {
        $stmt = mysqli_prepare($connect, "
            INSERT INTO timetable_slots (TimetableID, SubjectID, TeacherID, ClassroomID, DayOfWeek, TimeStart, TimeEnd)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'iiiisss', $timetableID, $subjectID, $teacherID, $roomID, $day, $start, $end);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_query($connect, "UPDATE timetables SET UpdatedAt = NOW() WHERE TimetableID = " . $timetableID);
            flash('ttNotice', 'Class added.');
        } else {
            flash('ttError', 'There is already a class in that day/time slot on this schedule.');
        }
    }
    header('Location: timetable_builder.php?id=' . $timetableID);
    exit;
}

if ($action === 'delete_slot') {
    $slotID = (int) ($_POST['slot_id'] ?? 0);
    $stmt = mysqli_prepare($connect, "DELETE FROM timetable_slots WHERE SlotID = ? AND TimetableID = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $slotID, $timetableID);
    mysqli_stmt_execute($stmt);
    mysqli_query($connect, "UPDATE timetables SET UpdatedAt = NOW() WHERE TimetableID = " . $timetableID);
    header('Location: timetable_builder.php?id=' . $timetableID);
    exit;
}

if ($action === 'rename') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = mysqli_prepare($connect, "UPDATE timetables SET Name = ?, UpdatedAt = NOW() WHERE TimetableID = ?");
        mysqli_stmt_bind_param($stmt, 'si', $name, $timetableID);
        mysqli_stmt_execute($stmt);
    }
    header('Location: timetable_builder.php?id=' . $timetableID);
    exit;
}

$notice = takeFlash('ttNotice');
$error  = takeFlash('ttError');

$subjects   = mysqli_query($connect, "SELECT SubjectID, SubjectName FROM subjects ORDER BY SubjectName");
$teachers   = mysqli_query($connect, "SELECT TeacherID, TeacherName FROM teachers ORDER BY TeacherName");
$classrooms = mysqli_query($connect, "SELECT ClassroomID, ClassroomName FROM classrooms ORDER BY ClassroomName");
$teacherSubjectMap    = tt_teacher_subject_map($connect);
$teacherSubjectColumn = tt_has_teacher_subject_column($connect);

$mismatchSelect = $teacherSubjectColumn
    ? "(t.SubjectID IS NOT NULL AND t.SubjectID <> ts.SubjectID) AS Mismatch"
    : "0 AS Mismatch";
$slotsResult = mysqli_query($connect, "
    SELECT ts.SlotID, ts.DayOfWeek, ts.TimeStart, ts.TimeEnd,
           s.SubjectName, t.TeacherName, c.ClassroomName, $mismatchSelect
    FROM timetable_slots ts
    JOIN subjects s ON ts.SubjectID = s.SubjectID
    JOIN teachers t ON ts.TeacherID = t.TeacherID
    JOIN classrooms c ON ts.ClassroomID = c.ClassroomID
    WHERE ts.TimetableID = $timetableID
    ORDER BY FIELD(ts.DayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday'), ts.TimeStart
");
$byDay = [];
while ($row = mysqli_fetch_assoc($slotsResult)) {
    $byDay[$row['DayOfWeek']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($timetable['Name']) ?> | Builder</title>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style_timetable.css">
</head>
<body>

<div class="header-row">
  <h1><?= htmlspecialchars($timetable['Name']) ?></h1>
  <a href="timetables.php" class="enroll-link">← All schedules</a>
</div>
<p class="sub">
  Status: <span class="badge badge-<?= $timetable['Status'] ?>"><?= ucfirst($timetable['Status']) ?></span>
  · <a href="timetable_assign.php?id=<?= $timetableID ?>">Assign this schedule →</a>
</p>

<div class="panel generate-cta">
  <div>
    <h2>Generate it automatically</h2>
    <p class="muted">Fill the whole week in one click — free teachers and rooms are picked for you.</p>
  </div>
  <a class="big-btn-link" href="timetable_generate.php?id=<?= $timetableID ?>">Auto-generate →</a>
</div>

<?php if ($notice): ?><p class="notice"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<div class="panel">
  <h2>Rename</h2>
  <form method="POST" class="inline-form">
    <input type="hidden" name="action" value="rename">
    <input type="hidden" name="id" value="<?= $timetableID ?>">
    <input type="text" name="name" value="<?= htmlspecialchars($timetable['Name']) ?>" required>
    <button type="submit">Save name</button>
  </form>
</div>

<div class="panel">
  <h2>Add a class</h2>
  <form method="POST" class="grid-form">
    <input type="hidden" name="action" value="add_slot">
    <input type="hidden" name="id" value="<?= $timetableID ?>">
    <label>Day
      <select name="day" required>
        <?php foreach ($days as $d): ?><option value="<?= $d ?>"><?= $d ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Start <input type="time" name="time_start" required></label>
    <label>End <input type="time" name="time_end" required></label>
    <label>Subject
      <select name="subject_id" id="subject-select" required>
        <option value="">Select…</option>
        <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
          <option value="<?= $s['SubjectID'] ?>"><?= htmlspecialchars($s['SubjectName']) ?></option>
        <?php endwhile; ?>
      </select>
    </label>
    <label>Teacher
      <select name="teacher_id" id="teacher-select" required>
        <option value="">Select…</option>
        <?php
        mysqli_data_seek($teachers, 0);
        while ($t = mysqli_fetch_assoc($teachers)):
          $tSubject = $teacherSubjectMap[(int) $t['TeacherID']] ?? '';
        ?>
          <option value="<?= $t['TeacherID'] ?>" data-subject="<?= $tSubject ?>"><?= htmlspecialchars($t['TeacherName']) ?></option>
        <?php endwhile; ?>
      </select>
      <?php if ($teacherSubjectColumn): ?>
        <span class="muted small">Narrows to teachers of the chosen subject.</span>
      <?php endif; ?>
    </label>
    <label>Room
      <select name="classroom_id" required>
        <option value="">Select…</option>
        <?php while ($c = mysqli_fetch_assoc($classrooms)): ?>
          <option value="<?= $c['ClassroomID'] ?>"><?= htmlspecialchars($c['ClassroomName']) ?></option>
        <?php endwhile; ?>
      </select>
    </label>
    <button type="submit">Add class</button>
  </form>
</div>

<div class="panel">
  <h2>This week</h2>
  <?php if (empty($byDay)): ?>
    <p class="muted">No classes yet — add the first one above.</p>
  <?php endif; ?>
  <?php foreach ($days as $day): if (empty($byDay[$day])) continue; ?>
    <h3><?= $day ?></h3>
    <table class="tt-table">
      <tr><th>Time</th><th>Subject</th><th>Teacher</th><th>Room</th><th></th></tr>
      <?php foreach ($byDay[$day] as $slot): ?>
      <tr>
        <td><?= substr($slot['TimeStart'], 0, 5) ?>–<?= substr($slot['TimeEnd'], 0, 5) ?></td>
        <td><?= htmlspecialchars($slot['SubjectName']) ?></td>
        <td>
          <?= htmlspecialchars($slot['TeacherName']) ?>
          <?php if (!empty($slot['Mismatch'])): ?>
            <span class="badge badge-mismatch" title="This teacher's assigned subject doesn't match this class.">⚠ wrong subject</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($slot['ClassroomName']) ?></td>
        <td>
          <form method="POST" class="inline" onsubmit="return confirm('Remove this class from the schedule?');">
            <input type="hidden" name="action" value="delete_slot">
            <input type="hidden" name="id" value="<?= $timetableID ?>">
            <input type="hidden" name="slot_id" value="<?= $slot['SlotID'] ?>">
            <button type="submit" class="link-btn danger">Remove</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endforeach; ?>
</div>

<script>
// Progressive enhancement: once a subject is picked, hide teachers who
// aren't assigned to it (a teacher with no subject assigned stays visible
// for every subject). The server still enforces this even with JS off.
(function () {
  var subjectSelect = document.getElementById('subject-select');
  var teacherSelect = document.getElementById('teacher-select');
  if (!subjectSelect || !teacherSelect) return;

  function applyFilter() {
    var subjectID = subjectSelect.value;
    var options = teacherSelect.querySelectorAll('option[data-subject]');
    var currentStillValid = false;

    options.forEach(function (opt) {
      var subj = opt.getAttribute('data-subject');
      var show = !subjectID || subj === '' || subj === subjectID;
      opt.hidden = !show;
      if (opt.selected && show) currentStillValid = true;
    });

    if (!currentStillValid) {
      teacherSelect.value = '';
    }
  }

  subjectSelect.addEventListener('change', applyFilter);
  applyFilter();
})();
</script>

</body>
</html>