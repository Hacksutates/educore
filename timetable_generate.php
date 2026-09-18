<?php
include 'timetable_common.php';
include 'timetable_engine.php';

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

// --- Reference data ---------------------------------------------------
/** Read an id => name lookup table, tolerating a missing table. */
function ttg_lookup($connect, string $sql, string $idCol, string $nameCol): array
{
    $out = [];
    $res = tt_query($connect, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $out[(int) $row[$idCol]] = $row[$nameCol];
        }
    }
    return $out;
}

$allSubjects = ttg_lookup($connect,
    "SELECT SubjectID, SubjectName FROM subjects ORDER BY SubjectName",
    'SubjectID', 'SubjectName');

$allTeachers = ttg_lookup($connect,
    "SELECT TeacherID, TeacherName FROM teachers ORDER BY TeacherName",
    'TeacherID', 'TeacherName');

$allRooms = ttg_lookup($connect,
    "SELECT ClassroomID, ClassroomName FROM classrooms ORDER BY ClassroomName",
    'ClassroomID', 'ClassroomName');

// Which teacher teaches which subject, so the generator (and the "Teacher"
// dropdown below) never assigns someone outside their own subject.
$teacherSubjectMap    = tt_teacher_subject_map($connect);
$teacherSubjectColumn = tt_has_teacher_subject_column($connect);

// --- Defaults, so "Generate" works with zero configuration ------------
$defaults = [
    'periods_per_day' => 6,
    'start_time'      => '08:30',
    'lesson_minutes'  => 45,
    'break_minutes'   => 10,
    'days'            => $days,
    'one_room'        => 1,
    'replace'         => 1,
];

$missing = [];
if (!$allSubjects) $missing[] = 'subjects';
if (!$allTeachers) $missing[] = 'teachers';
if (!$allRooms)    $missing[] = 'classrooms';

// --- Handle generation ------------------------------------------------
if (($_POST['action'] ?? '') === 'generate' && !$missing) {

    $periodsPerDay = max(1, min(12, (int) ($_POST['periods_per_day'] ?? $defaults['periods_per_day'])));
    $startTime     = $_POST['start_time'] ?? $defaults['start_time'];
    $lessonMinutes = max(15, min(180, (int) ($_POST['lesson_minutes'] ?? $defaults['lesson_minutes'])));
    $breakMinutes  = max(0,  min(60,  (int) ($_POST['break_minutes']  ?? $defaults['break_minutes'])));
    $oneRoom       = !empty($_POST['one_room']);
    $replace       = !empty($_POST['replace']);

    $chosenDays = $_POST['days'] ?? $defaults['days'];
    $chosenDays = array_values(array_intersect($days, (array) $chosenDays));
    if (!$chosenDays) {
        $chosenDays = $days;
    }

    $capacity = count($chosenDays) * $periodsPerDay;

    // Which subjects, and how many lessons a week each.
    // No selection at all => every subject, spread evenly over the week.
    $postedCounts   = $_POST['subject_count']   ?? [];
    $postedTeachers = $_POST['subject_teacher'] ?? [];

    $demand = [];
    foreach ($allSubjects as $subjectID => $subjectName) {
        $count = isset($postedCounts[$subjectID]) ? (int) $postedCounts[$subjectID] : -1;
        if ($count < 0) {
            $count = 0;   // form not filled in for this subject
        }
        if ($count > 0) {
            // Only teachers assigned to this subject (plus any generalist
            // teacher with no subject assigned) are ever eligible — a
            // teacher who is assigned to a *different* subject is never
            // picked, even by "Any teacher".
            $qualified = tt_teachers_eligible_for_subject($teacherSubjectMap, (int) $subjectID, array_keys($allTeachers));

            $teacherChoice = (int) ($postedTeachers[$subjectID] ?? 0);
            $eligible = ($teacherChoice && in_array($teacherChoice, $qualified, true))
                ? [$teacherChoice]
                : $qualified;

            $demand[$subjectID] = [
                'count'    => $count,
                'teachers' => $eligible,
                'name'     => $subjectName,
            ];
        }
    }

    // Nothing requested: auto-fill the whole week evenly across all subjects.
    if (!$demand) {
        $subjectIDs = array_keys($allSubjects);
        $perSubject = intdiv($capacity, max(1, count($subjectIDs)));
        $remainder  = $capacity - $perSubject * count($subjectIDs);

        foreach ($subjectIDs as $i => $subjectID) {
            $count = $perSubject + ($i < $remainder ? 1 : 0);
            if ($count > 0) {
                $demand[$subjectID] = [
                    'count'    => $count,
                    'teachers' => tt_teachers_eligible_for_subject($teacherSubjectMap, (int) $subjectID, array_keys($allTeachers)),
                    'name'     => $allSubjects[$subjectID],
                ];
            }
        }
    }

    $requested = array_sum(array_column($demand, 'count'));

    if ($requested === 0) {
        flash('ttError', 'Set at least one subject to more than 0 lessons a week.');
    } elseif ($requested > $capacity) {
        flash('ttError', "You asked for $requested lessons but the week only has $capacity periods. "
                       . "Lower some counts or add periods per day.");
    } else {
        $periods = ttg_build_periods($startTime, $lessonMinutes, $breakMinutes, $periodsPerDay);
        $busy    = ttg_load_external_bookings($connect, $timetableID);

        $result = ttg_generate($demand, $chosenDays, $periods, array_keys($allRooms), $busy, $oneRoom);
        $saved  = ttg_save($connect, $timetableID, $result['placed'], $replace);

        if ($saved === 0) {
            flash('ttError', 'Nothing could be scheduled — every teacher or room is already busy '
                           . 'at those times on another schedule.');
        } elseif ($result['unplaced'] > 0) {
            flash('ttNotice', "Generated $saved classes. {$result['unplaced']} could not be placed — "
                            . "the teachers or rooms they needed were already booked. "
                            . "Add a teacher or room, or reduce the weekly counts, then generate again.");
        } else {
            $extra = $result['relaxed']
                ? ' Some subjects appear twice in a day — there was not enough room in the week to spread them out.'
                : '';
            flash('ttNotice', "Generated a full schedule: $saved classes placed." . $extra);
        }

        header('Location: timetable_builder.php?id=' . $timetableID);
        exit;
    }
}

$notice = takeFlash('ttNotice');
$error  = takeFlash('ttError');

// Sensible starting counts for the form: fill the default week evenly.
$defaultCapacity = count($defaults['days']) * $defaults['periods_per_day'];
// If there are more subjects than periods this comes out 0, which makes the
// generator fall back to spreading the week evenly by itself.
$suggestedCount  = $allSubjects ? intdiv($defaultCapacity, count($allSubjects)) : 0;

$existingSlots = 0;
$res = tt_query($connect, "SELECT COUNT(*) AS c FROM timetable_slots WHERE TimetableID = $timetableID");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    if ($row) {
        $existingSlots = (int) $row['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auto-generate | <?= htmlspecialchars($timetable['Name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style_timetable.css">
</head>
<body>

<div class="header-row">
  <h1>Auto-generate: <?= htmlspecialchars($timetable['Name']) ?></h1>
  <a href="timetable_builder.php?id=<?= $timetableID ?>" class="enroll-link">← Back to builder</a>
</div>

<?php if ($notice): ?><p class="notice"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<?php if ($missing): ?>
  <div class="panel">
    <p class="error">
      There are no <?= htmlspecialchars(implode(' and ', $missing)) ?> in the database yet.
      The generator needs at least one subject, one teacher and one classroom before it can build a week.
    </p>
    <p class="muted small">
      Subjects come from the <code>subjects</code> table — running
      <code>sql/01_schedule_generator.sql</code> adds a starter list.
    </p>
  </div>
<?php else: ?>

<form method="POST">
  <input type="hidden" name="action" value="generate">
  <input type="hidden" name="id" value="<?= $timetableID ?>">

  <div class="panel generate-panel">
    <h2>One click</h2>
    <p class="muted">
      Builds a full week for all <?= count($allSubjects) ?> subjects using
      <?= $defaults['periods_per_day'] ?> periods a day from <?= $defaults['start_time'] ?>,
      picking free teachers and rooms automatically. Teachers and rooms already busy
      on another schedule are worked around.
    </p>
    <?php if ($existingSlots > 0): ?>
      <p class="warn">This schedule already has <?= $existingSlots ?> classes — generating replaces them.</p>
    <?php endif; ?>
    <button type="submit" class="big-btn"
      <?= $existingSlots > 0 ? 'onclick="return confirm(\'Replace the ' . $existingSlots . ' existing classes with a freshly generated week?\');"' : '' ?>>
      Generate schedule
    </button>
  </div>

  <details class="panel">
    <summary><h2 class="inline-h2">Settings (optional)</h2></summary>

    <div class="settings-grid">
      <label>Periods per day
        <input type="number" name="periods_per_day" min="1" max="12" value="<?= $defaults['periods_per_day'] ?>">
      </label>
      <label>First lesson starts
        <input type="time" name="start_time" value="<?= $defaults['start_time'] ?>">
      </label>
      <label>Lesson length (min)
        <input type="number" name="lesson_minutes" min="15" max="180" value="<?= $defaults['lesson_minutes'] ?>">
      </label>
      <label>Break between lessons (min)
        <input type="number" name="break_minutes" min="0" max="60" value="<?= $defaults['break_minutes'] ?>">
      </label>
    </div>

    <h3 class="muted-h">Days</h3>
    <div class="day-checks">
      <?php foreach ($days as $d): ?>
        <label><input type="checkbox" name="days[]" value="<?= $d ?>" checked> <?= $d ?></label>
      <?php endforeach; ?>
    </div>

    <h3 class="muted-h">Options</h3>
    <label class="check-line"><input type="checkbox" name="one_room" value="1" checked>
      Keep the class in one room where possible</label>
    <label class="check-line"><input type="checkbox" name="replace" value="1" checked>
      Clear existing classes before generating</label>

    <h3 class="muted-h">Subjects — lessons per week</h3>
    <p class="muted small">
      Leave every count at <?= $suggestedCount ?> for an even week, or set your own.
      "Any teacher" lets the generator pick whoever is free among teachers qualified for that subject.
    </p>
    <?php if (!$teacherSubjectColumn): ?>
      <p class="warn">
        Teachers don't have an assigned subject yet, so the generator can't stop a teacher being
        booked outside their subject. Run <code>sql/02_cleanup_and_teacher_subjects.sql</code> to fix this.
      </p>
    <?php endif; ?>
    <table class="tt-table">
      <tr><th>Subject</th><th>Lessons / week</th><th>Teacher</th></tr>
      <?php foreach ($allSubjects as $sid => $sname):
        $qualifiedIDs = tt_teachers_eligible_for_subject($teacherSubjectMap, (int) $sid, array_keys($allTeachers));
      ?>
      <tr>
        <td><?= htmlspecialchars($sname) ?></td>
        <td>
          <input type="number" class="count-input" min="0" max="20"
                 name="subject_count[<?= $sid ?>]" value="<?= $suggestedCount ?>">
        </td>
        <td>
          <select name="subject_teacher[<?= $sid ?>]">
            <option value="0">Any teacher</option>
            <?php foreach ($qualifiedIDs as $tid): ?>
              <option value="<?= $tid ?>"><?= htmlspecialchars($allTeachers[$tid]) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($teacherSubjectColumn && !$qualifiedIDs): ?>
            <p class="no-lessons">No teacher is assigned to this subject yet.</p>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>

    <button type="submit">Generate with these settings</button>
  </details>
</form>

<?php endif; ?>

</body>
</html>