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
  <body>

    <div class="header-row">
        <h1>Teacher |</h1>
        <a href="scheduleteacher.php" class="enroll-link">My Schedule</a>
        <h1>| Mark Attendance</h1>
    </div>

    <?php if ($attendanceSuccess): ?>
        <p class="success"><?= htmlspecialchars($attendanceSuccess) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($notice): ?>
        <p class="schedule-notice"><?= htmlspecialchars($notice) ?></p>
    <?php endif; ?>

    <?php if (!$error && ($allSlots || $allExtraSlots)): ?>

    <!-- TAB SWITCH -->
    <div class="attendance-tabs">
        <a class="tab-link <?= $tab === 'class' ? 'active' : '' ?>"
           href="markattendance.php?tab=class&date=<?= urlencode($selectedDate) ?>">My Classes</a>
        <a class="tab-link <?= $tab === 'extra' ? 'active' : '' ?>"
           href="markattendance.php?tab=extra&date=<?= urlencode($selectedDate) ?>">Additional Subjects</a>
    </div>

    <!-- DATE PICKER -->
    <form method="get" action="markattendance.php" class="date-picker-form">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <label for="attendance-date-input"><strong>Date:</strong></label>
        <input type="date" id="attendance-date-input" name="date"
               value="<?= htmlspecialchars($selectedDate) ?>"
               onchange="this.form.submit()">
        <input type="submit" value="Go">
        <span class="selected-day">
            <?= htmlspecialchars(date('l, j F Y', strtotime($selectedDate))) ?>
        </span>
    </form>

    <?php if ($tab === 'class'): ?>
    <!-- ======================= MY CLASSES TAB ======================= -->

    <?php if (!$allSlots): ?>
        <p class="schedule-notice">You are not on any class schedule yet. Once an admin assigns and publishes your timetable, your classes will appear here.</p>
    <?php elseif (!$isSchoolDay): ?>
        <p class="schedule-notice">No classes are scheduled on <?= htmlspecialchars($selectedDay) ?>s. Pick a weekday to mark attendance.</p>
    <?php elseif (!$daySlots): ?>
        <p class="schedule-notice">You have no classes on <?= htmlspecialchars($selectedDay) ?>s.</p>
    <?php else: ?>

    <!-- CLASSES FOR THE SELECTED DAY -->
    <div class="slot-grid">
        <?php foreach ($daySlots as $s):
            $isActive = ((int) $s['SlotID'] === $selectedSlot);
            $isMarked = in_array((int) $s['SlotID'], $markedSlotIDs, true);
        ?>
        <a class="slot-card <?= $isActive ? 'active' : '' ?>"
           href="markattendance.php?tab=class&date=<?= urlencode($selectedDate) ?>&slot=<?= (int) $s['SlotID'] ?>">
            <div class="slot-time"><?= htmlspecialchars(substr($s['TimeStart'], 0, 5)) ?>&ndash;<?= htmlspecialchars(substr($s['TimeEnd'], 0, 5)) ?></div>
            <div class="slot-subject"><?= htmlspecialchars($s['SubjectName']) ?></div>
            <div class="slot-meta"><?= htmlspecialchars($s['TimetableName']) ?> &middot; <?= (int) $s['StudentCount'] ?> students &middot; Room <?= htmlspecialchars($s['ClassroomName']) ?></div>
            <span class="att-badge <?= $isMarked ? 'att-badge-done' : 'att-badge-pending' ?>">
                <?= $isMarked ? 'Attendance recorded' : 'Not marked yet' ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <!-- ROSTER: CLASS -->
    <?php if ($rosterSlot): ?>
    <form method="post" action="markattendance.php">
        <input type="hidden" name="tab" value="class">
        <input type="hidden" name="slot_id" value="<?= (int) $selectedSlot ?>">
        <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>">

        <div class="roster-panel">
            <div class="roster-head">
                <div>
                    <h2><?= htmlspecialchars($rosterSlot['SubjectName']) ?></h2>
                    <p class="muted">
                        <?= htmlspecialchars($rosterSlot['TimetableName']) ?> &middot;
                        Room <?= htmlspecialchars($rosterSlot['ClassroomName']) ?> &middot;
                        <?= htmlspecialchars(substr($rosterSlot['TimeStart'], 0, 5)) ?>&ndash;<?= htmlspecialchars(substr($rosterSlot['TimeEnd'], 0, 5)) ?> &middot;
                        <?= htmlspecialchars(date('j M Y', strtotime($selectedDate))) ?>
                    </p>
                </div>
                <?php if ($roster): ?>
                <div class="roster-bulk">
                    <button type="button" class="bulk-btn" onclick="setAllAttendance(this, 'Present')">Mark all present</button>
                    <button type="button" class="bulk-btn" onclick="setAllAttendance(this, 'Absent')">Mark all absent</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$roster): ?>
                <p class="no-lessons">No students are assigned to this grade's timetable yet.</p>
            <?php else: ?>
                <div class="table-scroll">
                <table class="roster-table">
                    <tr>
                        <th>Student</th>
                        <th>Attendance</th>
                    </tr>
                    <?php foreach ($roster as $st):
                        $sid    = (int) $st['StudentID'];
                        $status = $attendanceMap[$sid] ?? 'Present';
                    ?>
                    <tr>
                        <td class="student-name"><?= htmlspecialchars($st['StudentName']) ?></td>
                        <td>
                            <div class="attendance-toggle">
                                <label class="att-pill att-present <?= $status === 'Present' ? 'active' : '' ?>">
                                    <input type="radio" name="status[<?= $sid ?>]" value="Present" <?= $status === 'Present' ? 'checked' : '' ?>>
                                    Present
                                </label>
                                <label class="att-pill att-absent <?= $status === 'Absent' ? 'active' : '' ?>">
                                    <input type="radio" name="status[<?= $sid ?>]" value="Absent" <?= $status === 'Absent' ? 'checked' : '' ?>>
                                    Absent
                                </label>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                </div>
                <br>
                <button type="submit" name="save_attendance" value="1" class="save-btn">Save attendance</button>
            <?php endif; ?>
        </div>
    </form>
    <?php endif; ?>

    <?php else: ?>
    <!-- =================== ADDITIONAL SUBJECTS TAB =================== -->

    <?php if (!$allExtraSlots): ?>
        <p class="schedule-notice">You don't run any additional subjects yet. Once an admin assigns you a club, it will appear here.</p>
    <?php elseif (!$isSchoolDay): ?>
        <p class="schedule-notice">No additional subjects are scheduled on <?= htmlspecialchars($selectedDay) ?>s. Pick a weekday to mark attendance.</p>
    <?php elseif (!$dayExtraSlots): ?>
        <p class="schedule-notice">You have no additional subjects on <?= htmlspecialchars($selectedDay) ?>s.</p>
    <?php else: ?>

    <!-- ADDITIONAL SUBJECTS FOR THE SELECTED DAY -->
    <div class="slot-grid">
        <?php foreach ($dayExtraSlots as $s):
            $isActive = ((int) $s['LessonID'] === $selectedLesson);
            $isMarked = in_array((int) $s['LessonID'], $markedLessonIDs, true);
        ?>
        <a class="slot-card <?= $isActive ? 'active' : '' ?>"
           href="markattendance.php?tab=extra&date=<?= urlencode($selectedDate) ?>&lesson=<?= (int) $s['LessonID'] ?>">
            <div class="slot-time"><?= htmlspecialchars(substr($s['TimeStart'], 0, 5)) ?></div>
            <div class="slot-subject"><?= htmlspecialchars($s['SubjectName']) ?></div>
            <div class="slot-meta"><?= (int) $s['StudentCount'] ?> students &middot; Room <?= htmlspecialchars($s['ClassroomName']) ?></div>
            <span class="att-badge <?= $isMarked ? 'att-badge-done' : 'att-badge-pending' ?>">
                <?= $isMarked ? 'Attendance recorded' : 'Not marked yet' ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <!-- ROSTER: ADDITIONAL SUBJECT -->
    <?php if ($rosterLesson): ?>
    <form method="post" action="markattendance.php">
        <input type="hidden" name="tab" value="extra">
        <input type="hidden" name="lesson_id" value="<?= (int) $selectedLesson ?>">
        <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>">

        <div class="roster-panel">
            <div class="roster-head">
                <div>
                    <h2><?= htmlspecialchars($rosterLesson['SubjectName']) ?></h2>
                    <p class="muted">
                        Additional subject &middot;
                        Room <?= htmlspecialchars($rosterLesson['ClassroomName']) ?> &middot;
                        <?= htmlspecialchars(date('j M Y', strtotime($selectedDate))) ?>
                    </p>
                </div>
                <?php if ($extraRoster): ?>
                <div class="roster-bulk">
                    <button type="button" class="bulk-btn" onclick="setAllAttendance(this, 'Present')">Mark all present</button>
                    <button type="button" class="bulk-btn" onclick="setAllAttendance(this, 'Absent')">Mark all absent</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$extraRoster): ?>
                <p class="no-lessons">No students are enrolled in this subject yet.</p>
            <?php else: ?>
                <div class="table-scroll">
                <table class="roster-table">
                    <tr>
                        <th>Student</th>
                        <th>Attendance</th>
                    </tr>
                    <?php foreach ($extraRoster as $st):
                        $eid    = (int) $st['EnrollmentID'];
                        $status = $extraAttendanceMap[$eid] ?? 'Present';
                    ?>
                    <tr>
                        <td class="student-name"><?= htmlspecialchars($st['StudentName']) ?></td>
                        <td>
                            <div class="attendance-toggle">
                                <label class="att-pill att-present <?= $status === 'Present' ? 'active' : '' ?>">
                                    <input type="radio" name="status[<?= $eid ?>]" value="Present" <?= $status === 'Present' ? 'checked' : '' ?>>
                                    Present
                                </label>
                                <label class="att-pill att-absent <?= $status === 'Absent' ? 'active' : '' ?>">
                                    <input type="radio" name="status[<?= $eid ?>]" value="Absent" <?= $status === 'Absent' ? 'checked' : '' ?>>
                                    Absent
                                </label>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                </div>
                <br>
                <button type="submit" name="save_extra_attendance" value="1" class="save-btn">Save attendance</button>
            <?php endif; ?>
        </div>
    </form>
    <?php endif; ?>

    <?php endif; ?>

    <?php endif; ?>

    <script>
    document.querySelectorAll('.attendance-toggle').forEach(function (group) {
        group.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                group.querySelectorAll('.att-pill').forEach(function (p) { p.classList.remove('active'); });
                radio.closest('.att-pill').classList.add('active');
            });
        });
    });

    function setAllAttendance(button, status) {
        var panel = button.closest('.roster-panel');
        panel.querySelectorAll('.attendance-toggle').forEach(function (group) {
            var radio = group.querySelector('input[value="' + status + '"]');
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    }
    </script>

    <!-- Footer -->
    <div class="footer">
        <a href="main.html">&larr; Back to Main</a>
        <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
        <df-messenger
          intent="WELCOME"
          chat-title="EduCoreHelp"
          agent-id="77543f0d-f289-4ae0-aed1-b394c854cf54"
          language-code="ru"></df-messenger>
    </div>

  </body>
</html>
