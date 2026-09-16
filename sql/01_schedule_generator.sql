-- ============================================================
-- EduCore — Schedule Generator migration
-- Safe to run more than once: everything is CREATE ... IF NOT EXISTS.
-- Nothing existing is altered or dropped.
--
--   mysql -u student1 -p automated_system < sql/01_schedule_generator.sql
-- ============================================================

-- Subjects taught on a timetable (Math, Physics, ...).
CREATE TABLE IF NOT EXISTS subjects (
    SubjectID   INT AUTO_INCREMENT PRIMARY KEY,
    SubjectName VARCHAR(100) NOT NULL,
    UNIQUE KEY uniq_subject_name (SubjectName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A named weekly schedule, e.g. "Grade 10A - Term 1".
CREATE TABLE IF NOT EXISTS timetables (
    TimetableID INT AUTO_INCREMENT PRIMARY KEY,
    Name        VARCHAR(150) NOT NULL,
    Status      ENUM('draft','published') NOT NULL DEFAULT 'draft',
    CreatedBy   INT NULL,
    CreatedAt   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One class period on a timetable.
CREATE TABLE IF NOT EXISTS timetable_slots (
    SlotID      INT AUTO_INCREMENT PRIMARY KEY,
    TimetableID INT NOT NULL,
    SubjectID   INT NOT NULL,
    TeacherID   INT NOT NULL,
    ClassroomID INT NOT NULL,
    DayOfWeek   VARCHAR(10) NOT NULL,
    TimeStart   TIME NOT NULL,
    TimeEnd     TIME NOT NULL,
    -- one class at a time per timetable: blocks double-booking a period
    UNIQUE KEY uniq_slot (TimetableID, DayOfWeek, TimeStart),
    KEY idx_teacher (TeacherID, DayOfWeek, TimeStart),
    KEY idx_room (ClassroomID, DayOfWeek, TimeStart),
    KEY idx_timetable (TimetableID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Which timetable is active for which student / teacher.
CREATE TABLE IF NOT EXISTS timetable_assignments (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    TimetableID  INT NOT NULL,
    AssigneeType ENUM('student','teacher') NOT NULL,
    AssigneeID   INT NOT NULL,
    AssignedAt   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- one active timetable per person: re-assigning replaces the old one
    UNIQUE KEY uniq_active_person (AssigneeType, AssigneeID),
    KEY idx_timetable (TimetableID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A starter set of subjects so the generator has something to work with.
INSERT IGNORE INTO subjects (SubjectName) VALUES
    ('Mathematics'), ('Physics'), ('Chemistry'), ('Biology'),
    ('History'), ('Geography'), ('English'), ('Literature'),
    ('Computer Science'), ('Physical Education');
