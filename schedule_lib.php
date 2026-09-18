<?php
/**
 * Shared read helpers for the student/teacher schedule pages.
 * Kept separate so schedule1.php and schedule2.php stay short and
 * behave identically.
 */

/** The school week. */
function tt_days(): array
{
    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
}

/**
 * Fallback period grid, used so the table still draws its rows when a
 * person has nothing scheduled. Matches the generator's defaults.
 */
function tt_default_times(): array
{
    return ['08:30', '09:25', '10:20', '11:15', '12:10', '13:05'];
}

/**
 * Run a query and return false instead of blowing up when something is
 * wrong (missing table, bad column). PHP 8.1+ makes mysqli throw by
 * default, so the try/catch matters as much as the @.
 */
function tt_query($connect, string $sql)
{
    try {
        return @mysqli_query($connect, $sql);
    } catch (Throwable $e) {
        return false;
    }
}

/** Prepared-statement version of tt_query: returns false rather than throwing. */
function tt_prepare($connect, string $sql)
{
    try {
        return @mysqli_prepare($connect, $sql);
    } catch (Throwable $e) {
        return false;
    }
}

/** True when the schedule-generator tables have actually been created. */
function tt_tables_ready($connect): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    $ready = true;
    foreach (['timetables', 'timetable_slots', 'timetable_assignments', 'subjects'] as $table) {
        $res = tt_query($connect, "SHOW TABLES LIKE '$table'");
        if (!$res || mysqli_num_rows($res) === 0) {
            $ready = false;
            break;
        }
    }
    return $ready;
}

/**
 * Timetable IDs assigned to a person. Returns every match rather than just
 * one, so a database left with duplicate assignment rows still works.
 */
function tt_active_timetable_ids($connect, string $type, int $assigneeID): array
{
    $type = ($type === 'teacher') ? 'teacher' : 'student';
    $ids  = [];

    $res = tt_query($connect, "
        SELECT TimetableID FROM timetable_assignments
        WHERE AssigneeType = '$type' AND AssigneeID = $assigneeID
        ORDER BY AssignmentID DESC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ids[] = (int) $row['TimetableID'];
        }
    }
    return $ids;
}

/**
 * All classes on the given timetables, optionally narrowed to one teacher.
 * Uses LEFT JOINs so a slot still shows up if its teacher or room row was
 * deleted — previously one missing row silently removed the whole class.
 */
function tt_fetch_slots($connect, array $timetableIDs, ?int $teacherID = null): array
{
    $timetableIDs = array_filter(array_map('intval', $timetableIDs));
    if (!$timetableIDs) {
        return [];
    }

    $idList = implode(',', $timetableIDs);
    $where  = "ts.TimetableID IN ($idList)";
    if ($teacherID !== null) {
        $where .= " AND ts.TeacherID = " . (int) $teacherID;
    }

    $res = tt_query($connect, "
        SELECT ts.SlotID, ts.DayOfWeek, ts.TimeStart, ts.TimeEnd,
               COALESCE(s.SubjectName, 'Class')     AS SubjectName,
               COALESCE(t.TeacherName, 'TBA')       AS TeacherName,
               COALESCE(c.ClassroomName, 'TBA')     AS ClassroomName
        FROM timetable_slots ts
        LEFT JOIN subjects   s ON ts.SubjectID   = s.SubjectID
        LEFT JOIN teachers   t ON ts.TeacherID   = t.TeacherID
        LEFT JOIN classrooms c ON ts.ClassroomID = c.ClassroomID
        WHERE $where
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

/** Sorted, de-duplicated period list — never empty, so rows always render. */
function tt_normalise_times(array $times): array
{
    $times = array_values(array_unique(array_filter($times)));
    if (!$times) {
        return tt_default_times();
    }
    sort($times, SORT_STRING);
    return $times;
}

/**
 * Whether `teachers.SubjectID` exists yet (added by
 * sql/02_cleanup_and_teacher_subjects.sql). Older installs that haven't run
 * that migration don't have it, so every caller checks this before relying
 * on the column.
 */
function tt_has_teacher_subject_column($connect): bool
{
    static $has = null;
    if ($has !== null) {
        return $has;
    }
    $res = tt_query($connect, "SHOW COLUMNS FROM teachers LIKE 'SubjectID'");
    $has = (bool) ($res && mysqli_num_rows($res) > 0);
    return $has;
}

/**
 * Each teacher's assigned subject, where set. A teacher with no assigned
 * subject (NULL, or the column doesn't exist yet) is treated as a
 * generalist who can be booked for anything — only teachers who *do* have
 * an assigned subject are restricted to it.
 * Returns [TeacherID => SubjectID], omitting teachers with no assignment.
 */
function tt_teacher_subject_map($connect): array
{
    $map = [];
    if (!tt_has_teacher_subject_column($connect)) {
        return $map;
    }
    $res = tt_query($connect, "SELECT TeacherID, SubjectID FROM teachers WHERE SubjectID IS NOT NULL");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $map[(int) $row['TeacherID']] = (int) $row['SubjectID'];
        }
    }
    return $map;
}

/**
 * Narrow a list of teacher IDs down to the ones allowed to teach a given
 * subject: teachers assigned to that subject, plus any generalist teacher
 * who has no assigned subject at all. This is the single place that
 * enforces "a teacher only teaches their assigned subject".
 */
function tt_teachers_eligible_for_subject(array $teacherSubjectMap, int $subjectID, array $allTeacherIDs): array
{
    return array_values(array_filter($allTeacherIDs, function ($tid) use ($teacherSubjectMap, $subjectID) {
        return !isset($teacherSubjectMap[$tid]) || $teacherSubjectMap[$tid] === $subjectID;
    }));
}

/**
 * Extracurricular lessons a student is enrolled in (clubs, electives —
 * the `enrollments` / `extracurricular_lessons` tables), in the same shape
 * the timetable rows use, plus a real LessonID so the card can link to
 * subject_brief.php.
 */
function tt_fetch_enrolled_lessons($connect, int $studentID): array
{
    $res = tt_query($connect, "
        SELECT
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
        WHERE enrollments.StudentID = " . (int) $studentID . "
        ORDER BY lessonschedule.DayOfWeek, lessonschedule.TimeStart
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
 * Extracurricular lessons a teacher personally teaches (clubs, electives),
 * in the same shape the timetable rows use.
 */
function tt_fetch_teacher_lessons($connect, int $teacherID): array
{
    $res = tt_query($connect, "
        SELECT
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
        WHERE extracurricular_lessons.TeacherID = " . (int) $teacherID . "
        ORDER BY lessonschedule.DayOfWeek, lessonschedule.TimeStart
    ");

    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}