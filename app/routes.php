<?php
use App\Controllers\{HomeController, AuthController, CourseController, LearnController, DashboardController,
    CertificateController, ManageController, AdminController, TeamController, ProjectController, ApiController, StaffController};

/** @var App\Core\Router $router */
$r = $router;
$auth = ['auth'];
$staff = ['auth', 'role:facilitator,admin'];
$admin = ['auth', 'role:admin'];

// ---- Public -----------------------------------------------------------------
$r->get('/', [HomeController::class, 'index']);
$r->get('/offline', [HomeController::class, 'offline']);
$r->post('/lite', [HomeController::class, 'lite']);
$r->get('/api/search', [HomeController::class, 'search']);
$r->get('/courses', [CourseController::class, 'index']);
$r->get('/course/{slug}', [CourseController::class, 'show']);
$r->get('/certificate/{code}', [CertificateController::class, 'show']);

// ---- Accounts ---------------------------------------------------------------
$r->get('/login', [AuthController::class, 'loginForm'], ['guest']);
$r->post('/login', [AuthController::class, 'login'], ['guest']);
$r->get('/register', [AuthController::class, 'registerForm'], ['guest']);
$r->post('/register', [AuthController::class, 'register'], ['guest']);
$r->get('/auth/google', [\App\Controllers\GoogleAuthController::class, 'start'], ['guest']);
$r->get('/auth/google/callback', [\App\Controllers\GoogleAuthController::class, 'callback'], ['guest']);
$r->post('/logout', [AuthController::class, 'logout'], $auth);

// ---- Level One: learner -----------------------------------------------------
$r->get('/my-learning', [DashboardController::class, 'index'], $auth);
$r->get('/profile', [DashboardController::class, 'profile'], $auth);
$r->post('/profile', [DashboardController::class, 'saveProfile'], $auth);
$r->get('/notifications', [DashboardController::class, 'notifications'], $auth);
$r->post('/notifications/read', [DashboardController::class, 'markRead'], $auth);

$r->post('/course/{slug}/enroll', [CourseController::class, 'enroll'], $auth);
$r->post('/course/{slug}/drop', [CourseController::class, 'drop'], $auth);
$r->post('/course/{slug}/review', [CourseController::class, 'review'], $auth);

$r->get('/learn/{slug}', [LearnController::class, 'start'], $auth);
$r->get('/learn/{slug}/lesson/{id}', [LearnController::class, 'lesson'], $auth);
$r->post('/learn/{slug}/lesson/{id}/complete', [LearnController::class, 'complete'], $auth);
$r->get('/learn/{slug}/quiz/{id}', [LearnController::class, 'quiz'], $auth);
$r->post('/learn/{slug}/quiz/{id}', [LearnController::class, 'submitQuiz'], $auth);
$r->get('/learn/{slug}/assignment/{id}', [LearnController::class, 'assignment'], $auth);
$r->post('/learn/{slug}/assignment/{id}', [LearnController::class, 'submitAssignment'], $auth);
$r->get('/learn/{slug}/discussion', [LearnController::class, 'discussion'], $auth);
$r->post('/learn/{slug}/discussion', [LearnController::class, 'post'], $auth);

// ---- Facilitator / admin: course management ---------------------------------
$r->get('/manage/courses', [ManageController::class, 'courses'], $staff);
$r->get('/manage/courses/new', [ManageController::class, 'newCourse'], $staff);
$r->post('/manage/courses', [ManageController::class, 'createCourse'], $staff);
$r->get('/manage/courses/{id}', [ManageController::class, 'editCourse'], $staff);
$r->post('/manage/courses/{id}', [ManageController::class, 'updateCourse'], $staff);
$r->post('/manage/courses/{id}/status', [ManageController::class, 'setStatus'], $staff);
$r->post('/manage/courses/{id}/weeks', [ManageController::class, 'addWeek'], $staff);
$r->get('/manage/courses/{id}/learners', [ManageController::class, 'learners'], $staff);
$r->get('/manage/modules/{id}', [ManageController::class, 'editModule'], $staff);
$r->post('/manage/modules/{id}', [ManageController::class, 'updateModule'], $staff);
$r->post('/manage/modules/{id}/delete', [ManageController::class, 'deleteModule'], $staff);
$r->post('/manage/modules/{id}/lessons', [ManageController::class, 'addLesson'], $staff);
$r->post('/manage/modules/{id}/questions', [ManageController::class, 'addQuestion'], $staff);
$r->post('/manage/modules/{id}/assignment', [ManageController::class, 'saveAssignment'], $staff);
$r->get('/manage/lessons/{id}', [ManageController::class, 'editLesson'], $staff);
$r->post('/manage/lessons/{id}', [ManageController::class, 'updateLesson'], $staff);
$r->post('/manage/lessons/{id}/delete', [ManageController::class, 'deleteLesson'], $staff);
$r->post('/manage/questions/{id}/delete', [ManageController::class, 'deleteQuestion'], $staff);
$r->get('/manage/submissions', [ManageController::class, 'submissions'], $staff);
$r->post('/manage/submissions/{id}/grade', [ManageController::class, 'grade'], $staff);

// ---- Admin ------------------------------------------------------------------
$r->get('/admin', [AdminController::class, 'dashboard'], $admin);
$r->get('/admin/users', [AdminController::class, 'users'], $admin);
$r->post('/admin/users', [AdminController::class, 'createUser'], $admin);
$r->post('/admin/users/{id}/update', [AdminController::class, 'updateUser'], $admin);
$r->post('/admin/users/{id}/reset', [AdminController::class, 'resetPassword'], $admin);
$r->get('/admin/categories', [AdminController::class, 'categories'], $admin);
$r->post('/admin/categories', [AdminController::class, 'saveCategory'], $admin);
$r->get('/admin/projects', [AdminController::class, 'projects'], $admin);
$r->get('/admin/audit', [AdminController::class, 'audit'], $admin);

// ---- Level Two: teams & projects ---------------------------------------------
$r->get('/teams', [TeamController::class, 'index'], $auth);
$r->get('/teams/new', [TeamController::class, 'create'], $auth);
$r->post('/teams', [TeamController::class, 'store'], $auth);
$r->get('/teams/{pid}', [TeamController::class, 'show'], $auth);
$r->post('/teams/{pid}/invite', [TeamController::class, 'invite'], $auth);
$r->post('/teams/{pid}/join', [TeamController::class, 'requestJoin'], $auth);
$r->post('/teams/{pid}/respond', [TeamController::class, 'respond'], $auth);
$r->post('/teams/{pid}/member', [TeamController::class, 'updateMember'], $auth);
$r->post('/teams/{pid}/leave', [TeamController::class, 'leave'], $auth);
$r->post('/teams/{pid}/projects', [ProjectController::class, 'store'], $auth);

$r->get('/staff/projects', [StaffController::class, 'projects'], ['auth', 'role:mentor,facilitator,stakeholder,admin']);

$r->get('/projects/{pid}', [ProjectController::class, 'show'], $auth);
$r->post('/projects/{pid}', [ProjectController::class, 'update'], $auth);
$r->post('/projects/{pid}/transition', [ProjectController::class, 'transition'], $auth);
$r->post('/projects/{pid}/milestones', [ProjectController::class, 'addMilestone'], $auth);
$r->post('/projects/{pid}/milestones/{id}', [ProjectController::class, 'toggleMilestone'], $auth);
$r->post('/projects/{pid}/tasks', [ProjectController::class, 'addTask'], $auth);
$r->post('/projects/{pid}/tasks/{id}', [ProjectController::class, 'updateTask'], $auth);
$r->post('/projects/{pid}/evidence', [ProjectController::class, 'addEvidence'], $auth);
$r->get('/projects/{pid}/evidence/{eid}', [ProjectController::class, 'downloadEvidence'], $auth);
$r->post('/projects/{pid}/feedback', [ProjectController::class, 'addFeedback'], $auth);
$r->post('/projects/{pid}/messages', [ProjectController::class, 'postMessage'], $auth);
$r->post('/projects/{pid}/assess', [ProjectController::class, 'assess'], $auth);
$r->post('/projects/{pid}/validate', [ProjectController::class, 'validateProject'], $auth);
$r->post('/projects/{pid}/staff', [ProjectController::class, 'assignStaff'], $auth);

// ---- Lightweight JSON API (tiny payloads for slow links) ---------------------
$r->get('/api/projects/{pid}/pulse', [ApiController::class, 'pulse'], $auth);
$r->get('/api/projects/{pid}/messages', [ApiController::class, 'messages'], $auth);
$r->get('/api/progress/{slug}', [ApiController::class, 'progress'], $auth);
