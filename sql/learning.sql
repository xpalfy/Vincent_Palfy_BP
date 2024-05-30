-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 20, 2024 at 03:24 PM
-- Server version: 10.11.6-MariaDB-0+deb12u1
-- PHP Version: 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learning`
--

-- --------------------------------------------------------

--
-- Table structure for table `learn`
--

CREATE TABLE `learn` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `english_text` text DEFAULT NULL,
  `slovak_text` text DEFAULT NULL,
  `english_test_database` varchar(255) NOT NULL,
  `slovak_test_database` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learn`
--

INSERT INTO `learn` (`id`, `name`, `img`, `english_text`, `slovak_text`, `english_test_database`, `slovak_test_database`) VALUES
(1, 'Cpp', '/img/prog_lang/img_1.png', 'C++ offers a powerful blend of performance and abstraction, making it essential for system programming, game development, and many high-performance applications.', 'Jazyk C++ ponúka výkonnú kombináciu výkonnosti a abstrakcie, vďaka čomu je nevyhnutný na systémové programovanie, a mnohých výkonných aplikácií.', 'questions_cpp', 'otazky_cpp'),
(2, 'Python', '/img/prog_lang/img_2.png', 'Python\'s versatility and simplicity make it an ideal language for beginners and experts alike, catering to a wide range of applications from web development to data analysis and artificial intelligence.', 'Univerzálnosť a jednoduchosť jazyka Python z neho robia ideálny jazyk pre začiatočníkov aj expertov, je vhodný pre širokú škálu aplikácií od vývoja webových stránok až po analýzu údajov a umelú inteligenciu.\r\n            ', 'questions_python', 'otazky_python'),
(3, 'Java', '/img/prog_lang/img_3.png', 'Java\'s platform-independent nature and widespread adoption make it a key language for web, mobile, and enterprise applications, ensuring a broad range of career opportunities.', 'Nezávislosť Javy na platforme a jej široké rozšírenie z nej robia kľúčový jazyk pre web, mobilných a podnikových aplikácií, čo zaručuje široké spektrum kariérnych príležitostí.', 'questions_java', 'otazky_java'),
(4, 'JavaScript', '/img/prog_lang/img_6.png', 'JavaScript is the foundational scripting language for the web, enabling dynamic interactivity and paving the way for modern web application development.', 'JavaScript je základný skriptovací jazyk pre web, umožňuje dynamickú interaktivitu a pripravuje pôdu pre vývoj moderných webových aplikácií.', 'questions_js', 'otazky_js'),
(5, 'HTML', '/img/prog_lang/img_4.png', 'HTML is the foundational markup language for creating web pages, serving as the structural backbone of nearly all content on the internet.', 'HTML je základný značkovací jazyk na vytváranie webových stránok, slúži ako štrukturálny základ takmer všetkého obsahu na internete.', 'questions_html', 'otazky_html'),
(6, 'CSS', '/img/prog_lang/img_5.png', 'CSS empowers designers and developers to create visually appealing and responsive web layouts, enhancing the user experience across devices.', 'CSS umožňuje dizajnérom a vývojárom vytvárať vizuálne atraktívne a responzívne webové rozvrhnutia, zlepšuje používateľský zážitok na všetkých zariadeniach.', 'questions_css', 'otazky_css');

-- --------------------------------------------------------

--
-- Table structure for table `lesson`
--

CREATE TABLE `lesson` (
  `id` int(11) NOT NULL,
  `slovak_name` varchar(255) DEFAULT NULL,
  `english_name` varchar(255) DEFAULT NULL,
  `learn` varchar(255) DEFAULT NULL,
  `test` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `page` int(11) DEFAULT NULL,
  `creator` varchar(50) NOT NULL,
  `num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson`
--

INSERT INTO `lesson` (`id`, `slovak_name`, `english_name`, `learn`, `test`, `pdf`, `page`, `creator`, `num`) VALUES
(1, 'Premenná', 'Variable', 'Cpp', 'cpp_variables', '/pdf/cpp.pdf', 11, 'xpalfy', 5),
(2, 'Podmienky a rekurzie', 'Conditions and Recursions', 'Cpp', 'cpp_conditionals_recursions', '/pdf/cpp.pdf', 34, 'xpalfy', 5),
(3, 'Funkcie', 'Functions', 'Cpp', 'cpp_functions', '/pdf/cpp.pdf', 41, 'xpalfy', 5),
(4, 'Iterácie', 'Iterations', 'Cpp', 'cpp_iterations', '/pdf/cpp.pdf', 35, 'xpalfy', 5),
(5, 'Reťazce', 'Strings', 'Cpp', 'cpp_strings', '/pdf/cpp.pdf', 60, 'xpalfy', 5),
(6, 'Premenná', 'Variable', 'Java', 'java_variables', '/pdf/java.pdf', 39, 'xpalfy', 5),
(7, 'Podmienky a rekurzie', 'Conditions and Recursions', 'Java', 'java_conditionals_recursions', '/pdf/java.pdf', 93, 'xpalfy', 5),
(8, 'Funkcie', 'Functions', 'Java', 'java_functions', '/pdf/java.pdf', 73, 'xpalfy', 5),
(9, 'Iterácie', 'Iterations', 'Java', 'java_iterations', '/pdf/java.pdf', 111, 'xpalfy', 5),
(10, 'Reťazce', 'Strings', 'Java', 'java_strings', '/pdf/java.pdf', 111, 'xpalfy', 5),
(11, 'Premenná', 'Variable', 'JavaScript', 'javascript_variables', '/pdf/javascript.pdf', 14, 'xpalfy', 5),
(12, 'Podmienky a rekurzie', 'Conditions and Recursions', 'JavaScript', 'javascript_conditionals_recursions', '/pdf/javascript.pdf', 26, 'xpalfy', 5),
(13, 'Funkcie', 'Functions', 'JavaScript', 'javascript_functions', '/pdf/javascript.pdf', 38, 'xpalfy', 5),
(14, 'Iterácie', 'Iterations', 'JavaScript', 'javascript_iterations', '/pdf/javascript.pdf', 35, 'xpalfy', 5),
(15, 'Reťazce', 'Strings', 'JavaScript', 'javascript_strings', '/pdf/javascript.pdf', 28, 'xpalfy', 5),
(16, 'Premenná', 'Variable', 'Python', 'python_variables', '/pdf/python.pdf', 31, 'xpalfy', 5),
(17, 'Podmienky a rekurzie', 'Conditions and Recursions', 'Python', 'python_conditionals_recursions', '/pdf/python.pdf', 61, 'xpalfy', 5),
(18, 'Funkcie', 'Functions', 'Python', 'python_functions', '/pdf/python.pdf', 39, 'xpalfy', 5),
(19, 'Iterácie', 'Iterations', 'Python', 'python_iterations', '/pdf/python.pdf', 85, 'xpalfy', 5),
(20, 'Reťazce', 'Strings', 'Python', 'python_strings', '/pdf/python.pdf', 93, 'xpalfy', 5),
(21, 'Html ukážka', 'Html tutorial', 'HTML', 'html_questions', '/pdf/html.pdf', 1, 'xpalfy', 10),
(22, 'Css ukážka', 'Css tutorial', 'CSS', 'css_questions', '/pdf/css.pdf', 1, 'xpalfy', 10);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `test_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `passed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `role` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `email`, `telephone`, `created_at`, `role`, `active`, `two_factor_secret`) VALUES
(1, 'admin', '$2y$10$IwtJfeRO8fg9OAXH2ikOE.ux1arqIyym6L.7/H0KXnu8UrN4Gjxg.', 'admin', 'admin', 'admin@stuba.sk', '+421903925800', '2024-02-21 18:37:31', 'Admin', 1, 'MWF6CIB77BAMJPIU'),
(2, 'xpalfy', '$2y$10$BfJyJDpHjvp0i4kcB/LbrOV6imwFUY4BOLPXZdw7NtI2vc69TnCFC', 'Vincent', 'Pálfy', 'palfyvincent@gmail.com', '+421903925800', '2024-02-21 18:32:00', 'Teacher', 1, 'Y7FT76AFYLC75C5K');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `learn`
--
ALTER TABLE `learn`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lesson`
--
ALTER TABLE `lesson`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_learn_name` (`learn`),
  ADD KEY `fk_lesson_creator` (`creator`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_results_username` (`username`),
  ADD KEY `fk_results_lesson_id` (`test_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `learn`
--
ALTER TABLE `learn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `lesson`
--
ALTER TABLE `lesson`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lesson`
--
ALTER TABLE `lesson`
  ADD CONSTRAINT `fk_learn_name` FOREIGN KEY (`learn`) REFERENCES `learn` (`name`),
  ADD CONSTRAINT `fk_lesson_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`username`);

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_results_lesson_id` FOREIGN KEY (`test_id`) REFERENCES `lesson` (`id`),
  ADD CONSTRAINT `fk_results_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
