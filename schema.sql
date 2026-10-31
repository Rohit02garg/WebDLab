-- Campus Event Registration System - IIT (ISM) Dhanbad
-- Database Schema and Sample Data
-- Database: campus_events_db

CREATE DATABASE IF NOT EXISTS campus_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_events_db;

-- --------------------------------------------------------
-- Table structure for `students`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `events`;

CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_no` VARCHAR(50) NOT NULL UNIQUE,
  `student_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `events`
-- --------------------------------------------------------

CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `date_time` DATETIME NOT NULL,
  `venue` VARCHAR(255) NOT NULL,
  `capacity` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `registrations`
-- --------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `student_name` VARCHAR(150) NOT NULL,
  `admission_no` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- Foreign Key Constraint
  CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  -- Duplicate prevention constraints
  CONSTRAINT `unique_event_admission` UNIQUE (`event_id`, `admission_no`),
  CONSTRAINT `unique_event_email` UNIQUE (`event_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sample Events for IIT (ISM) Dhanbad
-- --------------------------------------------------------
INSERT INTO `events` (`id`, `title`, `category`, `description`, `date_time`, `venue`, `capacity`) VALUES
(1, 'Srijan 2026: Battle of the Bands', 'Cultural', 'The flagship rock and acoustic band showdown of Eastern India\'s largest cultural festival. Showcase your band\'s musical mastery live on stage.', '2026-11-15 18:00:00', 'Penman Auditorium', 30),
(2, 'Concetto: Autonomous RoboSoccer', 'Technical', 'High-speed robotic soccer championship organized by RoboISM. Teams build autonomous bots that maneuver, pass, and shoot on a miniaturized turf.', '2026-11-20 10:00:00', 'SAC Indoor Arena', 10),
(3, 'Khanan: Geo-Mining AI Hackathon', 'Technical', 'Solve real-world resource extraction, subsurface imaging, and mine safety challenges using computer vision, sensor telemetry, and modern AI.', '2026-11-22 09:30:00', 'GJLT Hall 1', 25),
(4, 'CodeISM Winter Coding Sprint', 'Technical', 'Speed algorithmic problem solving challenge across dynamic programming, graph algorithms, and competitive data structures.', '2026-11-25 14:00:00', 'Computer Centre Lab 3', 20),
(5, 'Basant: Kavyanjali Poetry Slam', 'Literary', 'A soulful evening celebrating Urdu, Hindi, and English poetry, ghazals, and spoken word recitals alongside esteemed alumni and poets.', '2026-12-01 17:30:00', 'Central Library Audi', 40),
(6, 'Parakram: Inter-Hostel Badminton Clash', 'Sports', 'Singles and doubles knockout tournament featuring fierce rivalries between Aquamarine, Amber, Jasper, and Diamond hostels.', '2026-12-05 16:00:00', 'SAC Sports Complex', 6),
(7, 'Pratibimb: "The Miner\'s Legacy" Play', 'Cultural', 'A gripping theatrical production portraying the poignant history, perseverance, and camaraderie of the Dhanbad coal mining pioneers.', '2026-12-10 19:00:00', 'Penman Auditorium', 50),
(8, 'E-Summit: Angel Pitch Arena', 'Entrepreneurship', 'Early-stage student startups pitch directly to prominent venture capitalists, alumni angels, and startup incubators.', '2026-12-14 11:00:00', 'EDC Seminar Hall', 4);

-- --------------------------------------------------------
-- Sample Registrations (Including almost-full and full events for testing)
-- --------------------------------------------------------
-- Event 6 (Capacity 6, 5 registrations already - 1 remaining seat!)
INSERT INTO `registrations` (`event_id`, `student_name`, `admission_no`, `email`, `phone`, `department`) VALUES
(6, 'Aarav Sharma', '22JE0101', 'aarav.sharma@iitism.ac.in', '9876543210', 'Computer Science & Engineering'),
(6, 'Rohan Verma', '22JE0145', 'rohan.verma@iitism.ac.in', '9876543211', 'Mining Engineering'),
(6, 'Priya Singh', '23JE0210', 'priya.singh@iitism.ac.in', '9876543212', 'Petroleum Engineering'),
(6, 'Karthik Nair', '21JE0305', 'karthik.nair@iitism.ac.in', '9876543213', 'Mechanical Engineering'),
(6, 'Ananya Das', '23JE0422', 'ananya.das@iitism.ac.in', '9876543214', 'Electronics Engineering');

-- Event 8 (Capacity 4, 4 registrations already - FULL / 0 remaining seats!)
INSERT INTO `registrations` (`event_id`, `student_name`, `admission_no`, `email`, `phone`, `department`) VALUES
(8, 'Vikram Malhotra', '21JE0501', 'vikram.m@iitism.ac.in', '9876543220', 'Mining Machinery Engineering'),
(8, 'Sneha Patel', '22JE0612', 'sneha.p@iitism.ac.in', '9876543221', 'Environmental Science'),
(8, 'Tanmay Joshi', '22JE0733', 'tanmay.j@iitism.ac.in', '9876543222', 'Applied Geophysics'),
(8, 'Meera Iyer', '23JE0844', 'meera.i@iitism.ac.in', '9876543223', 'Mathematics & Computing');

-- Event 1 (Capacity 30, 2 sample registrations)
INSERT INTO `registrations` (`event_id`, `student_name`, `admission_no`, `email`, `phone`, `department`) VALUES
(1, 'Aditya Sen', '22JE0911', 'aditya.sen@iitism.ac.in', '9876543230', 'Chemical Engineering'),
(1, 'Devika Menon', '23JE0925', 'devika.m@iitism.ac.in', '9876543231', 'Civil Engineering');

-- Event 2 & Event 4 (Sample registrations for Rohit Garg 24JE0917)
INSERT INTO `registrations` (`event_id`, `student_name`, `admission_no`, `email`, `phone`, `department`) VALUES
(2, 'Rohit Garg', '24JE0917', 'rohit.24je0917@iitism.ac.in', '9876543299', 'Computer Science & Engineering'),
(4, 'Rohit Garg', '24JE0917', 'rohit.24je0917@iitism.ac.in', '9876543299', 'Computer Science & Engineering');
