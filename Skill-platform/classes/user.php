<?php
// File: classes/User.php
// User Model Class

class User {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Register new user
    public function register($email, $password, $full_name, $career_goal = '', $sector = 'healthcare') {
        $sql = "INSERT INTO users (email, password_hash, full_name, career_goal, sector_focus) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt->execute([$email, $password_hash, $full_name, $career_goal, $sector]);
            return [
                'success' => true,
                'user_id' => $this->conn->lastInsertId(),
                'message' => 'User registered successfully'
            ];
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    // Login user
    public function login($email, $password) {
        $sql = "SELECT user_id, email, password_hash, full_name, career_goal, sector_focus, profile_image, is_admin 
                FROM users WHERE email = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful'
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    // Update last login
    public function updateLastLogin($user_id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id]);
    }
    
    // Get user profile
    public function getProfile($user_id) {
        $sql = "SELECT user_id, email, full_name, profile_image, career_goal, sector_focus, created_at 
                FROM users WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    // Update user profile
    public function updateProfile($user_id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['full_name', 'career_goal', 'sector_focus', 'profile_image'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute($params)) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Update failed'];
    }
    
    // Get user statistics
    public function getUserStats($user_id) {
        $stats = [];
        
        // Count skills
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM user_skills WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_skills'] = $stmt->fetch()['count'];
        
        // Count courses
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM courses WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_courses'] = $stmt->fetch()['count'];
        
        // Count projects
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_projects'] = $stmt->fetch()['count'];
        
        // Count achievements
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM achievements WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_achievements'] = $stmt->fetch()['count'];
        
        // Count skill gaps
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM skill_gaps WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['skill_gaps'] = $stmt->fetch()['count'];
        
        return $stats;
    }
}
?>