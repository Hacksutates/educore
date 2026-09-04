<?php
session_start();
include 'connection.php';

if (isset($_POST['enroll'], $_POST['lesson_id'])) {

    $studentID = $_SESSION['StudentID'];
    $idlesson  = $_POST['lesson_id'];

     $lessonQuery = mysqli_query($connect, "SELECT LessonName FROM extracurricular_lessons WHERE LessonID = '$idlesson'");
     $lesson = mysqli_fetch_assoc($lessonQuery);
     $lessonName = $lesson['LessonName'];

    $check = mysqli_query($connect, "SELECT 1 FROM enrollments WHERE StudentID = '$studentID' AND LessonID = '$idlesson'");
    if (mysqli_num_rows($check) > 0) {
      $_SESSION['enrollError'] =
           "You've already enrolled to <b>$lessonName</b>";
       header("Location: index.php");
       exit;
   }

    $queryEnroll = mysqli_query(
        $connect,
        "INSERT INTO enrollments (StudentID, LessonID)
         VALUES ('$studentID', '$idlesson')"
    );

    if ($queryEnroll) {
      $_SESSION['enrollSuccess'] =
           "You've been successfully enrolled to <b>$lessonName</b>";

       header("Location: ind.php");
       exit;
   }

}
?>
