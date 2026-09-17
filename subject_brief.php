<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['StudentID'])) {
    header("Location: registration.php");
    exit;
}

$studentID = $_SESSION['StudentID'];
$days      = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

$lessonId  = isset($_GET['lesson_id']) ? $_GET['lesson_id'] : null;
$slotId    = isset($_GET['slot_id']) ? $_GET['slot_id'] : null;
$lessonInfo = null;
$scheduleList = [];

if ($lessonId !== null && $lessonId !== '') {

    $lessonId = mysqli_real_escape_string($connect, $lessonId);

    // Only show the brief if the logged-in student is actually enrolled in this lesson.
    $query = mysqli_query($connect, "SELECT
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
      AND extracurricular_lessons.LessonID = '$lessonId'
    ORDER BY lessonschedule.DayOfWeek, lessonschedule.TimeStart;");

    while ($row = mysqli_fetch_assoc($query)) {
        if ($lessonInfo === null) {
            $lessonInfo = [
                'LessonID'      => $row['LessonID'],
                'SlotID'        => null,
                'LessonName'    => $row['LessonName'],
                'TeacherName'   => $row['TeacherName'],
                'ClassroomName' => $row['ClassroomName'],
            ];
        }
        $scheduleList[] = [
            'day'  => $row['DayOfWeek'],
            'time' => substr($row['TimeStart'], 0, 5),
        ];
    }

} elseif ($slotId !== null && $slotId !== '') {

    $slotId = mysqli_real_escape_string($connect, $slotId);

    // Only show the brief if the requested slot belongs to a timetable that
    // is actually assigned to the logged-in student — a standard (non-
    // elective) class has no `enrollments` row to check against instead.
    $baseQuery = mysqli_query($connect, "SELECT
        ts.SubjectID,
        ts.TimetableID,
        COALESCE(s.SubjectName, 'Class') AS SubjectName
    FROM timetable_slots ts
    JOIN timetable_assignments ta
        ON ta.TimetableID = ts.TimetableID
       AND ta.AssigneeType = 'student'
       AND ta.AssigneeID = '$studentID'
    LEFT JOIN subjects s ON ts.SubjectID = s.SubjectID
    WHERE ts.SlotID = '$slotId';");

    $base = $baseQuery ? mysqli_fetch_assoc($baseQuery) : null;

    if ($base) {
        // Show every period of this subject on the student's timetable
        // this week, the same way an elective's weekly schedule is shown.
        $query = mysqli_query($connect, "SELECT
            ts.SlotID,
            ts.DayOfWeek,
            ts.TimeStart,
            COALESCE(t.TeacherName, 'TBA')   AS TeacherName,
            COALESCE(c.ClassroomName, 'TBA') AS ClassroomName
        FROM timetable_slots ts
        LEFT JOIN teachers   t ON ts.TeacherID   = t.TeacherID
        LEFT JOIN classrooms c ON ts.ClassroomID = c.ClassroomID
        WHERE ts.TimetableID = '{$base['TimetableID']}'
          AND ts.SubjectID   = '{$base['SubjectID']}'
        ORDER BY FIELD(ts.DayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday'), ts.TimeStart;");

        while ($row = mysqli_fetch_assoc($query)) {
            if ($lessonInfo === null) {
                $lessonInfo = [
                    'LessonID'      => null,   // a standard class has no extracurricular-lesson id
                    'SlotID'        => $slotId,
                    'LessonName'    => $base['SubjectName'],
                    'TeacherName'   => $row['TeacherName'],
                    'ClassroomName' => $row['ClassroomName'],
                ];
            }
            $scheduleList[] = [
                'day'  => $row['DayOfWeek'],
                'time' => substr($row['TimeStart'], 0, 5),
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style6.css">

    <title>Subject Brief</title>
</head>
<style>
.ai {
  font-family: 'Quicksand', sans-serif;
  background-color: #4a2fa5;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  margin: 10px;
}
h3 {
  font-family: 'Playfair Display', serif;
  color: #3b2a7d;
  font-size: 20px;
  margin: 10px;
  white-space: nowrap;
}
.brief-card {
  background: white;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.06);
  margin-top: 10px;
}
</style>

<body>

<!-- HEADER -->
<div class="header-row">
    <h1>Student | Subject Brief |</h1>
    <a href="schedulestudent.php" class="enroll-link">← My Schedule</a>
</div>

<?php if ($lessonInfo): ?>

    <!-- SUBJECT INFO -->
    <div class="brief-card">
        <div class="info">
            <p><span>Subject:</span> <?= $lessonInfo['LessonName'] ?></p>
            <p><span>Teacher:</span> <?= $lessonInfo['TeacherName'] ?></p>
            <p><span>Classroom:</span> <?= $lessonInfo['ClassroomName'] ?></p>
            <p><span>Schedule:</span></p>
            <ul class="schedule-list">
                <?php foreach ($scheduleList as $entry): ?>
                    <li><?= $entry['day'] ?> — <?= $entry['time'] ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
    const currentUser   = "<?= $_SESSION['StudentID'] ?>";
    const currentLesson = "<?= $lessonInfo['LessonID'] ?? ('slot_' . $lessonInfo['SlotID']) ?>";
    const notesKey      = "notes_lesson_" + currentLesson;
    </script>

    <div class="ai-study-box">
      <h3>Smart Learning Assistant</h3>

      <button class="ai" onclick="generateStudy()">Open ChatBot</button>

      <div id="aiResult"></div>


      <div id="notesContainer" class="notes-container"></div>
    </div>

    <script>
    let chatLoading = false;
let chatLoaded = false;

async function generateStudy() {

    if (chatLoading || chatLoaded) {
        return;
    }

    chatLoading = true;

    const button = document.querySelector(".ai");
    button.disabled = true;
    button.textContent = "Loading...";

    try {
        const response = await fetch("ai_cot.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                lesson_id: currentLesson
            })
        });

        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }

        const data = await response.text();

        document.getElementById("aiResult").innerHTML = data;

        chatLoaded = true;

        const saveBtn = document.getElementById("saveBtn");
        if (saveBtn) {
            saveBtn.style.display = "inline-block";
        }

    } catch (error) {
        console.error(error);

        button.disabled = false;
        button.textContent = "Open ChatBot";
    }

    chatLoading = false;
}
    </script>

<?php else: ?>

    <div class="brief-card">
        <p class="error">We couldn't find that subject, or you're not enrolled in it.</p>
    </div>

<?php endif; ?>

<!-- FOOTER -->
<div class="footer">
    <a href="main.html">← Back to Main</a>
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
intent="WELCOME"
chat-title="EduCoreHelp"
agent-id="77543f0d-f289-4ae0-aed1-b394c854cf54"
language-code="ru"></df-messenger>
</div>

</body>
</html>