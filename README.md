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

---

# What changed in this update

## 1. Fixed: student and teacher schedules showed no rows

The grid is drawn by looping over `$times`, and `$times` was built purely from
the rows the query returned. So whenever the query returned nothing, `$times`
was an empty array and the page rendered the header row and stopped — a table
with no rows, and no explanation.

Four things caused the query to come back empty:

| Cause | Fix |
|---|---|
| The `sql/` migration was never in the package, so `timetables`, `timetable_slots`, `timetable_assignments` and `subjects` did not exist. Every query failed silently. | `sql/01_schedule_generator.sql` is now included. Run it once. |
| No timetable assigned to that person yet. | The page now says so instead of showing a blank grid. |
| A teacher was put on a class but never *assigned* a timetable. | `schedule2.php` now also picks up any **published** timetable that lists them as the teacher. |
| A slot whose teacher or room row had been deleted was dropped by the `JOIN`. | Queries use `LEFT JOIN` with `TBA` fallbacks, so the class still appears. |

The grid now always renders its rows (falling back to the default period times
when empty), and any problem is explained in a message above the table.

Also fixed: the student view linked every class to
`subject_brief.php?lesson_id=` with an empty id, because timetable classes have
no `LessonID`. Cards are only wrapped in a link when there is something to link
to. Output is escaped with `htmlspecialchars` throughout.

## 2. New: one-click automatic generation

`timetable_generate.php` — reachable from the builder ("Auto-generate") and from
the *Generate* action on the schedules list.

Press **Generate schedule** and it fills the entire week. No configuration is
required; the defaults are 5 days, 6 periods a day, 45-minute lessons from
08:30 with 10-minute breaks, and every subject in the database spread evenly
across the week.

The engine (`timetable_engine.php`) respects:

- one class per period on the timetable
- no teacher in two rooms at the same time — **checked across every timetable**,
  not just the one being generated
- no room used by two classes at the same time, same global check
- a subject is not repeated within a day unless the week is too tight to avoid it
- an optional home room, so the class stays put where possible

It works by randomised greedy placement with restarts: each attempt shuffles the
order lessons and periods are tried, hardest subjects (fewest eligible teachers)
go first, and the best of 60 attempts is kept. A normal school week solves in
well under a second. If something genuinely cannot fit — a pinned teacher who is
already booked solid — it places everything else and tells you exactly how many
classes it could not place and why.

Optional settings sit behind a collapsed *Settings* panel: periods per day,
start time, lesson length, break length, which days to use, lessons-per-week per
subject, and a specific teacher per subject ("Any teacher" by default).

## 3. New: install guard

Supervisor pages used to die with a raw SQL error when the tables were missing.
They now show a short page telling you which migration to run.

## Files added

- `sql/01_schedule_generator.sql` — the missing migration
- `sql/02_repair_existing.sql` — only if you created the tables by hand; removes
  duplicate assignments and adds the unique keys the app relies on
- `schedule_lib.php` — shared, error-tolerant read helpers
- `timetable_engine.php` — the generation algorithm
- `timetable_generate.php` — the generator page

## Files changed

`schedule1.php`, `schedule2.php`, `schedulestudent.php`, `scheduleteacher.php`,
`timetable_common.php`, `timetable_builder.php`, `timetables.php`,
`style6.css`, `style_timetable.css`

## 4. New: teacher class attendance (by day, whole grade at once)

A teacher can now mark attendance straight from their own timetable,
instead of hand-picking a lesson ID and typing a student's name.

- **`scheduleteacher.php`** → **Mark Attendance** opens `markattendance.php`.
- Pick a date (defaults to today). The page shows every class *that
  teacher personally teaches* on that date's weekday, pulled from their
  timetable — subject, time, room, and which grade (timetable) it is.
- Click a class to see the whole grade's roster — every student assigned
  to that class's timetable — with a Present/Absent toggle per student
  (defaults to Present, or whatever was last saved for that date).
  "Mark all present" / "Mark all absent" speed up the common case.
- **Save attendance** writes one row per student to the new
  `class_attendance` table (`SlotID`, `StudentID`, `Date`, `Status`,
  `TeacherID`); saving again for the same class/date overwrites the
  previous marks, so mistakes are easy to fix.

This is separate from the existing `attendance` table, which is only
for extracurricular-club enrollments (`enrollments` /
`extracurricular_lessons`) and still powers the supervisor's
`attendancemanagement.php` reports — nothing there changed.

### Install

Run the extra migration (needs `timetable_slots` / `timetable_assignments`
from `01_schedule_generator.sql` already in place):

```
mysql -u student1 -p automated_system < sql/03_class_attendance.sql
```

### Files added

- `sql/03_class_attendance.sql` — the `class_attendance` table
- `attendance_lib.php` — read/write helpers for it

### Files changed

- `mark.php` — now the attendance controller (date → day's classes →
  roster → save), reading from the timetable instead of a hardcoded
  lesson dropdown
- `markattendance.php` — new view: date picker, class-of-the-day cards,
  roster table with Present/Absent pills
- `style6.css` — styles for the date picker, class cards, roster table
  and attendance pills (mobile-responsive, matching the rest of the site)

## To get running

1. `mysql -u student1 -p automated_system < sql/01_schedule_generator.sql`
2. Make sure `classrooms` and `teachers` have rows (the migration seeds
   `subjects` for you).
3. Log in as supervisor → **Schedules** → create one → **Auto-generate** →
   *Generate schedule*.
4. **Assign** it to students and teachers, and **Publish** it.
5. Open a student or teacher account — the grid is filled in.
