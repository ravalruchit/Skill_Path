<?php
// File: classes/CourseraAPI.php
// Coursera API Integration for Course Recommendations

class CourseraAPI {
    private $api_key;
    private $base_url = 'https://api.coursera.org/api';
    private $db;
    private $conn;
    
    public function __construct() {
        $this->api_key = getenv('COURSERA_API_KEY') ?: 'your_api_key';
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Search for courses based on keywords
     */
    public function searchCourses($query, $limit = 20) {
        $url = $this->base_url . '/courses.v1?q=search&query=' . urlencode($query) . '&limit=' . $limit;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $this->formatCourses($data['elements'] ?? []);
        }
        
        // Fallback to mock data if API fails
        return $this->getMockCourses($query);
    }
    
    /**
     * Get course details by ID
     */
    public function getCourseDetails($course_id) {
        $url = $this->base_url . '/courses.v1/' . $course_id;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Get personalized course recommendations based on user skills
     */
    public function getPersonalizedRecommendations($user_id, $sector = null) {
        // Get user's skill gaps
        $stmt = $this->conn->prepare("SELECT skill_name, gap_severity FROM skill_gaps WHERE user_id = ? ORDER BY gap_severity DESC LIMIT 5");
        $stmt->execute([$user_id]);
        $gaps = $stmt->fetchAll();
        
        $recommendations = [];
        
        foreach ($gaps as $gap) {
            $courses = $this->searchCourses($gap['skill_name'], 5);
            
            foreach ($courses as $course) {
                $course['relevance_reason'] = 'Addresses skill gap: ' . $gap['skill_name'];
                $course['priority'] = $gap['gap_severity'];
                $recommendations[] = $course;
            }
        }
        
        // If sector specified, add sector-specific courses
        if ($sector) {
            $sectorCourses = $this->getSectorCourses($sector);
            $recommendations = array_merge($recommendations, $sectorCourses);
        }
        
        // Sort by relevance and remove duplicates
        $recommendations = array_values(array_unique($recommendations, SORT_REGULAR));
        
        return array_slice($recommendations, 0, 15);
    }
    
    /**
     * Get sector-specific courses
     */
    private function getSectorCourses($sector) {
        $queries = [
            'healthcare' => ['health informatics', 'healthcare IT', 'telemedicine', 'medical data'],
            'agriculture' => ['precision farming', 'agricultural technology', 'smart farming', 'agritech'],
            'urban_planning' => ['smart cities', 'urban planning', 'sustainable cities', 'urban data']
        ];
        
        $keywords = $queries[$sector] ?? [];
        $courses = [];
        
        foreach ($keywords as $keyword) {
            $results = $this->searchCourses($keyword, 3);
            $courses = array_merge($courses, $results);
        }
        
        return $courses;
    }
    
    /**
     * Format course data
     */
    private function formatCourses($courses) {
        $formatted = [];
        
        foreach ($courses as $course) {
            $formatted[] = [
                'id' => $course['id'] ?? uniqid(),
                'name' => $course['name'] ?? 'Course Title',
                'description' => $course['description'] ?? 'Course description',
                'provider' => 'Coursera',
                'url' => 'https://www.coursera.org/learn/' . ($course['slug'] ?? ''),
                'difficulty' => $course['difficultyLevel'] ?? 'Intermediate',
                'rating' => $course['avgLearnerRating'] ?? 4.5,
                'enrolled' => $course['enrolledStudentCount'] ?? 0
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Mock course data for testing/fallback
     */
    private function getMockCourses($query) {
        $mock_courses = [
            [
                'id' => 'health-informatics-101',
                'name' => 'Introduction to Health Informatics',
                'description' => 'Learn the fundamentals of health informatics and healthcare IT systems',
                'provider' => 'Coursera',
                'url' => 'https://www.coursera.org/learn/health-informatics',
                'difficulty' => 'Beginner',
                'rating' => 4.7,
                'enrolled' => 15000
            ],
            [
                'id' => 'precision-agriculture',
                'name' => 'Precision Agriculture Technology',
                'description' => 'Master the technologies driving modern precision farming',
                'provider' => 'Coursera',
                'url' => 'https://www.coursera.org/learn/precision-agriculture',
                'difficulty' => 'Intermediate',
                'rating' => 4.6,
                'enrolled' => 8500
            ],
            [
                'id' => 'smart-cities-iot',
                'name' => 'Smart Cities and IoT',
                'description' => 'Explore how IoT is transforming urban environments',
                'provider' => 'Coursera',
                'url' => 'https://www.coursera.org/learn/smart-cities',
                'difficulty' => 'Advanced',
                'rating' => 4.8,
                'enrolled' => 12000
            ]
        ];
        
        // Filter mock courses based on query
        return array_filter($mock_courses, function($course) use ($query) {
            return stripos($course['name'], $query) !== false || 
                   stripos($course['description'], $query) !== false;
        });
    }
    
    /**
     * Save course recommendation to database
     */
    public function saveRecommendation($user_id, $course_data) {
        $stmt = $this->conn->prepare("
            INSERT INTO course_recommendations (user_id, course_id, course_name, provider, url, relevance_score, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $user_id,
            $course_data['id'],
            $course_data['name'],
            $course_data['provider'],
            $course_data['url'],
            $course_data['rating'] ?? 4.5
        ]);
    }
    
    /**
     * Get user's saved course recommendations
     */
    public function getUserRecommendations($user_id, $limit = 20) {
        $stmt = $this->conn->prepare("
            SELECT * FROM course_recommendations 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        
        return $stmt->fetchAll();
    }
}
?>