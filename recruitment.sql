-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 06:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recruitment`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `attachmentName` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `attachmentOwner` bigint(20) UNSIGNED NOT NULL,
  `moduleName` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authentication_log`
--

CREATE TABLE `authentication_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `authenticatable_type` varchar(255) NOT NULL,
  `authenticatable_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_at` timestamp NULL DEFAULT NULL,
  `login_successful` tinyint(1) NOT NULL DEFAULT 0,
  `logout_at` timestamp NULL DEFAULT NULL,
  `cleared_by_user` tinyint(1) NOT NULL DEFAULT 0,
  `location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auto_numbers`
--

CREATE TABLE `auto_numbers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `number` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `auto_numbers`
--

INSERT INTO `auto_numbers` (`id`, `name`, `number`, `created_at`, `updated_at`) VALUES
(1, 'f9ed2e270094f23a5c3283fec4d63bdd', 100014, '2025-02-27 00:43:45', '2025-02-27 00:43:46'),
(2, '6c31ce924d4d21eb814b2fded3e548ac', 100000, '2025-02-27 00:58:55', '2025-02-27 00:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `CandidateId` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) GENERATED ALWAYS AS (concat(`FirstName`,' ',`LastName`)) VIRTUAL,
  `FirstName` varchar(255) DEFAULT NULL,
  `LastName` varchar(255) NOT NULL,
  `Mobile` varchar(255) DEFAULT NULL,
  `ExperienceInYears` varchar(255) DEFAULT NULL,
  `CurrentJobTitle` varchar(255) DEFAULT NULL,
  `ExpectedSalary` varchar(255) DEFAULT NULL,
  `SkillSet` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`SkillSet`)),
  `HighestQualificationHeld` varchar(255) DEFAULT NULL,
  `CurrentEmployer` varchar(255) DEFAULT NULL,
  `CurrentSalary` varchar(255) DEFAULT NULL,
  `AdditionalInformation` longtext DEFAULT NULL,
  `Street` varchar(255) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `ZipCode` varchar(255) DEFAULT NULL,
  `State` varchar(255) DEFAULT NULL,
  `School` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`School`)),
  `ExperienceDetails` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ExperienceDetails`)),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `CandidateId`, `email`, `FirstName`, `LastName`, `Mobile`, `ExperienceInYears`, `CurrentJobTitle`, `ExpectedSalary`, `SkillSet`, `HighestQualificationHeld`, `CurrentEmployer`, `CurrentSalary`, `AdditionalInformation`, `Street`, `City`, `Country`, `ZipCode`, `State`, `School`, `ExperienceDetails`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'RLR_100000_CANDP', 'candidate.user@gmail.com', 'Candidate', 'User', NULL, NULL, NULL, NULL, '[{\"skill\":null,\"proficiency\":null,\"experience\":null,\"last_used\":null}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"school_name\":\"Abc\",\"major\":\"Abc\",\"duration\":\"4years\",\"pursuing\":true}]', '[{\"current\":false,\"company_name\":null,\"duration\":null,\"role\":null,\"company_address\":null}]', '2025-02-27 08:47:14', '2025-02-27 00:58:55', '2025-02-27 08:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_portal_invitations`
--

CREATE TABLE `candidate_portal_invitations` (
  `id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `candidate_portal_invitations`
--

INSERT INTO `candidate_portal_invitations` (`id`, `email`, `name`, `sent_at`, `joined_at`, `created_at`, `updated_at`) VALUES
('9e4ed6ec-c964-4d69-b8fb-925ee1c4cfa0', 'candidate.user@gmail.com', 'Candidate User', '2025-02-27 01:01:44', NULL, '2025-02-27 01:01:44', '2025-02-27 01:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_users`
--

CREATE TABLE `candidate_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `candidate_users`
--

INSERT INTO `candidate_users` (`id`, `name`, `email`, `password`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Candidate User', 'candidate.user@gmail.com', '$2y$10$28ZH6OoPokxBeZflI5n2L.8hWqrWi.dqXLvtUkvVBH15Fv4LrO1Ky', '2025-02-27 00:20:09', NULL, '2025-02-27 00:20:09', '2025-02-27 00:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `DepartmentName` varchar(255) NOT NULL,
  `ParentDepartment` bigint(20) UNSIGNED DEFAULT NULL,
  `CreatedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `ModifiedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `DeletedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `DepartmentName`, `ParentDepartment`, `CreatedBy`, `ModifiedBy`, `DeletedBy`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology', NULL, NULL, 1, NULL, NULL, '2025-02-27 00:43:44', '2025-02-27 08:53:19'),
(2, 'Human Capital', NULL, NULL, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 08:53:44'),
(3, 'Retail Brokerage', NULL, NULL, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 08:55:36'),
(4, 'Customer Relationship Management', NULL, NULL, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 08:56:43'),
(5, 'Investment Banking', NULL, NULL, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 08:57:18'),
(6, 'Finance & Accounting', NULL, NULL, 1, NULL, NULL, '2025-02-27 09:05:17', '2025-02-27 09:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_candidates`
--

CREATE TABLE `job_candidates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `JobCandidateId` varchar(255) DEFAULT NULL,
  `JobId` bigint(20) UNSIGNED DEFAULT NULL,
  `candidate` bigint(20) UNSIGNED NOT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `ExperienceInYears` varchar(255) DEFAULT NULL,
  `CurrentJobTitle` varchar(255) DEFAULT NULL,
  `ExpectedSalary` varchar(255) DEFAULT NULL,
  `SkillSet` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`SkillSet`)),
  `HighestQualificationHeld` varchar(255) DEFAULT NULL,
  `CurrentEmployer` varchar(255) DEFAULT NULL,
  `CurrentSalary` varchar(255) DEFAULT NULL,
  `Street` varchar(255) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `ZipCode` varchar(255) DEFAULT NULL,
  `State` varchar(255) DEFAULT NULL,
  `CandidateStatus` varchar(255) DEFAULT NULL,
  `CandidateSource` varchar(255) DEFAULT NULL,
  `CandidateOwner` bigint(20) UNSIGNED DEFAULT NULL,
  `CreatedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `ModifiedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `DeletedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_openings`
--

CREATE TABLE `job_openings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `postingTitle` varchar(255) NOT NULL,
  `NumberOfPosition` varchar(255) NOT NULL,
  `JobTitle` varchar(255) NOT NULL,
  `JobOpeningSystemID` varchar(255) DEFAULT NULL,
  `TargetDate` varchar(255) NOT NULL,
  `Status` varchar(255) NOT NULL DEFAULT 'new',
  `Industry` varchar(255) DEFAULT NULL,
  `Salary` varchar(255) DEFAULT NULL,
  `Department` bigint(20) UNSIGNED DEFAULT NULL,
  `HiringManager` varchar(255) DEFAULT NULL,
  `AssignedRecruiters` varchar(255) DEFAULT NULL,
  `DateOpened` varchar(255) NOT NULL,
  `JobType` varchar(255) NOT NULL,
  `RequiredSkill` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`RequiredSkill`)),
  `WorkExperience` varchar(255) NOT NULL,
  `JobDescription` longtext DEFAULT NULL,
  `JobRequirement` longtext DEFAULT NULL,
  `JobBenefits` longtext DEFAULT NULL,
  `AdditionalNotes` text DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `State` varchar(255) DEFAULT NULL,
  `ZipCode` varchar(255) DEFAULT NULL,
  `RemoteJob` tinyint(1) NOT NULL DEFAULT 0,
  `published_career_site` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `ModifiedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `DeletedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `job_openings`
--

INSERT INTO `job_openings` (`id`, `postingTitle`, `NumberOfPosition`, `JobTitle`, `JobOpeningSystemID`, `TargetDate`, `Status`, `Industry`, `Salary`, `Department`, `HiringManager`, `AssignedRecruiters`, `DateOpened`, `JobType`, `RequiredSkill`, `WorkExperience`, `JobDescription`, `JobRequirement`, `JobBenefits`, `AdditionalNotes`, `City`, `Country`, `State`, `ZipCode`, `RemoteJob`, `published_career_site`, `CreatedBy`, `ModifiedBy`, `DeletedBy`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Automotive Master Mechanic', '4', 'Automotive Master Mechanic', 'RLR_100000_JOB', '2025-07-27 07:43:45', 'Opened', 'Aut vero non aperiam enim rerum commodi animi sed. Veritatis voluptatem odit expedita facilis occaecati temporibus repellendus vitae. Reiciendis aut amet id est odio maxime aut. Dicta tempora consequatur exercitationem corrupti et.', '7', 1, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Voluptates asperiores quis a quas. Nobis tempore explicabo repellendus odio asperiores vitae dolores at. Maxime eius adipisci voluptatem voluptatem quo omnis dolorem quia.', 'Quia aperiam consequatur eos cupiditate et sit. Consequatur aut esse iste ducimus. Et eum ut est recusandae.', 'Fugiat animi ut incidunt molestias porro voluptatem facilis. Modi dolores ullam et voluptatibus. Aperiam molestiae iste voluptas amet deleniti excepturi eos qui.', NULL, 'Port Cassieborough', 'French Guiana', 'dolorem', '99088-5604', 1, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(2, 'Timing Device Assemblers', '2', 'Timing Device Assemblers', 'RLR_100001_JOB', '2025-07-27 07:43:45', 'Opened', 'Ullam possimus fugiat amet. Quos commodi eos odio et ullam. Et tenetur labore consequuntur est nisi. Quibusdam ratione pariatur est natus a et quia.', '5', 1, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Eos unde aut sed deleniti esse. Ut aut vitae aperiam corrupti iusto. Consequatur et corrupti voluptatem sed ea.', 'Qui non numquam assumenda nihil architecto ut eos. Quas deleniti possimus odio impedit non. Quisquam distinctio porro provident.', 'Quia rerum distinctio consequuntur distinctio qui ipsam. Qui exercitationem blanditiis quisquam in. Est saepe autem doloribus nemo exercitationem inventore corrupti.', NULL, 'North Darryl', 'Martinique', 'asperiores', '61698-9040', 0, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(3, 'Athletes and Sports Competitor', '6', 'Athletes and Sports Competitor', 'RLR_100002_JOB', '2025-07-27 07:43:45', 'Opened', 'Ratione et necessitatibus saepe quis excepturi minima. Ipsam sunt exercitationem laboriosam ut. Est illum voluptatem rerum hic accusantium. Aut omnis consequatur quia.', '6', 1, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Ullam quo nihil similique sequi ut odio qui et. Non odio totam nobis impedit qui explicabo accusamus. Et voluptates illum maiores incidunt autem.', 'Quibusdam explicabo occaecati qui sunt sed cum. Nemo impedit in accusantium consequuntur sunt. Minima inventore sunt officia minus dolor est dolorum.', 'In amet ea sunt numquam suscipit incidunt perspiciatis. Beatae est omnis vel aliquid ex veniam autem. Omnis et reiciendis veniam earum.', NULL, 'New Tarynton', 'Saint Lucia', 'doloribus', '96758', 1, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(4, 'Telephone Operator', '3', 'Telephone Operator', 'RLR_100003_JOB', '2025-07-27 07:43:45', 'Opened', 'Quo occaecati est officia enim. Velit id dolor voluptatem voluptatem optio omnis sed perspiciatis. Et nesciunt eius ducimus nihil repudiandae sit officia.', '1', 1, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Cum consequatur qui perferendis minus ab similique quo. Autem nobis libero at nisi architecto vel maiores. Pariatur incidunt voluptatem nesciunt et.', 'Facilis error iusto laborum et. Corrupti est ad ut inventore consequatur. Voluptatem vel temporibus porro dolorem.', 'Dolor harum voluptas qui dicta. Sunt dicta repellendus rerum blanditiis dolorum rerum. Unde veniam dolorum maiores deleniti enim.', NULL, 'Genesisbury', 'Spain', 'omnis', '45372-0315', 0, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(5, 'Textile Dyeing Machine Operator', '9', 'Textile Dyeing Machine Operator', 'RLR_100004_JOB', '2025-07-27 07:43:45', 'Opened', 'Quia voluptatum est soluta. Aut cupiditate quos qui aut maxime id aliquam. Facere et porro aperiam odit mollitia aut.', '9', 1, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Ut omnis numquam nobis est consequuntur. Modi nihil quis sunt dolor molestiae a iure. Quo aut nam magni occaecati nemo.', 'Facere odio enim quaerat. Iste fugit magni eos vitae rerum et necessitatibus. Sunt ea ex iure in.', 'Ex at consequatur molestiae omnis modi neque ut. Aut autem quia aut rerum et veniam. Qui doloremque error non repudiandae impedit est autem.', NULL, 'Wilkinsonmouth', 'Estonia', 'reprehenderit', '56312', 1, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(6, 'Supervisor of Customer Service', '4', 'Supervisor of Customer Service', 'RLR_100005_JOB', '2025-07-27 07:43:45', 'Opened', 'Tempora voluptatum sequi consequuntur doloribus et quisquam harum nemo. Dignissimos et ex ut corrupti aut. Rem esse quasi est eum blanditiis amet.', '7', 5, '1', NULL, '2025-02-27 07:43:45', 'Permanent', '\"Management\"', '0_1year', 'Porro ut temporibus quia quasi est quia magni. Voluptatem assumenda necessitatibus qui praesentium natus quos. Ipsam accusamus soluta et et.', 'Ullam consequatur ducimus explicabo laudantium quis. Dolore error atque consectetur. Alias fugit repellendus ut ipsa ut molestiae quo.', 'Vero nostrum pariatur quam hic neque. Dolorem debitis amet ipsam consequatur aut. Nihil in impedit laudantium omnis minima quod facilis consequatur.', NULL, 'Hammesview', 'French Polynesia', 'incidunt', '74584', 0, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:45', '2025-02-27 00:43:45'),
(7, 'Radio and Television Announcer', '7', 'Radio and Television Announcer', 'RLR_100006_JOB', '2025-07-27 07:43:46', 'Opened', 'Rerum quaerat sunt enim assumenda adipisci et est voluptatibus. Cum repellendus reiciendis perferendis. Voluptatibus et id quibusdam aliquid dicta et. Cumque aut quibusdam dolore quasi maiores. Aperiam mollitia quam sunt optio et.', '5', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Suscipit ipsa eos fuga qui aliquid dolorem. Quod ratione reprehenderit rerum nihil. Tenetur eligendi sed vel deserunt soluta officiis quia.', 'Quibusdam fugit neque necessitatibus et molestiae aut eos. Non quo ducimus unde. Et praesentium tempora reprehenderit quae eligendi ducimus.', 'Facere est perspiciatis blanditiis quisquam vitae perspiciatis tempora autem. Magnam qui voluptatem dignissimos quia. Est sed esse id debitis fuga quam et odit.', NULL, 'Schambergerton', 'Niger', 'corporis', '25303-5127', 0, 0, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(8, 'Metal Worker', '7', 'Metal Worker', 'RLR_100007_JOB', '2025-07-27 07:43:46', 'Opened', 'Quia quam sit natus nulla eum neque. Tenetur suscipit ducimus et molestiae hic recusandae. Tempora atque delectus at alias. Laboriosam enim labore non et.', '3', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Rerum sint error est doloremque atque minus est. Consequatur voluptate sit eum aspernatur. Qui eos veritatis repudiandae velit officia blanditiis est aut.', 'Cumque blanditiis eligendi voluptas natus repellendus libero consequatur. Et et voluptas explicabo accusantium sit ipsa. Nam tempora odit qui exercitationem quo consequuntur fugit.', 'Quam quis quod quam. Repellat quibusdam eum unde sunt laboriosam. Pariatur et perspiciatis est porro ipsam tempore.', NULL, 'South Damonport', 'Kenya', 'sapiente', '08191-0231', 1, 0, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(9, 'State', '1', 'State', 'RLR_100008_JOB', '2025-07-27 07:43:46', 'Opened', 'Beatae omnis velit autem nihil rerum. Fugit voluptatem rerum aut consequatur a itaque.', '3', 4, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Numquam sed repudiandae amet. Recusandae perferendis assumenda ut perspiciatis dicta. Exercitationem perferendis voluptatibus esse optio.', 'Odit itaque ratione eum hic molestiae. Corrupti quia consectetur dolores et consequatur possimus et. Atque sit vitae est voluptatibus illum.', 'Aliquam quae aliquam provident sed molestiae autem. Ea dolor odit maxime perferendis voluptatibus deserunt neque. Esse perferendis sed iusto eum.', NULL, 'Wilburnburgh', 'Panama', 'iste', '38609-0350', 1, 0, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(10, 'Aircraft Body Repairer', '4', 'Aircraft Body Repairer', 'RLR_100009_JOB', '2025-07-27 07:43:46', 'Opened', 'Minus delectus eum quae ratione aut. Iusto qui vitae quia similique quis optio. Sapiente possimus fugiat eum veritatis aut natus.', '7', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Eius eum corrupti aut in corrupti laboriosam. Commodi facilis suscipit recusandae magnam. Qui error aut dignissimos ducimus voluptas enim voluptatem.', 'Voluptas nesciunt impedit sapiente reprehenderit et. Fugit dolor tempore nisi ducimus. Iste molestiae neque et.', 'Tempore alias tempora animi non. Quia ducimus sequi ipsam ullam voluptas. Ipsa et qui nemo voluptas.', NULL, 'East Kara', 'Panama', 'maiores', '49782', 1, 0, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(11, 'Etcher', '2', 'Etcher', 'RLR_100010_JOB', '2025-07-27 07:43:46', 'Opened', 'Laborum sed rem sed quia. Modi omnis eos veniam molestiae dolor nisi et non. Rerum ut ea molestiae ut vitae quo.', '4', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Nemo pariatur ea et fugiat porro nostrum vitae. Accusamus ut voluptate voluptatibus nisi alias sint mollitia. Qui reiciendis reiciendis est voluptatum.', 'Sunt porro labore quo est. Sunt doloribus illum aperiam minus dolor beatae unde. Sed quis ducimus blanditiis nihil.', 'Sint a aspernatur dolorem et molestias aut. Qui pariatur est temporibus. Non consequatur rerum dicta inventore deserunt vitae accusamus.', NULL, 'North Effie', 'Portugal', 'rerum', '74394', 1, 0, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(12, 'Rough Carpenter', '5', 'Rough Carpenter', 'RLR_100011_JOB', '2025-07-27 07:43:46', 'Opened', 'Voluptatibus aut ducimus quia. Deserunt incidunt aut blanditiis consequatur necessitatibus. Inventore accusantium labore in qui adipisci. Ex esse quia quo et impedit distinctio eligendi. Illo consequatur molestiae quaerat itaque adipisci.', '5', 2, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Voluptatibus omnis minima quo nam eveniet. Fuga nobis ratione repudiandae beatae id ad consequatur. Optio velit non voluptas voluptatibus ipsam ad.', 'Quibusdam alias quisquam nam corrupti voluptatibus minima. Eos aut dignissimos eius voluptatibus quas. Dolor numquam rem et eos.', 'Consequuntur et voluptatibus ut molestias. Similique est assumenda consequuntur voluptatem iure voluptatem non. Sequi et consequuntur voluptas.', NULL, 'East Eloise', 'Sudan', 'officiis', '92219', 0, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(13, 'Shoe Machine Operators', '9', 'Shoe Machine Operators', 'RLR_100012_JOB', '2025-07-27 07:43:46', 'Opened', 'Maxime excepturi nihil assumenda ut minima ea. Quod at perferendis et reiciendis voluptas. Est delectus molestiae tenetur omnis.', '9', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Dolor esse aperiam ut illo quia qui. Facere aut aliquam vel et incidunt sit. Voluptatem provident in quia natus maiores nihil.', 'Voluptas sapiente sint et occaecati quia. In ducimus aut blanditiis. Numquam in aut tenetur totam et qui similique.', 'Eaque minima accusamus deserunt qui molestiae nemo enim. Esse consequuntur iusto aut in quia et cumque. Aspernatur maiores amet eos modi beatae dolorem.', NULL, 'West Kennethtown', 'Gambia', 'molestias', '06695-2583', 1, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(14, 'Business Operations Specialist', '6', 'Business Operations Specialist', 'RLR_100013_JOB', '2025-07-27 07:43:46', 'Opened', 'Nisi est ea placeat magni. Voluptatibus magni nihil reprehenderit sed dignissimos. Facere velit sint porro aspernatur. Cum recusandae eaque molestias et dignissimos quia debitis.', '1', 1, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Sed qui velit iste. Illum dolor laborum qui et et eaque. Ullam occaecati ad perferendis ut dignissimos et quae.', 'Mollitia veritatis iste eos sed animi. Earum amet sit libero odit id optio. Sunt commodi suscipit culpa ducimus voluptas.', 'Commodi optio sunt quaerat aut laudantium delectus impedit ipsam. Dignissimos necessitatibus nesciunt culpa necessitatibus illo. Et aut itaque est necessitatibus cumque dolorem corporis rerum.', NULL, 'West Brandttown', 'Heard Island and McDonald Islands', 'dolorem', '35287-8775', 0, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46'),
(15, 'Infantry', '8', 'Infantry', 'RLR_100014_JOB', '2025-07-27 07:43:46', 'Opened', 'Tempora voluptatum quia est et. Velit cumque tempore corrupti. Et voluptatem ex sed quaerat magnam aut non. Quod provident voluptatum ullam molestiae eum et quasi.', '2', 2, '1', NULL, '2025-02-27 07:43:46', 'Permanent', '\"Management\"', '0_1year', 'Quos ut culpa unde quia necessitatibus in iusto voluptate. Repellendus voluptas dicta et autem. Neque voluptatem itaque quibusdam.', 'Enim in corrupti odit. Laudantium vitae veritatis laboriosam minus vitae. Ratione quis sit eaque placeat aliquid laboriosam.', 'Magni velit perferendis nam quia molestiae asperiores aut. Consectetur rerum et molestias dignissimos. Harum nam est amet qui.', NULL, 'Port Amani', 'Mozambique', 'repellat', '45225', 1, 1, 1, 1, NULL, NULL, '2025-02-27 00:43:46', '2025-02-27 00:43:46');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2017_08_03_055212_create_auto_numbers', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2022_12_14_083707_create_settings_table', 1),
(7, '2023_07_21_020941_create_permission_tables', 1),
(8, '2023_08_07_115631_create_job_openings_table', 1),
(9, '2023_08_10_122745_create_departments_table', 1),
(10, '2023_08_10_135333_create_candidates_table', 1),
(11, '2023_08_13_124543_create_job_candidates_table', 1),
(12, '2023_08_16_170431_create_attachments_table', 1),
(13, '2023_08_17_111204_create_referrals_table', 1),
(14, '2023_09_17_110200_create_sessions_table', 1),
(15, '2023_09_20_045657_create_company_detail_settings', 1),
(16, '2023_09_21_034627_create_authentication_log_table', 1),
(17, '2023_09_21_132829_add_themes_settings_to_users_table', 1),
(18, '2023_09_24_140655_create_candidate_users_table', 1),
(19, '2023_09_29_195504_create_saved_jobs_table', 1),
(20, '2023_11_02_092409_create_jobs_table', 1),
(21, '2023_11_02_212905_create_candidate_portal_invitations_table', 1),
(22, '2023_11_04_223939_add_joined_and_sent_column_at_users', 1),
(23, '2023_11_04_234719_add_invitation_uuid_at_users', 1),
(24, '2024_05_06_042913_add_additional_note_to_table_job_openings', 1),
(25, '2024_05_06_125739_job_opening_required_skills', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(92, 'Attachments.view-any', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(93, 'Attachments.view', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(94, 'Attachments.create', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(95, 'Attachments.update', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(96, 'Attachments.delete', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(97, 'Attachments.restore', 'web', '2025-02-27 00:43:53', '2025-02-27 00:43:53'),
(98, 'Attachments.force-delete', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(99, 'Attachments.replicate', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(100, 'Attachments.reorder', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(101, 'CandidateUser.view-any', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(102, 'CandidateUser.view', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(103, 'CandidateUser.create', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(104, 'CandidateUser.update', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(105, 'CandidateUser.delete', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(106, 'CandidateUser.restore', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(107, 'CandidateUser.force-delete', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(108, 'CandidateUser.replicate', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(109, 'CandidateUser.reorder', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(110, 'Candidates.view-any', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(111, 'Candidates.view', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(112, 'Candidates.create', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(113, 'Candidates.update', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(114, 'Candidates.delete', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(115, 'Candidates.restore', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(116, 'Candidates.force-delete', 'web', '2025-02-27 00:43:54', '2025-02-27 00:43:54'),
(117, 'Candidates.replicate', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(118, 'Candidates.reorder', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(119, 'Departments.view-any', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(120, 'Departments.view', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(121, 'Departments.create', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(122, 'Departments.update', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(123, 'Departments.delete', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(124, 'Departments.restore', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(125, 'Departments.force-delete', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(126, 'Departments.replicate', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(127, 'Departments.reorder', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(128, 'JobCandidates.view-any', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(129, 'JobCandidates.view', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(130, 'JobCandidates.create', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(131, 'JobCandidates.update', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(132, 'JobCandidates.delete', 'web', '2025-02-27 00:43:55', '2025-02-27 00:43:55'),
(133, 'JobCandidates.restore', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(134, 'JobCandidates.force-delete', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(135, 'JobCandidates.replicate', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(136, 'JobCandidates.reorder', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(137, 'JobOpenings.view-any', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(138, 'JobOpenings.view', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(139, 'JobOpenings.create', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(140, 'JobOpenings.update', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(141, 'JobOpenings.delete', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(142, 'JobOpenings.restore', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(143, 'JobOpenings.force-delete', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(144, 'JobOpenings.replicate', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(145, 'JobOpenings.reorder', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(146, 'Referrals.view-any', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(147, 'Referrals.view', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(148, 'Referrals.create', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(149, 'Referrals.update', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(150, 'Referrals.delete', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(151, 'Referrals.restore', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(152, 'Referrals.force-delete', 'web', '2025-02-27 00:43:56', '2025-02-27 00:43:56'),
(153, 'Referrals.replicate', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(154, 'Referrals.reorder', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(155, 'SavedJob.view-any', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(156, 'SavedJob.view', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(157, 'SavedJob.create', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(158, 'SavedJob.update', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(159, 'SavedJob.delete', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(160, 'SavedJob.restore', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(161, 'SavedJob.force-delete', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(162, 'SavedJob.replicate', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(163, 'SavedJob.reorder', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(164, 'User.view-any', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(165, 'User.view', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(166, 'User.create', 'web', '2025-02-27 00:43:57', '2025-02-27 00:43:57'),
(167, 'User.update', 'web', '2025-02-27 00:43:58', '2025-02-27 00:43:58'),
(168, 'User.delete', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(169, 'User.restore', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(170, 'User.force-delete', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(171, 'User.replicate', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(172, 'User.reorder', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(173, 'candidatePortalInvitation.view-any', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(174, 'candidatePortalInvitation.view', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(175, 'candidatePortalInvitation.create', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(176, 'candidatePortalInvitation.update', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(177, 'candidatePortalInvitation.delete', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(178, 'candidatePortalInvitation.restore', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(179, 'candidatePortalInvitation.force-delete', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(180, 'candidatePortalInvitation.replicate', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(181, 'candidatePortalInvitation.reorder', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59'),
(182, 'User.impersonate', 'web', '2025-02-27 00:43:59', '2025-02-27 00:43:59');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume` varchar(255) NOT NULL,
  `ReferringJob` bigint(20) UNSIGNED NOT NULL,
  `JobCandidate` bigint(20) UNSIGNED DEFAULT NULL,
  `Candidate` bigint(20) UNSIGNED NOT NULL,
  `ReferredBy` bigint(20) UNSIGNED NOT NULL,
  `AssignedRecruiter` bigint(20) UNSIGNED NOT NULL,
  `Relationship` varchar(255) DEFAULT NULL,
  `KnownPeriod` varchar(255) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `CreatedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `ModifiedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `DeletedBy` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2025-02-27 00:43:23', '2025-02-27 00:43:23'),
(2, 'Administrator', 'web', '2025-02-27 00:43:30', '2025-02-27 00:43:30'),
(3, 'Standard', 'web', '2025-02-27 00:43:38', '2025-02-27 00:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job` bigint(20) UNSIGNED NOT NULL,
  `record_owner` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('qu8RY1Hnm2qy8gwYa20O32Y0AmpXm0GudqJLq0Tn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVJsUk15cXg4V1ROQ0NCcU1oVU5zNkE2bjh4SkdzMTlXcTFCaWdZVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fX0=', 1744691610);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `group`, `name`, `locked`, `payload`, `created_at`, `updated_at`) VALUES
(1, 'company', 'site_name', 0, '\"BNI Sekuritas - Careers\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(2, 'company', 'company_name', 0, '\"BNI Sekuritas\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(3, 'company', 'company_website', 0, '\"www.bnisekuritas.co.id\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(4, 'company', 'company_primary_contact_email', 0, '\"customercare@bnisekuritas.co.id\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(5, 'company', 'company_employee_count', 0, '255', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(6, 'company', 'company_country', 0, '\"Indonesia\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(7, 'company', 'company_state', 0, '\"DKI Jakarta\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(8, 'company', 'company_city', 0, '\"South Jakarta\"', '2025-02-27 00:43:03', '2025-04-14 15:51:37'),
(9, 'job_opening_settings', 'required_skills', 0, '{\"laravel\":\"Laravel\"}', '2025-02-27 00:43:10', '2025-02-27 00:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `theme` varchar(255) DEFAULT 'default',
  `theme_color` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `invitation_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `profile_photo_path`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `deleted_at`, `created_at`, `updated_at`, `theme`, `theme_color`, `sent_at`, `joined_at`, `invitation_id`) VALUES
(1, NULL, 'Super Admin', 'superadmin.user@gmail.com', '2025-02-27 00:43:43', '$2y$10$eege9KmIOGOGSuG5n6wHmeqbgOD1wZatDBWJxFHJ.757GJPfC7qP6', 'vG9VVwVNnBnXCHmSQw2YHpERR99hlkIsx51gUPS803muYXXm2Id5CbcJtiCO', NULL, '2025-02-27 00:43:44', '2025-02-27 00:43:44', 'default', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authentication_log`
--
ALTER TABLE `authentication_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authentication_log_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`);

--
-- Indexes for table `auto_numbers`
--
ALTER TABLE `auto_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_portal_invitations`
--
ALTER TABLE `candidate_portal_invitations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_users`
--
ALTER TABLE `candidate_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `candidate_users_email_unique` (`email`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_candidates`
--
ALTER TABLE `job_candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_openings`
--
ALTER TABLE `job_openings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_group_name_unique` (`group`,`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authentication_log`
--
ALTER TABLE `authentication_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auto_numbers`
--
ALTER TABLE `auto_numbers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidate_users`
--
ALTER TABLE `candidate_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_candidates`
--
ALTER TABLE `job_candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_openings`
--
ALTER TABLE `job_openings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
