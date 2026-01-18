<?php
// File: classes/CourseProject.php
// Course and Project Management Classes

class Course {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    public function addCourse($user_id, $data) {
        $sql = "INSERT INTO courses (user_id, course_name, institution, completion_status, 
                start_date, end_date, grade, sector) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([
            $user_id,
            $data['course_name'],
            $data['institution'] ?? '',
            $data['completion_status'] ?? 'in_progress',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['grade'] ?? '',
            $data['sector'] ?? ''
        ])) {
            return ['success' => true, 'course_id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Failed to add course'];
    }
    
    public function getUserCourses($user_id) {
        $sql = "SELECT * FROM courses WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function updateCourse($course_id, $user_id, $data) {
        $fields = [];
        $params = [];
        
        $allowed = ['course_name', 'institution', 'completion_status', 'start_date', 'end_date', 'grade', 'sector'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) return ['success' => false];
        
        $params[] = $course_id;
        $params[] = $user_id;
        $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE course_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return ['success' => $stmt->execute($params)];
    }
    
    public function deleteCourse($course_id, $user_id) {
        $sql = "DELETE FROM courses WHERE course_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return ['success' => $stmt->execute([$course_id, $user_id])];
    }
}

class Project {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    public function addProject($user_id, $data) {
        $sql = "INSERT INTO projects (user_id, project_name, description, technologies_used, 
                project_url, start_date, end_date, sector) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([
            $user_id,
            $data['project_name'],
            $data['description'] ?? '',
            $data['technologies_used'] ?? '',
            $data['project_url'] ?? '',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['sector'] ?? ''
        ])) {
            return ['success' => true, 'project_id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Failed to add project'];
    }
    
    public function getUserProjects($user_id) {
        $sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function updateProject($project_id, $user_id, $data) {
        $fields = [];
        $params = [];
        
        $allowed = ['project_name', 'description', 'technologies_used', 'project_url', 'start_date', 'end_date', 'sector'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) return ['success' => false];
        
        $params[] = $project_id;
        $params[] = $user_id;
        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE project_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return ['success' => $stmt->execute($params)];
    }
    
    public function deleteProject($project_id, $user_id) {
        $sql = "DELETE FROM projects WHERE project_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return ['success' => $stmt->execute([$project_id, $user_id])];
    }
}

class Achievement {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    public function addAchievement($user_id, $data) {
        $sql = "INSERT INTO achievements (user_id, title, description, issuer, issue_date, achievement_type) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([
            $user_id,
            $data['title'],
            $data['description'] ?? '',
            $data['issuer'] ?? '',
            $data['issue_date'] ?? null,
            $data['achievement_type'] ?? 'certification'
        ])) {
            return ['success' => true, 'achievement_id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false];
    }
    
    public function getUserAchievements($user_id) {
        $sql = "SELECT * FROM achievements WHERE user_id = ? ORDER BY issue_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function deleteAchievement($achievement_id, $user_id) {
        $sql = "DELETE FROM achievements WHERE achievement_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return ['success' => $stmt->execute([$achievement_id, $user_id])];
    }
}
?>