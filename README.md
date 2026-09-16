# EduCore — Schedule Generator

Adds a "supervisor" schedule generator on top of your existing EduCore setup,
without touching `schedulestudent.php` or `scheduleteacher.php` at all.

## What it does

- A supervisor builds a **timetable**: a named weekly schedule made of classes
  (day, time, subject, teacher, room) — e.g. "Grade 10A – Term 1".
- The supervisor **assigns** that timetable as the **active schedule** for any
  number of students and/or teachers, from one simple checkbox list.
- Each student/teacher only ever has **one active timetable**. Assigning a new
  one automatically replaces their old one — no manual cleanup needed.
- A **student's** schedule page shows every subject on their active timetable
  (Math, Physics, English, ...).
- A **teacher's** schedule page shows only the periods on that timetable where
  *they* are the assigned teacher — not the whole class's schedule.

## Install

1. **Back up first**, then run the migration:
   `sql/01_schedule_generator.sql` against your `automated_system` database.
   It only *adds* tables (`subjects`, `timetables`, `timetable_slots`,
   `timetable_assignments`) — nothing existing is altered or dropped.

2. Copy these files into your project root, next to `connection.php`:
   - `timetable_common.php`
   - `timetables.php`
   - `timetable_builder.php`
   - `timetable_assign.php`
   - `style_timetable.css`

3. **Replace** your existing `schedule1.php` and `schedule2.php` with the
   versions in this folder (back up the originals first — they're currently
   built off `enrollments` / a fixed `TeacherID`, this version reads from the
   new active-timetable tables instead). `schedulestudent.php` and
   `scheduleteacher.php` don't need any changes — they just `include` these
   two files and keep working.

4. Add a link to `timetables.php` from wherever your supervisor's dashboard
   lives (e.g. `main.html` / `admin.php`), so supervisors can get to it.

## Two assumptions worth double-checking

These are based on the naming patterns already used elsewhere in your code
(`TeacherName`, `ClassroomName`, `supervisor_name`) — if your actual columns
differ, these are the only two places to adjust:

- **`students.StudentName`** — used in `timetable_assign.php`. If your
  students table names this column differently, update the `SELECT` queries
  there.
- **`$_SESSION['supervisor_id']`** — `timetable_common.php` assumes your
  login script (`script1.php`) sets this on supervisor login, matching the
  `supervisors.supervisor_id` column already joined in `admin.php`. If your
  session key is different, change the one line at the top of
  `timetable_common.php`.

## Supervisor workflow

1. Open **Schedules** (`timetables.php`) → *Create a new schedule* → name it.
2. You land in the **builder** (`timetable_builder.php`) → add classes one at
   a time (day, start/end time, subject, teacher, room). The week view below
   fills in as you go; remove any class with one click. Double-booked
   day/time slots on the same schedule are rejected automatically.
3. Click **Assign this schedule** → tick the students and/or teachers it
   should apply to → *Assign selected*. Done — their schedule pages update
   immediately.
4. To move a class or a teacher onto a different schedule later, just assign
   them to the new one; the old assignment is replaced automatically. To pull
   someone off a schedule entirely, hit *Remove* next to their name on the
   assign page.

## Extending later (not needed now, but easy to add)

- **Multiple active timetables per teacher** — useful if one teacher teaches
  across several grades' schedules. Currently a teacher has one active
  timetable at a time, same as students. To allow more, drop the
  `uniq_active_person` unique key's restriction for `AssigneeType = 'teacher'`
  and have `schedule2.php` union slots across all of a teacher's assigned
  timetables instead of looking up a single `TimetableID`.
- **Duplicate a schedule** — handy for copying last term's timetable as a
  starting point. Add a "Duplicate" action in `timetables.php` that copies a
  `timetables` row plus its `timetable_slots`.
- **Bulk-assign a whole grade at once** — if students have a `GradeLevel` or
  `ClassGroup` column, add a "select by class" shortcut above the checkbox
  list in `timetable_assign.php`.
