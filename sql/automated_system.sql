-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Сен 17 2026 г., 17:57
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `automated_system`
--

-- --------------------------------------------------------

--
-- Структура таблицы `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Status` enum('Present','Absent') DEFAULT NULL,
  `EnrollmentID` int(4) DEFAULT NULL,
  `ScheduleID` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `attendance`
--

INSERT INTO `attendance` (`AttendanceID`, `Date`, `Status`, `EnrollmentID`, `ScheduleID`) VALUES
(1, '2026-02-11', 'Present', 4, NULL),
(2, '2026-02-11', 'Present', 5, NULL),
(3, '2026-02-11', 'Absent', 11, NULL),
(4, '2026-02-04', 'Absent', 4, NULL),
(5, '2026-02-04', 'Absent', 5, NULL),
(6, '2026-02-04', 'Present', 11, NULL),
(7, '2026-02-17', 'Present', 4, NULL),
(8, '2026-02-17', 'Absent', 5, NULL),
(9, '2026-02-17', 'Absent', 11, NULL),
(10, '2026-02-17', 'Present', 15, NULL),
(11, '2026-09-01', 'Absent', 4, NULL),
(12, '2026-09-03', 'Present', 4, NULL),
(13, '2026-09-08', 'Present', 4, NULL),
(14, '2026-09-10', 'Absent', 4, NULL),
(15, '2026-09-15', 'Absent', 4, NULL),
(16, '2026-09-17', 'Absent', 4, NULL),
(17, '2026-09-22', 'Absent', 4, NULL),
(18, '2026-09-24', 'Present', 4, NULL),
(19, '2026-09-01', 'Absent', 5, NULL),
(20, '2026-09-03', 'Absent', 5, NULL),
(21, '2026-09-08', 'Absent', 5, NULL),
(22, '2026-09-10', 'Present', 5, NULL),
(23, '2026-09-15', 'Absent', 5, NULL),
(24, '2026-09-17', 'Present', 5, NULL),
(25, '2026-09-22', 'Absent', 5, NULL),
(26, '2026-09-24', 'Absent', 5, NULL),
(27, '2026-09-01', 'Present', 11, NULL),
(28, '2026-09-03', 'Present', 11, NULL),
(29, '2026-09-08', 'Absent', 11, NULL),
(30, '2026-09-10', 'Present', 11, NULL),
(31, '2026-09-15', 'Absent', 11, NULL),
(32, '2026-09-17', 'Absent', 11, NULL),
(33, '2026-09-22', 'Absent', 11, NULL),
(34, '2026-09-24', 'Absent', 11, NULL),
(35, '2026-09-01', 'Present', 15, NULL),
(36, '2026-09-03', 'Present', 15, NULL),
(37, '2026-09-08', 'Absent', 15, NULL),
(38, '2026-09-10', 'Absent', 15, NULL),
(39, '2026-09-15', 'Present', 15, NULL),
(40, '2026-09-17', 'Present', 15, NULL),
(41, '2026-09-22', 'Absent', 15, NULL),
(42, '2026-09-24', 'Present', 15, NULL),
(45, '2026-10-06', 'Present', 4, NULL),
(46, '2026-10-06', 'Present', 5, NULL),
(47, '2026-10-06', 'Absent', 11, NULL),
(48, '2026-10-06', 'Present', 15, NULL),
(49, '2026-10-06', 'Present', 16, NULL),
(50, '2026-10-08', 'Present', 4, NULL),
(51, '2026-10-08', 'Absent', 5, NULL),
(52, '2026-10-08', 'Present', 11, NULL),
(53, '2026-10-08', 'Present', 15, NULL),
(54, '2026-10-08', 'Absent', 16, NULL),
(55, '2026-10-13', 'Present', 4, NULL),
(56, '2026-10-13', 'Present', 5, NULL),
(57, '2026-10-13', 'Present', 11, NULL),
(58, '2026-10-13', 'Absent', 15, NULL),
(59, '2026-10-13', 'Present', 16, NULL),
(60, '2026-10-15', 'Absent', 4, NULL),
(61, '2026-10-15', 'Present', 5, NULL),
(62, '2026-10-15', 'Present', 11, NULL),
(63, '2026-10-15', 'Present', 15, NULL),
(64, '2026-10-15', 'Present', 16, NULL),
(65, '2026-11-03', 'Present', 4, NULL),
(66, '2026-11-03', 'Present', 5, NULL),
(67, '2026-11-03', 'Present', 11, NULL),
(68, '2026-11-03', 'Absent', 15, NULL),
(69, '2026-11-03', 'Present', 16, NULL),
(70, '2026-11-05', 'Absent', 4, NULL),
(71, '2026-11-05', 'Present', 5, NULL),
(72, '2026-11-05', 'Present', 11, NULL),
(73, '2026-11-05', 'Present', 15, NULL),
(74, '2026-11-05', 'Present', 16, NULL),
(75, '2026-11-10', 'Present', 4, NULL),
(76, '2026-11-10', 'Present', 5, NULL),
(77, '2026-11-10', 'Absent', 11, NULL),
(78, '2026-11-10', 'Present', 15, NULL),
(79, '2026-11-10', 'Present', 16, NULL),
(80, '2026-11-12', 'Present', 4, NULL),
(81, '2026-11-12', 'Absent', 5, NULL),
(82, '2026-11-12', 'Present', 11, NULL),
(83, '2026-11-12', 'Present', 15, NULL),
(84, '2026-11-12', 'Present', 16, NULL),
(85, '2026-12-01', 'Present', 4, NULL),
(86, '2026-12-01', 'Present', 5, NULL),
(87, '2026-12-01', 'Absent', 11, NULL),
(88, '2026-12-01', 'Present', 15, NULL),
(89, '2026-12-01', 'Present', 16, NULL),
(90, '2026-12-03', 'Present', 4, NULL),
(91, '2026-12-03', 'Absent', 5, NULL),
(92, '2026-12-03', 'Present', 11, NULL),
(93, '2026-12-03', 'Present', 15, NULL),
(94, '2026-12-03', 'Present', 16, NULL),
(95, '2026-09-08', 'Present', 2, NULL),
(96, '2026-09-08', 'Absent', 13, NULL),
(97, '2026-09-08', 'Present', 8, NULL),
(98, '2026-09-10', 'Present', 2, NULL),
(99, '2026-09-10', 'Present', 13, NULL),
(100, '2026-09-10', 'Absent', 8, NULL),
(101, '2026-09-15', 'Present', 2, NULL),
(102, '2026-09-15', 'Present', 13, NULL),
(103, '2026-09-15', 'Present', 8, NULL),
(104, '2026-09-17', 'Absent', 2, NULL),
(105, '2026-09-17', 'Present', 13, NULL),
(106, '2026-09-17', 'Present', 8, NULL),
(107, '2026-10-06', 'Present', 2, NULL),
(108, '2026-10-06', 'Present', 13, NULL),
(109, '2026-10-06', 'Present', 8, NULL),
(110, '2026-10-08', 'Absent', 2, NULL),
(111, '2026-10-08', 'Present', 13, NULL),
(112, '2026-10-08', 'Present', 8, NULL),
(113, '2026-11-03', 'Present', 2, NULL),
(114, '2026-11-03', 'Absent', 13, NULL),
(115, '2026-11-03', 'Present', 8, NULL),
(116, '2026-11-05', 'Present', 2, NULL),
(117, '2026-11-05', 'Present', 13, NULL),
(118, '2026-11-05', 'Absent', 8, NULL),
(119, '2026-12-01', 'Present', 2, NULL),
(120, '2026-12-01', 'Present', 13, NULL),
(121, '2026-12-01', 'Present', 8, NULL),
(122, '2026-12-03', 'Absent', 2, NULL),
(123, '2026-12-03', 'Present', 13, NULL),
(124, '2026-12-03', 'Present', 8, NULL),
(125, '2026-10-13', 'Present', 2, NULL),
(126, '2026-10-13', 'Present', 13, NULL),
(127, '2026-10-13', 'Absent', 8, NULL),
(128, '2026-10-15', 'Present', 2, NULL),
(129, '2026-10-15', 'Present', 13, NULL),
(130, '2026-10-15', 'Present', 8, NULL),
(131, '2026-10-20', 'Absent', 2, NULL),
(132, '2026-10-20', 'Present', 13, NULL),
(133, '2026-10-20', 'Present', 8, NULL),
(134, '2026-10-22', 'Present', 2, NULL),
(135, '2026-10-22', 'Absent', 13, NULL),
(136, '2026-10-22', 'Present', 8, NULL),
(137, '2026-10-27', 'Present', 2, NULL),
(138, '2026-10-27', 'Present', 13, NULL),
(139, '2026-10-27', 'Present', 8, NULL),
(140, '2026-10-29', 'Present', 2, NULL),
(141, '2026-10-29', 'Present', 13, NULL),
(142, '2026-10-29', 'Absent', 8, NULL),
(143, '2026-11-10', 'Present', 2, NULL),
(144, '2026-11-10', 'Present', 13, NULL),
(145, '2026-11-10', 'Present', 8, NULL),
(146, '2026-11-12', 'Absent', 2, NULL),
(147, '2026-11-12', 'Present', 13, NULL),
(148, '2026-11-12', 'Present', 8, NULL),
(149, '2026-11-17', 'Present', 2, NULL),
(150, '2026-11-17', 'Present', 13, NULL),
(151, '2026-11-17', 'Absent', 8, NULL),
(152, '2026-11-19', 'Present', 2, NULL),
(153, '2026-11-19', 'Absent', 13, NULL),
(154, '2026-11-19', 'Present', 8, NULL),
(155, '2026-11-24', 'Present', 2, NULL),
(156, '2026-11-24', 'Present', 13, NULL),
(157, '2026-11-24', 'Present', 8, NULL),
(158, '2026-11-26', 'Absent', 2, NULL),
(159, '2026-11-26', 'Present', 13, NULL),
(160, '2026-11-26', 'Present', 8, NULL),
(161, '2026-12-08', 'Present', 2, NULL),
(162, '2026-12-08', 'Present', 13, NULL),
(163, '2026-12-08', 'Present', 8, NULL),
(164, '2026-12-10', 'Present', 2, NULL),
(165, '2026-12-10', 'Absent', 13, NULL),
(166, '2026-12-10', 'Present', 8, NULL),
(167, '2026-12-15', 'Present', 2, NULL),
(168, '2026-12-15', 'Present', 13, NULL),
(169, '2026-12-15', 'Present', 8, NULL),
(170, '2026-12-17', 'Absent', 2, NULL),
(171, '2026-12-17', 'Present', 13, NULL),
(172, '2026-12-17', 'Present', 8, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `classes`
--

CREATE TABLE `classes` (
  `ClassID` int(4) NOT NULL,
  `Class` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `classes`
--

INSERT INTO `classes` (`ClassID`, `Class`) VALUES
(1, '11B'),
(2, '11D'),
(3, '10D');

-- --------------------------------------------------------

--
-- Структура таблицы `classrooms`
--

CREATE TABLE `classrooms` (
  `ClassroomID` int(4) NOT NULL,
  `ClassroomName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `classrooms`
--

INSERT INTO `classrooms` (`ClassroomID`, `ClassroomName`) VALUES
(1, 'Gym Hall'),
(2, 'Room 102'),
(3, 'Room 210'),
(4, 'Room 220');

-- --------------------------------------------------------

--
-- Структура таблицы `enrollments`
--

CREATE TABLE `enrollments` (
  `EnrollmentID` int(4) NOT NULL,
  `StudentID` int(4) DEFAULT NULL,
  `LessonID` int(4) DEFAULT NULL,
  `EnrollDate` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `enrollments`
--

INSERT INTO `enrollments` (`EnrollmentID`, `StudentID`, `LessonID`, `EnrollDate`) VALUES
(1, 1, 1, '2025-09-09'),
(2, 2, 2, '2025-09-09'),
(3, 3, 3, '2025-09-09'),
(4, 23, 4, '2026-01-27'),
(5, 19, 4, '2026-01-27'),
(6, 19, 1, '2026-01-30'),
(7, 19, 3, '2026-01-30'),
(8, 21, 2, '2026-01-30'),
(9, 21, 1, '2026-01-30'),
(11, 21, 4, '2026-01-30'),
(13, 3, 2, '2026-02-10'),
(14, 3, 1, '2026-02-10'),
(15, 30, 4, '2026-02-17'),
(16, 31, 4, '2026-03-31'),
(17, 23, 2, '2026-04-08'),
(18, 21, 3, '2026-04-09'),
(19, 34, 1, '2026-09-07'),
(20, 34, 3, '2026-09-16'),
(21, 34, 2, '2026-09-16');

-- --------------------------------------------------------

--
-- Структура таблицы `extracurricular_lessons`
--

CREATE TABLE `extracurricular_lessons` (
  `LessonID` int(4) NOT NULL,
  `LessonName` varchar(255) NOT NULL,
  `TeacherID` int(4) NOT NULL,
  `ClassroomID` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `extracurricular_lessons`
--

INSERT INTO `extracurricular_lessons` (`LessonID`, `LessonName`, `TeacherID`, `ClassroomID`) VALUES
(1, 'Dance club', 1, 1),
(2, 'Debate club', 2, 2),
(3, 'Chess club', 3, 3),
(4, 'Robotics', 5, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `lessonschedule`
--

CREATE TABLE `lessonschedule` (
  `ScheduleID` int(4) NOT NULL,
  `LessonID` int(4) DEFAULT NULL,
  `DayOfWeek` varchar(10) DEFAULT NULL,
  `TimeStart` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `lessonschedule`
--

INSERT INTO `lessonschedule` (`ScheduleID`, `LessonID`, `DayOfWeek`, `TimeStart`) VALUES
(1, 1, 'Monday', '15:45:00'),
(2, 1, 'Wednesday', '15:45:00'),
(3, 2, 'Monday', '15:00:00'),
(4, 2, 'Wednesday', '15:00:00'),
(5, 3, 'Tuesday', '16:30:00'),
(6, 3, 'Thursday', '16:30:00'),
(7, 4, 'Tuesday', '14:15:00'),
(8, 4, 'Thursday', '14:15:00');

-- --------------------------------------------------------

--
-- Структура таблицы `reports`
--

CREATE TABLE `reports` (
  `ReportID` int(11) NOT NULL,
  `LessonID` int(11) DEFAULT NULL,
  `Attested` int(11) DEFAULT NULL,
  `NotAttested` int(11) DEFAULT NULL,
  `Comment` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `PeriodType` varchar(20) DEFAULT NULL,
  `TotalStudents` int(11) DEFAULT NULL,
  `TotalLessons` int(11) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `PeriodValue` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `reports`
--

INSERT INTO `reports` (`ReportID`, `LessonID`, `Attested`, `NotAttested`, `Comment`, `CreatedAt`, `PeriodType`, `TotalStudents`, `TotalLessons`, `supervisor_id`, `PeriodValue`) VALUES
(1, 4, 2, 3, 'lol', '2026-04-05 16:45:55', NULL, NULL, NULL, NULL, NULL),
(2, 4, 0, 5, 'idk', '2026-04-06 07:40:13', NULL, NULL, NULL, NULL, NULL),
(3, 4, 3, 2, '', '2026-04-06 07:53:08', NULL, NULL, NULL, NULL, NULL),
(4, 4, 2, 3, 'lol', '2026-04-06 08:41:44', 'sem1', 5, 8, NULL, NULL),
(5, 4, 0, 5, 'lol', '2026-04-06 08:49:28', 'sem1', 5, 8, NULL, NULL),
(6, 4, 0, 5, 'lol', '2026-04-06 08:50:25', 'sem1', 5, 8, NULL, NULL),
(25, 4, 0, 5, 'Attendance across the group is mixed this period, averaging 63%. Of 5 students, 1 student maintained strong attendance at 80% or above, and 4 students showed moderate attendance between 50% and 79%.', '2026-04-07 13:18:00', 'sem1', 5, 18, 2, 'sem1'),
(26, 4, 2, 3, 'lol', '2026-04-07 13:19:21', 'sem1', NULL, NULL, 2, 'sem1'),
(28, 4, 2, 3, 'pop', '2026-04-07 16:31:40', 'sem1', 5, 11, 2, 'sem1'),
(29, 2, 0, 3, 'The attendance is average', '2026-04-08 11:28:20', 'month', 3, 4, 1, '2026-09'),
(30, 2, 0, 3, 'Changes in attendance are minimal', '2026-04-08 11:32:41', 'month', 3, 8, 1, '2026-10'),
(31, 2, 0, 3, 'good', '2026-04-08 11:33:13', 'month', 3, 8, 1, '2026-11'),
(32, 2, 0, 3, 'Students attend the lessons quite well', '2026-04-08 11:33:49', 'month', 3, 6, 1, '2026-12'),
(34, 2, 2, 1, 'excellent', '2026-04-08 14:30:36', 'sem1', 3, 26, 2, 'sem1'),
(35, 4, 0, 5, 'fine', '2026-04-08 14:57:50', 'month', 5, 4, 2, '2026-10');

-- --------------------------------------------------------

--
-- Структура таблицы `report_students`
--

CREATE TABLE `report_students` (
  `id` int(11) NOT NULL,
  `ReportID` int(11) DEFAULT NULL,
  `EnrollmentID` int(11) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `report_students`
--

INSERT INTO `report_students` (`id`, `ReportID`, `EnrollmentID`, `Status`) VALUES
(1, 6, 4, 'not_attested'),
(2, 6, 5, 'not_attested'),
(3, 6, 11, 'not_attested'),
(4, 6, 15, 'not_attested'),
(5, 6, 16, 'not_attested'),
(6, 26, 4, 'attested'),
(7, 26, 5, 'not_attested'),
(8, 26, 11, 'attested'),
(9, 26, 15, 'not_attested'),
(10, 26, 16, 'not_attested'),
(16, 28, 4, 'not_attested'),
(17, 28, 5, 'not_attested'),
(18, 28, 11, 'not_attested'),
(19, 28, 15, 'attested'),
(20, 28, 16, 'not_attested'),
(45, 34, 2, 'attested'),
(46, 34, 8, 'attested'),
(47, 34, 13, 'attested'),
(73, 25, 4, 'not_attested'),
(74, 25, 5, 'not_attested'),
(75, 25, 11, 'not_attested'),
(76, 25, 15, 'not_attested'),
(77, 25, 16, 'not_attested');

-- --------------------------------------------------------

--
-- Структура таблицы `students`
--

CREATE TABLE `students` (
  `StudentID` int(4) NOT NULL,
  `StudentName` varchar(255) DEFAULT NULL,
  `ClassID` int(4) DEFAULT NULL,
  `StudentRole` int(1) DEFAULT NULL,
  `Login_Student` varchar(255) DEFAULT NULL,
  `Password_Student` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `students`
--

INSERT INTO `students` (`StudentID`, `StudentName`, `ClassID`, `StudentRole`, `Login_Student`, `Password_Student`) VALUES
(1, 'Shakherezada Islamkhan', 1, 1, 'sha@nis', 'sha123khe'),
(2, 'Asemgul Kazym ', 2, 1, 'kaz@nis', 'kaz223ym'),
(3, 'Danial Zhaxalyk', 3, 1, 'zha@nis', 'zha323xal'),
(12, '', NULL, NULL, 'student1', '123456'),
(13, '', NULL, NULL, 'student1', '123456'),
(14, '', NULL, NULL, 'student2', '87654321'),
(15, '', NULL, NULL, 'student2', '87654321'),
(16, '', NULL, NULL, 'student3', '000'),
(17, '', NULL, NULL, 'student4', '0987'),
(18, '', NULL, NULL, 'student11', '000'),
(19, 'Zhansaya Zhanibekova\r\n', NULL, NULL, 'student1', '123456'),
(20, '', NULL, NULL, 'student8', '888'),
(21, 'Polina Kossenko', NULL, NULL, 'student@228', '22800000'),
(22, 'Vova Kossenko', NULL, NULL, 'vova228', '000'),
(23, 'Sabina Kelbatyrova', NULL, NULL, 'student@111', '555000555'),
(24, 'Polina Kossenko', NULL, NULL, 'student1', '123456'),
(25, 'Sultan Kabdykolikov', NULL, NULL, 'student000', '123456'),
(26, 'Farida', NULL, NULL, 'student333', '123456'),
(27, 'Farida', NULL, NULL, 'student333', '123456'),
(28, 'Zhansaya Zhanibekova', NULL, NULL, 'student1', '12345678'),
(30, 'Andrey Yenbakhtov', NULL, NULL, 'andrey@nis', '87654321'),
(31, 'Madina Shayakhmetova', NULL, NULL, 'student@555', '12345600'),
(32, 'Inara Irgizbayeva', NULL, NULL, 'student@000', '000000000'),
(33, 'Alisher Khaliyev', NULL, NULL, 'student@2222', '00000000'),
(34, 'Alimzhan Zhangalishev', NULL, NULL, 'hacksutates@gmail.com', '12345678'),
(35, 'Alimzhan Zhangalishev', NULL, NULL, 'hacksutates@gmail.com', '12345678');

-- --------------------------------------------------------

--
-- Структура таблицы `subjects`
--

CREATE TABLE `subjects` (
  `SubjectID` int(11) NOT NULL,
  `SubjectName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `subjects`
--

INSERT INTO `subjects` (`SubjectID`, `SubjectName`) VALUES
(10, 'Art'),
(4, 'Biology'),
(3, 'Chemistry'),
(8, 'Computer Science'),
(5, 'English'),
(7, 'Geography'),
(6, 'History'),
(11, 'Literature'),
(1, 'Mathematics'),
(9, 'Physical Education'),
(2, 'Physics');

-- --------------------------------------------------------

--
-- Структура таблицы `supervisors`
--

CREATE TABLE `supervisors` (
  `supervisor_id` int(4) NOT NULL,
  `supervisor_name` varchar(255) DEFAULT NULL,
  `supervisor_login` varchar(255) DEFAULT NULL,
  `supervisor_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `supervisors`
--

INSERT INTO `supervisors` (`supervisor_id`, `supervisor_name`, `supervisor_login`, `supervisor_password`) VALUES
(1, 'Richard Hill', 'rich@nis', '000111000'),
(2, 'Alex Morgan', 'alex@nis', 'alex00000'),
(3, 'Alimzhan Zhangalishev', 'hacksutates2@gmail.com', '12345678');

-- --------------------------------------------------------

--
-- Структура таблицы `teachers`
--

CREATE TABLE `teachers` (
  `TeacherID` int(4) NOT NULL,
  `TeacherName` varchar(255) DEFAULT NULL,
  `Login_Teacher` varchar(255) DEFAULT NULL,
  `Password_Teacher` varchar(255) DEFAULT NULL,
  `TeacherRole` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `teachers`
--

INSERT INTO `teachers` (`TeacherID`, `TeacherName`, `Login_Teacher`, `Password_Teacher`, `TeacherRole`) VALUES
(1, 'Mr. Carter', 'car@nis', 'car123ter', 2),
(2, 'Ms. Brown', 'bro@nis', 'bro223wn', 2),
(3, 'Ms. Miller', 'mil@nis', 'mil323ler', 2),
(4, 'Olga Sizova', 'teacher1', '111', NULL),
(5, 'A.Shertser', 'she@nis', 'she123rtser', 2),
(6, 'GG WP', 'hacksutates52@gmail.com', '12345678', NULL),
(7, 'Alimzhan Zhangalishev', 'hacksutates2@gmail.com', '12345678', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `timetables`
--

CREATE TABLE `timetables` (
  `TimetableID` int(11) NOT NULL,
  `Name` varchar(150) NOT NULL,
  `Status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `timetables`
--

INSERT INTO `timetables` (`TimetableID`, `Name`, `Status`, `CreatedBy`, `CreatedAt`, `UpdatedAt`) VALUES
(2, 'Grade 10A - Term 1', 'draft', 3, '2026-09-16 18:22:22', '2026-09-16 18:22:26'),
(3, '52', 'draft', 3, '2026-09-17 20:40:26', '2026-09-17 20:52:40');

-- --------------------------------------------------------

--
-- Структура таблицы `timetable_assignments`
--

CREATE TABLE `timetable_assignments` (
  `AssignmentID` int(11) NOT NULL,
  `TimetableID` int(11) NOT NULL,
  `AssigneeType` enum('student','teacher') NOT NULL,
  `AssigneeID` int(11) NOT NULL,
  `AssignedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `timetable_assignments`
--

INSERT INTO `timetable_assignments` (`AssignmentID`, `TimetableID`, `AssigneeType`, `AssigneeID`, `AssignedAt`) VALUES
(1, 2, 'student', 34, '2026-09-16 18:22:59'),
(2, 2, 'student', 33, '2026-09-16 18:22:59'),
(3, 2, 'student', 30, '2026-09-16 18:22:59'),
(4, 2, 'student', 2, '2026-09-16 18:22:59'),
(5, 2, 'student', 3, '2026-09-16 18:22:59'),
(6, 2, 'student', 26, '2026-09-16 18:22:59'),
(7, 2, 'student', 32, '2026-09-16 18:22:59'),
(8, 2, 'student', 31, '2026-09-16 18:22:59'),
(9, 2, 'teacher', 5, '2026-09-16 18:23:05'),
(10, 2, 'teacher', 7, '2026-09-16 18:23:05'),
(11, 2, 'teacher', 1, '2026-09-16 18:23:05'),
(12, 2, 'teacher', 2, '2026-09-16 18:23:05'),
(13, 2, 'teacher', 3, '2026-09-16 18:23:05'),
(14, 2, 'teacher', 4, '2026-09-16 18:23:05');

-- --------------------------------------------------------

--
-- Структура таблицы `timetable_slots`
--

CREATE TABLE `timetable_slots` (
  `SlotID` int(11) NOT NULL,
  `TimetableID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `TeacherID` int(11) NOT NULL,
  `ClassroomID` int(11) NOT NULL,
  `DayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `TimeStart` time NOT NULL,
  `TimeEnd` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `timetable_slots`
--

INSERT INTO `timetable_slots` (`SlotID`, `TimetableID`, `SubjectID`, `TeacherID`, `ClassroomID`, `DayOfWeek`, `TimeStart`, `TimeEnd`) VALUES
(1, 2, 7, 7, 1, 'Tuesday', '11:15:00', '12:00:00'),
(2, 2, 1, 4, 1, 'Monday', '10:20:00', '11:05:00'),
(3, 2, 8, 5, 1, 'Thursday', '08:30:00', '09:15:00'),
(4, 2, 7, 3, 1, 'Friday', '11:15:00', '12:00:00'),
(5, 2, 5, 2, 1, 'Tuesday', '12:10:00', '12:55:00'),
(6, 2, 1, 3, 1, 'Tuesday', '09:25:00', '10:10:00'),
(7, 2, 2, 6, 1, 'Wednesday', '09:25:00', '10:10:00'),
(8, 2, 2, 7, 1, 'Monday', '08:30:00', '09:15:00'),
(9, 2, 5, 5, 1, 'Wednesday', '10:20:00', '11:05:00'),
(10, 2, 4, 7, 1, 'Friday', '08:30:00', '09:15:00'),
(11, 2, 6, 6, 1, 'Wednesday', '11:15:00', '12:00:00'),
(12, 2, 10, 5, 1, 'Wednesday', '08:30:00', '09:15:00'),
(13, 2, 11, 2, 1, 'Monday', '12:10:00', '12:55:00'),
(14, 2, 9, 3, 1, 'Friday', '09:25:00', '10:10:00'),
(15, 2, 4, 7, 1, 'Wednesday', '12:10:00', '12:55:00'),
(16, 2, 3, 2, 1, 'Tuesday', '10:20:00', '11:05:00'),
(17, 2, 3, 6, 1, 'Monday', '09:25:00', '10:10:00'),
(18, 2, 11, 2, 1, 'Friday', '10:20:00', '11:05:00'),
(19, 2, 8, 4, 1, 'Monday', '13:05:00', '13:50:00'),
(20, 2, 9, 7, 1, 'Monday', '11:15:00', '12:00:00'),
(21, 2, 10, 7, 1, 'Thursday', '10:20:00', '11:05:00'),
(22, 2, 6, 2, 1, 'Thursday', '11:15:00', '12:00:00'),
(23, 3, 10, 2, 4, 'Friday', '08:30:00', '09:15:00'),
(24, 3, 1, 1, 4, 'Friday', '09:25:00', '10:10:00'),
(25, 3, 5, 5, 4, 'Friday', '10:20:00', '11:05:00'),
(26, 3, 2, 5, 4, 'Friday', '11:15:00', '12:00:00'),
(27, 3, 11, 6, 4, 'Friday', '12:10:00', '12:55:00'),
(28, 3, 10, 6, 4, 'Monday', '08:30:00', '09:15:00'),
(29, 3, 1, 1, 4, 'Monday', '09:25:00', '10:10:00'),
(30, 3, 6, 2, 4, 'Friday', '13:05:00', '13:50:00'),
(31, 3, 3, 5, 4, 'Monday', '10:20:00', '11:05:00'),
(32, 3, 9, 6, 4, 'Monday', '11:15:00', '12:00:00'),
(33, 3, 8, 4, 4, 'Monday', '12:10:00', '12:55:00'),
(34, 3, 6, 5, 4, 'Monday', '13:05:00', '13:50:00'),
(35, 3, 9, 6, 4, 'Tuesday', '08:30:00', '09:15:00'),
(36, 3, 8, 7, 4, 'Tuesday', '09:25:00', '10:10:00'),
(37, 3, 7, 6, 4, 'Tuesday', '10:20:00', '11:05:00'),
(38, 3, 7, 2, 4, 'Thursday', '08:30:00', '09:15:00'),
(39, 3, 3, 2, 4, 'Tuesday', '11:15:00', '12:00:00'),
(40, 3, 5, 5, 4, 'Tuesday', '12:10:00', '12:55:00'),
(41, 3, 2, 3, 4, 'Tuesday', '13:05:00', '13:50:00'),
(42, 3, 11, 5, 4, 'Thursday', '09:25:00', '10:10:00'),
(43, 3, 4, 4, 4, 'Thursday', '10:20:00', '11:05:00'),
(44, 3, 4, 1, 4, 'Wednesday', '08:30:00', '09:15:00');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD UNIQUE KEY `unique_attendance` (`EnrollmentID`,`Date`),
  ADD KEY `fk_attendance_schedule` (`ScheduleID`),
  ADD KEY `AttendanceID` (`AttendanceID`,`Date`);

--
-- Индексы таблицы `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`ClassID`);

--
-- Индексы таблицы `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`ClassroomID`);

--
-- Индексы таблицы `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`EnrollmentID`),
  ADD UNIQUE KEY `StudentID` (`StudentID`,`LessonID`),
  ADD KEY `LessonID` (`LessonID`);

--
-- Индексы таблицы `extracurricular_lessons`
--
ALTER TABLE `extracurricular_lessons`
  ADD PRIMARY KEY (`LessonID`),
  ADD KEY `TeacherID` (`TeacherID`),
  ADD KEY `ClassroomID` (`ClassroomID`);

--
-- Индексы таблицы `lessonschedule`
--
ALTER TABLE `lessonschedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `LessonID` (`LessonID`);

--
-- Индексы таблицы `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `LessonID` (`LessonID`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Индексы таблицы `report_students`
--
ALTER TABLE `report_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EnrollmentID` (`EnrollmentID`),
  ADD KEY `report_students_ibfk_1` (`ReportID`);

--
-- Индексы таблицы `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`StudentID`),
  ADD KEY `ClassID` (`ClassID`);

--
-- Индексы таблицы `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`SubjectID`),
  ADD UNIQUE KEY `SubjectName` (`SubjectName`);

--
-- Индексы таблицы `supervisors`
--
ALTER TABLE `supervisors`
  ADD PRIMARY KEY (`supervisor_id`);

--
-- Индексы таблицы `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`TeacherID`);

--
-- Индексы таблицы `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`TimetableID`),
  ADD KEY `fk_tt_supervisor` (`CreatedBy`);

--
-- Индексы таблицы `timetable_assignments`
--
ALTER TABLE `timetable_assignments`
  ADD PRIMARY KEY (`AssignmentID`),
  ADD UNIQUE KEY `uniq_active_person` (`AssigneeType`,`AssigneeID`),
  ADD KEY `fk_assign_timetable` (`TimetableID`);

--
-- Индексы таблицы `timetable_slots`
--
ALTER TABLE `timetable_slots`
  ADD PRIMARY KEY (`SlotID`),
  ADD UNIQUE KEY `uniq_day_time` (`TimetableID`,`DayOfWeek`,`TimeStart`),
  ADD UNIQUE KEY `uniq_slot` (`TimetableID`,`DayOfWeek`,`TimeStart`),
  ADD KEY `fk_slot_subject` (`SubjectID`),
  ADD KEY `idx_teacher` (`TeacherID`,`DayOfWeek`,`TimeStart`),
  ADD KEY `idx_room` (`ClassroomID`,`DayOfWeek`,`TimeStart`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `attendance`
--
ALTER TABLE `attendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT для таблицы `classes`
--
ALTER TABLE `classes`
  MODIFY `ClassID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `ClassroomID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `EnrollmentID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `extracurricular_lessons`
--
ALTER TABLE `extracurricular_lessons`
  MODIFY `LessonID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `lessonschedule`
--
ALTER TABLE `lessonschedule`
  MODIFY `ScheduleID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `reports`
--
ALTER TABLE `reports`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT для таблицы `report_students`
--
ALTER TABLE `report_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT для таблицы `students`
--
ALTER TABLE `students`
  MODIFY `StudentID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT для таблицы `subjects`
--
ALTER TABLE `subjects`
  MODIFY `SubjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `supervisors`
--
ALTER TABLE `supervisors`
  MODIFY `supervisor_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `teachers`
--
ALTER TABLE `teachers`
  MODIFY `TeacherID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `timetables`
--
ALTER TABLE `timetables`
  MODIFY `TimetableID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `timetable_assignments`
--
ALTER TABLE `timetable_assignments`
  MODIFY `AssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `timetable_slots`
--
ALTER TABLE `timetable_slots`
  MODIFY `SlotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_enrollment` FOREIGN KEY (`EnrollmentID`) REFERENCES `enrollments` (`EnrollmentID`),
  ADD CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`ScheduleID`) REFERENCES `lessonschedule` (`ScheduleID`);

--
-- Ограничения внешнего ключа таблицы `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `students` (`StudentID`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`LessonID`) REFERENCES `extracurricular_lessons` (`LessonID`);

--
-- Ограничения внешнего ключа таблицы `extracurricular_lessons`
--
ALTER TABLE `extracurricular_lessons`
  ADD CONSTRAINT `extracurricular_lessons_ibfk_1` FOREIGN KEY (`TeacherID`) REFERENCES `teachers` (`TeacherID`),
  ADD CONSTRAINT `extracurricular_lessons_ibfk_2` FOREIGN KEY (`ClassroomID`) REFERENCES `classrooms` (`ClassroomID`);

--
-- Ограничения внешнего ключа таблицы `lessonschedule`
--
ALTER TABLE `lessonschedule`
  ADD CONSTRAINT `lessonschedule_ibfk_1` FOREIGN KEY (`LessonID`) REFERENCES `extracurricular_lessons` (`LessonID`);

--
-- Ограничения внешнего ключа таблицы `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`LessonID`) REFERENCES `extracurricular_lessons` (`LessonID`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `report_students`
--
ALTER TABLE `report_students`
  ADD CONSTRAINT `report_students_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `reports` (`ReportID`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_students_ibfk_2` FOREIGN KEY (`EnrollmentID`) REFERENCES `enrollments` (`EnrollmentID`);

--
-- Ограничения внешнего ключа таблицы `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`ClassID`) REFERENCES `classes` (`ClassID`);

--
-- Ограничения внешнего ключа таблицы `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `fk_tt_supervisor` FOREIGN KEY (`CreatedBy`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `timetable_assignments`
--
ALTER TABLE `timetable_assignments`
  ADD CONSTRAINT `fk_assign_timetable` FOREIGN KEY (`TimetableID`) REFERENCES `timetables` (`TimetableID`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `timetable_slots`
--
ALTER TABLE `timetable_slots`
  ADD CONSTRAINT `fk_slot_classroom` FOREIGN KEY (`ClassroomID`) REFERENCES `classrooms` (`ClassroomID`),
  ADD CONSTRAINT `fk_slot_subject` FOREIGN KEY (`SubjectID`) REFERENCES `subjects` (`SubjectID`),
  ADD CONSTRAINT `fk_slot_teacher` FOREIGN KEY (`TeacherID`) REFERENCES `teachers` (`TeacherID`),
  ADD CONSTRAINT `fk_slot_timetable` FOREIGN KEY (`TimetableID`) REFERENCES `timetables` (`TimetableID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
