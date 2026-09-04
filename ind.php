<?php
session_start();
include 'connection.php';

$studentID = $_SESSION['StudentID'];
$enrollSuccess = $_SESSION['enrollSuccess'] ?? '';
$enrollError   = $_SESSION['enrollError'] ?? '';

unset($_SESSION['enrollSuccess'], $_SESSION['enrollError']);

$lessons = [];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$times = ['14:15','15:00','15:45','16:30'];
$lessonInfo = null;
$schedule = [];
$idlesson = null;


if (isset($_POST['lessonsearch']) && !empty($_POST['lesson_id'])) {

    $idlesson = $_POST['lesson_id'];

    $queryLesson = mysqli_query(
      $connect, "SELECT extracurricular_lessons.LessonName,
     teachers.TeacherName,
     classrooms.ClassroomName, lessonschedule.DayOfWeek, lessonschedule.TimeStart
     FROM extracurricular_lessons
     JOIN teachers ON extracurricular_lessons.TeacherID = teachers.TeacherID
     JOIN classrooms ON extracurricular_lessons.ClassroomID = classrooms.ClassroomID
     JOIN lessonschedule ON extracurricular_lessons.LessonID = lessonschedule.LessonID
     WHERE extracurricular_lessons.LessonID = '$idlesson'"
  );

  while ($row = mysqli_fetch_assoc($queryLesson)) {

    $day = $row['DayOfWeek'];
    $realTime = $row['TimeStart'];
    $time = substr($realTime, 0, 5);
    $schedule[$day] = $time;

        if ($lessonInfo === null) {
            $lessonInfo = [
                'LessonName' => $row['LessonName'],
                'TeacherName' => $row['TeacherName'],
                'ClassroomName' => $row['ClassroomName']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style6.css">

      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
  </head>
<style>
 .enroll {
    margin-right: 145px;
 }
 </style>
    <body>

<form method = "post">

  <div class="header-row">
  <h1>Student |</h1>
  <a href="schedulestudent.php" class="enroll-link">My Schedule</a>
   <h1>| Enroll in Lesson</h1>
 </div>
        <?php if ($enrollSuccess): ?>
          <p class="success"><?= $enrollSuccess ?></p>
      <?php endif; ?>

      <?php if ($enrollError): ?>
          <p class="error"><?= $enrollError ?></p>
      <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <select name ="lesson_id">
                <option value ="">Select a subject</option>
                <option value = "1">Dance club</option>
                <option value = "2">Debate club</option>
                <option value = "3">Chess club</option>
                <option value = "4">Robotics</option>

            </select>


<input type = "submit" name = "lessonsearch" value = "Find a Lesson">
</div>
</form>


        <!-- Schedule -->
        <table class="schedule-table">
            <tr>
                <th></th>
                <?php foreach($days as $day): ?>
                <th><?= $day ?></th>
              <?php endforeach; ?>
            </tr>

            <?php foreach ($times as $time): ?>
            <tr>
                <td class="time"><?= $time ?></td>

                <?php foreach ($days as $day): ?>
                    <td>
                      <?php if (isset($schedule[$day]) && $schedule[$day] === $time): ?>
                      <span class="lesson"><?= $time ?></span>
                      <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </table>
        <!-- ===== MOBILE VERSION ===== -->
        <div class="mobile-schedule">

<?php foreach ($days as $day): ?>
    <div class="day-block">
        <div class="day-title"><?= $day ?></div>

        <?php foreach ($times as $time): ?>
            <?php if (isset($schedule[$day]) && $schedule[$day] === $time): ?>
                <div class="lesson-item">
                    <div class="lesson-item-time"><?= $time ?></div>

                    <div class="lesson-item-info">
                        <strong><?= $lessonInfo['LessonName'] ?></strong><br>
                        <?= $lessonInfo['TeacherName'] ?><br>
                        Room: <?= $lessonInfo['ClassroomName'] ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

    </div>
<?php endforeach; ?>

</div>

        <!-- Selected lesson -->
        <?php if ($lessonInfo): ?>
        <div class="selected">
            <div class="info">
                <p><span>Teacher:</span> <?= $lessonInfo['TeacherName'] ?></p>
                <p><span>Subject:</span> <?= $lessonInfo['LessonName'] ?></p>
          <p><span>Classroom:</span> <?= $lessonInfo['ClassroomName'] ?></p>
          <p><span>Schedule:</span></p>
          <ul class="schedule-list">
  <?php foreach ($schedule as $day => $time): ?>
      <li><?= $day ?> — <?= $time ?></li>
  <?php endforeach; ?>
  </ul>
      </div>
      <form method="post" action="enroll.php">
    <input type="hidden" name="lesson_id" value="<?= $idlesson ?>">
    <input type="submit" name="enroll" value="Enroll">

</form>
 <?php endif; ?>
</div>
        <!-- Footer -->
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
