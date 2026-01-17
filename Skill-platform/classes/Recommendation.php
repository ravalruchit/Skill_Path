<?php
// File: classes/Recommendation.php
// AI-Powered Recommendation Engine Class

class Recommendation {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Generate personalized recommendations
    public function generateRecommendations($user_id) {
        // Get user profile and gaps
        $user = $this->getUserProfile($user_id);
        $gaps = $this->getSkillGaps($user_id);
        $user_skills = $this->getUserSkills($user_id);
        
        // Clear old recommendations
        $this->clearOldRecommendations($user_id);
        
        // Generate course recommendations based on skill gaps
        $this->generateCourseRecommendations($user_id, $user, $gaps);
        
        // Generate project recommendations based on current skills
        $this->generateProjectRecommendations($user_id, $user, $user_skills);
        
        // Generate skill recommendations for career advancement
        $this->generateSkillRecommendations($user_id, $user);
        
        return $this->getRecommendations($user_id);
    }
    
    private function getUserProfile($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    private function getSkillGaps($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM skill_gaps WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    private function getUserSkills($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM user_skills WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    private function clearOldRecommendations($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM recommendations WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    
    private function generateCourseRecommendations($user_id, $user, $gaps) {
        $sector = $user['sector_focus'];
        
        // Course database with sector-specific recommendations
        $course_db = [
            'healthcare' => [
                ['name' => 'Health Informatics Fundamentals', 'skills' => ['Health Informatics'], 'platform' => 'Coursera'],
                ['name' => 'HIPAA Compliance Training', 'skills' => ['HIPAA Compliance'], 'platform' => 'Udemy'],
                ['name' => 'Medical Database Systems', 'skills' => ['Medical Database Management'], 'platform' => 'edX'],
                ['name' => 'Telemedicine Platform Development', 'skills' => ['Telemedicine Systems'], 'platform' => 'Pluralsight'],
                ['name' => 'Healthcare Data Analytics', 'skills' => ['Health Informatics', 'Medical Database Management'], 'platform' => 'Coursera']
            ],
            'agriculture' => [
                ['name' => 'Precision Agriculture Technologies', 'skills' => ['Precision Farming'], 'platform' => 'Coursera'],
                ['name' => 'Agricultural IoT Systems', 'skills' => ['Agricultural IoT'], 'platform' => 'edX'],
                ['name' => 'Farm Data Analytics', 'skills' => ['Crop Analytics'], 'platform' => 'Udacity'],
                ['name' => 'Supply Chain Management for Agriculture', 'skills' => ['Supply Chain Management'], 'platform' => 'LinkedIn Learning'],
                ['name' => 'Smart Farming Solutions', 'skills' => ['Precision Farming', 'Agricultural IoT'], 'platform' => 'FutureLearn']
            ],
            'urban_planning' => [
                ['name' => 'Smart City Systems Design', 'skills' => ['Smart City Systems'], 'platform' => 'edX'],
                ['name' => 'Urban Data Analytics', 'skills' => ['Urban Data Analytics'], 'platform' => 'Coursera'],
                ['name' => 'IoT for Smart Cities', 'skills' => ['IoT for Cities'], 'platform' => 'Udacity'],
                ['name' => 'Sustainable Urban Planning', 'skills' => ['Sustainability Planning'], 'platform' => 'FutureLearn'],
                ['name' => 'GIS and Urban Mapping', 'skills' => ['Urban Data Analytics', 'Smart City Systems'], 'platform' => 'Esri']
            ]
        ];
        
        $courses = $course_db[$sector] ?? [];
        
        foreach ($gaps as $gap) {
            foreach ($courses as $course) {
                if (in_array($gap['skill_name'], $course['skills'])) {
                    $relevance = $this->calculateRelevance($gap['gap_severity']);
                    
                    $stmt = $this->conn->prepare(
                        "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
                         VALUES (?, 'course', ?, ?, ?, ?)"
                    );
                    
                    $description = "Learn " . implode(', ', $course['skills']) . " to fill your skill gaps";
                    
                    $stmt->execute([
                        $user_id,
                        $course['name'],
                        $description,
                        $relevance,
                        $course['platform']
                    ]);
                }
            }
        }
    }
    
    private function generateProjectRecommendations($user_id, $user, $skills) {
        $sector = $user['sector_focus'];
        
        $project_db = [
            'healthcare' => [
                'Patient Management System' => ['Health Informatics', 'Medical Database Management'],
                'Telemedicine Platform' => ['Telemedicine Systems', 'Health Informatics'],
                'Health Analytics Dashboard' => ['Health Informatics', 'Medical Database Management'],
                'HIPAA-Compliant Data System' => ['HIPAA Compliance', 'Medical Database Management']
            ],
            'agriculture' => [
                'Smart Farm Monitoring System' => ['Agricultural IoT', 'Precision Farming'],
                'Crop Yield Prediction Tool' => ['Crop Analytics', 'Precision Farming'],
                'Agricultural Supply Chain Tracker' => ['Supply Chain Management'],
                'IoT Sensor Network for Farms' => ['Agricultural IoT']
            ],
            'urban_planning' => [
                'Smart City Dashboard' => ['Smart City Systems', 'Urban Data Analytics'],
                'Urban IoT Platform' => ['IoT for Cities', 'Smart City Systems'],
                'Sustainability Monitoring System' => ['Sustainability Planning', 'Urban Data Analytics'],
                'Traffic Management System' => ['Smart City Systems', 'IoT for Cities']
            ]
        ];
        
        $projects = $project_db[$sector] ?? [];
        
        foreach ($projects as $project_name => $required_skills) {
            $match_count = 0;
            foreach ($skills as $user_skill) {
                if (in_array($user_skill['skill_name'], $required_skills)) {
                    $match_count++;
                }
            }
            
            if ($match_count > 0) {
                $relevance = ($match_count / count($required_skills)) * 100;
                
                $stmt = $this->conn->prepare(
                    "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
                     VALUES (?, 'project', ?, ?, ?, ?)"
                );
                
                $description = "Build this project using: " . implode(', ', $required_skills);
                
                $stmt->execute([
                    $user_id,
                    $project_name,
                    $description,
                    $relevance,
                    'Platform Generated'
                ]);
            }
        }
    }
    
    private function generateSkillRecommendations($user_id, $user) {
        $sector = $user['sector_focus'];
        
        $stmt = $this->conn->prepare(
            "SELECT * FROM career_pathways WHERE sector = ? ORDER BY level"
        );
        $stmt->execute([$sector]);
        $pathways = $stmt->fetchAll();
        
        foreach ($pathways as $pathway) {
            $required_skills = explode(', ', $pathway['required_skills']);
            
            foreach ($required_skills as $skill) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
                     VALUES (?, 'skill', ?, ?, ?, ?)"
                );
                
                $description = "Required for " . $pathway['role_name'] . " (" . $pathway['level'] . " level)";
                
                $stmt->execute([
                    $user_id,
                    $skill,
                    $description,
                    75.0,
                    'Career Pathway'
                ]);
            }
        }
    }
    
    private function calculateRelevance($severity) {
        $scores = [
            'critical' => 95.0,
            'high' => 85.0,
            'medium' => 70.0,
            'low' => 55.0
        ];
        return $scores[$severity] ?? 60.0;
    }
    
    public function getRecommendations($user_id, $type = null) {
        if ($type) {
            $stmt = $this->conn->prepare(
                "SELECT * FROM recommendations WHERE user_id = ? AND recommendation_type = ? 
                 ORDER BY relevance_score DESC LIMIT 10"
            );
            $stmt->execute([$user_id, $type]);
        } else {
            $stmt = $this->conn->prepare(
                "SELECT * FROM recommendations WHERE user_id = ? 
                 ORDER BY relevance_score DESC LIMIT 20"
            );
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    }
}
?>