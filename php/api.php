<?php
session_start();
require_once __DIR__ . '/Database.php';

$db = new Database();
$response = ['success' => false, 'message' => '', 'data' => null];

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception('Все поля обязательны');
            }
            
            $existingUser = $db->getUserByUsername($username);
            if (!empty($existingUser)) {
                throw new Exception('Пользователь с таким именем уже существует');
            }
            
            $userId = $db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'student'
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Регистрация успешна';
            $response['data'] = ['user_id' => $userId];
            break;
            
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                throw new Exception('Введите логин и пароль');
            }
            
            $users = $db->getUserByUsername($username);
            if (empty($users)) {
                throw new Exception('Пользователь не найден');
            }
            
            $user = $users[0];
            if (!password_verify($password, $user['password'])) {
                throw new Exception('Неверный пароль');
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            $response['success'] = true;
            $response['message'] = 'Вход выполнен';
            $response['data'] = [
                'user_id' => $user['id'],
                'role' => $user['role']
            ];
            break;
            
        case 'logout':
            session_destroy();
            $response['success'] = true;
            $response['message'] = 'Выход выполнен';
            break;
            
        case 'create_teacher':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Доступ запрещен');
            }
            
            $userId = intval($_POST['user_id'] ?? 0);
            $fullName = trim($_POST['full_name'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');
            
            if (empty($fullName)) {
                throw new Exception('Укажите ФИО преподавателя');
            }
            
            $db->insert('teachers', [
                'user_id' => $userId,
                'full_name' => $fullName,
                'specialization' => $specialization
            ]);
            
            $db->update('users', $userId, ['role' => 'teacher']);
            
            $response['success'] = true;
            $response['message'] = 'Преподаватель создан';
            break;
            
        case 'create_course':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($title)) {
                throw new Exception('Укажите название курса');
            }
            
            $teachers = $db->findBy('teachers', 'user_id', $_SESSION['user_id']);
            if (empty($teachers)) {
                throw new Exception('Преподаватель не найден');
            }
            
            $teacherId = $teachers[0]['id'];
            
            $courseId = $db->insert('courses', [
                'teacher_id' => $teacherId,
                'title' => $title,
                'description' => $description
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Курс создан';
            $response['data'] = ['course_id' => $courseId];
            break;
            
        case 'create_lesson':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $courseId = intval($_POST['course_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $type = $_POST['type'] ?? 'text';
            $attachment = trim($_POST['attachment'] ?? '');
            
            if (empty($title) || empty($courseId)) {
                throw new Exception('Укажите название урока и курс');
            }
            
            $lessons = $db->getLessonsByCourse($courseId);
            $order = count($lessons) + 1;
            
            $lessonId = $db->insert('lessons', [
                'course_id' => $courseId,
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'attachment' => $attachment,
                'order' => $order
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Урок создан';
            $response['data'] = ['lesson_id' => $lessonId];
            break;
            
        case 'enroll_student':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $courseId = intval($_POST['course_id'] ?? 0);
            $studentId = intval($_POST['student_id'] ?? 0);
            
            if (empty($courseId) || empty($studentId)) {
                throw new Exception('Укажите курс и студента');
            }
            
            $db->insert('enrollments', [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'progress' => 0
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Студент зачислен на курс';
            break;
            
        case 'create_assignment':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $lessonId = intval($_POST['lesson_id'] ?? 0);
            $type = $_POST['type'] ?? 'practice';
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $dueDate = $_POST['due_date'] ?? null;
            
            if (empty($lessonId) || empty($title)) {
                throw new Exception('Укажите урок и название задания');
            }
            
            $assignmentId = $db->insert('assignments', [
                'lesson_id' => $lessonId,
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'due_date' => $dueDate
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Задание создано';
            $response['data'] = ['assignment_id' => $assignmentId];
            break;
            
        case 'submit_assignment':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
                throw new Exception('Доступ запрещен');
            }
            
            $assignmentId = intval($_POST['assignment_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            
            if (empty($assignmentId) || empty($content)) {
                throw new Exception('Укажите задание и решение');
            }
            
            $submissionId = $db->insert('submissions', [
                'assignment_id' => $assignmentId,
                'student_id' => $_SESSION['user_id'],
                'content' => $content,
                'grade' => null,
                'feedback' => null
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Задание отправлено';
            $response['data'] = ['submission_id' => $submissionId];
            break;
            
        case 'grade_submission':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $submissionId = intval($_POST['submission_id'] ?? 0);
            $grade = intval($_POST['grade'] ?? 0);
            $feedback = trim($_POST['feedback'] ?? '');
            
            $db->update('submissions', $submissionId, [
                'grade' => $grade,
                'feedback' => $feedback
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Оценка выставлена';
            break;
            
        case 'create_test':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                throw new Exception('Доступ запрещен');
            }
            
            $lessonId = intval($_POST['lesson_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $questions = json_decode($_POST['questions'] ?? '[]', true);
            
            if (empty($lessonId) || empty($title)) {
                throw new Exception('Укажите урок и название теста');
            }
            
            $testId = $db->insert('tests', [
                'lesson_id' => $lessonId,
                'title' => $title,
                'questions' => $questions
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Тест создан';
            $response['data'] = ['test_id' => $testId];
            break;
            
        case 'submit_test':
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
                throw new Exception('Доступ запрещен');
            }
            
            $testId = intval($_POST['test_id'] ?? 0);
            $answers = json_decode($_POST['answers'] ?? '[]', true);
            
            $test = $db->getById('tests', $testId);
            if (!$test) {
                throw new Exception('Тест не найден');
            }
            
            $score = 0;
            $totalQuestions = count($test['questions']);
            $correctAnswers = 0;
            
            foreach ($test['questions'] as $index => $question) {
                if (isset($answers[$index]) && $answers[$index] == $question['correct']) {
                    $correctAnswers++;
                }
            }
            
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            
            $resultId = $db->insert('test_results', [
                'test_id' => $testId,
                'student_id' => $_SESSION['user_id'],
                'answers' => $answers,
                'score' => $score
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Тест пройден';
            $response['data'] = ['score' => $score, 'result_id' => $resultId];
            break;
            
        case 'get_user_data':
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Пользователь не авторизован');
            }
            
            $user = $db->getById('users', $_SESSION['user_id']);
            $teacherData = null;
            
            if ($user['role'] === 'teacher') {
                $teachers = $db->findBy('teachers', 'user_id', $_SESSION['user_id']);
                if (!empty($teachers)) {
                    $teacherData = $teachers[0];
                }
            }
            
            $response['success'] = true;
            $response['data'] = [
                'user' => $user,
                'teacher' => $teacherData
            ];
            break;
            
        case 'get_courses':
            $courses = $db->getAll('courses');
            $response['success'] = true;
            $response['data'] = ['courses' => $courses];
            break;
            
        case 'get_course_details':
            $courseId = intval($_GET['course_id'] ?? 0);
            $course = $db->getById('courses', $courseId);
            if (!$course) {
                throw new Exception('Курс не найден');
            }
            
            $lessons = $db->getLessonsByCourse($courseId);
            $students = $db->getStudentsByCourse($courseId);
            
            $response['success'] = true;
            $response['data'] = [
                'course' => $course,
                'lessons' => $lessons,
                'students' => $students
            ];
            break;
            
        case 'get_crm_data':
            $courseId = intval($_GET['course_id'] ?? 0);
            $students = $db->getStudentsByCourse($courseId);
            
            $crmData = [];
            foreach ($students as $student) {
                $studentData = [
                    'id' => $student['id'],
                    'username' => $student['username'],
                    'email' => $student['email'],
                    'grades' => [],
                    'test_scores' => []
                ];
                
                $submissions = $db->findBy('submissions', 'student_id', $student['id']);
                foreach ($submissions as $submission) {
                    if ($submission['grade'] !== null) {
                        $studentData['grades'][] = $submission['grade'];
                    }
                }
                
                $testResults = $db->findBy('test_results', 'student_id', $student['id']);
                foreach ($testResults as $result) {
                    $studentData['test_scores'][] = $result['score'];
                }
                
                $crmData[] = $studentData;
            }
            
            $response['success'] = true;
            $response['data'] = ['crm' => $crmData];
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
