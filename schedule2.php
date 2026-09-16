<?php
/**
 * Teacher schedule data.
 * Output contract used by scheduleteacher.php:
 *   $days, $times, $schedule, $scheduleNotice
 *
 * Shows every period this teacher personally teaches. Slots are collected
 * from the timetable assigned to them AND from any published timetable that
 * lists them as the teacher — so a teacher's schedule fills in as soon as
 * they are put on a class, even if nobody assigned them a timetable by hand.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'connection.php';
include_once 'schedule_lib.php';

$days     = tt_days();
$times    = [];
$schedule = [];
$scheduleNotice = '';

$teacherID = (int) ($_SESSION['TeacherID'] ?? 0);

if ($teacherID <= 0) {
    $scheduleNotice = 'Please log in again to see your schedule.';
} elseif (!tt_tables_ready($connect)) {
    $scheduleNotice = 'The schedule tables are not installed yet. '
                    . 'Run sql/01_schedule_generator.sql on the database.';
} else {
    // Timetables assigned to this teacher, plus published ones they teach on.
    $timetableIDs = tt_active_timetable_ids($connect, 'teacher', $teacherID);

    $extra = tt_query($connect, "
        SELECT DISTINCT ts.TimetableID
        FROM timetable_slots ts
        JOIN timetables tt ON tt.TimetableID = ts.TimetableID
        WHERE ts.TeacherID = $teacherID AND tt.Status = 'published'
    ");
    if ($extra) {
        while ($row = mysqli_fetch_assoc($extra)) {
            $timetableIDs[] = (int) $row['TimetableID'];
        }
    }
    $timetableIDs = array_values(array_unique($timetableIDs));

    if (!$timetableIDs) {
        $scheduleNotice = 'You are not on any schedule yet. Once a supervisor puts you '
                        . 'on a class and publishes that schedule, it appears here.';
    } else {
        $rows = tt_fetch_slots($connect, $timetableIDs, $teacherID);

        if (!$rows) {
            $scheduleNotice = 'None of your schedules list you as the teacher for a class yet.';
        }

        foreach ($rows as $row) {
            $day  = $row['DayOfWeek'];
            $time = substr($row['TimeStart'], 0, 5);

            if (!in_array($time, $times, true)) {
                $times[] = $time;
            }

            // First one wins if two timetables clash on the same period.
            if (!isset($schedule[$day][$time])) {
                $schedule[$day][$time] = [
                    'LessonID'      => null,
                    'SlotID'        => $row['SlotID'],
                    'LessonName'    => $row['SubjectName'],
                    'TeacherName'   => $row['TeacherName'],
                    'ClassroomName' => $row['ClassroomName'],
                    'TimeEnd'       => substr($row['TimeEnd'], 0, 5),
                ];
            }
        }
    }
}

$times = tt_normalise_times($times);
?>
