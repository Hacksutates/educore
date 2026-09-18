-- ============================================================
-- 03_class_attendance.sql
--
-- Adds attendance for regular timetable classes (a teacher's normal
-- schedule — Math, Physics, etc.), separate from the existing
-- `attendance` table which is used for extracurricular club
-- enrollments (`enrollments` / `extracurricular_lessons`).
--
-- Run this AFTER 01_schedule_generator.sql (needs `timetable_slots`
-- and `timetable_assignments` to already exist):
--   mysql -u student1 -p automated_system < sql/03_class_attendance.sql
--
-- Only ADDS a table. Nothing existing is changed.
-- ============================================================

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `class_attendance` (
  `AttendanceID` INT(11) NOT NULL AUTO_INCREMENT,
  `SlotID` INT(11) NOT NULL,
  `TimetableID` INT(11) NOT NULL,
  `StudentID` INT(11) NOT NULL,
  `TeacherID` INT(11) NOT NULL,
  `Date` DATE NOT NULL,
  `Status` ENUM('Present','Absent') NOT NULL DEFAULT 'Present',
  `MarkedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AttendanceID`),
  UNIQUE KEY `uniq_slot_student_date` (`SlotID`, `StudentID`, `Date`),
  KEY `idx_timetable_date` (`TimetableID`, `Date`),
  KEY `idx_student` (`StudentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
