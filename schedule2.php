<?php
/**
 * Replaces the original schedule2.php.
 * Same output contract as before ($days, $times, $schedule), so
 * scheduleteacher.php needs no changes at all — it just includes this file.
 *
 * Pulls whichever timetable a supervisor has set as the teacher's ACTIVE
 * schedule, filtered down to just the periods where this teacher is the
 * one teaching — not the whole class's schedule.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$teacherID = (int) $_SESSION['TeacherID'];

$days     = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$times    = [];
$schedule = [];

$activeID = null;
$q = mysqli_query($connect, "
    SELECT TimetableID FROM timetable_assignments
    WHERE AssigneeType = 'teacher' AND AssigneeID = $teacherID
    LIMIT 1
");
if ($row = mysqli_fetch_assoc($q)) {
    $activeID = (int) $row['TimetableID'];
}

if ($activeID) {
    $querySchedule2 = mysqli_query($connect, "
        SELECT s.SubjectName, t.TeacherName, c.ClassroomName, ts.DayOfWeek, ts.TimeStart
        FROM timetable_slots ts
        JOIN subjects s   ON ts.SubjectID   = s.SubjectID
        JOIN teachers t   ON ts.TeacherID   = t.TeacherID
        JOIN classrooms c ON ts.ClassroomID = c.ClassroomID
        WHERE ts.TimetableID = $activeID AND ts.TeacherID = $teacherID
        ORDER BY ts.DayOfWeek, ts.TimeStart
    ");
    while ($row = mysqli_fetch_assoc($querySchedule2)) {
        $day  = $row['DayOfWeek'];
        $time = substr($row['TimeStart'], 0, 5);
        if (!in_array($time, $times, true)) {
            $times[] = $time;
        }
        $schedule[$day][$time] = [
            'LessonName'    => $row['SubjectName'],
            'TeacherName'   => $row['TeacherName'],
            'ClassroomName' => $row['ClassroomName'],
        ];
    }
    sort($times);
}
?>
