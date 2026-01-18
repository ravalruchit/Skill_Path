<?php
// File: classes/AIRecommendation.php
// Advanced AI-Powered Recommendation Engine using Claude API

class AIRecommendation {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Generate AI-powered recommendations using Claude
    public function generateAIRecommendations($user_id) {
        // Get comprehensive user profile
        $userProfile = $this->buildUserProfile($user_id);
        
        // Clear old recommendations
        $this->clearOldRecommendations($user_id);
        
        // Use Claude API for intelligent recommendations
        $aiRecommendations = $this->getClaudeRecommendations($userProfile);
        
        // Store recommendations in database
        $this->storeRecommendations($user_id, $aiRecommendations);
        
        // Also generate rule-based recommendations as fallback
        $this->generateRuleBasedRecommendations($user_id, $userProfile);
        
        return $this->getRecommendations($user_id);
    }
    
    private function buildUserProfile($user_id) {
        $profile = [];
        
        // Get user basic info
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile['user'] = $stmt->fetch();
        
        // Get skills
        $stmt = $this->conn->prepare("SELECT * FROM user_skills WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile['skills'] = $stmt->fetchAll();
        
        // Get courses
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile['courses'] = $stmt->fetchAll();
        
        // Get projects
        $stmt = $this->conn->prepare("SELECT * FROM projects WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile['projects'] = $stmt->fetchAll();
        
        // Get skill gaps
        $stmt = $this->conn->prepare("SELECT * FROM skill_gaps WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile['gaps'] = $stmt->fetchAll();
        
        // Get career pathway info
        $stmt = $this->conn->prepare(
            "SELECT * FROM career_pathways WHERE sector = ? ORDER BY level"
        );
        $stmt->execute([$profile['user']['sector_focus']]);
        $profile['career_pathways'] = $stmt->fetchAll();
        
        return $profile;
    }
    
    private function getClaudeRecommendations($userProfile) {
        // Build AI prompt
        $prompt = $this->buildAIPrompt($userProfile);
        
        // Call Claude API
        $claudeResponse = $this->callClaudeAPI($prompt);
        
        if ($claudeResponse && isset($claudeResponse['recommendations'])) {
            return $claudeResponse['recommendations'];
        }
        
        return [];
    }
    
    private function buildAIPrompt($profile) {
        $sector = $profile['user']['sector_focus'];
        $careerGoal = $profile['user']['career_goal'];
        
        $skillsList = array_map(function($s) {
            return $s['skill_name'] . ' (' . $s['proficiency_level'] . ')';
        }, $profile['skills']);
        
        $gapsList = array_map(function($g) {
            return $g['skill_name'] . ' (Current: ' . $g['current_level'] . 
                   ', Required: ' . $g['required_level'] . ', Severity: ' . 
                   $g['gap_severity'] . ')';
        }, $profile['gaps']);
        
        $prompt = "You are an AI career advisor specializing in " . ucfirst($sector) . " technology careers.\n\n";
        $prompt .= "User Profile:\n";
        $prompt .= "- Name: " . $profile['user']['full_name'] . "\n";
        $prompt .= "- Career Goal: " . $careerGoal . "\n";
        $prompt .= "- Sector: " . ucfirst(str_replace('_', ' ', $sector)) . "\n\n";
        
        $prompt .= "Current Skills:\n";
        foreach ($skillsList as $skill) {
            $prompt .= "- " . $skill . "\n";
        }
        
        if (!empty($gapsList)) {
            $prompt .= "\nIdentified Skill Gaps:\n";
            foreach ($gapsList as $gap) {
                $prompt .= "- " . $gap . "\n";
            }
        }
        
        $prompt .= "\nCompleted Courses: " . count($profile['courses']) . "\n";
        $prompt .= "Projects: " . count($profile['projects']) . "\n\n";
        
        $prompt .= "Based on this profile, provide personalized recommendations in JSON format with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"type\": \"course\" | \"project\" | \"skill\" | \"certification\",\n";
        $prompt .= "      \"title\": \"Title of recommendation\",\n";
        $prompt .= "      \"description\": \"Why this is recommended\",\n";
        $prompt .= "      \"relevance_score\": 85.5,\n";
        $prompt .= "      \"source\": \"Platform name or reason\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        $prompt .= "Provide 8-12 highly relevant recommendations focusing on:\n";
        $prompt .= "1. Addressing critical skill gaps\n";
        $prompt .= "2. Advancing toward career goal: " . $careerGoal . "\n";
        $prompt .= "3. Industry-relevant certifications and courses\n";
        $prompt .= "4. Hands-on projects to build portfolio\n";
        $prompt .= "5. Next-level skills for career progression\n\n";
        $prompt .= "Return ONLY the JSON, no additional text.";
        
        return $prompt;
    }
    
    private function callClaudeAPI($prompt) {
        $apiUrl = "https://api.anthropic.com/v1/messages";
        
        $data = [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . (getenv('ANTHROPIC_API_KEY') ?: ''),
            'anthropic-version: 2023-06-01'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            
            if (isset($result['content'][0]['text'])) {
                $text = $result['content'][0]['text'];
                // Extract JSON from response
                $text = preg_replace('/```json\s*/', '', $text);
                $text = preg_replace('/```\s*$/', '', $text);
                $text = trim($text);
                
                $recommendations = json_decode($text, true);
                return $recommendations;
            }
        }
        
        // If API fails, return empty to use fallback
        return null;
    }
    
    private function generateRuleBasedRecommendations($user_id, $profile) {
        $sector = $profile['user']['sector_focus'];
        $gaps = $profile['gaps'];
        $skills = $profile['skills'];
        
        // Course recommendations based on gaps
        $courseDatabase = $this->getCourseDatabase($sector);
        
        foreach ($gaps as $gap) {
            foreach ($courseDatabase as $course) {
                if (in_array($gap['skill_name'], $course['skills'])) {
                    $relevance = $this->calculateRelevance($gap['gap_severity']);
                    
                    $stmt = $this->conn->prepare(
                        "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
                         VALUES (?, 'course', ?, ?, ?, ?)"
                    );
                    
                    $description = "Learn " . implode(', ', $course['skills']) . " to address your skill gaps";
                    
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
        
        // Project recommendations
        $projectDatabase = $this->getProjectDatabase($sector);
        
        foreach ($projectDatabase as $projectName => $requiredSkills) {
            $matchCount = 0;
            foreach ($skills as $userSkill) {
                if (in_array($userSkill['skill_name'], $requiredSkills)) {
                    $matchCount++;
                }
            }
            
            if ($matchCount > 0) {
                $relevance = ($matchCount / count($requiredSkills)) * 100;
                
                $stmt = $this->conn->prepare(
                    "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
                     VALUES (?, 'project', ?, ?, ?, ?)"
                );
                
                $description = "Build this project using: " . implode(', ', $requiredSkills);
                
                $stmt->execute([
                    $user_id,
                    $projectName,
                    $description,
                    $relevance,
                    'AI Algorithm'
                ]);
            }
        }
    }
    
    private function storeRecommendations($user_id, $recommendations) {
        if (empty($recommendations)) return;
        
        $stmt = $this->conn->prepare(
            "INSERT INTO recommendations (user_id, recommendation_type, title, description, relevance_score, source) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($recommendations as $rec) {
            $stmt->execute([
                $user_id,
                $rec['type'] ?? 'course',
                $rec['title'] ?? 'Recommendation',
                $rec['description'] ?? '',
                $rec['relevance_score'] ?? 75.0,
                $rec['source'] ?? 'AI Powered'
            ]);
        }
    }
    
    private function clearOldRecommendations($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM recommendations WHERE user_id = ?");
        $stmt->execute([$user_id]);
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
    
    private function getCourseDatabase($sector) {
        $databases = [
            'healthcare' => [
                ['name' => 'Health Informatics Fundamentals', 'skills' => ['Health Informatics'], 'platform' => 'Coursera'],
                ['name' => 'HIPAA Compliance Training', 'skills' => ['HIPAA Compliance'], 'platform' => 'Udemy'],
                ['name' => 'Medical Database Systems', 'skills' => ['Medical Database Management'], 'platform' => 'edX'],
                ['name' => 'Telemedicine Platform Development', 'skills' => ['Telemedicine Systems'], 'platform' => 'Pluralsight'],
                ['name' => 'HL7 and FHIR Standards', 'skills' => ['HL7/FHIR Standards'], 'platform' => 'Coursera'],
                ['name' => 'Healthcare Data Analytics', 'skills' => ['Healthcare Analytics', 'Health Informatics'], 'platform' => 'edX']
            ],
            'agriculture' => [
                ['name' => 'Precision Agriculture Technologies', 'skills' => ['Precision Farming'], 'platform' => 'Coursera'],
                ['name' => 'Agricultural IoT Systems', 'skills' => ['Agricultural IoT'], 'platform' => 'edX'],
                ['name' => 'Farm Data Analytics', 'skills' => ['Crop Analytics'], 'platform' => 'Udacity'],
                ['name' => 'Supply Chain Management for Agriculture', 'skills' => ['Supply Chain Management'], 'platform' => 'LinkedIn Learning'],
                ['name' => 'Drone Technology for Farming', 'skills' => ['Drone Technology'], 'platform' => 'Udemy'],
                ['name' => 'Smart Farming Solutions', 'skills' => ['Precision Farming', 'Agricultural IoT'], 'platform' => 'FutureLearn']
            ],
            'urban_planning' => [
                ['name' => 'Smart City Systems Design', 'skills' => ['Smart City Systems'], 'platform' => 'edX'],
                ['name' => 'Urban Data Analytics', 'skills' => ['Urban Data Analytics'], 'platform' => 'Coursera'],
                ['name' => 'IoT for Smart Cities', 'skills' => ['IoT for Cities'], 'platform' => 'Udacity'],
                ['name' => 'Sustainable Urban Planning', 'skills' => ['Sustainability Planning'], 'platform' => 'FutureLearn'],
                ['name' => 'GIS and Urban Mapping', 'skills' => ['GIS Mapping'], 'platform' => 'Esri'],
                ['name' => 'Smart Traffic Management', 'skills' => ['Traffic Management Systems'], 'platform' => 'Coursera']
            ]
        ];
        
        return $databases[$sector] ?? [];
    }
    
    private function getProjectDatabase($sector) {
        $databases = [
            'healthcare' => [
                'Patient Management System' => ['Health Informatics', 'Medical Database Management', 'HIPAA Compliance'],
                'Telemedicine Platform' => ['Telemedicine Systems', 'Health Informatics'],
                'Health Analytics Dashboard' => ['Healthcare Analytics', 'Health Informatics'],
                'Electronic Health Records System' => ['Medical Database Management', 'HL7/FHIR Standards'],
                'Clinical Decision Support System' => ['Clinical Decision Support', 'Healthcare Analytics']
            ],
            'agriculture' => [
                'Smart Farm Monitoring System' => ['Agricultural IoT', 'Precision Farming'],
                'Crop Yield Prediction Tool' => ['Crop Analytics', 'Precision Farming'],
                'Agricultural Supply Chain Tracker' => ['Supply Chain Management'],
                'IoT Sensor Network for Farms' => ['Agricultural IoT'],
                'Drone-based Crop Monitoring' => ['Drone Technology', 'Crop Analytics']
            ],
            'urban_planning' => [
                'Smart City Dashboard' => ['Smart City Systems', 'Urban Data Analytics'],
                'Urban IoT Platform' => ['IoT for Cities', 'Smart City Systems'],
                'Sustainability Monitoring System' => ['Sustainability Planning', 'Urban Data Analytics'],
                'Traffic Management System' => ['Traffic Management Systems', 'IoT for Cities'],
                'Smart Parking Solution' => ['IoT for Cities', 'Urban Data Analytics']
            ]
        ];
        
        return $databases[$sector] ?? [];
    }
    
    public function getRecommendations($user_id, $type = null) {
        if ($type) {
            $stmt = $this->conn->prepare(
                "SELECT * FROM recommendations WHERE user_id = ? AND recommendation_type = ? 
                 ORDER BY relevance_score DESC LIMIT 20"
            );
            $stmt->execute([$user_id, $type]);
        } else {
            $stmt = $this->conn->prepare(
                "SELECT * FROM recommendations WHERE user_id = ? 
                 ORDER BY relevance_score DESC LIMIT 30"
            );
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    }
}
?>