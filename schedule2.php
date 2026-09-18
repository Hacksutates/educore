<?php
/**
 * Teacher schedule data.
 * Output contract used by scheduleteacher.php:
 *   $days, $times, $schedule, $scheduleNotice
 *
 * Combines three sources onto one grid:
 *   1. The timetable assigned to this teacher.
 *   2. Any published timetable that lists them as the teacher on a slot —
 *      so a teacher's schedule fills in as soon as they are put on a
 *      class, even if nobody assigned them a timetable by hand.
 *   3. Extracurricular lessons (clubs) this teacher personally runs, via
 *      the `extracurricular_lessons` table — same as the old schedule.
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
} else {
    $timetableRows = [];

    if (tt_tables_ready($connect)) {
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

        if ($timetableIDs) {
            $timetableRows = tt_fetch_slots($connect, $timetableIDs, $teacherID);
        }
    }

    foreach ($timetableRows as $row) {
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

    // Extracurricular lessons this teacher personally runs (clubs, electives).
    $extracurricularRows = tt_fetch_teacher_lessons($connect, $teacherID);

    foreach ($extracurricularRows as $row) {
        $day  = $row['DayOfWeek'];
        $time = substr($row['TimeStart'], 0, 5);

        if (!in_array($time, $times, true)) {
            $times[] = $time;
        }

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

    if (!$timetableRows && !$extracurricularRows) {
        $scheduleNotice = 'You are not on any schedule yet. Once an admin puts you '
                        . 'on a class and publishes that schedule, or you are assigned to '
                        . 'teach a lesson, it appears here.';
    }
}

$times = tt_normalise_times($times);
?>
