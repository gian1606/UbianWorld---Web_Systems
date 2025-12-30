-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 21, 2025 at 02:33 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ubiandb`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `content_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content_text` text NOT NULL,
  `content_url` varchar(500) DEFAULT NULL,
  `page_type` varchar(50) DEFAULT 'announcement',
  `is_archived` tinyint(1) DEFAULT 0,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`content_id`, `title`, `content_text`, `content_url`, `page_type`, `is_archived`, `date_posted`, `updated_at`) VALUES
(4, 'Mandatory Orientation for the New Exchange Students', 'All incoming exchange students for the Spring semester must attend this mandatory virtual orientation session. Check your university email for the link and full schedule details.', '#', 'announcement', 0, '2025-12-04 16:00:00', '2025-12-02 07:23:42'),
(5, 'Final Deadline for Visa Extension Submissions', 'A final reminder that the deadline for submitting all necessary documents for visa extensions is approaching. Please consult the Guides section for the step-by-step process.', 'guides.html', 'announcement', 0, '2025-11-29 16:00:00', '2025-12-01 17:15:30'),
(9, 'Visa Processing Updates 2023', 'Important updates regarding visa processing times and requirements for the 2023-2024 academic year.', '#', 'announcement', 1, '2023-12-11 16:00:00', '2025-12-02 06:23:16'),
(10, 'UB International Week 2022', 'Join us for a week of cultural celebrations, workshops, and networking events for international students.', '#', 'announcement', 1, '2022-11-13 16:00:00', '2025-12-02 06:23:16'),
(15, 'YOYOYO', 'ayko na guys hehe', '', 'announcement', 1, '2025-05-01 16:00:00', '2025-12-02 06:23:16'),
(22, 'Ang pogi ni ronnie', 'ttttt', '', 'announcement', 1, '2025-12-03 04:54:13', '2025-12-03 06:33:10'),
(23, 'YOYOYO', 'dkadaiajdijad', '', 'announcement', 0, '2025-12-03 19:59:06', '2025-12-03 19:59:06'),
(24, 'YOYOYO', 'ndjajdhahidhaidiah', '', 'announcement', 0, '2025-12-10 00:28:57', '2025-12-10 00:28:57'),
(25, 'Enrollment & Registration Process', 'A detailed walk-through of subject enlistment, fee payment, and final registration for the semester.', '', 'guide', 0, '2025-12-10 02:07:09', '2025-12-10 02:07:09'),
(26, 'F – ISSO-01', 'Inquiry form', 'uploads/forms/form_1765333827_course-withdrawal.docx', 'form', 0, '2025-12-10 02:30:27', '2025-12-10 02:30:27'),
(27, 'F – ISSO-03', 'duhaudhua', 'uploads/forms/form_1765424220_course-withdrawal.docx', 'form', 0, '2025-12-11 03:37:00', '2025-12-11 03:37:00');

-- --------------------------------------------------------

--
-- Table structure for table `CONTACT_MESSAGE`
--

CREATE TABLE `CONTACT_MESSAGE` (
  `MessageID` int(11) NOT NULL,
  `FullName` varchar(150) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `MessageText` text NOT NULL,
  `Status` enum('new','read','responded','archived') DEFAULT 'new',
  `SubmittedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `ReadAt` timestamp NULL DEFAULT NULL,
  `ResponseText` text DEFAULT NULL,
  `RespondedBy` int(11) DEFAULT NULL,
  `RespondedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `CONTACT_MESSAGE`
--

INSERT INTO `CONTACT_MESSAGE` (`MessageID`, `FullName`, `Email`, `Subject`, `MessageText`, `Status`, `SubmittedAt`, `ReadAt`, `ResponseText`, `RespondedBy`, `RespondedAt`) VALUES
(1, 'Gian', 'dili@gmail.com', 'ahdhadhh', 'kikiayy', 'new', '2025-12-01 18:10:41', NULL, NULL, NULL, NULL),
(2, 'Gian', 'dili@gmail.com', 'Hakko', 'Hallo world', 'new', '2025-12-01 18:24:17', NULL, NULL, NULL, NULL),
(3, 'Gian', '1700945@ub.edu.ph', 'ahdhadhh', 'nye', 'new', '2025-12-01 18:53:44', '2025-12-02 09:00:51', NULL, NULL, NULL),
(4, 'John Doe', 'john.doe@student.ub.edu.ph', 'Concern', 'concern yarn?', 'new', '2025-12-01 18:56:49', NULL, NULL, NULL, NULL),
(5, 'Gian', '1700945@ub.edu.ph', 'WS101', 'hi po', 'new', '2025-12-01 23:28:53', '2025-12-02 10:37:52', 'dsdss', 1, '2025-12-02 14:03:10'),
(6, 'rami', 'rami@gmail.com', 'heeh', 'huhu', 'responded', '2025-12-01 23:29:22', '2025-12-10 00:28:40', 'wait for 5 days', 1, '2025-12-10 00:28:40'),
(7, 'Gian', '1700945@ub.edu.ph', 'relatives', 'visiting', 'new', '2025-12-10 01:12:38', NULL, NULL, NULL, NULL),
(8, 'Kenley', '2101839@ub.edu.ph', 'Going back to china', 'I\'ll be going back to china', 'new', '2025-12-10 01:16:31', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `CONTENT`
--

CREATE TABLE `CONTENT` (
  `ResourceID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Type` enum('Announcement','Guide','FAQ') NOT NULL,
  `ContentText` text NOT NULL,
  `ContentURL` varchar(500) DEFAULT NULL,
  `DatePosted` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `DOCUMENT`
--

CREATE TABLE `DOCUMENT` (
  `DocumentID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `FileName` varchar(255) NOT NULL,
  `FileType` varchar(50) DEFAULT NULL,
  `FilePath` varchar(500) DEFAULT NULL,
  `UploadDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `ReviewStatus` enum('Pending','Approved','Rejected','Under Review') DEFAULT 'Pending',
  `ReviewedBy` int(11) DEFAULT NULL,
  `ReviewDate` timestamp NULL DEFAULT NULL,
  `ReviewNotes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `DOCUMENT`
--

INSERT INTO `DOCUMENT` (`DocumentID`, `StudentID`, `FileName`, `FileType`, `FilePath`, `UploadDate`, `ReviewStatus`, `ReviewedBy`, `ReviewDate`, `ReviewNotes`) VALUES
(1, 2, 'Passport Photo', 'jpg', 'uploads/documents/student_2_1764669083_sampel.jpg', '2025-12-02 09:51:23', 'Under Review', 1, '2025-12-03 06:29:17', ''),
(2, 2, 'Enrollment Form', 'pdf', 'uploads/documents/student_2_1764797611_SAMPLE-PDF.pdf', '2025-12-03 21:33:31', 'Pending', NULL, NULL, NULL),
(3, 2, 'Visa Copy', 'jpg', 'uploads/documents/student_2_1765326301_visa sample.jpg', '2025-12-10 00:25:01', 'Pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `faq_id` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`faq_id`, `question`, `answer`, `category`, `display_order`, `created_at`, `updated_at`) VALUES
(5, 'How do I apply for a student visa?', 'To apply for a student visa, you need to submit your admission letter, proof of financial capacity, medical certificate, and complete the visa application form. Visit the ISSO office for detailed guidance and document verification.', 'Visa', 1, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(6, 'What documents do I need for enrollment?', 'Required documents include: valid passport, student visa, admission letter, academic transcripts, medical clearance certificate, proof of health insurance, and passport-sized photos. All documents should be authenticated and translated to English if necessary.', 'General', 2, '2025-12-01 17:15:30', '2025-12-02 14:31:18'),
(7, 'How can I extend my student visa?', 'To extend your visa, submit a request to ISSO at least 30 days before expiration. Required documents include enrollment certificate, financial proof, and completed extension form. ISSO will guide you through the immigration process.', 'Visa', 3, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(8, 'Where can I find housing assistance?', 'ISSO provides a comprehensive list of recommended accommodations near campus, including dormitories, apartment rentals, and homestay options. Visit our office or check the Guides & Forms section for the housing directory.', 'Housing', 4, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(9, 'What health insurance options are available?', 'All international students must have valid health insurance. The university offers a group insurance plan, or you can provide proof of equivalent coverage from your home country. Contact ISSO for insurance enrollment assistance.', 'Health', 5, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(10, 'Can I work while studying?', 'International students may work part-time on campus with proper work authorization. Off-campus employment requires special permits. ISSO can help you understand work restrictions and application procedures based on your visa type.', 'Employment', 6, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(11, 'How do I report a lost passport?', 'Immediately report a lost passport to ISSO and your country\'s embassy. ISSO will provide a letter for immigration authorities and guide you through the passport replacement process. Keep copies of important documents in a secure location.', 'Emergency', 7, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(12, 'What support services are available for international students?', 'ISSO offers academic advising, cultural adjustment counseling, visa assistance, orientation programs, social events, and 24/7 emergency support. We also organize cultural celebrations and networking opportunities throughout the year.', 'General', 8, '2025-12-01 17:15:30', '2025-12-01 17:15:30'),
(15, 'Where to find friends', 'on campus', 'General', 0, '2025-12-10 00:29:22', '2025-12-10 00:29:22');

-- --------------------------------------------------------

--
-- Table structure for table `INQUIRY`
--

CREATE TABLE `INQUIRY` (
  `InquiryID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `Priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `AssignedTo` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ResolvedAt` timestamp NULL DEFAULT NULL,
  `Response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `INQUIRY`
--

INSERT INTO `INQUIRY` (`InquiryID`, `StudentID`, `Subject`, `Description`, `Status`, `Priority`, `AssignedTo`, `CreatedAt`, `UpdatedAt`, `ResolvedAt`, `Response`) VALUES
(1, 1, 'Visa Extension Question', 'I would like to know the requirements for extending my student visa. My current visa expires in 2 months.', 'pending', 'medium', NULL, '2025-12-01 15:04:33', '2025-12-01 15:04:33', NULL, NULL),
(2, 2, 'ahdhadhh', 'jdhahda djkahdjaha kjdbajbda dkaj', 'resolved', 'medium', 1, '2025-12-01 17:48:39', '2025-12-02 07:30:05', '2025-12-02 07:30:05', 'jjshhfahfa'),
(3, 1, 'ahdhadhh', 'djkanjdabjdbajd', 'closed', 'medium', 1, '2025-12-01 17:50:19', '2025-12-03 06:28:53', '2025-12-03 06:28:53', ''),
(4, 2, 'Visa Extension', 'My visa has expired as of today, and I don\'t know know where to renew it. Where should I go?', 'in_progress', 'medium', 1, '2025-12-10 00:25:51', '2025-12-10 00:28:28', NULL, 'wait for 5 days'),
(5, 2, 'Document Issue', 'my documents got wet, where can I get new ones?', 'pending', 'medium', NULL, '2025-12-10 01:13:46', '2025-12-10 01:13:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `STAFF`
--

CREATE TABLE `STAFF` (
  `StaffID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Position` varchar(100) DEFAULT 'ISSO Staff',
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Department` varchar(100) DEFAULT 'International Student Services',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `STAFF`
--

INSERT INTO `STAFF` (`StaffID`, `UserID`, `FirstName`, `LastName`, `Email`, `Position`, `PhoneNumber`, `Department`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 'Admin', 'User', 'admin@ubianworld.edu.ph', 'ISSO Director', NULL, 'International Student Services', '2025-12-01 15:04:33', '2025-12-01 15:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `STUDENT`
--

CREATE TABLE `STUDENT` (
  `StudentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Nationality` varchar(100) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `ProgramOfStudy` varchar(150) DEFAULT NULL,
  `EnrollmentStatus` enum('Active','Inactive','Graduated','Withdrawn') DEFAULT 'Active',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `STUDENT`
--

INSERT INTO `STUDENT` (`StudentID`, `UserID`, `FirstName`, `LastName`, `Email`, `Nationality`, `PhoneNumber`, `DateOfBirth`, `ProgramOfStudy`, `EnrollmentStatus`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 2, 'John', 'Doe', 'john.doe@student.ub.edu.ph', 'United States', NULL, NULL, 'Computer Science', 'Active', '2025-12-01 15:04:33', '2025-12-01 15:04:33'),
(2, 3, 'Gian', 'Pita', '1700945@ub.edu.ph', 'Filipino', NULL, NULL, NULL, 'Active', '2025-12-01 16:53:58', '2025-12-01 16:53:58'),
(3, 4, 'Kenley', 'Briones', '2101839@ub.edu.ph', 'Chinese', NULL, NULL, NULL, 'Active', '2025-12-10 01:15:01', '2025-12-10 01:15:01');

-- --------------------------------------------------------

--
-- Table structure for table `USERS`
--

CREATE TABLE `USERS` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Role` enum('student','admin') NOT NULL DEFAULT 'student',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastLogin` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `USERS`
--

INSERT INTO `USERS` (`UserID`, `Username`, `PasswordHash`, `Role`, `CreatedAt`, `LastLogin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-12-01 15:04:33', NULL),
(2, '2024-12345', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2025-12-01 15:04:33', NULL),
(3, '1700945', '$2y$10$PwZZmwytvO.4nebUXn0qJ.HvpAFEn1hNJI1GpMp1WfihG4sKRQvU6', 'student', '2025-12-01 16:53:58', NULL),
(4, '2101839', '$2y$10$lwVsm0Lo5UPafJ9nLhnej.KV5VV.oW0BjBOS42XE3OdGgSbMfDYYC', 'student', '2025-12-10 01:15:01', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `idx_page_type` (`page_type`),
  ADD KEY `idx_date_posted` (`date_posted`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indexes for table `CONTACT_MESSAGE`
--
ALTER TABLE `CONTACT_MESSAGE`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_submitted_at` (`SubmittedAt`),
  ADD KEY `idx_responded_by` (`RespondedBy`);

--
-- Indexes for table `CONTENT`
--
ALTER TABLE `CONTENT`
  ADD PRIMARY KEY (`ResourceID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `idx_type` (`Type`),
  ADD KEY `idx_date_posted` (`DatePosted`);

--
-- Indexes for table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  ADD PRIMARY KEY (`DocumentID`),
  ADD KEY `ReviewedBy` (`ReviewedBy`),
  ADD KEY `idx_student_id` (`StudentID`),
  ADD KEY `idx_review_status` (`ReviewStatus`),
  ADD KEY `idx_upload_date` (`UploadDate`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`faq_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `INQUIRY`
--
ALTER TABLE `INQUIRY`
  ADD PRIMARY KEY (`InquiryID`),
  ADD KEY `AssignedTo` (`AssignedTo`),
  ADD KEY `idx_student_id` (`StudentID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_created_at` (`CreatedAt`);

--
-- Indexes for table `STAFF`
--
ALTER TABLE `STAFF`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_email` (`Email`),
  ADD KEY `idx_user_id` (`UserID`);

--
-- Indexes for table `STUDENT`
--
ALTER TABLE `STUDENT`
  ADD PRIMARY KEY (`StudentID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_email` (`Email`),
  ADD KEY `idx_user_id` (`UserID`),
  ADD KEY `idx_name` (`LastName`,`FirstName`);

--
-- Indexes for table `USERS`
--
ALTER TABLE `USERS`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `idx_username` (`Username`),
  ADD KEY `idx_role` (`Role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `CONTACT_MESSAGE`
--
ALTER TABLE `CONTACT_MESSAGE`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `CONTENT`
--
ALTER TABLE `CONTENT`
  MODIFY `ResourceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  MODIFY `DocumentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `INQUIRY`
--
ALTER TABLE `INQUIRY`
  MODIFY `InquiryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `STAFF`
--
ALTER TABLE `STAFF`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `STUDENT`
--
ALTER TABLE `STUDENT`
  MODIFY `StudentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `USERS`
--
ALTER TABLE `USERS`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CONTACT_MESSAGE`
--
ALTER TABLE `CONTACT_MESSAGE`
  ADD CONSTRAINT `contact_message_ibfk_1` FOREIGN KEY (`RespondedBy`) REFERENCES `STAFF` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `CONTENT`
--
ALTER TABLE `CONTENT`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `STAFF` (`StaffID`) ON DELETE CASCADE;

--
-- Constraints for table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `STUDENT` (`StudentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`ReviewedBy`) REFERENCES `STAFF` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `INQUIRY`
--
ALTER TABLE `INQUIRY`
  ADD CONSTRAINT `inquiry_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `STUDENT` (`StudentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `inquiry_ibfk_2` FOREIGN KEY (`AssignedTo`) REFERENCES `STAFF` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `STAFF`
--
ALTER TABLE `STAFF`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `USERS` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `STUDENT`
--
ALTER TABLE `STUDENT`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `USERS` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
