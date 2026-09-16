<?php
/**
 * Replaces the original schedule1.php.
 * Same output contract as before ($days, $times, $schedule), so
 * schedulestudent.php needs no changes at all — it just includes this file.
 *
 * Instead of pulling every extracurricular lesson a student enrolled in,
 * this pulls whichever timetable a supervisor has set as the student's
 * ACTIVE schedule (all subjects — Math, Physics, etc.).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$studentID = (int) $_SESSION['StudentID'];

$days     = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$times    = [];
$schedule = [];

$activeID = null;
$q = mysqli_query($connect, "
    SELECT TimetableID FROM timetable_assignments
    WHERE AssigneeType = 'student' AND AssigneeID = $studentID
    LIMIT 1
");
if ($row = mysqli_fetch_assoc($q)) {
    $activeID = (int) $row['TimetableID'];
}

if ($activeID) {
    $querySchedule = mysqli_query($connect, "
        SELECT s.SubjectName, t.TeacherName, c.ClassroomName, ts.DayOfWeek, ts.TimeStart
        FROM timetable_slots ts
        JOIN subjects s   ON ts.SubjectID   = s.SubjectID
        JOIN teachers t   ON ts.TeacherID   = t.TeacherID
        JOIN classrooms c ON ts.ClassroomID = c.ClassroomID
        WHERE ts.TimetableID = $activeID
        ORDER BY ts.DayOfWeek, ts.TimeStart
    ");
    while ($row = mysqli_fetch_assoc($querySchedule)) {
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
