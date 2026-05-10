<?php
session_start();
require_once __DIR__ . '/Database.php';

header('Content-Type: application/json');

$db = new Database();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'student'; // По умолчанию студент

            if (empty($login) || empty($password) || empty($name)) {
                throw new Exception('Заполните все обязательные поля');
            }

            if ($db->getUserByLogin($login)) {
                throw new Exception('Пользователь с таким логином уже существует');
            }

            $user = $db->createUser($login, $password, $role, $name, $email);
            $_SESSION['user'] = $user;
            echo json_encode(['success' => true, 'user' => $user, 'redirect' => getRedirectPage($role)]);
            break;

        case 'login':
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($login) || empty($password)) {
                throw new Exception('Введите логин и пароль');
            }

            $user = $db->getUserByLogin($login);
            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception('Неверный логин или пароль');
            }

            $_SESSION['user'] = $user;
            echo json_encode(['success' => true, 'user' => $user, 'redirect' => getRedirectPage($user['role'])]);
            break;

        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;

        case 'check_auth':
            if (isset($_SESSION['user'])) {
                echo json_encode(['authenticated' => true, 'user' => $_SESSION['user']]);
            } else {
                echo json_encode(['authenticated' => false]);
            }
            break;

        case 'create_course':
            checkAuth(['admin', 'teacher']);
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($title)) {
                throw new Exception('Введите название курса');
            }

            $course = $db->createCourse($title, $description, $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'course' => $course]);
            break;

        case 'get_courses':
            checkAuth();
            $role = $_SESSION['user']['role'];
            if ($role === 'student') {
                $courses = $db->getCoursesByStudent($_SESSION['user']['id']);
            } elseif ($role === 'teacher' || $role === 'admin') {
                $courses = $db->getAllCourses();
            } else {
                $courses = [];
            }
            echo json_encode(['success' => true, 'courses' => $courses]);
            break;

        case 'create_lesson':
            checkAuth(['admin', 'teacher']);
            $courseId = $_POST['courseId'] ?? '';
            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'text';
            $content = $_POST['content'] ?? '';

            if (empty($courseId) || empty($title)) {
                throw new Exception('Заполните обязательные поля');
            }

            $lesson = $db->createLesson($courseId, $title, $type, $content);
            echo json_encode(['success' => true, 'lesson' => $lesson]);
            break;

        case 'get_lessons':
            checkAuth();
            $courseId = $_GET['courseId'] ?? '';
            $lessons = $db->getLessonsByCourse($courseId);
            echo json_encode(['success' => true, 'lessons' => $lessons]);
            break;

        case 'enroll_student':
            checkAuth(['admin', 'teacher']);
            $studentId = $_POST['studentId'] ?? '';
            $courseId = $_POST['courseId'] ?? '';

            if (empty($studentId) || empty($courseId)) {
                throw new Exception('Выберите студента и курс');
            }

            $result = $db->enrollStudent($studentId, $courseId);
            if (!$result) {
                throw new Exception('Студент уже зачислен на этот курс');
            }
            echo json_encode(['success' => true]);
            break;

        case 'get_students':
            checkAuth(['admin', 'teacher']);
            $courseId = $_GET['courseId'] ?? '';
            $students = $db->getStudentsByCourse($courseId);
            echo json_encode(['success' => true, 'students' => $students]);
            break;

        case 'submit_assignment':
            checkAuth(['student']);
            $lessonId = $_POST['lessonId'] ?? '';
            $fileContent = $_POST['fileContent'] ?? '';
            $fileName = $_POST['fileName'] ?? 'файл.txt';

            if (empty($lessonId)) {
                throw new Exception('Ошибка урока');
            }

            $submission = $db->submitAssignment($lessonId, $_SESSION['user']['id'], $fileContent, $fileName);
            echo json_encode(['success' => true, 'submission' => $submission]);
            break;

        case 'get_submissions':
            checkAuth(['admin', 'teacher']);
            $lessonId = $_GET['lessonId'] ?? '';
            $submissions = $db->getSubmissionsByLesson($lessonId);
            
            // Добавляем информацию о студентах
            $allUsers = $db->getAllUsers();
            foreach ($submissions as &$sub) {
                foreach ($allUsers as $u) {
                    if ($u['id'] == $sub['studentId']) {
                        $sub['studentName'] = $u['name'];
                        break;
                    }
                }
            }
            
            echo json_encode(['success' => true, 'submissions' => $submissions]);
            break;

        case 'grade_submission':
            checkAuth(['admin', 'teacher']);
            $submissionId = $_POST['submissionId'] ?? '';
            $grade = $_POST['grade'] ?? '';
            $feedback = $_POST['feedback'] ?? '';

            if (empty($submissionId) || $grade === '') {
                throw new Exception('Заполните оценку');
            }

            $db->gradeSubmission($submissionId, $grade, $feedback);
            echo json_encode(['success' => true]);
            break;

        case 'create_test':
            checkAuth(['admin', 'teacher']);
            $lessonId = $_POST['lessonId'] ?? '';
            $questions = json_decode($_POST['questions'] ?? '[]', true);

            if (empty($lessonId) || empty($questions)) {
                throw new Exception('Заполните вопросы теста');
            }

            $test = $db->createTest($lessonId, $questions);
            echo json_encode(['success' => true, 'test' => $test]);
            break;

        case 'get_test':
            checkAuth();
            $lessonId = $_GET['lessonId'] ?? '';
            $test = $db->getTestByLesson($lessonId);
            echo json_encode(['success' => true, 'test' => $test]);
            break;

        case 'submit_test':
            checkAuth(['student']);
            $testId = $_POST['testId'] ?? '';
            $answers = json_decode($_POST['answers'] ?? '{}', true);

            if (empty($testId)) {
                throw new Exception('Ошибка теста');
            }

            $result = $db->submitTestAnswer($testId, $_SESSION['user']['id'], $answers);
            echo json_encode(['success' => true, 'result' => $result]);
            break;

        case 'get_test_result':
            checkAuth();
            $testId = $_GET['testId'] ?? '';
            $answer = $db->getTestAnswersByStudent($testId, $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'answer' => $answer]);
            break;

        case 'get_all_users':
            checkAuth(['admin', 'teacher']);
            $role = $_GET['role'] ?? '';
            if ($role) {
                $users = $db->getUsersByRole($role);
            } else {
                $users = $db->getAllUsers();
            }
            echo json_encode(['success' => true, 'users' => array_values($users)]);
            break;

        case 'create_teacher':
            checkAuth(['admin']);
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';

            if (empty($login) || empty($password) || empty($name)) {
                throw new Exception('Заполните все поля');
            }

            if ($db->getUserByLogin($login)) {
                throw new Exception('Пользователь с таким логином уже существует');
            }

            $user = $db->createUser($login, $password, 'teacher', $name, $email);
            echo json_encode(['success' => true, 'user' => $user]);
            break;

        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function checkAuth($allowedRoles = null) {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Требуется авторизация');
    }
    
    if ($allowedRoles && !in_array($_SESSION['user']['role'], $allowedRoles)) {
        throw new Exception('Недостаточно прав');
    }
}

function getRedirectPage($role) {
    switch ($role) {
        case 'admin': return '/html/admin.html';
        case 'teacher': return '/html/teacher.html';
        case 'student': return '/html/student.html';
        default: return '/html/index.html';
    }
}
?>
