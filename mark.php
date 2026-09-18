<?php
/**
 * Teacher attendance controller.
 *
 * Two independent attendance features share this one page, switched
 * with a tab:
 *   - "class"  : a teacher's normal timetable subjects (Math, Physics...)
 *                backed by timetable_slots / class_attendance.
 *   - "extra"  : additional subjects — the clubs a teacher runs
 *                (Robotics, Dance...) backed by lessonschedule /
 *                extracurricular_lessons / enrollments / attendance.
 *
 * Output contract used by markattendance.php:
 *   $days, $selectedDate, $selectedDay, $isSchoolDay, $error, $notice,
 *   $attendanceSuccess, $tab
 *
 *   Class tab:  $allSlots, $daySlots, $markedSlotIDs,
 *               $selectedSlot, $rosterSlot, $roster, $attendanceMap
 *   Extra tab:  $allExtraSlots, $dayExtraSlots, $markedLessonIDs,
 *               $selectedLesson, $rosterLesson, $extraRoster, $extraAttendanceMap
 *
 * Flow (both tabs):
 *   1. Teacher picks a calendar date (defaults to today) and a tab.
 *   2. The page shows every class/club *that teacher personally runs*
 *      that falls on that date's weekday.
 *   3. Picking one of those shows the roster so the teacher can mark
 *      each student Present/Absent and save it.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';
include_once 'schedule_lib.php';
include_once 'attendance_lib.php';

$teacherID = (int) ($_SESSION['TeacherID'] ?? 0);
$days      = tt_days();

$attendanceSuccess = $_SESSION['attendanceSuccess'] ?? '';
unset($_SESSION['attendanceSuccess']);

$attendanceReady = tt_class_attendance_ready($connect);

// --- Which tab is the teacher on? ---------------------------------------
$tab = $_POST['tab'] ?? $_GET['tab'] ?? 'class';
$tab = ($tab === 'extra') ? 'extra' : 'class';

// --- Which date is the teacher looking at? -----------------------------
$selectedDate = $_POST['attendance_date'] ?? $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) || !strtotime($selectedDate)) {
    $selectedDate = date('Y-m-d');
}
$selectedDay = date('l', strtotime($selectedDate));
$isSchoolDay = in_array($selectedDay, $days, true);

// --- Which class (slot) is selected, if any? ---------------------------
$selectedSlot = 0;
if (isset($_POST['slot_id'])) {
    $selectedSlot = (int) $_POST['slot_id'];
} elseif (isset($_GET['slot'])) {
    $selectedSlot = (int) $_GET['slot'];
}

// --- Which club (lesson) is selected, if any? ---------------------------
$selectedLesson = 0;
if (isset($_POST['lesson_id'])) {
    $selectedLesson = (int) $_POST['lesson_id'];
} elseif (isset($_GET['lesson'])) {
    $selectedLesson = (int) $_GET['lesson'];
}

$allSlots      = [];
$daySlots      = [];
$markedSlotIDs = [];
$rosterSlot    = null;
$roster        = [];
$attendanceMap = [];

$allExtraSlots     = [];
$dayExtraSlots     = [];
$markedLessonIDs   = [];
$rosterLesson      = null;
$extraRoster       = [];
$extraAttendanceMap = [];

$error  = '';
$notice = '';

if ($teacherID <= 0) {
    $error = 'Please log in again to mark attendance.';
} elseif (!$attendanceReady) {
    $error = 'Attendance storage has not been set up yet. Ask an admin to run sql/03_class_attendance.sql.';
} else {
    // ----- Regular classes (timetable) -----
    $allSlots = tt_teacher_all_slots($connect, $teacherID);

    if ($allSlots) {
        $markedSlotIDs = tt_marked_slot_ids($connect, $selectedDate, array_column($allSlots, 'SlotID'));

        if ($isSchoolDay) {
            foreach ($allSlots as $s) {
                if ($s['DayOfWeek'] === $selectedDay) {
                    $daySlots[] = $s;
                }
            }
        }

        if ($selectedSlot > 0) {
            foreach ($allSlots as $s) {
                if ((int) $s['SlotID'] === $selectedSlot) {
                    $rosterSlot = $s;
                    break;
                }
            }
            if ($rosterSlot) {
                $roster        = tt_timetable_students($connect, (int) $rosterSlot['TimetableID']);
                $attendanceMap = tt_fetch_attendance_map($connect, $selectedSlot, $selectedDate);
            } else {
                $error        = 'That class could not be found on your schedule.';
                $selectedSlot = 0;
            }
        }
    }

    // ----- Additional subjects (clubs) -----
    $allExtraSlots = tt_teacher_extra_slots($connect, $teacherID);

    if ($allExtraSlots) {
        $markedLessonIDs = tt_extra_marked_lesson_ids($connect, $selectedDate, array_column($allExtraSlots, 'LessonID'));

        if ($isSchoolDay) {
            foreach ($allExtraSlots as $s) {
                if ($s['DayOfWeek'] === $selectedDay) {
                    $dayExtraSlots[] = $s;
                }
            }
        }

        if ($selectedLesson > 0) {
            $rosterLesson = tt_extra_lesson($connect, $selectedLesson, $teacherID);
            if ($rosterLesson) {
                $extraRoster        = tt_extra_lesson_students($connect, $selectedLesson);
                $extraAttendanceMap = tt_extra_attendance_map($connect, $selectedLesson, $selectedDate);
            } else {
                if ($tab === 'extra') {
                    $error = 'That additional subject could not be found on your schedule.';
                }
                $selectedLesson = 0;
            }
        }
    }

    if (!$allSlots && !$allExtraSlots) {
        $notice = 'You are not on any schedule yet. Once an admin assigns and '
                . 'publishes your timetable or clubs, they will appear here for attendance.';
    }
}

// --- Save: regular class ---------------------------------------------------
if ($teacherID > 0 && $attendanceReady && $rosterSlot && isset($_POST['save_attendance'])) {
    $posted          = $_POST['status'] ?? [];
    $statusByStudent = [];

    foreach ($roster as $st) {
        $sid                     = (int) $st['StudentID'];
        $statusByStudent[$sid]   = (($posted[$sid] ?? '') === 'Absent') ? 'Absent' : 'Present';
    }

    tt_save_class_attendance($connect, $selectedSlot, (int) $rosterSlot['TimetableID'], $teacherID, $selectedDate, $statusByStudent);

    $count = count($roster);
    $_SESSION['attendanceSuccess'] = 'Attendance saved for ' . $rosterSlot['SubjectName'] . ' on '
        . date('j M Y', strtotime($selectedDate)) . ' — ' . $count . ' student' . ($count === 1 ? '' : 's') . '.';

    header('Location: markattendance.php?tab=class&date=' . urlencode($selectedDate) . '&slot=' . $selectedSlot);
    exit;
}

// --- Save: additional subject ----------------------------------------------
if ($teacherID > 0 && $attendanceReady && $rosterLesson && isset($_POST['save_extra_attendance'])) {
    $posted             = $_POST['status'] ?? [];
    $statusByEnrollment = [];

    foreach ($extraRoster as $st) {
        $eid                        = (int) $st['EnrollmentID'];
        $statusByEnrollment[$eid]   = (($posted[$eid] ?? '') === 'Absent') ? 'Absent' : 'Present';
    }

    tt_save_extra_attendance($connect, $selectedLesson, $selectedDate, $statusByEnrollment);

    $count = count($extraRoster);
    $_SESSION['attendanceSuccess'] = 'Attendance saved for ' . $rosterLesson['SubjectName'] . ' on '
        . date('j M Y', strtotime($selectedDate)) . ' — ' . $count . ' student' . ($count === 1 ? '' : 's') . '.';

    header('Location: markattendance.php?tab=extra&date=' . urlencode($selectedDate) . '&lesson=' . $selectedLesson);
    exit;
}
