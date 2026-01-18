<?php
// File: classes/Skill.php
// Skill Management Class

class Skill {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Add user skill
    public function addSkill($user_id, $skill_name, $proficiency_level, $category = '') {
        $sql = "INSERT INTO user_skills (user_id, skill_name, proficiency_level, category) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([$user_id, $skill_name, $proficiency_level, $category])) {
            return [
                'success' => true,
                'skill_id' => $this->conn->lastInsertId(),
                'message' => 'Skill added successfully'
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add skill'];
    }
    
    // Get user skills
    public function getUserSkills($user_id) {
        $sql = "SELECT * FROM user_skills WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Update skill
    public function updateSkill($skill_id, $user_id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['skill_name', 'proficiency_level', 'category', 'verified'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields'];
        }
        
        $params[] = $skill_id;
        $params[] = $user_id;
        $sql = "UPDATE user_skills SET " . implode(', ', $fields) . 
               " WHERE skill_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute($params)) {
            return ['success' => true, 'message' => 'Skill updated'];
        }
        
        return ['success' => false, 'message' => 'Update failed'];
    }
    
    // Delete skill
    public function deleteSkill($skill_id, $user_id) {
        $sql = "DELETE FROM user_skills WHERE skill_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([$skill_id, $user_id])) {
            return ['success' => true, 'message' => 'Skill deleted'];
        }
        
        return ['success' => false, 'message' => 'Delete failed'];
    }
    
    // Get skill framework by sector
    public function getSkillFramework($sector) {
        $sql = "SELECT * FROM skill_framework WHERE sector = ? ORDER BY importance_level, skill_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$sector]);
        return $stmt->fetchAll();
    }
    
    // Analyze skill gaps
    public function analyzeSkillGaps($user_id) {
        // Get user sector
        $user_stmt = $this->conn->prepare("SELECT sector_focus FROM users WHERE user_id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $sector = $user['sector_focus'];
        
        // Get required skills for sector
        $required_stmt = $this->conn->prepare(
            "SELECT skill_name, importance_level FROM skill_framework WHERE sector = ?"
        );
        $required_stmt->execute([$sector]);
        $required_skills = $required_stmt->fetchAll();
        
        // Get user's current skills
        $user_stmt = $this->conn->prepare(
            "SELECT skill_name, proficiency_level FROM user_skills WHERE user_id = ?"
        );
        $user_stmt->execute([$user_id]);
        $user_skills = $user_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Clear existing gaps
        $delete_stmt = $this->conn->prepare("DELETE FROM skill_gaps WHERE user_id = ?");
        $delete_stmt->execute([$user_id]);
        
        // Identify gaps
        $gaps = [];
        $insert_stmt = $this->conn->prepare(
            "INSERT INTO skill_gaps (user_id, skill_name, current_level, required_level, gap_severity, sector) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($required_skills as $required) {
            $skill_name = $required['skill_name'];
            $importance = $required['importance_level'];
            
            $current_level = isset($user_skills[$skill_name]) ? $user_skills[$skill_name] : 'none';
            $required_level = ($importance === 'essential') ? 'advanced' : 'intermediate';
            
            // Determine gap severity
            $levels = ['none' => 0, 'beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
            $gap = $levels[$required_level] - $levels[$current_level];
            
            if ($gap > 0) {
                $severity = 'low';
                if ($importance === 'essential' && $gap >= 3) $severity = 'critical';
                elseif ($importance === 'essential' && $gap >= 2) $severity = 'high';
                elseif ($gap >= 2) $severity = 'medium';
                
                $insert_stmt->execute([
                    $user_id, $skill_name, $current_level, $required_level, $severity, $sector
                ]);
                
                $gaps[] = [
                    'skill_name' => $skill_name,
                    'current_level' => $current_level,
                    'required_level' => $required_level,
                    'gap_severity' => $severity
                ];
            }
        }
        
        return [
            'success' => true,
            'gaps' => $gaps,
            'total_gaps' => count($gaps)
        ];
    }
    
    // Get skill gaps
    public function getSkillGaps($user_id) {
        $sql = "SELECT * FROM skill_gaps WHERE user_id = ? ORDER BY 
                FIELD(gap_severity, 'critical', 'high', 'medium', 'low')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}
?>