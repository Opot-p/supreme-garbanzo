<?php
class Database {
    private $dbFile;

    public function __construct() {
        $this->dbFile = __DIR__ . '/../db/database.json';
        if (!file_exists($this->dbFile)) {
            $this->initDB();
        }
    }

    private function initDB() {
        $initialData = [
            'users' => [
                [
                    'id' => 1,
                    'login' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'name' => 'Администратор',
                    'email' => 'admin@example.com'
                ]
            ],
            'courses' => [],
            'lessons' => [],
            'enrollments' => [],
            'submissions' => [],
            'tests' => [],
            'test_answers' => []
        ];
        file_put_contents($this->dbFile, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getData() {
        return json_decode(file_get_contents($this->dbFile), true);
    }

    public function saveData($data) {
        file_put_contents($this->dbFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getUserByLogin($login) {
        $data = $this->getData();
        foreach ($data['users'] as $user) {
            if ($user['login'] === $login) {
                return $user;
            }
        }
        return null;
    }

    public function createUser($login, $password, $role, $name, $email) {
        $data = $this->getData();
        $newId = count($data['users']) > 0 ? max(array_column($data['users'], 'id')) + 1 : 1;
        $newUser = [
            'id' => $newId,
            'login' => $login,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'name' => $name,
            'email' => $email
        ];
        $data['users'][] = $newUser;
        $this->saveData($data);
        return $newUser;
    }

    public function createCourse($title, $description, $teacherId) {
        $data = $this->getData();
        $newId = count($data['courses']) > 0 ? max(array_column($data['courses'], 'id')) + 1 : 1;
        $course = [
            'id' => $newId,
            'title' => $title,
            'description' => $description,
            'teacherId' => $teacherId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $data['courses'][] = $course;
        $this->saveData($data);
        return $course;
    }

    public function getCoursesByTeacher($teacherId) {
        $data = $this->getData();
        return array_filter($data['courses'], fn($c) => $c['teacherId'] == $teacherId);
    }

    public function getAllCourses() {
        $data = $this->getData();
        return $data['courses'];
    }

    public function getCourseById($id) {
        $data = $this->getData();
        foreach ($data['courses'] as $course) {
            if ($course['id'] == $id) return $course;
        }
        return null;
    }

    public function createLesson($courseId, $title, $type, $content) {
        $data = $this->getData();
        $newId = count($data['lessons']) > 0 ? max(array_column($data['lessons'], 'id')) + 1 : 1;
        $lesson = [
            'id' => $newId,
            'courseId' => $courseId,
            'title' => $title,
            'type' => $type, // video, text, test, practice
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $data['lessons'][] = $lesson;
        $this->saveData($data);
        return $lesson;
    }

    public function getLessonsByCourse($courseId) {
        $data = $this->getData();
        return array_filter($data['lessons'], fn($l) => $l['courseId'] == $courseId);
    }

    public function enrollStudent($studentId, $courseId) {
        $data = $this->getData();
        foreach ($data['enrollments'] as $e) {
            if ($e['studentId'] == $studentId && $e['courseId'] == $courseId) {
                return false;
            }
        }
        $newId = count($data['enrollments']) > 0 ? max(array_column($data['enrollments'], 'id')) + 1 : 1;
        $enrollment = [
            'id' => $newId,
            'studentId' => $studentId,
            'courseId' => $courseId,
            'enrolled_at' => date('Y-m-d H:i:s')
        ];
        $data['enrollments'][] = $enrollment;
        $this->saveData($data);
        return true;
    }

    public function getStudentsByCourse($courseId) {
        $data = $this->getData();
        $enrollments = array_filter($data['enrollments'], fn($e) => $e['courseId'] == $courseId);
        $students = [];
        foreach ($enrollments as $e) {
            foreach ($data['users'] as $u) {
                if ($u['id'] == $e['studentId'] && $u['role'] === 'student') {
                    $students[] = $u;
                }
            }
        }
        return $students;
    }

    public function getCoursesByStudent($studentId) {
        $data = $this->getData();
        $enrollments = array_filter($data['enrollments'], fn($e) => $e['studentId'] == $studentId);
        $courses = [];
        foreach ($enrollments as $e) {
            foreach ($data['courses'] as $c) {
                if ($c['id'] == $e['courseId']) {
                    $courses[] = $c;
                }
            }
        }
        return $courses;
    }

    public function submitAssignment($lessonId, $studentId, $fileContent, $fileName) {
        $data = $this->getData();
        $newId = count($data['submissions']) > 0 ? max(array_column($data['submissions'], 'id')) + 1 : 1;
        $submission = [
            'id' => $newId,
            'lessonId' => $lessonId,
            'studentId' => $studentId,
            'fileContent' => $fileContent,
            'fileName' => $fileName,
            'submitted_at' => date('Y-m-d H:i:s'),
            'grade' => null,
            'feedback' => null
        ];
        $data['submissions'][] = $submission;
        $this->saveData($data);
        return $submission;
    }

    public function getSubmissionsByLesson($lessonId) {
        $data = $this->getData();
        return array_filter($data['submissions'], fn($s) => $s['lessonId'] == $lessonId);
    }

    public function gradeSubmission($submissionId, $grade, $feedback) {
        $data = $this->getData();
        foreach ($data['submissions'] as &$s) {
            if ($s['id'] == $submissionId) {
                $s['grade'] = $grade;
                $s['feedback'] = $feedback;
                break;
            }
        }
        $this->saveData($data);
    }

    public function createTest($lessonId, $questions) {
        $data = $this->getData();
        $newId = count($data['tests']) > 0 ? max(array_column($data['tests'], 'id')) + 1 : 1;
        $test = [
            'id' => $newId,
            'lessonId' => $lessonId,
            'questions' => $questions
        ];
        $data['tests'][] = $test;
        $this->saveData($data);
        return $test;
    }

    public function getTestByLesson($lessonId) {
        $data = $this->getData();
        foreach ($data['tests'] as $test) {
            if ($test['lessonId'] == $lessonId) return $test;
        }
        return null;
    }

    public function submitTestAnswer($testId, $studentId, $answers) {
        $data = $this->getData();
        $newId = count($data['test_answers']) > 0 ? max(array_column($data['test_answers'], 'id')) + 1 : 1;
        
        $test = $this->getTestByLesson($this->getLessonByTestId($testId)['id']);
        $score = 0;
        $total = count($test['questions']);
        
        foreach ($test['questions'] as $index => $question) {
            if (isset($answers[$index]) && $answers[$index] == $question['correct']) {
                $score++;
            }
        }
        
        $finalGrade = round(($score / $total) * 5);
        
        $answer = [
            'id' => $newId,
            'testId' => $testId,
            'studentId' => $studentId,
            'answers' => $answers,
            'score' => $score,
            'total' => $total,
            'grade' => $finalGrade,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        $data['test_answers'][] = $answer;
        $this->saveData($data);
        return $answer;
    }

    private function getLessonByTestId($testId) {
        $data = $this->getData();
        foreach ($data['lessons'] as $lesson) {
            foreach ($data['tests'] as $test) {
                if ($test['id'] == $testId && $test['lessonId'] == $lesson['id']) {
                    return $lesson;
                }
            }
        }
        return null;
    }

    public function getTestAnswersByStudent($testId, $studentId) {
        $data = $this->getData();
        foreach ($data['test_answers'] as $a) {
            if ($a['testId'] == $testId && $a['studentId'] == $studentId) {
                return $a;
            }
        }
        return null;
    }

    public function getAllUsers() {
        $data = $this->getData();
        return $data['users'];
    }

    public function getUsersByRole($role) {
        $data = $this->getData();
        return array_filter($data['users'], fn($u) => $u['role'] === $role);
    }
}
?>
