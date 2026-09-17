<?php
/**
 * Student schedule data.
 * Output contract used by schedulestudent.php:
 *   $days, $times, $schedule, $scheduleNotice
 *
 * Combines two sources onto one grid:
 *   1. Whichever timetable a supervisor set as this student's ACTIVE
 *      schedule (all subjects — Math, Physics, etc.).
 *   2. Extracurricular lessons (clubs) the student enrolled in themselves
 *      via ind.php / enroll.php — these carry a LessonID so the card can
 *      link through to subject_brief.php.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'connection.php';
include_once 'schedule_lib.php';

$days     = tt_days();
$times    = [];
$schedule = [];
$scheduleNotice = '';          // shown by the view when the grid is empty

$studentID = (int) ($_SESSION['StudentID'] ?? 0);

if ($studentID <= 0) {
    $scheduleNotice = 'Please log in again to see your schedule.';
} else {
    $timetableRows = [];

    if (tt_tables_ready($connect)) {
        $timetableIDs = tt_active_timetable_ids($connect, 'student', $studentID);
        if ($timetableIDs) {
            $timetableRows = tt_fetch_slots($connect, $timetableIDs);
        }
    }

    foreach ($timetableRows as $row) {
        $day  = $row['DayOfWeek'];
        $time = substr($row['TimeStart'], 0, 5);

        if (!in_array($time, $times, true)) {
            $times[] = $time;
        }

        $schedule[$day][$time] = [
            'LessonID'      => null,   // timetable classes have no extracurricular brief page
            'SlotID'        => $row['SlotID'],
            'LessonName'    => $row['SubjectName'],
            'TeacherName'   => $row['TeacherName'],
            'ClassroomName' => $row['ClassroomName'],
            'TimeEnd'       => substr($row['TimeEnd'], 0, 5),
        ];
    }

    // Extracurricular lessons the student enrolled in themselves. These
    // always come from `enrollments`, independent of whether the
    // timetable-generator tables exist, so they show up even on an
    // otherwise fresh install.
    $enrolledRows = tt_fetch_enrolled_lessons($connect, $studentID);

    foreach ($enrolledRows as $row) {
        $day  = $row['DayOfWeek'];
        $time = substr($row['TimeStart'], 0, 5);

        if (!in_array($time, $times, true)) {
            $times[] = $time;
        }

        // A timetable class already occupying this day/time wins — an
        // enrolled club is not expected to clash with a core class, but if
        // it ever does, don't silently hide the graded class for it.
        if (!isset($schedule[$day][$time])) {
            $schedule[$day][$time] = [
                'LessonID'      => $row['LessonID'],
                'SlotID'        => null,
                'LessonName'    => $row['LessonName'],
                'TeacherName'   => $row['TeacherName'],
                'ClassroomName' => $row['ClassroomName'],
                'TimeEnd'       => null,
            ];
        }
    }

    if (!$timetableRows && !$enrolledRows) {
        if (!tt_tables_ready($connect)) {
            $scheduleNotice = 'No schedule has been assigned to you yet, and you have not '
                            . 'enrolled in any lessons. Ask your supervisor for a schedule, or '
                            . 'enroll in a lesson from the "Enroll to Lesson" page.';
        } else {
            $scheduleNotice = 'No schedule has been assigned to you yet, and you have not '
                            . 'enrolled in any lessons. Your supervisor assigns a schedule from '
                            . 'the Schedules page, or you can enroll in a lesson yourself.';
        }
    }
}

// Always render a usable grid, even with nothing scheduled — an empty
// $times array was what made the table show up with no rows at all.
$times = tt_normalise_times($times);
?>
