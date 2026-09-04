<?php
session_start();
include 'connection.php';

$teacherID = $_SESSION['TeacherID'];
$enrollSuccess = $_SESSION['enrollSuccess'] ?? '';

unset($_SESSION['enrollSuccess']);


$queryMark = null;
$date = null;
$idlesson = null;

if (
    isset($_POST['lessonsearch']) &&
    isset($_POST['lesson_id']) &&
    !empty($_POST['lesson_id']) &&
    isset($_POST['datemark']) &&
    !empty($_POST['datemark'])
) {
    $idlesson = $_POST['lesson_id'];
    $date     = $_POST['datemark'];
    $enrollmentID = $_POST['enrollmentID'];

    $queryMark = mysqli_query(
        $connect,
        "SELECT
            extracurricular_lessons.LessonName,
            students.StudentName,
            students.StudentID,
            enrollments.EnrollmentID
         FROM enrollments
         JOIN students
            ON enrollments.StudentID = students.StudentID
         JOIN extracurricular_lessons
            ON enrollments.LessonID = extracurricular_lessons.LessonID
         WHERE enrollments.LessonID = '$idlesson'"
    );
    $arr_for_sort = [];
    while ($data1 = mysqli_fetch_assoc($queryMark)) {
      $arr_for_sort[] = $data1;
    }
    $temp = null;
    for ($i = 0; $i<count($arr_for_sort)-1; $i++) {
      for ($j = 0; $j < count($arr_for_sort) - $i - 1; $j++) {
       if ($arr_for_sort[$j]['StudentName'] > $arr_for_sort[$j+1]['StudentName']) {
          $temp = $arr_for_sort[$j];
          $arr_for_sort[$j] = $arr_for_sort[$j+1];
          $arr_for_sort[$j +1] = $temp;
        }
      }
    }
    $result = $arr_for_sort;
    if (isset($_POST['studentname']) && !empty($_POST['studentname'])) {

    $searchName = $_POST['studentname'];
    $left = 0;
    $right = count($arr_for_sort)-1;
    $indexFind = -1;
    while ($left <= $right) {
      $mid = floor(($left + $right)/2);
      if ($arr_for_sort[$mid]['StudentName'] == $searchName) {
        $indexFind = $mid;
        break;
      }
      if ($arr_for_sort[$mid]['StudentName'] < $searchName) {
        $left = $mid + 1;
      }
      else {
        $right = $mid - 1;
      }
    }
      if ($indexFind != -1){
        $result = [$arr_for_sort[$indexFind]];
      }
      else {
        $result = [];
      }
    }
    }

    if (
        isset($_POST['lessonsearch']) &&
        isset($_POST['lesson_id']) &&
        !empty($_POST['lesson_id']) &&
        isset($_POST['datemark']) &&
        !empty($_POST['datemark'])
    ) {

          $idlesson = $_POST['lesson_id'];
          $date     = $_POST['datemark'];
          $enrollmentID = $_POST['enrollmentID'];
          $studentname = $_POST['studentname'];



if (isset($_POST['save_attendance'])) {

    $lessonID = $_POST['lesson_id'];
    $date     = $_POST['datemark'];

    $attendance = $_POST['attendance'] ?? [];

    $all = mysqli_query($connect, "
        SELECT EnrollmentID
        FROM enrollments
        WHERE LessonID = '$lessonID'
    ");

    while ($row = mysqli_fetch_assoc($all)) {

        $enrollmentID = $row['EnrollmentID'];

        if (isset($attendance[$enrollmentID])) {
      $status = 'Present';
  } else {
      $status = 'Absent';
  }

      $querySubmit =  mysqli_query($connect, "
            INSERT INTO attendance (EnrollmentID, Date, Status)
            VALUES ('$enrollmentID', '$date', '$status')
            ON DUPLICATE KEY UPDATE Status = '$status'
        ");
    }
    if ($querySubmit) {
      $_SESSION['enrollSuccess'] =
           "You've successfully submitted the attendance!";

       header("Location: markattendance.php");
       exit;
   }
} }

?>
