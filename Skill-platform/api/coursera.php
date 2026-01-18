<?php
// File: api/coursera.php
// Coursera API Endpoint

header('Content-Type: application/json');
session_start();

require_once '../classes/Database.php';
require_once '../classes/CourseraAPI.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$coursera = new CourseraAPI();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'search':
        // Search for courses
        $query = $_GET['query'] ?? '';
        $limit = intval($_GET['limit'] ?? 20);
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'Query required']);
            exit();
        }
        
        $courses = $coursera->searchCourses($query, $limit);
        echo json_encode([
            'success' => true,
            'courses' => $courses,
            'count' => count($courses)
        ]);
        break;
        
    case 'recommendations':
        // Get personalized recommendations
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get user sector
        $stmt = $conn->prepare("SELECT sector_focus FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $sector = $user['sector_focus'] ?? null;
        
        $courses = $coursera->getPersonalizedRecommendations($user_id, $sector);
        
        echo json_encode([
            'success' => true,
            'courses' => $courses,
            'count' => count($courses)
        ]);
        break;
        
    case 'course-details':
        // Get specific course details
        $course_id = $_GET['course_id'] ?? '';
        
        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID required']);
            exit();
        }
        
        $course = $coursera->getCourseDetails($course_id);
        
        if ($course) {
            echo json_encode([
                'success' => true,
                'course' => $course
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
        }
        break;
        
    case 'save':
        // Save course recommendation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $course_data = $input['course'] ?? null;
        
        if (!$course_data) {
            echo json_encode(['success' => false, 'message' => 'Course data required']);
            exit();
        }
        
        $result = $coursera->saveRecommendation($user_id, $course_data);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Course saved successfully' : 'Failed to save course'
        ]);
        break;
        
    case 'saved':
        // Get user's saved recommendations
        $limit = intval($_GET['limit'] ?? 20);
        $courses = $coursera->getUserRecommendations($user_id, $limit);
        
        echo json_encode([
            'success' => true,
            'courses' => $courses,
            'count' => count($courses)
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>