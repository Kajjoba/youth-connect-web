-- YouthConnect Platform schema (MySQL 5.7+ / MariaDB 10.3+), InnoDB, utf8mb4
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS sessions, throttle, audit_log, notifications, user_badges, badges, validations,
  assessments, feedback, project_messages, evidence, tasks, milestones, project_staff, projects,
  team_members, teams, certificates, discussion_posts, reviews, submissions, assignments,
  quiz_attempts, options, questions, quizzes, lesson_progress, lessons, modules, enrollments,
  courses, categories, profiles, users;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  google_id VARCHAR(40) NULL UNIQUE,
  role ENUM('participant','facilitator','mentor','stakeholder','admin') NOT NULL DEFAULT 'participant',
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  level2_override TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL,
  KEY idx_role (role)
) ENGINE=InnoDB;

CREATE TABLE profiles (
  user_id INT UNSIGNED PRIMARY KEY,
  country VARCHAR(60) NULL,
  age_group ENUM('18-24','25-30','31-35') NULL,
  headline VARCHAR(160) NULL,
  bio TEXT NULL,
  expertise VARCHAR(160) NULL,
  skills VARCHAR(255) NULL,
  interests VARCHAR(255) NULL,
  digital_level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  connectivity ENUM('good','limited','very_limited') NOT NULL DEFAULT 'good',
  lite_mode TINYINT(1) NOT NULL DEFAULT 0,
  organisation VARCHAR(160) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  owner_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  summary VARCHAR(300) NOT NULL,
  description TEXT NOT NULL,
  what_you_learn TEXT NOT NULL,
  skills VARCHAR(300) NULL,
  level ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  hours_per_week TINYINT UNSIGNED NOT NULL DEFAULT 3,
  language VARCHAR(40) NOT NULL DEFAULT 'English',
  color VARCHAR(7) NOT NULL DEFAULT '#0b5cd5',
  has_certificate TINYINT(1) NOT NULL DEFAULT 1,
  pass_mark TINYINT UNSIGNED NOT NULL DEFAULT 70,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_status (status), KEY idx_cat (category_id),
  FULLTEXT KEY ft_courses (title, summary, skills),
  CONSTRAINT fk_course_cat FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_course_owner FOREIGN KEY (owner_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- a module is one week of a course
CREATE TABLE modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  week_no TINYINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  hours_estimate TINYINT UNSIGNED NOT NULL DEFAULT 3,
  UNIQUE KEY uq_course_week (course_id, week_no),
  CONSTRAINT fk_mod_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_id INT UNSIGNED NOT NULL,
  position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  type ENUM('reading','video','discussion') NOT NULL DEFAULT 'reading',
  title VARCHAR(160) NOT NULL,
  content MEDIUMTEXT NULL,
  video_url VARCHAR(300) NULL,
  minutes SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  is_preview TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_mod_pos (module_id, position),
  CONSTRAINT fk_les_mod FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quizzes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_id INT UNSIGNED NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  pass_mark TINYINT UNSIGNED NOT NULL DEFAULT 70,
  minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  CONSTRAINT fk_quiz_mod FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT UNSIGNED NOT NULL,
  position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  body VARCHAR(500) NOT NULL,
  explanation VARCHAR(500) NULL,
  KEY idx_quiz (quiz_id, position),
  CONSTRAINT fk_q_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  body VARCHAR(300) NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_q (question_id),
  CONSTRAINT fk_opt_q FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  status ENUM('active','completed','dropped') NOT NULL DEFAULT 'active',
  enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  last_lesson_id INT UNSIGNED NULL,
  UNIQUE KEY uq_user_course (user_id, course_id),
  KEY idx_course (course_id),
  CONSTRAINT fk_enr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_enr_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
  user_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED NOT NULL,
  completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, lesson_id),
  CONSTRAINT fk_lp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_lp_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  quiz_id INT UNSIGNED NOT NULL,
  score TINYINT UNSIGNED NOT NULL,
  passed TINYINT(1) NOT NULL,
  answers TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_quiz (user_id, quiz_id),
  CONSTRAINT fk_att_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_att_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_id INT UNSIGNED NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  instructions TEXT NOT NULL,
  max_score TINYINT UNSIGNED NOT NULL DEFAULT 100,
  CONSTRAINT fk_asg_mod FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  status ENUM('submitted','graded') NOT NULL DEFAULT 'submitted',
  score TINYINT UNSIGNED NULL,
  feedback TEXT NULL,
  graded_by INT UNSIGNED NULL,
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  graded_at DATETIME NULL,
  UNIQUE KEY uq_asg_user (assignment_id, user_id),
  CONSTRAINT fk_sub_asg FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment VARCHAR(600) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review (course_id, user_id),
  CONSTRAINT fk_rev_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT fk_rev_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE discussion_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  body VARCHAR(2000) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_course (course_id, created_at),
  CONSTRAINT fk_dp_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT fk_dp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- LEVEL TWO ---------------------------------------------------------------

CREATE TABLE teams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  public_id CHAR(16) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(500) NULL,
  looking_for VARCHAR(255) NULL,
  is_open TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_team_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE team_members (
  team_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('lead','member') NOT NULL DEFAULT 'member',
  discipline VARCHAR(80) NULL,
  status ENUM('invited','requested','active') NOT NULL DEFAULT 'active',
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (team_id, user_id),
  KEY idx_user (user_id),
  CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  public_id CHAR(16) NOT NULL UNIQUE,
  team_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  theme VARCHAR(80) NULL,
  summary VARCHAR(500) NOT NULL,
  problem TEXT NULL,
  solution TEXT NULL,
  impact TEXT NULL,
  status ENUM('draft','proposed','changes_requested','approved','submitted','assessed','validated','completed') NOT NULL DEFAULT 'draft',
  version INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_team (team_id), KEY idx_status (status),
  CONSTRAINT fk_proj_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_staff (
  project_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('mentor','facilitator','stakeholder') NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (project_id, user_id, role),
  KEY idx_user (user_id),
  CONSTRAINT fk_ps_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE milestones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  due_date DATE NULL,
  status ENUM('open','done') NOT NULL DEFAULT 'open',
  position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  KEY idx_proj (project_id),
  CONSTRAINT fk_ms_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  milestone_id INT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  assignee_id INT UNSIGNED NULL,
  status ENUM('todo','doing','done') NOT NULL DEFAULT 'todo',
  due_date DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_proj (project_id),
  CONSTRAINT fk_task_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_ms FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE evidence (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  public_id CHAR(16) NOT NULL UNIQUE,
  project_id INT UNSIGNED NOT NULL,
  milestone_id INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  kind ENUM('file','link','note') NOT NULL,
  file_path VARCHAR(255) NULL,
  original_name VARCHAR(190) NULL,
  mime VARCHAR(100) NULL,
  size_bytes INT UNSIGNED NULL,
  url VARCHAR(400) NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_proj (project_id),
  CONSTRAINT fk_ev_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  kind ENUM('mentor','facilitator','stakeholder') NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_proj (project_id, created_at),
  CONSTRAINT fk_fb_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_fb_user FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE project_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body VARCHAR(2000) NOT NULL,
  client_key CHAR(32) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_client (project_id, user_id, client_key),
  KEY idx_proj (project_id, id),
  CONSTRAINT fk_msg_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE assessments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL UNIQUE,
  assessor_id INT UNSIGNED NOT NULL,
  impact TINYINT UNSIGNED NOT NULL,
  collaboration TINYINT UNSIGNED NOT NULL,
  technical TINYINT UNSIGNED NOT NULL,
  presentation TINYINT UNSIGNED NOT NULL,
  sustainability TINYINT UNSIGNED NOT NULL,
  total TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_as_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE validations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  stakeholder_id INT UNSIGNED NOT NULL,
  decision ENUM('endorsed','not_endorsed') NOT NULL,
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_val (project_id, stakeholder_id),
  CONSTRAINT fk_val_proj FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE badges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  icon VARCHAR(8) NOT NULL DEFAULT '*'
) ENGINE=InnoDB;

CREATE TABLE user_badges (
  user_id INT UNSIGNED NOT NULL,
  badge_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NULL,
  awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, badge_id),
  CONSTRAINT fk_ub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ub_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(14) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  kind ENUM('course','project') NOT NULL,
  course_id INT UNSIGNED NOT NULL DEFAULT 0,
  project_id INT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(200) NOT NULL,
  issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_course (user_id, kind, course_id, project_id),
  CONSTRAINT fk_cert_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PLATFORM ----------------------------------------------------------------

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  message VARCHAR(255) NOT NULL,
  link VARCHAR(255) NULL,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user (user_id, read_at),
  CONSTRAINT fk_not_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  entity VARCHAR(40) NULL,
  entity_id VARCHAR(40) NULL,
  detail VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_action (action, created_at)
) ENGINE=InnoDB;

CREATE TABLE throttle (
  k VARCHAR(120) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_k (k, attempted_at)
) ENGINE=InnoDB;

CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  data MEDIUMTEXT NOT NULL,
  updated_at INT UNSIGNED NOT NULL,
  KEY idx_upd (updated_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
