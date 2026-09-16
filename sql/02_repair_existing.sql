-- ============================================================
-- EduCore — repair script
-- Only needed if you created the schedule-generator tables BEFORE
-- running 01_schedule_generator.sql (e.g. by hand). It removes
-- duplicate assignments and adds the unique keys the app relies on.
--
-- Each ALTER may report "Duplicate key name" if the key already
-- exists — that error is harmless, just move on to the next line.
-- ============================================================

-- 1. Drop duplicate assignments, keeping the most recent one per person.
DELETE ta FROM timetable_assignments ta
JOIN timetable_assignments keep
  ON ta.AssigneeType = keep.AssigneeType
 AND ta.AssigneeID   = keep.AssigneeID
 AND ta.AssignmentID < keep.AssignmentID;

-- 2. One active timetable per person (makes re-assigning replace, not stack).
ALTER TABLE timetable_assignments
    ADD UNIQUE KEY uniq_active_person (AssigneeType, AssigneeID);

-- 3. One class per period per timetable.
ALTER TABLE timetable_slots
    ADD UNIQUE KEY uniq_slot (TimetableID, DayOfWeek, TimeStart);

-- 4. Indexes the conflict checker uses.
ALTER TABLE timetable_slots ADD KEY idx_teacher (TeacherID, DayOfWeek, TimeStart);
ALTER TABLE timetable_slots ADD KEY idx_room (ClassroomID, DayOfWeek, TimeStart);
