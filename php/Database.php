<?php
class Database {
    private $dbFile;
    private $data;

    public function __construct() {
        $this->dbFile = __DIR__ . '/../db/database.json';
        $this->load();
    }

    private function load() {
        if (file_exists($this->dbFile)) {
            $this->data = json_decode(file_get_contents($this->dbFile), true);
        } else {
            $this->data = [
                'users' => [],
                'teachers' => [],
                'courses' => [],
                'lessons' => [],
                'enrollments' => [],
                'assignments' => [],
                'submissions' => [],
                'tests' => [],
                'test_results' => []
            ];
        }
    }

    private function save() {
        file_put_contents($this->dbFile, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getAll($collection) {
        return isset($this->data[$collection]) ? $this->data[$collection] : [];
    }

    public function getById($collection, $id) {
        if (!isset($this->data[$collection])) return null;
        foreach ($this->data[$collection] as $item) {
            if ($item['id'] == $id) return $item;
        }
        return null;
    }

    public function insert($collection, $data) {
        if (!isset($this->data[$collection])) {
            $this->data[$collection] = [];
        }
        $maxId = 0;
        foreach ($this->data[$collection] as $item) {
            if ($item['id'] > $maxId) $maxId = $item['id'];
        }
        $data['id'] = $maxId + 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->data[$collection][] = $data;
        $this->save();
        return $data['id'];
    }

    public function update($collection, $id, $data) {
        if (!isset($this->data[$collection])) return false;
        foreach ($this->data[$collection] as &$item) {
            if ($item['id'] == $id) {
                $item = array_merge($item, $data);
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function delete($collection, $id) {
        if (!isset($this->data[$collection])) return false;
        foreach ($this->data[$collection] as $key => $item) {
            if ($item['id'] == $id) {
                unset($this->data[$collection][$key]);
                $this->data[$collection] = array_values($this->data[$collection]);
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function findBy($collection, $field, $value) {
        if (!isset($this->data[$collection])) return [];
        $result = [];
        foreach ($this->data[$collection] as $item) {
            if (isset($item[$field]) && $item[$field] == $value) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function getUserByUsername($username) {
        return $this->findBy('users', 'username', $username);
    }

    public function getUserByEmail($email) {
        return $this->findBy('users', 'email', $email);
    }

    public function getCoursesByTeacher($teacherId) {
        return $this->findBy('courses', 'teacher_id', $teacherId);
    }

    public function getLessonsByCourse($courseId) {
        return $this->findBy('lessons', 'course_id', $courseId);
    }

    public function getStudentsByCourse($courseId) {
        $enrollments = $this->findBy('enrollments', 'course_id', $courseId);
        $students = [];
        foreach ($enrollments as $enrollment) {
            $user = $this->getById('users', $enrollment['student_id']);
            if ($user) $students[] = $user;
        }
        return $students;
    }

    public function getSubmissionsByAssignment($assignmentId) {
        return $this->findBy('submissions', 'assignment_id', $assignmentId);
    }

    public function getTestResultsByTest($testId) {
        return $this->findBy('test_results', 'test_id', $testId);
    }
}
?>
