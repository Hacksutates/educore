-- ============================================================
-- 02_cleanup_and_teacher_subjects.sql
--
-- Run this AFTER your existing automated_system.sql import:
--   mysql -u student1 -p automated_system < sql/02_cleanup_and_teacher_subjects.sql
--
-- BACK UP YOUR DATABASE FIRST. This deletes a handful of junk rows
-- and adds a column to `teachers`.
-- ============================================================

START TRANSACTION;

-- ------------------------------------------------------------
-- 1. Give every teacher exactly one assigned subject
--    (this is the missing piece that lets you enforce
--    "a teacher only teaches their subject")
-- ------------------------------------------------------------
ALTER TABLE `teachers`
  ADD COLUMN `SubjectID` INT(11) DEFAULT NULL AFTER `TeacherName`;

ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teacher_subject`
  FOREIGN KEY (`SubjectID`) REFERENCES `subjects` (`SubjectID`);

-- Best-guess assignment based on which subject each teacher already
-- teaches most often in your existing timetable_slots data.
-- >>> CHECK THESE AGAINST REALITY AND EDIT BEFORE RUNNING <<<
UPDATE `teachers` SET `SubjectID` = 1  WHERE `TeacherID` = 1; -- Mr. Carter    -> Mathematics
UPDATE `teachers` SET `SubjectID` = 11 WHERE `TeacherID` = 2; -- Ms. Brown     -> Literature
UPDATE `teachers` SET `SubjectID` = 7  WHERE `TeacherID` = 3; -- Ms. Miller    -> Geography
UPDATE `teachers` SET `SubjectID` = 8  WHERE `TeacherID` = 4; -- Olga Sizova   -> Computer Science
UPDATE `teachers` SET `SubjectID` = 5  WHERE `TeacherID` = 5; -- A.Shertser    -> English
UPDATE `teachers` SET `SubjectID` = 6  WHERE `TeacherID` = 6; -- (renamed below) -> History
UPDATE `teachers` SET `SubjectID` = 4  WHERE `TeacherID` = 7; -- Alimzhan      -> Biology

-- ------------------------------------------------------------
-- 2. Clean up junk / joke data
-- ------------------------------------------------------------

-- "GG WP" was a placeholder name, not a real teacher
UPDATE `teachers` SET `TeacherName` = 'Nurlan Saparov' WHERE `TeacherID` = 6;

-- Trailing \r\n left over from a copy-paste in the student name
UPDATE `students` SET `StudentName` = TRIM(`StudentName`) WHERE `StudentID` = 19;

-- Empty test accounts (no name, no class, not referenced by any
-- enrollment or attendance row) — safe to remove
DELETE FROM `students` WHERE `StudentID` IN (12,13,14,15,16,17,18,20);

-- Exact duplicate accounts of a real student, not referenced elsewhere
DELETE FROM `students` WHERE `StudentID` IN (24,27,35);
-- 24 = duplicate "Polina Kossenko" (keep 21)
-- 27 = duplicate "Farida"          (keep 26)
-- 35 = duplicate "Alimzhan Zhangalishev" (keep 34)

-- ------------------------------------------------------------
-- 3. Assign real students to their homeroom class
--    (previously only StudentID 1-3 had a ClassID at all)
-- ------------------------------------------------------------
UPDATE `students` SET `ClassID` = 1 WHERE `StudentID` IN (21, 23, 30);   -- 11B
UPDATE `students` SET `ClassID` = 2 WHERE `StudentID` IN (22, 25, 26);  -- 11D
UPDATE `students` SET `ClassID` = 3 WHERE `StudentID` IN (31, 32, 33, 34); -- 10D

-- ------------------------------------------------------------
-- 4. A few new teachers, each with a real subject from day one
-- ------------------------------------------------------------
INSERT INTO `teachers` (`TeacherName`, `SubjectID`, `Login_Teacher`, `Password_Teacher`, `TeacherRole`) VALUES
('Aigerim Bekova',   3, 'bekova@nis',  'chem12345', 2), -- Chemistry
('Yerlan Dosov',     9, 'dosov@nis',   'pe1234567', 2), -- Physical Education
('Saltanat Iskakova',10,'iskakova@nis','art123456', 2); -- Art

-- ------------------------------------------------------------
-- 5. A few new students
-- ------------------------------------------------------------
INSERT INTO `students` (`StudentName`, `ClassID`, `StudentRole`, `Login_Student`, `Password_Student`) VALUES
('Aruzhan Nurlanova', 1, 1, 'aru@nis', 'aru12345'),
('Bekzat Amirov',     2, 1, 'bek@nis', 'bek12345'),
('Dinara Tolegen',    3, 1, 'din@nis', 'din12345');

-- ------------------------------------------------------------
-- 6. Sanity check — run this after the migration and fix any
--    row it returns before you rely on the data:
--    teachers whose already-existing timetable slots don't
--    match their newly assigned subject.
-- ------------------------------------------------------------
-- SELECT ts.SlotID, ts.SubjectID AS SlotSubject, t.TeacherName, t.SubjectID AS AssignedSubject
-- FROM timetable_slots ts
-- JOIN teachers t ON t.TeacherID = ts.TeacherID
-- WHERE t.SubjectID IS NOT NULL AND t.SubjectID <> ts.SubjectID;

COMMIT;