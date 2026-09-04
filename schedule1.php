<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$studentID = $_SESSION['StudentID'];
$schedule = [];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$times = ['14:15','15:00','15:45','16:30'];

$querySchedule = mysqli_query($connect, "SELECT
    extracurricular_lessons.LessonID,
    extracurricular_lessons.LessonName,
    teachers.TeacherName,
    classrooms.ClassroomName,
    lessonschedule.DayOfWeek,
    lessonschedule.TimeStart
FROM enrollments
JOIN extracurricular_lessons
    ON enrollments.LessonID = extracurricular_lessons.LessonID
JOIN teachers
    ON extracurricular_lessons.TeacherID = teachers.TeacherID
JOIN classrooms
    ON extracurricular_lessons.ClassroomID = classrooms.ClassroomID
JOIN lessonschedule
    ON extracurricular_lessons.LessonID = lessonschedule.LessonID
WHERE enrollments.StudentID = '$studentID'
ORDER BY lessonschedule.DayOfWeek, lessonschedule.TimeStart;");

while ($row = mysqli_fetch_assoc($querySchedule)) {

    $day  = $row['DayOfWeek'];
    $time = substr($row['TimeStart'], 0, 5);

    $schedule[$day][$time] = [
        'LessonName'   => $row['LessonName'],
        'TeacherName'  => $row['TeacherName'],
        'ClassroomName'=> $row['ClassroomName']
    ];
}
?>
