<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$teacherID = $_SESSION['TeacherID'];
$schedule = [];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$times = ['14:15','15:00','15:45','16:30'];

$querySchedule2 = mysqli_query($connect, "SELECT
    extracurricular_lessons.LessonID,
    extracurricular_lessons.LessonName,
    teachers.TeacherName,
    classrooms.ClassroomName,
    lessonschedule.DayOfWeek,
    lessonschedule.TimeStart
FROM extracurricular_lessons
JOIN teachers
    ON extracurricular_lessons.TeacherID = teachers.TeacherID
JOIN classrooms
    ON extracurricular_lessons.ClassroomID = classrooms.ClassroomID
JOIN lessonschedule
    ON extracurricular_lessons.LessonID = lessonschedule.LessonID
WHERE extracurricular_lessons.TeacherID = '$teacherID'
ORDER BY lessonschedule.DayOfWeek, lessonschedule.TimeStart;");

while ($row = mysqli_fetch_assoc($querySchedule2)) {

    $day  = $row['DayOfWeek'];
    $time = substr($row['TimeStart'], 0, 5);

    $schedule[$day][$time] = [
        'LessonName'   => $row['LessonName'],
        'TeacherName'  => $row['TeacherName'],
        'ClassroomName'=> $row['ClassroomName']
    ];
}
?>
