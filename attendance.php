<?php
session_start();
include 'connection.php';


$supervisor_id = $_SESSION['supervisor_id'];
$dates = [];
$students = [];
$attendanceMap = [];
$attendanceStats = [];

$period_type = $_POST['period_type'] ?? 'month';
$lesson_id = $_POST['lesson_id'] ?? null;
$month = $_POST['month'] ?? null;

$dateCondition = "";


//  Determine period
if ($period_type == 'month' && !empty($month)) {
    $dateCondition = "Date LIKE '$month%'";
}

elseif ($period_type == 'sem1') {
    $dateCondition = "Date BETWEEN '2026-09-01' AND '2026-12-31'";
}

elseif ($period_type == 'sem2') {
    $dateCondition = "Date BETWEEN '2027-01-01' AND '2027-05-31'";
}

//  Only run if lesson selected
if (!empty($lesson_id) && !empty($dateCondition)) {

  $checkedStudents = [];

  $qReport = mysqli_query($connect, "
    SELECT ReportID
    FROM reports
    WHERE LessonID = '$lesson_id'
    AND PeriodType = '$period_type'
    AND PeriodValue = '" . ($period_type == 'month' ? $month : $period_type) . "'
    AND supervisor_id = '$supervisor_id'
    LIMIT 1
");

  $report = mysqli_fetch_assoc($qReport);

  if ($report) {
    $report_id = $report['ReportID'];

    $qStudents = mysqli_query($connect, "
        SELECT EnrollmentID
        FROM report_students
        WHERE ReportID = '$report_id'
        AND status = 'attested'
    ");

    while ($row = mysqli_fetch_assoc($qStudents)) {
        $checkedStudents[] = $row['EnrollmentID'];
    }
}


    // 1. Get lesson dates
    $q1 = mysqli_query($connect,
        "SELECT DISTINCT Date
         FROM attendance
         JOIN enrollments ON attendance.EnrollmentID = enrollments.EnrollmentID
         WHERE enrollments.LessonID = '$lesson_id'
         AND $dateCondition
         ORDER BY Date"
    );

    while ($row = mysqli_fetch_assoc($q1)) {
        $dates[] = $row['Date'];
    }

    // 2. Get students
    $q2 = mysqli_query($connect,
        "SELECT students.StudentName, enrollments.EnrollmentID
         FROM enrollments
         JOIN students ON enrollments.StudentID = students.StudentID
         WHERE enrollments.LessonID = '$lesson_id'"
    );

    while ($row = mysqli_fetch_assoc($q2)) {
        $students[] = $row;
    }

    // 3. Get attendance ONLY for selected period
    $q3 = mysqli_query($connect, "SELECT attendance.EnrollmentID, Date, Status
  FROM attendance
  JOIN enrollments ON attendance.EnrollmentID = enrollments.EnrollmentID
  WHERE enrollments.LessonID = '$lesson_id'
  AND $dateCondition");

    while ($row = mysqli_fetch_assoc($q3)) {

        $enrollID = $row['EnrollmentID'];
        $date = $row['Date'];
        $status = $row['Status'];

        // Map for table
        $attendanceMap[$enrollID][$date] = $status;

        // Stats for percentage
        if (!isset($attendanceStats[$enrollID])) {
            $attendanceStats[$enrollID] = [
                'present' => 0,
                'total' => 0
            ];
        }

        $attendanceStats[$enrollID]['total']++;

        if ($status == 'Present') {
            $attendanceStats[$enrollID]['present']++;
        }
    }
}

// Send everything to UI
include 'attendancemanagement.php';
?>
