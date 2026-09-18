<?php
/**
 * Read/write helpers for teacher-taken class attendance.
 *
 * This is separate from the existing `attendance` table (which tracks
 * extracurricular club enrollments). This one tracks attendance for a
 * teacher's normal timetable classes: one row per (SlotID, StudentID,
 * Date), where SlotID is a `timetable_slots` row and the roster is
 * every student assigned to that slot's timetable (i.e. that "grade").
 *
 * Depends on schedule_lib.php already being included for tt_query()
 * and tt_active_timetable_ids().
 */

/** True once sql/03_class_attendance.sql has been run. */
function tt_class_attendance_ready($connect): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    $res = tt_query($connect, "SHOW TABLES LIKE 'class_attendance'");
    $ready = (bool) ($res && mysqli_num_rows($res) > 0);
    return $ready;
}

/**
 * Every timetable slot this teacher personally teaches, across the whole
 * week, with the grade (timetable) name and how many students are on it.
 * Used to build the day tabs and to look up a chosen slot's timetable.
 */
function tt_teacher_all_slots($connect, int $teacherID): array
{
    $timetableIDs = tt_active_timetable_ids($connect, 'teacher', $teacherID);

    $extra = tt_query($connect, "
        SELECT DISTINCT ts.TimetableID
        FROM timetable_slots ts
        JOIN timetables tt ON tt.TimetableID = ts.TimetableID
        WHERE ts.TeacherID = $teacherID AND tt.Status = 'published'
    ");
    if ($extra) {
        while ($row = mysqli_fetch_assoc($extra)) {
            $timetableIDs[] = (int) $row['TimetableID'];
        }
    }
    $timetableIDs = array_values(array_unique(array_map('intval', $timetableIDs)));
    if (!$timetableIDs) {
        return [];
    }
    $idList = implode(',', $timetableIDs);

    $res = tt_query($connect, "
        SELECT ts.SlotID, ts.TimetableID, ts.DayOfWeek, ts.TimeStart, ts.TimeEnd,
               COALESCE(s.SubjectName, 'Class')                  AS SubjectName,
               COALESCE(c.ClassroomName, 'TBA')                  AS ClassroomName,
               COALESCE(tt.Name, CONCAT('Timetable #', ts.TimetableID)) AS TimetableName,
               (SELECT COUNT(*) FROM timetable_assignments ta
                 WHERE ta.TimetableID = ts.TimetableID AND ta.AssigneeType = 'student') AS StudentCount
        FROM timetable_slots ts
        LEFT JOIN subjects   s  ON ts.SubjectID   = s.SubjectID
        LEFT JOIN classrooms c  ON ts.ClassroomID = c.ClassroomID
        LEFT JOIN timetables tt ON tt.TimetableID = ts.TimetableID
        WHERE ts.TimetableID IN ($idList) AND ts.TeacherID = $teacherID
        ORDER BY FIELD(ts.DayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday'),
                 ts.TimeStart
    ");

    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * The roster for a timetable: every student assigned to it (the whole
 * grade), sorted by name. Falls back to login/ID when a name is blank —
 * some seed rows in `students` have an empty StudentName.
 */
function tt_timetable_students($connect, int $timetableID): array
{
    $res = tt_query($connect, "
        SELECT st.StudentID,
               COALESCE(NULLIF(TRIM(st.StudentName), ''), st.Login_Student, CONCAT('Student #', st.StudentID)) AS StudentName
        FROM timetable_assignments ta
        JOIN students st ON st.StudentID = ta.AssigneeID
        WHERE ta.TimetableID = $timetableID AND ta.AssigneeType = 'student'
        ORDER BY StudentName
    ");

    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/** Existing marks for one slot on one date: [StudentID => 'Present'|'Absent']. */
function tt_fetch_attendance_map($connect, int $slotID, string $date): array
{
    $date = mysqli_real_escape_string($connect, $date);
    $map  = [];

    $res = tt_query($connect, "
        SELECT StudentID, Status FROM class_attendance
        WHERE SlotID = $slotID AND Date = '$date'
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $map[(int) $row['StudentID']] = $row['Status'];
        }
    }
    return $map;
}

/** Which of the given slot IDs already have attendance recorded for a date. */
function tt_marked_slot_ids($connect, string $date, array $slotIDs): array
{
    $slotIDs = array_values(array_filter(array_map('intval', $slotIDs)));
    if (!$slotIDs) {
        return [];
    }
    $date   = mysqli_real_escape_string($connect, $date);
    $idList = implode(',', $slotIDs);

    $res = tt_query($connect, "
        SELECT DISTINCT SlotID FROM class_attendance
        WHERE Date = '$date' AND SlotID IN ($idList)
    ");

    $ids = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ids[] = (int) $row['SlotID'];
        }
    }
    return $ids;
}

/**
 * Upsert one status per student for a slot+date. Re-saving the same
 * slot/date just overwrites the previous marks (ON DUPLICATE KEY UPDATE),
 * so a teacher can safely correct a mistake later.
 */
function tt_save_class_attendance($connect, int $slotID, int $timetableID, int $teacherID, string $date, array $statusByStudent): bool
{
    $date = mysqli_real_escape_string($connect, $date);
    $ok   = true;

    foreach ($statusByStudent as $studentID => $status) {
        $studentID = (int) $studentID;
        $status    = ($status === 'Absent') ? 'Absent' : 'Present';

        $res = tt_query($connect, "
            INSERT INTO class_attendance (SlotID, TimetableID, StudentID, TeacherID, Date, Status)
            VALUES ($slotID, $timetableID, $studentID, $teacherID, '$date', '$status')
            ON DUPLICATE KEY UPDATE Status = VALUES(Status), TeacherID = VALUES(TeacherID), MarkedAt = CURRENT_TIMESTAMP
        ");
        if (!$res) {
            $ok = false;
        }
    }
    return $ok;
}

/**
 * ------------------------------------------------------------------
 * Additional subjects (extracurricular clubs).
 *
 * Separate feature, separate tables: `extracurricular_lessons` (a club
 * a teacher runs), `lessonschedule` (which weekday/time it meets),
 * `enrollments` (which students are in it) and `attendance` (the
 * Present/Absent marks, one row per EnrollmentID+Date).
 *
 * This existed before `class_attendance` and needs no migration —
 * these tables are already part of automated_system.sql.
 * ------------------------------------------------------------------
 */

/**
 * Every weekly meeting-time this teacher's clubs have, with the club
 * name, room and how many students are enrolled. Mirrors
 * tt_teacher_all_slots() so both tabs share the same day-grid UI.
 */
function tt_teacher_extra_slots($connect, int $teacherID): array
{
    $res = tt_query($connect, "
        SELECT ls.ScheduleID, ls.LessonID, ls.DayOfWeek, ls.TimeStart,
               el.LessonName                    AS SubjectName,
               COALESCE(c.ClassroomName, 'TBA') AS ClassroomName,
               (SELECT COUNT(*) FROM enrollments e WHERE e.LessonID = el.LessonID) AS StudentCount
        FROM lessonschedule ls
        JOIN extracurricular_lessons el ON el.LessonID = ls.LessonID
        LEFT JOIN classrooms c ON c.ClassroomID = el.ClassroomID
        WHERE el.TeacherID = $teacherID
        ORDER BY FIELD(ls.DayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday'),
                 ls.TimeStart
    ");

    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/** One club's own record (name/room/teacher check), regardless of when it meets. */
function tt_extra_lesson($connect, int $lessonID, int $teacherID): ?array
{
    $res = tt_query($connect, "
        SELECT el.LessonID, el.LessonName AS SubjectName,
               COALESCE(c.ClassroomName, 'TBA') AS ClassroomName
        FROM extracurricular_lessons el
        LEFT JOIN classrooms c ON c.ClassroomID = el.ClassroomID
        WHERE el.LessonID = $lessonID AND el.TeacherID = $teacherID
    ");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    return $row ?: null;
}

/** Everyone enrolled in a club, sorted by name, with their EnrollmentID for saving. */
function tt_extra_lesson_students($connect, int $lessonID): array
{
    $res = tt_query($connect, "
        SELECT en.EnrollmentID, st.StudentID,
               COALESCE(NULLIF(TRIM(st.StudentName), ''), st.Login_Student, CONCAT('Student #', st.StudentID)) AS StudentName
        FROM enrollments en
        JOIN students st ON st.StudentID = en.StudentID
        WHERE en.LessonID = $lessonID
        ORDER BY StudentName
    ");

    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/** Existing marks for one club on one date: [EnrollmentID => 'Present'|'Absent']. */
function tt_extra_attendance_map($connect, int $lessonID, string $date): array
{
    $date = mysqli_real_escape_string($connect, $date);
    $map  = [];

    $res = tt_query($connect, "
        SELECT a.EnrollmentID, a.Status
        FROM attendance a
        JOIN enrollments en ON en.EnrollmentID = a.EnrollmentID
        WHERE en.LessonID = $lessonID AND a.Date = '$date'
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $map[(int) $row['EnrollmentID']] = $row['Status'];
        }
    }
    return $map;
}

/** Which of the given LessonIDs already have attendance recorded for a date. */
function tt_extra_marked_lesson_ids($connect, string $date, array $lessonIDs): array
{
    $lessonIDs = array_values(array_filter(array_map('intval', $lessonIDs)));
    if (!$lessonIDs) {
        return [];
    }
    $date   = mysqli_real_escape_string($connect, $date);
    $idList = implode(',', $lessonIDs);

    $res = tt_query($connect, "
        SELECT DISTINCT en.LessonID
        FROM attendance a
        JOIN enrollments en ON en.EnrollmentID = a.EnrollmentID
        WHERE a.Date = '$date' AND en.LessonID IN ($idList)
    ");

    $ids = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ids[] = (int) $row['LessonID'];
        }
    }
    return $ids;
}

/**
 * Upsert one status per enrolled student for a club+date. Every student
 * currently enrolled gets a row — unset means Absent, same rule the
 * original club attendance used. Re-saving just overwrites the marks.
 */
function tt_save_extra_attendance($connect, int $lessonID, string $date, array $statusByEnrollment): bool
{
    $date = mysqli_real_escape_string($connect, $date);
    $ok   = true;

    $all = tt_query($connect, "SELECT EnrollmentID FROM enrollments WHERE LessonID = $lessonID");
    if (!$all) {
        return false;
    }

    while ($row = mysqli_fetch_assoc($all)) {
        $enrollmentID = (int) $row['EnrollmentID'];
        $status       = (($statusByEnrollment[$enrollmentID] ?? '') === 'Absent') ? 'Absent' : 'Present';

        $res = tt_query($connect, "
            INSERT INTO attendance (EnrollmentID, Date, Status)
            VALUES ($enrollmentID, '$date', '$status')
            ON DUPLICATE KEY UPDATE Status = VALUES(Status)
        ");
        if (!$res) {
            $ok = false;
        }
    }
    return $ok;
}
