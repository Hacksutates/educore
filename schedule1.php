<?php
/**
 * Student schedule data.
 * Output contract used by schedulestudent.php:
 *   $days, $times, $schedule, $scheduleNotice
 *
 * Reads whichever timetable a supervisor set as this student's ACTIVE
 * schedule (all subjects — Math, Physics, etc.).
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
} elseif (!tt_tables_ready($connect)) {
    $scheduleNotice = 'The schedule tables are not installed yet. '
                    . 'Run sql/01_schedule_generator.sql on the database.';
} else {
    $timetableIDs = tt_active_timetable_ids($connect, 'student', $studentID);

    if (!$timetableIDs) {
        $scheduleNotice = 'No schedule has been assigned to you yet. '
                        . 'Your supervisor assigns one from the Schedules page.';
    } else {
        $rows = tt_fetch_slots($connect, $timetableIDs);

        if (!$rows) {
            $scheduleNotice = 'Your schedule has been assigned but has no classes in it yet.';
        }

        foreach ($rows as $row) {
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
    }
}

// Always render a usable grid, even with nothing scheduled — an empty
// $times array was what made the table show up with no rows at all.
$times = tt_normalise_times($times);
?>
