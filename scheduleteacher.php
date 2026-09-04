<?php
include 'schedule2.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
      <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style6.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
  </head>

    <body>
<div class="header-row">
        <h1>Teacher | My Schedule |</h1>
        <a href="markattendance.php" class="enroll-link">Mark Attendance</a>


         </div>
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
              <?php if (isset($schedule[$day][$time])):
                  $lesson = $schedule[$day][$time];
              ?>
                  <div class="lesson-card">
                      <div class="lesson-time"><?= $time ?> </div>
                      <div class="lesson-subject"><?= $lesson['LessonName'] ?></div>
                      <div class="lesson-teacher">
                          <?= $lesson['TeacherName'] ?> | <?= $lesson['ClassroomName'] ?>
                      </div>
                  </div>
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
                    <?php if (isset($schedule[$day][$time])):
                        $lesson = $schedule[$day][$time];
                    ?>
                        <div class="lesson-item">
                            <div class="lesson-item-time"><?= $time ?></div>

                            <div class="lesson-item-info">
                                <strong><?= $lesson['LessonName'] ?></strong>
                                <?= $lesson['TeacherName'] ?> <br>
                                Room: <?= $lesson['ClassroomName'] ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>

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
    </div>

    </body>
    </html>
