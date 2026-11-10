
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `assessor_id` int(10) unsigned NOT NULL,
  `impact` tinyint(3) unsigned NOT NULL,
  `collaboration` tinyint(3) unsigned NOT NULL,
  `technical` tinyint(3) unsigned NOT NULL,
  `presentation` tinyint(3) unsigned NOT NULL,
  `sustainability` tinyint(3) unsigned NOT NULL,
  `total` tinyint(3) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`),
  CONSTRAINT `fk_as_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `assessments` WRITE;
/*!40000 ALTER TABLE `assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `instructions` text NOT NULL,
  `max_score` tinyint(3) unsigned NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_id` (`module_id`),
  CONSTRAINT `fk_asg_mod` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,3,'My personal AI plan','Choose one real task from your studies, work or community project (for example writing a report, planning a budget or preparing a presentation).\n\nIn 150 to 250 words describe: (1) how you would use an AI tool for part of the task, with one example prompt; (2) how you would check the result; and (3) what personal information you would never put into the tool.',100),(2,6,'Plan a project budget sheet','Imagine you are managing a small project with a budget, for example a school garden or a youth workshop.\n\nIn 120 to 200 words describe the spreadsheet you would build: the column headings you would use, three formulas you would include (and what each does), and one chart that would help your team understand the budget.',100),(3,9,'Analyse a viral message','Choose a message, post or forwarded story you have seen that you suspect may be false or misleading. Remove any personal names or numbers.\n\nIn 150 to 250 words: summarise the claim, show how you used at least two SIFT steps, say what you found, and explain how you would respond to the person who shared it.',100),(4,12,'A misunderstanding and what I learned','Think of a real misunderstanding you had with someone from a different background, language or way of working (you may change names).\n\nIn 150 to 250 words describe what happened, what each person may have expected, and how you would handle it now using ideas from this course.',100),(5,15,'Our team agreement','Imagine your team of four from Uganda, Kenya, Tanzania and Rwanda is about to start a project.\n\nWrite a team agreement of 150 to 250 words covering: your shared goal, how you will communicate (including what to do if someone has weak internet), how often you will meet and in which time zone, how decisions will be made, and how you will give feedback.',100),(6,18,'My STAR answer','Write a STAR answer of 150 to 250 words to this interview question: \"Tell me about a time you worked in a team to solve a problem.\"\n\nLabel the four parts: Situation, Task, Action, Result. Use a real example from school, work, volunteering or community life.',100);
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity` varchar(40) DEFAULT NULL,
  `entity_id` varchar(40) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon` varchar(8) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'collaborator','Collaborator','Completed a team project on YouthConnect','🤝'),(2,'cross_border','Cross-Border Collaborator','Worked in a team from three or more countries','🌍'),(3,'impact_maker','Impact Maker','Project assessed at 80% or above','🚀'),(4,'validated_impact','Validated Impact','Project endorsed by a community stakeholder','✅');
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'AI & Digital Literacy','ai-digital-literacy'),(2,'Critical Thinking','critical-thinking'),(3,'Intercultural Communication','intercultural-communication'),(4,'Collaboration & Teamwork','collaboration-teamwork'),(5,'Employability & Career Skills','employability-career-skills'),(6,'Entrepreneurship','entrepreneurship'),(7,'Data Skills','data-skills');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(14) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `kind` enum('course','project') NOT NULL,
  `course_id` int(10) unsigned NOT NULL DEFAULT 0,
  `project_id` int(10) unsigned NOT NULL DEFAULT 0,
  `title` varchar(200) NOT NULL,
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `uq_user_course` (`user_id`,`kind`,`course_id`,`project_id`),
  CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (1,'YCP-68EF3736AC',6,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(2,'YCP-0345176A35',7,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(3,'YCP-F458AB6820',8,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(4,'YCP-6003CF54C5',9,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(5,'YCP-8A7B416511',12,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(6,'YCP-BA2E23CED0',13,'course',5,0,'Certificate of Completion: Teamwork and Remote Collaboration','2026-11-10 12:28:03'),(7,'YCP-4F7A2C42DF',6,'course',1,0,'Certificate of Completion: AI Literacy for Everyday Life','2026-11-10 12:28:03'),(8,'YCP-C9B1A3731B',7,'course',1,0,'Certificate of Completion: AI Literacy for Everyday Life','2026-11-10 12:28:03');
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `summary` varchar(300) NOT NULL,
  `description` text NOT NULL,
  `what_you_learn` text NOT NULL,
  `skills` varchar(300) DEFAULT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  `hours_per_week` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `language` varchar(40) NOT NULL DEFAULT 'English',
  `color` varchar(7) NOT NULL DEFAULT '#0b5cd5',
  `has_certificate` tinyint(1) NOT NULL DEFAULT 1,
  `pass_mark` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_cat` (`category_id`),
  KEY `fk_course_owner` (`owner_id`),
  FULLTEXT KEY `ft_courses` (`title`,`summary`,`skills`),
  CONSTRAINT `fk_course_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_course_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,1,2,'AI Literacy for Everyday Life','ai-literacy-for-everyday-life','Understand what AI is, use AI tools with confidence, and know how to check, protect and question what they produce.','Artificial intelligence is already part of daily life, from mobile money fraud alerts to translation and search. This course explains AI in plain language, with no coding required.\n\nOver three weeks you will learn how AI systems learn from data, how to write clear requests for AI assistants, how to check their answers, and how to protect your privacy. You will finish with a personal plan for using AI responsibly in your studies or work.','Explain in simple words what AI is and how it learns from data\nWrite clear, specific requests (prompts) to get useful AI answers\nCheck AI output for mistakes and made-up facts\nProtect personal information when using AI tools\nDiscuss fairness, bias and how AI is changing jobs','AI literacy, Prompt writing, Fact checking, Digital safety, Ethical thinking','Beginner',3,'English','#0b5cd5',1,70,'published','2026-11-10 12:28:02','2026-11-10 12:28:02'),(2,1,2,'Digital Skills for the Workplace','digital-skills-for-the-workplace','Work safely and productively online: secure accounts, professional email, files, spreadsheets and cloud tools.','Employers expect new staff to work safely and confidently with digital tools. This beginner course covers the essentials: protecting your accounts, writing professional messages, organising files and making simple spreadsheets.\n\nEvery lesson works on a phone, and examples use situations common in East African workplaces and projects.','Create strong passwords and use two-step verification\nRecognise phishing and scam messages\nWrite clear, polite professional emails\nOrganise, name and back up files\nBuild a simple spreadsheet with formulas and a chart','Online safety, Email etiquette, File management, Spreadsheets, Cloud tools','Beginner',3,'English','#0e7c86',1,70,'published','2026-11-10 12:28:02','2026-11-10 12:28:02'),(3,2,3,'Critical Thinking and Spotting Misinformation','critical-thinking-and-spotting-misinformation','Question what you read, verify claims quickly, and build clear, evidence-based arguments.','False and misleading information spreads fast on messaging apps and social media, and it can harm health, money and trust in communities. Critical thinking is the habit of asking: how do I know this is true?\n\nThis course teaches practical, quick checks you can do on your phone, and the reasoning skills to explain your conclusions to others.','Recognise common thinking biases in yourself and others\nTell facts, opinions and claims apart\nUse the SIFT method to check a source in minutes\nSpot common reasoning mistakes\nBuild a short argument supported by evidence','Critical thinking, Fact checking, Media literacy, Argumentation, Decision making','Beginner',3,'English','#7a3fc4',1,70,'published','2026-11-10 12:28:02','2026-11-10 12:28:02'),(4,3,3,'Intercultural Communication across East Africa','intercultural-communication-across-east-africa','Communicate respectfully and clearly with people from different countries, languages and working styles.','East Africa brings together hundreds of languages and many ways of greeting, deciding and working. These differences are a strength, but they can also cause misunderstanding when people work together online.\n\nThis course helps you understand your own cultural habits, communicate clearly across languages, and run respectful virtual meetings across countries and time zones.','Describe how culture shapes communication and expectations\nAvoid stereotypes and assumptions\nUse plain language so everyone can understand\nPlan meetings across time zones and connection limits\nRepair a misunderstanding respectfully','Intercultural communication, Active listening, Plain language, Virtual meetings, Conflict resolution','Beginner',3,'English','#c2571a',1,70,'published','2026-11-10 12:28:02','2026-11-10 12:28:02'),(5,4,2,'Teamwork and Remote Collaboration','teamwork-and-remote-collaboration','Learn how effective teams plan, divide work, communicate and give feedback, even when members are in different countries.','Great ideas fail without good teamwork. This course prepares you for Level Two of YouthConnect, where you will work in multi-country teams on real projects.\n\nYou will learn how teams agree on roles and rules, how to plan with tasks and milestones, and how to collaborate on a slow connection. Completing this course unlocks team projects on the platform.','Agree team roles, goals and working rules\nBreak a project into tasks and milestones\nDefine what \"done\" means for each task\nCollaborate remotely with limited bandwidth\nGive and receive feedback constructively','Teamwork, Project planning, Remote collaboration, Feedback, Time management','Beginner',3,'English','#1f8a4c',1,70,'published','2026-11-10 12:28:03','2026-11-10 12:28:03'),(6,5,3,'Employability: CVs, Interviews and Online Presence','employability-cvs-interviews-and-online-presence','Identify your strengths, write a strong CV, perform well in interviews and build a professional online profile.','Many young people have real skills but struggle to show them to employers. This course turns your experience, including volunteering, study and community work, into a clear story.\n\nYou will write a focused CV, practise answering interview questions with the STAR method, and set up a professional online presence.','List your skills and transferable strengths\nWrite a clear one- to two-page CV tailored to a job\nAnswer interview questions using the STAR method\nPrepare questions to ask an employer\nBuild a professional online profile safely','CV writing, Interview skills, Personal branding, Networking, Goal setting','Beginner',3,'English','#b3261e',1,70,'published','2026-11-10 12:28:03','2026-11-10 12:28:03');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `discussion_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discussion_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `body` varchar(2000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_course` (`course_id`,`created_at`),
  KEY `fk_dp_user` (`user_id`),
  CONSTRAINT `fk_dp_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `discussion_posts` WRITE;
/*!40000 ALTER TABLE `discussion_posts` DISABLE KEYS */;
INSERT INTO `discussion_posts` VALUES (1,1,6,NULL,'My bank sends a warning when a payment looks unusual. I guess that is AI spotting a pattern. What did you find?','2026-11-10 12:28:04'),(2,1,7,NULL,'My phone keyboard suggests Swahili and English words depending on what I type. Great example of AI learning my habits.','2026-11-10 12:28:04'),(3,1,2,2,'Exactly, Wanjiru. The keyboard learns from patterns in your typing. Remember that this is also why privacy settings matter.','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `discussion_posts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `status` enum('active','completed','dropped') NOT NULL DEFAULT 'active',
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `last_lesson_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_course` (`user_id`,`course_id`),
  KEY `idx_course` (`course_id`),
  CONSTRAINT `fk_enr_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,6,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(2,7,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(3,8,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(4,9,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(5,12,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(6,13,5,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(7,6,1,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(8,7,1,'completed','2026-11-10 12:28:03','2026-11-10 12:28:03',NULL),(9,10,1,'active','2026-11-10 12:28:03',NULL,NULL),(10,11,3,'active','2026-11-10 12:28:03',NULL,NULL);
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `evidence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evidence` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `public_id` char(16) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `milestone_id` int(10) unsigned DEFAULT NULL,
  `uploaded_by` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `kind` enum('file','link','note') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(190) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size_bytes` int(10) unsigned DEFAULT NULL,
  `url` varchar(400) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_id` (`public_id`),
  KEY `idx_proj` (`project_id`),
  CONSTRAINT `fk_ev_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `evidence` WRITE;
/*!40000 ALTER TABLE `evidence` DISABLE KEYS */;
INSERT INTO `evidence` VALUES (1,'5c33a67c017b26eb',1,1,6,'Summary of farmer interviews','note',NULL,NULL,NULL,NULL,NULL,'We interviewed 10 farmers. 8 of 10 said they plant by observing the first rains, and 6 lost a planting in the last two seasons because rain stopped early. All 10 have a basic phone; 4 have a smartphone.','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `evidence` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `kind` enum('mentor','facilitator','stakeholder') NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proj` (`project_id`,`created_at`),
  KEY `fk_fb_user` (`author_id`),
  CONSTRAINT `fk_fb_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fb_user` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` VALUES (1,1,4,'mentor','Strong idea and a well balanced team. Before approval please (1) say how you will reach farmers who do not have smartphones, (2) name the weather data source you will use, and (3) add a measurable target for the pilot. Then resubmit.','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_progress` (
  `user_id` int(10) unsigned NOT NULL,
  `lesson_id` int(10) unsigned NOT NULL,
  `completed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`lesson_id`),
  KEY `fk_lp_lesson` (`lesson_id`),
  CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
INSERT INTO `lesson_progress` VALUES (6,1,'2026-11-10 12:28:03'),(6,2,'2026-11-10 12:28:03'),(6,3,'2026-11-10 12:28:03'),(6,4,'2026-11-10 12:28:03'),(6,5,'2026-11-10 12:28:03'),(6,6,'2026-11-10 12:28:03'),(6,7,'2026-11-10 12:28:03'),(6,8,'2026-11-10 12:28:03'),(6,9,'2026-11-10 12:28:03'),(6,37,'2026-11-10 12:28:03'),(6,38,'2026-11-10 12:28:03'),(6,39,'2026-11-10 12:28:03'),(6,40,'2026-11-10 12:28:03'),(6,41,'2026-11-10 12:28:03'),(6,42,'2026-11-10 12:28:03'),(6,43,'2026-11-10 12:28:03'),(6,44,'2026-11-10 12:28:03'),(6,45,'2026-11-10 12:28:03'),(7,1,'2026-11-10 12:28:03'),(7,2,'2026-11-10 12:28:03'),(7,3,'2026-11-10 12:28:03'),(7,4,'2026-11-10 12:28:03'),(7,5,'2026-11-10 12:28:03'),(7,6,'2026-11-10 12:28:03'),(7,7,'2026-11-10 12:28:03'),(7,8,'2026-11-10 12:28:03'),(7,9,'2026-11-10 12:28:03'),(7,37,'2026-11-10 12:28:03'),(7,38,'2026-11-10 12:28:03'),(7,39,'2026-11-10 12:28:03'),(7,40,'2026-11-10 12:28:03'),(7,41,'2026-11-10 12:28:03'),(7,42,'2026-11-10 12:28:03'),(7,43,'2026-11-10 12:28:03'),(7,44,'2026-11-10 12:28:03'),(7,45,'2026-11-10 12:28:03'),(8,37,'2026-11-10 12:28:03'),(8,38,'2026-11-10 12:28:03'),(8,39,'2026-11-10 12:28:03'),(8,40,'2026-11-10 12:28:03'),(8,41,'2026-11-10 12:28:03'),(8,42,'2026-11-10 12:28:03'),(8,43,'2026-11-10 12:28:03'),(8,44,'2026-11-10 12:28:03'),(8,45,'2026-11-10 12:28:03'),(9,37,'2026-11-10 12:28:03'),(9,38,'2026-11-10 12:28:03'),(9,39,'2026-11-10 12:28:03'),(9,40,'2026-11-10 12:28:03'),(9,41,'2026-11-10 12:28:03'),(9,42,'2026-11-10 12:28:03'),(9,43,'2026-11-10 12:28:03'),(9,44,'2026-11-10 12:28:03'),(9,45,'2026-11-10 12:28:03'),(10,1,'2026-11-10 12:28:03'),(10,2,'2026-11-10 12:28:03'),(10,3,'2026-11-10 12:28:03'),(12,37,'2026-11-10 12:28:03'),(12,38,'2026-11-10 12:28:03'),(12,39,'2026-11-10 12:28:03'),(12,40,'2026-11-10 12:28:03'),(12,41,'2026-11-10 12:28:03'),(12,42,'2026-11-10 12:28:03'),(12,43,'2026-11-10 12:28:03'),(12,44,'2026-11-10 12:28:03'),(12,45,'2026-11-10 12:28:03'),(13,37,'2026-11-10 12:28:03'),(13,38,'2026-11-10 12:28:03'),(13,39,'2026-11-10 12:28:03'),(13,40,'2026-11-10 12:28:03'),(13,41,'2026-11-10 12:28:03'),(13,42,'2026-11-10 12:28:03'),(13,43,'2026-11-10 12:28:03'),(13,44,'2026-11-10 12:28:03'),(13,45,'2026-11-10 12:28:03');
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `type` enum('reading','video','discussion') NOT NULL DEFAULT 'reading',
  `title` varchar(160) NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `video_url` varchar(300) DEFAULT NULL,
  `minutes` smallint(5) unsigned NOT NULL DEFAULT 10,
  `is_preview` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_mod_pos` (`module_id`,`position`),
  CONSTRAINT `fk_les_mod` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,1,1,'reading','AI in your pocket','Many phone apps already use AI. Your bank or mobile money service may flag unusual transactions. Your keyboard predicts the next word. Maps suggest the fastest route. Translation apps turn Swahili into English in seconds.\n\nAI is software that finds patterns in large amounts of examples, then uses those patterns to make predictions or suggestions. It does not think like a person. It is very good at narrow tasks and can fail in surprising ways.',NULL,10,1),(2,1,2,'reading','How machines learn from data','Traditional software follows rules that a programmer writes. A machine learning system is different: it is shown many examples and works out the rules itself.\n\n## Training data\nIf you show a system thousands of photos labelled \"maize\" or \"cassava\", it can learn to tell them apart. The examples are called training data.\n\n## Garbage in, garbage out\nBad, missing or one-sided data produces bad results. If the examples mostly come from one region or group, the system may work poorly for everyone else.',NULL,12,0),(3,1,3,'discussion','Spot the AI','Think of one app or service you used this week that probably uses AI. What does it predict or suggest? Share it in the course discussion and read two other answers.',NULL,5,0),(4,2,1,'reading','Asking good questions (prompts)','The quality of an AI answer depends on the quality of your request. A good prompt is specific.\n\n- Say who you are and what you need: \"I am a first-year student in Kampala.\"\n- Give the task and the format: \"Explain budgeting in five short bullet points.\"\n- Add limits: audience, length, language.\n- Improve step by step: ask for a shorter version, a simpler example, or a different tone.\n\nVague request: \"Tell me about farming.\" Better request: \"List four low-cost ways a smallholder maize farmer can reduce post-harvest losses.\"',NULL,12,0),(5,2,2,'reading','Checking what AI tells you','AI assistants can sound confident while being wrong. They sometimes invent facts, names, numbers and web links. This is often called a hallucination.\n\n## Check before you trust\n- Compare with a trusted source such as a government site, a university or a respected news organisation.\n- Double-check every number, date and quotation.\n- Ask the tool to explain its reasoning, then test that reasoning yourself.\n\nYou are responsible for what you submit or share, even if an AI helped you write it.',NULL,12,0),(6,2,3,'discussion','Improve a prompt','Write a vague request for an AI assistant, then rewrite it to be specific using the tips above. Post both versions in the discussion and say what you changed.',NULL,8,0),(7,3,1,'reading','Privacy and safety','Anything you type into an AI tool may be stored or used to improve the service. Treat it like a public notice board.\n\n## Never share\n- Passwords and PINs, including mobile money PINs\n- National ID, passport or bank numbers\n- Private photos or messages of other people\n- Confidential information from your school or employer\n\nIf you are unsure, remove names and details, or do not use the tool for that task.',NULL,12,0),(8,3,2,'reading','Bias, fairness and the future of work','AI learns from data created by people, so it can copy human unfairness. A hiring tool trained mostly on one group\'s past decisions may unfairly rank others lower.\n\n## Work is changing\nAI is likely to change many tasks rather than remove every job. People who combine their own knowledge with AI tools, check results carefully, communicate well and keep learning will stay valuable.',NULL,14,0),(9,3,3,'discussion','Where should humans decide?','Name one decision, for example approving a loan or marking an exam, where you believe a person should always make the final call, even if AI helps. Explain why.',NULL,8,0),(10,4,1,'reading','Passwords and two-step verification','A strong password is long, unique and hard to guess. A passphrase of several unrelated words, such as \"river-mango-clock-seven\", is easier to remember than random symbols and is stronger than a short complex password.\n\nUse a different password for every important account. Turn on two-step verification (a code sent to your phone or an app) for email, social media and money services. Even if someone learns your password, they still cannot get in.',NULL,10,1),(11,4,2,'reading','Spotting phishing and scams','Phishing messages try to trick you into sharing passwords, PINs or money.\n\n## Warning signs\n- Urgent threats: \"Your account will close today!\"\n- Requests for a PIN, password or one-time code\n- Unknown links or a sender address that looks slightly wrong\n- Offers that are too good to be true, such as a prize you never entered for\n\nA real bank or mobile money company will never ask for your PIN. When in doubt, do not click. Contact the company using the number you already know.',NULL,12,0),(12,4,3,'discussion','Share a scam story','Describe a scam message you or someone you know received (remove real names and numbers). What was the warning sign?',NULL,6,0),(13,5,1,'reading','Writing a professional email','A good work email is short, clear and polite.\n\n- Subject line: say what it is about, for example \"Request for meeting on Thursday\".\n- Greeting: \"Dear Ms Achieng,\" or \"Hello Peter,\".\n- Body: one main point per paragraph. State what you need and by when.\n- Close politely: \"Thank you\" and your full name.\n\nRead it once before sending. Check the recipient, the attachment and the tone.',NULL,12,0),(14,5,2,'reading','Documents in the cloud','Cloud tools let several people edit the same document and keep earlier versions. They also protect work if your phone or laptop is lost.\n\n## Good habits\n- Save important files online as well as on your device.\n- Share with specific people, not \"anyone with the link\", when the content is private.\n- Use comments and suggestions instead of sending many copies by email.\n- Name versions clearly, for example \"Budget_2026-03_v2\".',NULL,12,0),(15,5,3,'discussion','Rewrite the email','Rewrite this weak message as a professional email: \"hi send me the report now\". Post your improved version.',NULL,8,0),(16,6,1,'reading','Organising and backing up files','Messy files waste time and risk loss. Create a few clear folders (for example Projects, Studies, Personal) and name files with a date and a short description.\n\nFollow the 3-2-1 idea in a simple form: keep your important work in at least two places, such as your device and an online account, and remember to update the copy regularly.',NULL,10,0),(17,6,2,'reading','Spreadsheet basics','A spreadsheet stores data in rows and columns. Formulas do the arithmetic for you.\n\n- =SUM(B2:B10) adds the numbers in cells B2 to B10.\n- =AVERAGE(B2:B10) gives the average.\n- =B2*C2 multiplies two cells, for example price times quantity.\n\nA chart makes numbers easier to understand. Use a column chart to compare amounts and a line chart to show change over time. Always label the chart and the axes.',NULL,15,0),(18,6,3,'discussion','What would you track?','Name one thing in your life or project you could track in a spreadsheet, such as savings, crop yields or attendance. What columns would you use?',NULL,6,0),(19,7,1,'reading','Facts, opinions and claims','A fact can be checked and shown to be true or false: \"The Nile flows through Uganda.\" An opinion is a personal view: \"The Nile is the most beautiful river.\" A claim is a statement that might be true and needs evidence: \"This herb cures malaria.\"\n\nCritical thinkers ask of every claim: who says so, what is the evidence, and what might they gain?',NULL,10,1),(20,7,2,'reading','Mental shortcuts and bias','Our brains use shortcuts to decide quickly. They are useful but can mislead us.\n\n## Confirmation bias\nWe notice and remember information that agrees with what we already believe, and ignore what does not.\n\n## Emotional reasoning\nStrong feelings such as fear or anger make us share messages before checking them.\n\nSlow down when a message makes you feel very strong emotion. That is exactly when to check first.',NULL,12,0),(21,7,3,'discussion','A time I was wrong','Describe a time you believed something that turned out to be false. What made it feel believable, and how did you find out?',NULL,8,0),(22,8,1,'reading','The SIFT method','SIFT gives four quick moves for checking information.\n\n- Stop: pause before you react or share.\n- Investigate the source: who is behind this? Search their name and see what trusted sources say about them.\n- Find better coverage: look for the same claim in reliable news or official sites.\n- Trace claims to the original: find the study, photo or quotation the message refers to.\n\nOften two minutes is enough to learn that a viral message is false or missing context.',NULL,14,0),(23,8,2,'reading','Pictures, dates and context','Old photos and videos are often shared as if they were new. Before trusting an image, run a reverse image search to find where it first appeared. Check the date and place.\n\nAlso look for context. A real quotation can be shortened to change its meaning, and a real chart can have a misleading scale. Ask: what is missing?',NULL,12,0),(24,8,3,'discussion','Check one claim','Pick a claim you have seen in a group chat or on social media. Use SIFT to check it, then report what you found and how long it took.',NULL,10,0),(25,9,1,'reading','Common reasoning mistakes','Some arguments look strong but are not.\n\n- Attacking the person: rejecting an idea because of who said it, not what was said.\n- False choice: \"Either we ban phones in class or learning fails.\" Usually there are more options.\n- Hasty generalisation: concluding about everyone from one or two cases.\n- Correlation is not causation: two things happening together does not prove one caused the other.',NULL,12,0),(26,9,2,'reading','Building a clear argument','A good argument has three parts: a claim, reasons, and evidence.\n\n1. State your claim in one sentence.\n2. Give two or three reasons.\n3. Support each reason with evidence such as data, an example, or a trusted source.\n4. Acknowledge the strongest opposing point and respond to it.\n\nKeep your tone respectful. The goal is to find what is true, not to win.',NULL,14,0),(27,9,3,'discussion','Spot the mistake','Write a short example of one reasoning mistake from this week and explain why it is weak. Read a classmate\'s example and say which mistake it shows.',NULL,8,0),(28,10,1,'reading','What is culture?','Culture is the shared habits, values and ways of doing things in a group. It includes language, greetings, humour, respect for elders, and attitudes to time and decisions.\n\nCulture is like an iceberg. A little is visible, such as food and clothing. Most is hidden, such as beliefs about respect, family and fairness. Misunderstandings usually come from the hidden part.',NULL,10,1),(29,10,2,'reading','Direct and indirect styles','In some groups people say exactly what they mean. In others, meaning is carried by tone, context and what is left unsaid, and saying \"no\" directly can seem rude.\n\nNeither style is better. When you work across cultures, do not assume that silence means agreement, or that a polite \"yes\" means the plan is confirmed. Ask open questions such as \"What worries you about this plan?\"',NULL,12,0),(30,10,3,'discussion','A greeting from home','Describe how people usually greet each other where you live. What would a visitor need to know to greet someone respectfully?',NULL,6,0),(31,11,1,'reading','Plain language','Many East Africans speak two or three languages, and English may be a second or third language for some of your teammates. Plain language helps everyone.\n\n- Use short sentences and common words.\n- Avoid idioms such as \"hit the ground running\" and slang.\n- Explain abbreviations the first time.\n- Write one request per message and say clearly what you need and by when.',NULL,12,0),(32,11,2,'reading','Listening and checking understanding','Active listening means giving full attention and showing you understand.\n\n- Repeat back in your own words: \"So you will send the draft on Friday. Is that right?\"\n- Ask, do not assume: \"Can you tell me more about that?\"\n- Summarise decisions in writing after a call.\n\nSwahili is widely used across the region and learning a few greetings in your teammates\' languages shows respect.',NULL,12,0),(33,11,3,'discussion','Rewrite in plain language','Take a sentence full of idioms, such as \"Let\'s touch base offline and circle back,\" and rewrite it in plain language. Post both versions.',NULL,8,0),(34,12,1,'reading','Time zones and connectivity','Uganda, Kenya and Tanzania use East Africa Time (UTC+3). Rwanda and Burundi use Central Africa Time (UTC+2), one hour behind. A meeting at \"3 pm\" can easily be misunderstood, so always write the time zone and, if useful, both local times.\n\nNot everyone has fast internet. Offer an audio-only option, share slides in advance, keep files small, and record decisions in writing so people who miss a call can catch up.',NULL,12,0),(35,12,2,'reading','Repairing misunderstandings','Misunderstandings will happen. Handle them calmly.\n\n1. Assume good intent, then check facts.\n2. Describe what happened without blame: \"I understood the deadline was Monday.\"\n3. Ask how the other person saw it.\n4. Agree on what you will each do differently next time.\n\nA quick private message is often better than correcting someone in front of the group.',NULL,12,0),(36,12,3,'discussion','Plan a meeting','Your team has members in Kampala, Nairobi, Dar es Salaam and Kigali. Propose a meeting time, write it with both time zones, and list two things you would do to include people with weak connections.',NULL,10,0),(37,13,1,'reading','Goals and roles','Strong teams know where they are going and who does what.\n\n- A shared goal: one sentence everyone can repeat.\n- Clear roles: for example team lead, note-taker, researcher, and tester. Roles can rotate.\n- Complementary skills: the best teams mix disciplines. A farmer-turned-agronomist, a developer, a data analyst and an environmental scientist can solve problems none could solve alone.',NULL,10,1),(38,13,2,'reading','Team agreements','A team agreement is a short document written together at the start. It might say how often you will meet, which channel you use for urgent messages, how fast you will reply, and how you will make decisions.\n\nWriting rules early prevents arguments later. Review the agreement after a few weeks and improve it.',NULL,10,0),(39,13,3,'discussion','Best and worst team','Describe the best team you have been part of, and one thing that made it work. Then name one habit that harmed a team you were in.',NULL,8,0),(40,14,1,'reading','Tasks and milestones','A big project feels easier when it is split up.\n\n- A task is a small piece of work one person can finish, such as \"Interview three farmers.\"\n- A milestone is a major checkpoint made of several tasks, such as \"Prototype ready for testing.\"\n\nGive every task one owner and a due date. Many owners means no owner.',NULL,12,0),(41,14,2,'reading','Definition of done and simple boards','Agree what \"done\" means. \"Write the survey\" is done when it is reviewed by one teammate and tested with two people.\n\nA simple board with three columns, To do, In progress and Done, shows everyone the state of the work at a glance. Update it as you go. Small, frequent updates keep a remote team in sync.',NULL,12,0),(42,14,3,'discussion','Break it down','Pick a goal for a community project, such as setting up a school recycling programme. List five tasks and one milestone.',NULL,10,0),(43,15,1,'reading','Communication on a limited connection','Choose the lightest tool that does the job.\n\n- Short text messages for quick questions.\n- Written updates for decisions, so they are not lost.\n- Audio calls instead of video when bandwidth is weak.\n- Compress images and documents before sending, and avoid sending large files to everyone.\n\nWrite messages so they can be understood later without the whole chat history.',NULL,12,0),(44,15,2,'reading','Feedback and conflict','Feedback helps a team improve. Make it specific, kind and about the work, not the person.\n\nUse a simple pattern: what you observed, the effect it had, and what you suggest. For example: \"The report arrived after the deadline, so we could not review it. Could we agree to share drafts two days earlier?\"\n\nWhen conflict appears, talk early, listen first, focus on the problem, and ask a facilitator or mentor if you are stuck.',NULL,14,0),(45,15,3,'discussion','Give kind feedback','Rewrite this harsh comment as helpful feedback: \"Your slides are terrible.\" Post your version.',NULL,8,0),(46,16,1,'reading','Skills you already have','Skills come from jobs, school, volunteering, family responsibilities and projects. Technical skills are things like using spreadsheets or repairing phones. Transferable skills such as communication, problem solving, teamwork and reliability are useful in almost any job.\n\nExample: organising a church or community event shows planning, budgeting and teamwork.',NULL,10,1),(47,16,2,'reading','Setting career goals','Choose a direction before you apply. Ask yourself: What work do I enjoy? What does the local or regional market need? What skill should I build next?\n\nWrite one goal for six months and one for two years. Make them specific, for example \"Get an internship in data entry or analysis by June.\"',NULL,10,0),(48,16,3,'discussion','Three strengths','List three strengths and one real example for each. Share one in the discussion and encourage a classmate by naming a strength you notice in their post.',NULL,8,0),(49,17,1,'reading','Structure of a strong CV','Keep your CV to one or two pages, in a clean layout with a readable font.\n\n- Contact details and a short profile (two or three lines).\n- Work, volunteering or project experience, newest first.\n- Education and training.\n- Skills and languages.\n\nUse action words and numbers: \"Organised a clean-up for 60 volunteers and collected 40 bags of waste.\" Check spelling twice.',NULL,12,1),(50,17,2,'reading','Tailoring and cover letters','Read the job advert carefully and use its key words where they honestly describe you. Change your profile to fit each role.\n\nA cover letter is three short paragraphs: why you are writing, why you fit the role with one or two examples, and a polite close. Address a named person if you can.',NULL,12,0),(51,17,3,'discussion','Improve a bullet point','Rewrite this weak CV line to include an action and a number: \"Helped at a shop.\" Post your version.',NULL,8,0),(52,18,1,'reading','The STAR method','Interviewers often ask for a time you did something, such as \"Tell me about a time you solved a problem.\" Use STAR to answer clearly.\n\n- Situation: the context in one or two sentences.\n- Task: what you needed to achieve.\n- Action: what YOU did, step by step.\n- Result: the outcome, with a number if possible, and what you learned.\n\nPractise aloud. Also prepare two questions to ask the employer, such as how success is measured in the role.',NULL,12,0),(53,18,2,'reading','Your professional online presence','Employers often search for candidates online. Use a professional photo, a clear headline and an accurate summary on a professional networking profile. Review what is public on social media and remove content you would not show an employer.\n\nBe careful with scams: a genuine employer will never ask you to pay for a job. Never send ID numbers or bank details before you have verified the company.',NULL,12,0),(54,18,3,'discussion','Practise a STAR story','Write a short STAR answer about a time you solved a problem or worked in a team. Share it and give one kind suggestion to a classmate.',NULL,12,0);
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('open','done') NOT NULL DEFAULT 'open',
  `position` smallint(5) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_proj` (`project_id`),
  CONSTRAINT `fk_ms_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `milestones` WRITE;
/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
INSERT INTO `milestones` VALUES (1,1,'Farmer interviews completed','2026-11-24','done',1),(2,1,'Prototype ready for testing','2026-12-25','open',2);
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `week_no` tinyint(3) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `hours_estimate` tinyint(3) unsigned NOT NULL DEFAULT 3,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_week` (`course_id`,`week_no`),
  CONSTRAINT `fk_mod_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,1,1,'What is AI, really?','See where AI already shows up in your life and how machines learn patterns from examples.',3),(2,1,2,'Using AI tools well','Write clear prompts and learn why every AI answer needs to be checked.',3),(3,1,3,'AI, ethics and your future','Protect your privacy, understand bias, and plan how to use AI responsibly.',3),(4,2,1,'Working online safely','Protect your accounts and spot scams before they cost you.',3),(5,2,2,'Everyday productivity tools','Communicate professionally by email and work with documents in the cloud.',3),(6,2,3,'Working with files and data','Organise your files and build a simple spreadsheet with formulas and a chart.',3),(7,3,1,'How we think','Understand the mental shortcuts that make all of us easy to fool.',3),(8,3,2,'Checking claims quickly','Use the SIFT method and simple tools to verify what you see online.',3),(9,3,3,'Reasoning and arguing well','Avoid common reasoning mistakes and build an argument that others can trust.',3),(10,4,1,'Culture and communication','Understand how culture shapes what we say, how we say it and what we expect.',3),(11,4,2,'Communicating across languages','Use plain language and listening skills so that everyone understands.',3),(12,4,3,'Working across borders online','Plan inclusive meetings across countries, time zones and connection limits, and repair misunderstandings.',3),(13,5,1,'How good teams work','Set a shared goal, clear roles and simple team rules.',3),(14,5,2,'Planning and tracking work','Break a project into tasks and milestones and agree what \"done\" means.',3),(15,5,3,'Collaborating remotely','Communicate well, give useful feedback and handle conflict when you cannot meet in person.',3),(16,6,1,'Know your strengths','Turn your experience into skills employers value.',3),(17,6,2,'CVs and applications','Write a focused CV and a short cover letter tailored to the job.',3),(18,6,3,'Interviews and online presence','Answer questions with the STAR method and build a professional profile safely.',3);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`,`read_at`),
  CONSTRAINT `fk_not_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,6,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(2,7,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(3,8,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(4,9,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(5,12,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(6,13,'Congratulations! You completed \"Teamwork and Remote Collaboration\".','my-learning',NULL,'2026-11-10 12:28:03'),(7,6,'Congratulations! You completed \"AI Literacy for Everyday Life\".','my-learning',NULL,'2026-11-10 12:28:03'),(8,7,'Congratulations! You completed \"AI Literacy for Everyday Life\".','my-learning',NULL,'2026-11-10 12:28:03'),(9,6,'New feedback on \"Smallholder Planting Advisor\" from Amina Mwangi.','projects/dec608144c524639?tab=feedback',NULL,'2026-11-10 12:28:04'),(10,7,'New feedback on \"Smallholder Planting Advisor\" from Amina Mwangi.','projects/dec608144c524639?tab=feedback',NULL,'2026-11-10 12:28:04'),(11,8,'New feedback on \"Smallholder Planting Advisor\" from Amina Mwangi.','projects/dec608144c524639?tab=feedback',NULL,'2026-11-10 12:28:04'),(12,9,'New feedback on \"Smallholder Planting Advisor\" from Amina Mwangi.','projects/dec608144c524639?tab=feedback',NULL,'2026-11-10 12:28:04');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `body` varchar(300) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_q` (`question_id`),
  CONSTRAINT `fk_opt_q` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` VALUES (1,1,'Software that finds patterns in examples and uses them to predict or suggest',1),(2,1,'A robot that thinks exactly like a human',0),(3,1,'Any program that uses the internet',0),(4,1,'A type of computer screen',0),(5,2,'The examples an AI system learns from',1),(6,2,'The password used to log in',0),(7,2,'A lesson for human staff',0),(8,2,'The speed of your internet',0),(9,3,'It may work poorly on crops from other regions',1),(10,3,'It will always work perfectly everywhere',0),(11,3,'It will stop using electricity',0),(12,3,'It will translate to Swahili',0),(13,4,'Spoken words turned into text on your phone',1),(14,4,'A paper calendar',0),(15,4,'A battery charger',0),(16,4,'A printed map',0),(17,5,'\"Give me three tips for a first job interview in Nairobi, in simple English.\"',1),(18,5,'\"Interview.\"',0),(19,5,'\"Help.\"',0),(20,5,'\"Tell me everything.\"',0),(21,6,'A confident answer that contains made-up or wrong information',1),(22,6,'A slow internet connection',0),(23,6,'A picture that is too bright',0),(24,6,'A password reset email',0),(25,7,'Verify it with a trusted source before using it',1),(26,7,'Use it because the tool sounded sure',0),(27,7,'Delete your report',0),(28,7,'Share it immediately',0),(29,8,'Ask for a shorter version or a simpler example',1),(30,8,'Give up on AI forever',0),(31,8,'Copy it without reading',0),(32,8,'Switch off the phone',0),(33,9,'Your mobile money PIN',1),(34,9,'A question about healthy meals',0),(35,9,'A request to explain photosynthesis',0),(36,9,'A request to summarise a public article',0),(37,10,'They learn from data made by people, which can contain unfairness',1),(38,10,'Because they are too fast',0),(39,10,'Because they use too little electricity',0),(40,10,'Because they cannot read',0),(41,11,'Combine your knowledge with AI tools, check results, and keep learning',1),(42,11,'Refuse to learn anything new',0),(43,11,'Trust every AI answer',0),(44,11,'Only copy and paste',0),(45,12,'Ask for their permission and consider whether it is necessary',1),(46,12,'Upload it without telling them',0),(47,12,'Post it publicly first',0),(48,12,'Ignore privacy',0),(49,13,'river-mango-clock-seven',1),(50,13,'password123',0),(51,13,'Kampala1',0),(52,13,'qwerty',0),(53,14,'A second check, like a code, besides your password',1),(54,14,'A faster internet connection',0),(55,14,'A bigger screen',0),(56,14,'More storage',0),(57,15,'A scam',1),(58,15,'A normal service message',0),(59,15,'A bank policy',0),(60,15,'A software update',0),(61,16,'Do not click; contact the organisation using a number you already trust',1),(62,16,'Click quickly before it expires',0),(63,16,'Forward it to all friends',0),(64,16,'Reply with your details',0),(65,17,'It states clearly what the email is about',1),(66,17,'It is left empty',0),(67,17,'It is written in capital letters',0),(68,17,'It says only \"Hi\"',0),(69,18,'Several people can work on one copy and keep earlier versions',1),(70,18,'It removes the need for passwords',0),(71,18,'It makes files disappear',0),(72,18,'It works only with no internet',0),(73,19,'\"Thank you, Ruth Namutebi\"',1),(74,19,'\"bye\"',0),(75,19,'\"???\"',0),(76,19,'\"Whatever\"',0),(77,20,'Only the specific people who need it',1),(78,20,'Anyone with the link',0),(79,20,'The public',0),(80,20,'Nobody, ever, even teammates',0),(81,21,'=SUM(B2:B10)',1),(82,21,'=ADD B2 B10',0),(83,21,'=B2+B10 only',0),(84,21,'=PLUS(B)',0),(85,22,'Make numbers easier to understand at a glance',1),(86,22,'Hide the data',0),(87,22,'Replace all text',0),(88,22,'Slow down the computer',0),(89,23,'If one is lost or damaged you still have a copy',1),(90,23,'To use more storage',0),(91,23,'It is required by law',0),(92,23,'It makes the files bigger',0),(93,24,'Budget_2026-03_v2',1),(94,24,'final',0),(95,24,'document1',0),(96,24,'new new new',0),(97,25,'\"Matooke is the tastiest food.\"',1),(98,25,'\"Kampala is in Uganda.\"',0),(99,25,'\"Water boils at 100 degrees Celsius at sea level.\"',0),(100,25,'\"Kenya has 47 counties.\"',0),(101,26,'Noticing information that agrees with what you already believe',1),(102,26,'Checking many sources',0),(103,26,'Changing your mind quickly',0),(104,26,'Memorising numbers',0),(105,27,'Pause and check the claim before sharing',1),(106,27,'Share it to warn others',0),(107,27,'Reply with insults',0),(108,27,'Delete your phone',0),(109,28,'Evidence',1),(110,28,'Many shares',0),(111,28,'A loud voice',0),(112,28,'A nice picture',0),(113,29,'Stop and pause before reacting or sharing',1),(114,29,'Send it to everyone',0),(115,29,'Save it for later',0),(116,29,'Subscribe',0),(117,30,'A reverse image search to find where it first appeared',1),(118,30,'Trust it because it has many likes',0),(119,30,'Check only the caption',0),(120,30,'Ignore the date',0),(121,31,'Look for the claim in reliable news or official sources',1),(122,31,'Find more people who agree',0),(123,31,'Buy a newspaper',0),(124,31,'Zoom in on the picture',0),(125,32,'Whether the full speech gives a different meaning',1),(126,32,'Only the shocking clip',0),(127,32,'The number of views',0),(128,32,'The video format',0),(129,33,'A hasty generalisation',1),(130,33,'A strong argument',0),(131,33,'A proven fact',0),(132,33,'A question',0),(133,34,'They may share a common cause, hot sunny weather',1),(134,34,'Ice cream causes sunburn',0),(135,34,'Sunburn causes ice cream sales',0),(136,34,'They are unrelated in every way',0),(137,35,'A claim, reasons and evidence',1),(138,35,'Only strong opinions',0),(139,35,'Only a loud tone',0),(140,35,'Only one example without sources',0),(141,36,'More trustworthy',1),(142,36,'Weaker in every case',0),(143,36,'Shorter',0),(144,36,'Less polite',0),(145,37,'Values and beliefs',1),(146,37,'Clothing',0),(147,37,'Food',0),(148,37,'Music',0),(149,38,'Ask an open question to learn their view',1),(150,38,'Assume they agree',0),(151,38,'Assume they are rude',0),(152,38,'Stop the project',0),(153,39,'No style is better; they are different',1),(154,39,'Direct style is always correct',0),(155,39,'Indirect style is always polite',0),(156,39,'Style does not matter',0),(157,40,'A fixed idea about all people in a group',1),(158,40,'A proven fact',0),(159,40,'A greeting',0),(160,40,'A language',0),(161,41,'\"Please send the report by Friday.\"',1),(162,41,'\"Kindly revert with deliverables ASAP, going forward.\"',0),(163,41,'\"Let us circle back.\"',0),(164,41,'\"We should leverage synergies.\"',0),(165,42,'It confirms everyone understood the same thing',1),(166,42,'It wastes time',0),(167,42,'It replaces the call',0),(168,42,'It is required in every country',0),(169,43,'Checking understanding',1),(170,43,'Interrupting',0),(171,43,'Arguing',0),(172,43,'Translating',0),(173,44,'Avoid idioms and explain abbreviations',1),(174,44,'Use as much slang as possible',0),(175,44,'Write very long paragraphs',0),(176,44,'Use only abbreviations',0),(177,45,'2 pm',1),(178,45,'3 pm',0),(179,45,'4 pm',0),(180,45,'5 pm',0),(181,46,'An audio-only option and notes shared in writing',1),(182,46,'Requiring video for everyone',0),(183,46,'Sending very large files',0),(184,46,'Holding the call without notice',0),(185,47,'Describe what happened without blame and ask for their view',1),(186,47,'Complain to the whole group',0),(187,47,'Stop talking to them',0),(188,47,'Assume they lied',0),(189,48,'Different countries may use different times',1),(190,48,'It looks professional only',0),(191,48,'It is a legal rule',0),(192,48,'It makes the message longer',0),(193,49,'Clear enough that everyone can repeat it',1),(194,49,'Known only by the leader',0),(195,49,'Changed every day',0),(196,49,'Kept secret',0),(197,50,'Their different skills help solve problems together',1),(198,50,'To make the team larger only',0),(199,50,'To avoid planning',0),(200,50,'To avoid communication',0),(201,51,'Together, at the start of the project',1),(202,51,'By one person in secret',0),(203,51,'After the project ends',0),(204,51,'Never',0),(205,52,'Each member\'s private passwords',1),(206,52,'Meeting times',0),(207,52,'Reply times',0),(208,52,'How decisions are made',0),(209,53,'A major checkpoint made up of several tasks',1),(210,53,'A single email',0),(211,53,'A team member',0),(212,53,'A meeting room',0),(213,54,'One clear owner and a due date',1),(214,54,'Everyone as owner',0),(215,54,'No due date',0),(216,54,'A secret owner',0),(217,55,'Gives everyone the same standard for finished work',1),(218,55,'Makes work slower',0),(219,55,'Removes the need for review',0),(220,55,'Is only for managers',0),(221,56,'What is to do, in progress and done',1),(222,56,'Only completed tasks',0),(223,56,'Passwords',0),(224,56,'Weather',0),(225,57,'An audio call or short text updates',1),(226,57,'A video call for everyone',0),(227,57,'Sending large videos',0),(228,57,'Waiting until everyone has fast internet',0),(229,58,'Specific, kind and about the work',1),(230,58,'About the person\'s character',0),(231,58,'Vague',0),(232,58,'Shared only with others',0),(233,59,'So people who missed the call can catch up',1),(234,59,'To avoid talking',0),(235,59,'To impress managers',0),(236,59,'To make messages long',0),(237,60,'Ask a mentor or facilitator for help',1),(238,60,'Ignore it forever',0),(239,60,'Leave the team immediately',0),(240,60,'Insult the person',0),(241,61,'Communication',1),(242,61,'Knowing one particular software menu only',0),(243,61,'A hobby you never use',0),(244,61,'A secret',0),(245,62,'Planning and teamwork',1),(246,62,'Nothing useful',0),(247,62,'Only luck',0),(248,62,'Only free time',0),(249,63,'Specific, with a time frame',1),(250,63,'Vague',0),(251,63,'Secret',0),(252,63,'Impossible by definition',0),(253,64,'Understand your strengths and the direction you want',1),(254,64,'Send the same CV everywhere',0),(255,64,'Wait for luck',0),(256,64,'Avoid planning',0),(257,65,'\"Trained 25 farmers in mobile banking basics.\"',1),(258,65,'\"Responsible for stuff.\"',0),(259,65,'\"Good worker.\"',0),(260,65,'\"Helped.\"',0),(261,66,'One to two pages',1),(262,66,'Ten pages',0),(263,66,'Half a line',0),(264,66,'Unlimited',0),(265,67,'Adjusting it to fit the job you are applying for',1),(266,67,'Sending the same one to everyone',0),(267,67,'Adding false skills',0),(268,67,'Removing your name',0),(269,68,'Short and specific to the role',1),(270,68,'Very long and general',0),(271,68,'Copied from the internet',0),(272,68,'Left out always',0),(273,69,'Situation, Task, Action, Result',1),(274,69,'Skill, Time, Answer, Reply',0),(275,69,'Start, Talk, Ask, Rest',0),(276,69,'Study, Think, Act, Repeat',0),(277,70,'What you personally did',1),(278,70,'What everyone else did',0),(279,70,'Nothing',0),(280,70,'Only your feelings',0),(281,71,'Never ask you to pay to get a job',1),(282,71,'Ask for a registration fee',0),(283,71,'Ask for your PIN',0),(284,71,'Ask for your password',0),(285,72,'Reviewed so nothing embarrassing is visible',1),(286,72,'Left alone',0),(287,72,'Deleted without thought',0),(288,72,'Full of private details',0);
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `country` varchar(60) DEFAULT NULL,
  `age_group` enum('18-24','25-30','31-35') DEFAULT NULL,
  `headline` varchar(160) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `expertise` varchar(160) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `interests` varchar(255) DEFAULT NULL,
  `digital_level` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `connectivity` enum('good','limited','very_limited') NOT NULL DEFAULT 'good',
  `lite_mode` tinyint(1) NOT NULL DEFAULT 0,
  `organisation` varchar(160) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT INTO `profiles` VALUES (1,'Uganda','31-35','YouthConnect programme administrator',NULL,'Programme management',NULL,NULL,'advanced','good',0,'YouthConnect Steering Committee','2026-11-10 12:27:57'),(2,'Uganda','31-35','Digital literacy trainer, Kampala',NULL,'Digital skills training','Training, AI literacy',NULL,'advanced','good',0,'YouthConnect','2026-11-10 12:27:57'),(3,'Kenya','31-35','Employability and communication coach, Nairobi',NULL,'Career coaching','Coaching, communication',NULL,'advanced','good',0,'YouthConnect','2026-11-10 12:27:58'),(4,'Tanzania','31-35','Agricultural technology mentor, Arusha',NULL,'Agritech','Agribusiness, mentoring',NULL,'advanced','good',0,'AgriHub Tanzania','2026-11-10 12:27:58'),(5,'Uganda','31-35','Manager, Kabale Farmers\' Cooperative',NULL,'Farmer cooperatives',NULL,NULL,'advanced','good',0,'Kabale Farmers\' Cooperative','2026-11-10 12:27:59'),(6,'Uganda','18-24','Agribusiness student, Makerere University',NULL,'Agriculture','Farm planning, extension',NULL,'intermediate','good',0,'Makerere University','2026-11-10 12:27:59'),(7,'Kenya','25-30','Junior software developer, Nairobi',NULL,'Software development','PHP, JavaScript, mobile web',NULL,'intermediate','good',0,'Nairobi tech hub','2026-11-10 12:27:59'),(8,'Tanzania','25-30','Data analyst, Dar es Salaam',NULL,'Data analysis','Excel, SQL, data cleaning',NULL,'intermediate','limited',0,NULL,'2026-11-10 12:28:00'),(9,'Rwanda','18-24','Environmental science graduate, Kigali',NULL,'Environmental studies','GIS basics, field research',NULL,'intermediate','good',0,'University of Rwanda','2026-11-10 12:28:00'),(10,'Uganda','18-24','Recent graduate looking for work',NULL,'Business',NULL,NULL,'intermediate','limited',0,NULL,'2026-11-10 12:28:00'),(11,'Kenya','25-30','Community health volunteer',NULL,'Health',NULL,NULL,'intermediate','good',0,NULL,'2026-11-10 12:28:01'),(12,'Uganda','25-30','Water and sanitation technician',NULL,'Environmental studies','Water testing',NULL,'intermediate','good',0,NULL,'2026-11-10 12:28:01'),(13,'Tanzania','25-30','GIS and data officer',NULL,'Data analysis','Mapping, data',NULL,'intermediate','good',0,NULL,'2026-11-10 12:28:01');
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `project_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `body` varchar(2000) NOT NULL,
  `client_key` char(32) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_client` (`project_id`,`user_id`,`client_key`),
  KEY `idx_proj` (`project_id`,`id`),
  KEY `fk_msg_user` (`user_id`),
  CONSTRAINT `fk_msg_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `project_messages` WRITE;
/*!40000 ALTER TABLE `project_messages` DISABLE KEYS */;
INSERT INTO `project_messages` VALUES (1,1,7,'Hi team, I started a simple prototype layout. It loads in under 100 KB so it works on slow phones.',NULL,'2026-11-10 12:28:04'),(2,1,8,'Great. I will share the rainfall dataset on Friday.',NULL,'2026-11-10 12:28:04'),(3,1,9,'I can add notes about soil types for the highland districts.',NULL,'2026-11-10 12:28:04');
/*!40000 ALTER TABLE `project_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `project_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_staff` (
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `role` enum('mentor','facilitator','stakeholder') NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`project_id`,`user_id`,`role`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_ps_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `project_staff` WRITE;
/*!40000 ALTER TABLE `project_staff` DISABLE KEYS */;
INSERT INTO `project_staff` VALUES (1,2,'facilitator','2026-11-10 12:28:04'),(1,4,'mentor','2026-11-10 12:28:04'),(1,5,'stakeholder','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `project_staff` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `public_id` char(16) NOT NULL,
  `team_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `theme` varchar(80) DEFAULT NULL,
  `summary` varchar(500) NOT NULL,
  `problem` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `impact` text DEFAULT NULL,
  `status` enum('draft','proposed','changes_requested','approved','submitted','assessed','validated','completed') NOT NULL DEFAULT 'draft',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_id` (`public_id`),
  KEY `idx_team` (`team_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_proj_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'dec608144c524639',1,'Smallholder Planting Advisor','Agriculture & food security','A low-data mobile web tool that tells smallholder farmers the best time to plant, using local weather and soil information.','Smallholder farmers in Uganda, Kenya, Tanzania and Rwanda often plant too early or too late because rainfall has become less predictable, and advice from extension officers is hard to reach.','We will combine farmer interviews (agriculture), a lightweight web app that works on basic smartphones (software), seasonal rainfall data analysis (data) and local environmental conditions (environment) into a simple planting calendar delivered in English and Swahili.','We aim to reach 200 farmers in a pilot, reduce failed plantings, and measure the change in yield reported at the end of the season.','changes_requested',4,'2026-11-10 12:28:04','2026-11-10 12:28:04'),(2,'9459ac55a932d150',2,'Borehole Water Quality Map','Water & sanitation','A shared map showing which community boreholes have been tested and whether the water is safe to drink.',NULL,NULL,NULL,'draft',1,'2026-11-10 12:28:04','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` int(10) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `body` varchar(500) NOT NULL,
  `explanation` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_quiz` (`quiz_id`,`position`),
  CONSTRAINT `fk_q_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,1,'Which statement best describes artificial intelligence?','AI systems learn patterns from data. They do not think like people.'),(2,1,2,'What is \"training data\"?','Training data is the set of examples used to teach the system.'),(3,1,3,'An AI crop-disease app was trained only on photos from one region. What risk does this create?','One-sided training data can make results unreliable for people or places not represented.'),(4,1,4,'Which of these is the best example of everyday AI?','Speech-to-text learns patterns in how language sounds.'),(5,2,1,'Which prompt is most likely to give a useful answer?','Specific context, task and format make answers more useful.'),(6,2,2,'What is a \"hallucination\" in AI?','AI can invent facts that sound believable.'),(7,2,3,'An AI tool gives you a statistic for your report. What should you do?','Always verify numbers and facts yourself.'),(8,2,4,'If the first AI answer is too long, what is a good next step?','You can improve results by refining your request step by step.'),(9,3,1,'Which of these should you NEVER type into an AI chatbot?','PINs and passwords must stay private.'),(10,3,2,'Why can AI systems be biased?','Biased data leads to biased results.'),(11,3,3,'Which approach best prepares you for a workplace with more AI?','Judgement, communication and learning remain valuable.'),(12,3,4,'Before sharing a classmate\'s photo with an AI app, you should:','Other people\'s personal information needs their consent.'),(13,4,1,'Which password is strongest?','Long, unique passphrases are strong and easier to remember.'),(14,4,2,'What does two-step verification add?','A second step protects the account even if the password leaks.'),(15,4,3,'A message says \"Send your mobile money PIN to claim your prize.\" This is:','Nobody legitimate asks for your PIN.'),(16,4,4,'What is the safest response to a suspicious link?','Verify through a separate trusted channel.'),(17,5,1,'What makes a good email subject line?','A clear subject helps the reader act quickly.'),(18,5,2,'What is a benefit of editing documents in the cloud?','Cloud tools support collaboration and version history.'),(19,5,3,'Which closing is most appropriate for a work email?','A polite close with your name is professional.'),(20,5,4,'When a document contains private information you should share it with:','Limit access to people who need it.'),(21,6,1,'Which formula adds the numbers in cells B2 to B10?','SUM adds a range of cells.'),(22,6,2,'A chart is best used to:','Charts summarise patterns visually.'),(23,6,3,'Why keep important files in two places?','Backups protect your work.'),(24,6,4,'Which file name is clearest?','Descriptive names with dates and versions are easy to find.'),(25,7,1,'Which of these is an opinion?','Taste is a personal view, not a checkable fact.'),(26,7,2,'What is confirmation bias?','We tend to favour evidence that supports our existing beliefs.'),(27,7,3,'A message makes you very angry and says \"Share now!\" What should you do first?','Strong emotion is a signal to slow down and verify.'),(28,7,4,'A claim needs:','Evidence, not popularity, supports a claim.'),(29,8,1,'What does the \"S\" in SIFT remind you to do?','The first step is to stop.'),(30,8,2,'You see a dramatic photo said to be from yesterday. What is a good check?','Reverse image search often reveals old or reused images.'),(31,8,3,'\"Find better coverage\" means:','Reliable corroboration is stronger than repetition.'),(32,8,4,'A short clip of a speech seems to say something shocking. What should you consider?','Missing context can change the meaning.'),(33,9,1,'\"Everyone from that village is dishonest because one person cheated me.\" This is:','One case does not describe everyone.'),(34,9,2,'Ice cream sales and sunburn both rise in summer. Which conclusion is sound?','Correlation does not prove causation.'),(35,9,3,'A good argument includes:','Reasons must be supported by evidence.'),(36,9,4,'Responding to the strongest opposing point makes your argument:','Fairly answering objections builds credibility.'),(37,10,1,'Which part of culture is hardest to see?','The hidden part of culture includes values and beliefs.'),(38,10,2,'A teammate stays silent when you propose a plan. The best response is to:','Silence can mean many things.'),(39,10,3,'Which statement about communication styles is best?','Respect different styles and check understanding.'),(40,10,4,'A stereotype is:','Stereotypes ignore individual differences.'),(41,11,1,'Which sentence is plainer?','Short, direct sentences are easier to understand.'),(42,11,2,'Why summarise decisions in writing after a call?','Written summaries reduce misunderstandings.'),(43,11,3,'Repeating back what you heard is called:','It helps catch errors early.'),(44,11,4,'Which is best when writing to teammates who use English as a second language?','Clear, simple writing includes everyone.'),(45,12,1,'When it is 3 pm in Kampala, what time is it in Kigali?','Rwanda uses Central Africa Time, UTC+2, one hour behind East Africa Time.'),(46,12,2,'What helps teammates with weak connections?','Low-bandwidth options make participation fair.'),(47,12,3,'What is a good first step when you disagree after a misunderstanding?','Calm, specific conversation repairs trust.'),(48,12,4,'Why write the time zone with the meeting time?','Time zones differ within the region.'),(49,13,1,'A shared team goal should be:','A clear goal aligns everyone.'),(50,13,2,'Why include different disciplines in one team?','Complementary skills make teams stronger.'),(51,13,3,'A team agreement is best written:','Early, shared rules prevent conflict.'),(52,13,4,'Which is NOT usually part of a team agreement?','Passwords are private and never belong in a shared document.'),(53,14,1,'What is a milestone?','Milestones mark important progress points.'),(54,14,2,'Each task should have:','Clear ownership drives action.'),(55,14,3,'A \"definition of done\" helps because it:','It avoids confusion about what finished means.'),(56,14,4,'A simple three-column board shows:','It makes progress visible.'),(57,15,1,'When your connection is weak, which option is best for a team discussion?','Use the lightest tool that works for everyone.'),(58,15,2,'Good feedback is:','Focus on the work and suggest improvements.'),(59,15,3,'Why record decisions in writing?','Written records keep everyone aligned.'),(60,15,4,'If a conflict continues in your team, you should:','Seeking support early is a sign of maturity.'),(61,16,1,'Which is a transferable skill?','Communication applies to nearly every job.'),(62,16,2,'Organising a community event can show:','Volunteer experience builds real skills.'),(63,16,3,'A good career goal is:','Clear goals guide action.'),(64,16,4,'Before applying for jobs, you should first:','Self-knowledge helps you target well.'),(65,17,1,'Which line is strongest on a CV?','Specific actions with numbers show impact.'),(66,17,2,'How long should a CV for an early-career applicant normally be?','Short and focused is best.'),(67,17,3,'Tailoring your CV means:','Tailoring shows you read the advert and fit it.'),(68,17,4,'A good cover letter is:','Keep it clear and relevant.'),(69,18,1,'What does STAR stand for?','STAR structures a clear example.'),(70,18,2,'In the \"Action\" step you should describe:','Interviewers want to know your own contribution.'),(71,18,3,'A genuine employer will:','Requests for payment are a classic scam.'),(72,18,4,'Before applying, your public social media should be:','Employers often look at your online presence.');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `quiz_id` int(10) unsigned NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `passed` tinyint(1) NOT NULL,
  `answers` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_quiz` (`user_id`,`quiz_id`),
  KEY `fk_att_quiz` (`quiz_id`),
  CONSTRAINT `fk_att_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quiz_attempts` WRITE;
/*!40000 ALTER TABLE `quiz_attempts` DISABLE KEYS */;
INSERT INTO `quiz_attempts` VALUES (1,6,13,100,1,'{}','2026-11-10 12:28:03'),(2,6,14,100,1,'{}','2026-11-10 12:28:03'),(3,6,15,100,1,'{}','2026-11-10 12:28:03'),(4,7,13,100,1,'{}','2026-11-10 12:28:03'),(5,7,14,100,1,'{}','2026-11-10 12:28:03'),(6,7,15,100,1,'{}','2026-11-10 12:28:03'),(7,8,13,100,1,'{}','2026-11-10 12:28:03'),(8,8,14,100,1,'{}','2026-11-10 12:28:03'),(9,8,15,100,1,'{}','2026-11-10 12:28:03'),(10,9,13,100,1,'{}','2026-11-10 12:28:03'),(11,9,14,100,1,'{}','2026-11-10 12:28:03'),(12,9,15,100,1,'{}','2026-11-10 12:28:03'),(13,12,13,100,1,'{}','2026-11-10 12:28:03'),(14,12,14,100,1,'{}','2026-11-10 12:28:03'),(15,12,15,100,1,'{}','2026-11-10 12:28:03'),(16,13,13,100,1,'{}','2026-11-10 12:28:03'),(17,13,14,100,1,'{}','2026-11-10 12:28:03'),(18,13,15,100,1,'{}','2026-11-10 12:28:03'),(19,6,1,100,1,'{}','2026-11-10 12:28:03'),(20,6,2,100,1,'{}','2026-11-10 12:28:03'),(21,6,3,100,1,'{}','2026-11-10 12:28:03'),(22,7,1,100,1,'{}','2026-11-10 12:28:03'),(23,7,2,100,1,'{}','2026-11-10 12:28:03'),(24,7,3,100,1,'{}','2026-11-10 12:28:03');
/*!40000 ALTER TABLE `quiz_attempts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `pass_mark` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `minutes` smallint(5) unsigned NOT NULL DEFAULT 15,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_id` (`module_id`),
  CONSTRAINT `fk_quiz_mod` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quizzes` WRITE;
/*!40000 ALTER TABLE `quizzes` DISABLE KEYS */;
INSERT INTO `quizzes` VALUES (1,1,'Week 1 Quiz',70,10),(2,2,'Week 2 Quiz',70,10),(3,3,'Week 3 Quiz',70,10),(4,4,'Week 1 Quiz',70,10),(5,5,'Week 2 Quiz',70,10),(6,6,'Week 3 Quiz',70,10),(7,7,'Week 1 Quiz',70,10),(8,8,'Week 2 Quiz',70,10),(9,9,'Week 3 Quiz',70,10),(10,10,'Week 1 Quiz',70,10),(11,11,'Week 2 Quiz',70,10),(12,12,'Week 3 Quiz',70,10),(13,13,'Week 1 Quiz',70,10),(14,14,'Week 2 Quiz',70,10),(15,15,'Week 3 Quiz',70,10),(16,16,'Week 1 Quiz',70,10),(17,17,'Week 2 Quiz',70,10),(18,18,'Week 3 Quiz',70,10);
/*!40000 ALTER TABLE `quizzes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` varchar(600) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review` (`course_id`,`user_id`),
  KEY `fk_rev_user` (`user_id`),
  CONSTRAINT `fk_rev_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,5,6,5,'Practical and clear. The team agreement exercise helped our group a lot.','2026-11-10 12:28:04'),(2,5,7,5,'Short lessons that work well on my phone.','2026-11-10 12:28:04'),(3,5,8,4,'Good content. I would like more examples about remote work with weak internet.','2026-11-10 12:28:04'),(4,5,9,5,'The quizzes at the end of every week kept me focused.','2026-11-10 12:28:04'),(5,1,6,5,'I finally understand what AI is, without any jargon.','2026-11-10 12:28:04'),(6,1,7,4,'Very useful tips on checking AI answers.','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `data` mediumtext NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_upd` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `body` text NOT NULL,
  `status` enum('submitted','graded') NOT NULL DEFAULT 'submitted',
  `score` tinyint(3) unsigned DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(10) unsigned DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `graded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asg_user` (`assignment_id`,`user_id`),
  KEY `fk_sub_user` (`user_id`),
  CONSTRAINT `fk_sub_asg` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (1,5,6,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(2,5,7,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(3,5,8,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(4,5,9,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(5,5,12,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(6,5,13,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(7,1,6,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03'),(8,1,7,'Demo submission written for the seeded example learner.','graded',88,'Clear and practical. Good use of a real example. Next time, add one measurable result.',2,'2026-11-10 12:28:03','2026-11-10 12:28:03');
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `milestone_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `assignee_id` int(10) unsigned DEFAULT NULL,
  `status` enum('todo','doing','done') NOT NULL DEFAULT 'todo',
  `due_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proj` (`project_id`),
  KEY `fk_task_ms` (`milestone_id`),
  CONSTRAINT `fk_task_ms` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_task_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,1,1,'Interview 10 farmers in Kabale and Mbarara',6,'done','2026-12-01','2026-11-10 12:28:04'),(2,1,2,'Draft the planting calendar logic',8,'doing','2026-12-01','2026-11-10 12:28:04'),(3,1,2,'Build the mobile web prototype',7,'todo','2026-12-01','2026-11-10 12:28:04'),(4,1,2,'Collect soil and rainfall notes',9,'todo','2026-12-01','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members` (
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `role` enum('lead','member') NOT NULL DEFAULT 'member',
  `discipline` varchar(80) DEFAULT NULL,
  `status` enum('invited','requested','active') NOT NULL DEFAULT 'active',
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`team_id`,`user_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES (1,6,'lead','Agriculture','active','2026-11-10 12:28:04'),(1,7,'member','Software development','active','2026-11-10 12:28:04'),(1,8,'member','Data analysis','active','2026-11-10 12:28:04'),(1,9,'member','Environmental studies','active','2026-11-10 12:28:04'),(2,12,'lead','Environmental studies','active','2026-11-10 12:28:04'),(2,13,'member','Data analysis','active','2026-11-10 12:28:04');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `public_id` char(16) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `looking_for` varchar(255) DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_id` (`public_id`),
  KEY `fk_team_user` (`created_by`),
  CONSTRAINT `fk_team_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'ca65f6c8ab893b3b','AgriTech Bridge','A cross-border team building a mobile-friendly planting advisor for smallholder farmers.','An environmental scientist from Burundi or Ethiopia',1,6,'2026-11-10 12:28:04'),(2,'1fd2619b3a4bb508','Clean Water Data','Mapping borehole water quality in rural districts to help communities choose safe water sources.','A mobile developer',1,12,'2026-11-10 12:28:04');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `throttle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `throttle` (
  `k` varchar(120) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  KEY `idx_k` (`k`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `throttle` WRITE;
/*!40000 ALTER TABLE `throttle` DISABLE KEYS */;
/*!40000 ALTER TABLE `throttle` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `user_id` int(10) unsigned NOT NULL,
  `badge_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned DEFAULT NULL,
  `awarded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`badge_id`),
  KEY `fk_ub_badge` (`badge_id`),
  CONSTRAINT `fk_ub_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `google_id` varchar(40) DEFAULT NULL,
  `role` enum('participant','facilitator','mentor','stakeholder','admin') NOT NULL DEFAULT 'participant',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `level2_override` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Programme Admin','admin@youthconnect.test','$2y$12$M5tpilM4Qxqm5KbhyN4fZ.S4l0RNuH.oD2qYWBgsLHk1lMLBYLsiW',NULL,'admin','active',0,'2026-11-10 12:27:57',NULL),(2,'Grace Nakato','grace@youthconnect.test','$2y$12$JowuWmKatr5ziJ1cMw9.ju5SK71YyQ5jtsWGqLViEcxRe6ULsVMoK',NULL,'facilitator','active',0,'2026-11-10 12:27:57',NULL),(3,'David Otieno','david@youthconnect.test','$2y$12$eFag8IpBya8S00OV/JlnMugI24crvq3RvQFMVaQzFjUTZyEqgl4nq',NULL,'facilitator','active',0,'2026-11-10 12:27:58',NULL),(4,'Amina Mwangi','amina@youthconnect.test','$2y$12$4bms8ivqeO5hryGiTnAenu2yV3ihrxzSVsJLPYCs3UdXuP0WZ21e6',NULL,'mentor','active',0,'2026-11-10 12:27:58',NULL),(5,'Samuel Byaruhanga','samuel@youthconnect.test','$2y$12$TE.snDYFI9v6lsFX8RwPr.Ph3DQ7V0KSQGB8wmyupR00mOhsmDs8m',NULL,'stakeholder','active',0,'2026-11-10 12:27:59',NULL),(6,'Brian Ssemakula','brian@youthconnect.test','$2y$12$0QZmKad4X4W9ImHFPTVLjeTiitsIZ2zvDU7NSb8xdyZH5UrC28PZO',NULL,'participant','active',0,'2026-11-10 12:27:59',NULL),(7,'Wanjiru Kamau','wanjiru@youthconnect.test','$2y$12$Gw..PmtVjs74wG8/sCXQ8.U/fL19JHzDm0vLsKLvegh02r2h5fbsC',NULL,'participant','active',0,'2026-11-10 12:27:59',NULL),(8,'Joseph Mushi','joseph@youthconnect.test','$2y$12$ut2BZwjuQ5HqP7dU5GyKUOdbenv/OjUEW7KyljqSJfr.u/cBFYsJ6',NULL,'participant','active',0,'2026-11-10 12:28:00',NULL),(9,'Aline Uwase','aline@youthconnect.test','$2y$12$u3GvOJ8neEq4icuqwVlq/.5ge9SmwpkOM5oxKtYakOBwjqmPZMLtO',NULL,'participant','active',0,'2026-11-10 12:28:00',NULL),(10,'Peter Okello','peter@youthconnect.test','$2y$12$LLalQEn7KcOXA03WqPIrru5.JKeWtyG6.stLCWfxXaIxl6FGo0Dk6',NULL,'participant','active',0,'2026-11-10 12:28:00',NULL),(11,'Esther Achieng','esther@youthconnect.test','$2y$12$ggYl6WNeS6l8MHvNKuKw6eeiovVP1JpJtdC4eAo0bo.01Rj9ofL26',NULL,'participant','active',0,'2026-11-10 12:28:01',NULL),(12,'Moses Kato','moses@youthconnect.test','$2y$12$9azf/24j.o0N7Cw6TBT8tuQpfwKha2d1k1EY1c45CJYByzqpeTj1y',NULL,'participant','active',0,'2026-11-10 12:28:01',NULL),(13,'Fatuma Hassan','fatuma@youthconnect.test','$2y$12$eM8BlDXL.9syAZJUJb4VzewfT/dKJDRfld11hxuc0jdO6MOZYB5oC',NULL,'participant','active',0,'2026-11-10 12:28:01',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `validations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `validations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `stakeholder_id` int(10) unsigned NOT NULL,
  `decision` enum('endorsed','not_endorsed') NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_val` (`project_id`,`stakeholder_id`),
  CONSTRAINT `fk_val_proj` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `validations` WRITE;
/*!40000 ALTER TABLE `validations` DISABLE KEYS */;
/*!40000 ALTER TABLE `validations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

