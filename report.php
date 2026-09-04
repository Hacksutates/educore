<?php
session_start();
include 'connection.php';

if (isset($_POST['report'])) {

    // 🔹 GET DATA
    $attested = $_POST['attested'] ?? [];
    $comment = $_POST['comment'] ?? '';
    $lesson_id = $_POST['lesson_id'] ?? null;
    $period_type = $_POST['period_type'] ?? 'month';
    $month = $_POST['month'] ?? null;

    $totalStudents = $_POST['total_students'] ?? 0;
    $totalLessons = $_POST['total_lessons'] ?? 0;

    // 🔹 GET supervisor id
    if (!isset($_SESSION['supervisor_id'])) {
        die("Supervisor not logged in");
    }

    $supervisor_id = $_SESSION['supervisor_id'];

    // 🔹 DETERMINE PERIOD VALUE
    if ($period_type == 'month') {
        $period_value = $month;
    } else {
        $period_value = $period_type;
    }

    // 🔹 COUNT students in lesson
    $q = mysqli_query($connect,
        "SELECT COUNT(*) as total
         FROM enrollments
         WHERE LessonID = '$lesson_id'"
    );

    $data = mysqli_fetch_assoc($q);
    $total = $data['total'];

    // 🔹 COUNT attested
    $attestedCount = count($attested);
    $notAttestedCount = $total - $attestedCount;

    // 🔥 CHECK IF REPORT EXISTS
    $qCheck = mysqli_query($connect, "
        SELECT ReportID FROM reports
        WHERE LessonID = '$lesson_id'
        AND PeriodType = '$period_type'
        AND PeriodValue = '$period_value'
    ");

    $existing = mysqli_fetch_assoc($qCheck);

    if ($existing) {

        // 🔹 UPDATE EXISTING REPORT
        $report_id = $existing['ReportID'];

        mysqli_query($connect, "
            UPDATE reports
            SET Attested='$attestedCount',
                NotAttested='$notAttestedCount',
                Comment='$comment',
                TotalStudents='$totalStudents',
                TotalLessons='$totalLessons'
            WHERE ReportID='$report_id'
        ");

        // 🔹 DELETE OLD STUDENTS
        mysqli_query($connect, "
            DELETE FROM report_students
            WHERE ReportID = '$report_id'
        ");

    } else {

        // 🔹 INSERT NEW REPORT
        mysqli_query($connect, "
            INSERT INTO reports
            (LessonID, supervisor_id, PeriodType, PeriodValue, Attested, NotAttested, Comment, TotalStudents, TotalLessons)
            VALUES
            ('$lesson_id', '$supervisor_id', '$period_type', '$period_value', '$attestedCount', '$notAttestedCount', '$comment', '$totalStudents', '$totalLessons')
        ");

        $report_id = mysqli_insert_id($connect);
    }

    // 🔥 SAVE STUDENTS (ТОЛЬКО ДЛЯ СЕМЕСТРА)
    if ($period_type != 'month') {

        $q2 = mysqli_query($connect,
            "SELECT EnrollmentID FROM enrollments WHERE LessonID = '$lesson_id'"
        );

        while ($row = mysqli_fetch_assoc($q2)) {

            $id = $row['EnrollmentID'];

            // ✔ проверяем отмечен ли студент
            $status = in_array($id, $attested) ? 'attested' : 'not_attested';

            $queryR = mysqli_query($connect, "
                INSERT INTO report_students (ReportID, EnrollmentID, status)
                VALUES ('$report_id', '$id', '$status')
            ");
        }
    }

    // 🔹 SUCCESS
    if ($queryR) {
    $_SESSION['reportSuccess'] = "Report submitted successfully!";
    header("Location: attendance.php?lesson_id=$lesson_id&period_type=$period_type&month=$month");
  exit;
}
}
?>
