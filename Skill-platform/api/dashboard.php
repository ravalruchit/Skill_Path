<?php
// File: api/dashboard.php
// Dashboard API - Fixed Session Handling

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Skill.php';
require_once '../classes/CourseProject.php';
require_once '../classes/Recommendation.php';
require_once '../classes/AIRecommendation.php';

setCorsHeaders();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

$user = new User();
$skill = new Skill();
$course = new Course();
$project = new Project();
$achievement = new Achievement();
$recommendation = new AIRecommendation();

// Route to appropriate handler
switch($endpoint) {
    case 'profile':
        handleProfile($user, $user_id, $method);
        break;
    case 'skills':
        handleSkills($skill, $user_id, $method);
        break;
    case 'courses':
        handleCourses($course, $user_id, $method);
        break;
    case 'projects':
        handleProjects($project, $user_id, $method);
        break;
    case 'achievements':
        handleAchievements($achievement, $user_id, $method);
        break;
    case 'gaps':
        handleGaps($skill, $user_id, $method);
        break;
    case 'recommendations':
        handleRecommendations($recommendation, $user_id, $method);
        break;
    case 'stats':
        handleStats($user, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid endpoint']);
}

function handleProfile($user, $user_id, $method) {
    if ($method === 'GET') {
        $profile = $user->getProfile($user_id);
        echo json_encode(['success' => true, 'profile' => $profile]);
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $user->updateProfile($user_id, $data);
        echo json_encode($result);
    }
}

function handleSkills($skill, $user_id, $method) {
    switch($method) {
        case 'GET':
            $skills = $skill->getUserSkills($user_id);
            echo json_encode(['success' => true, 'skills' => $skills]);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $skill->addSkill(
                $user_id,
                $data['skill_name'],
                $data['proficiency_level'],
                $data['category'] ?? ''
            );
            echo json_encode($result);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $skill->updateSkill($data['skill_id'], $user_id, $data);
            echo json_encode($result);
            break;
        case 'DELETE':
            $skill_id = $_GET['id'] ?? 0;
            $result = $skill->deleteSkill($skill_id, $user_id);
            echo json_encode($result);
            break;
    }
}

function handleCourses($course, $user_id, $method) {
    switch($method) {
        case 'GET':
            $courses = $course->getUserCourses($user_id);
            echo json_encode(['success' => true, 'courses' => $courses]);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $course->addCourse($user_id, $data);
            echo json_encode($result);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $course->updateCourse($data['course_id'], $user_id, $data);
            echo json_encode($result);
            break;
        case 'DELETE':
            $course_id = $_GET['id'] ?? 0;
            $result = $course->deleteCourse($course_id, $user_id);
            echo json_encode($result);
            break;
    }
}

function handleProjects($project, $user_id, $method) {
    switch($method) {
        case 'GET':
            $projects = $project->getUserProjects($user_id);
            echo json_encode(['success' => true, 'projects' => $projects]);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $project->addProject($user_id, $data);
            echo json_encode($result);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $project->updateProject($data['project_id'], $user_id, $data);
            echo json_encode($result);
            break;
        case 'DELETE':
            $project_id = $_GET['id'] ?? 0;
            $result = $project->deleteProject($project_id, $user_id);
            echo json_encode($result);
            break;
    }
}

function handleAchievements($achievement, $user_id, $method) {
    switch($method) {
        case 'GET':
            $achievements = $achievement->getUserAchievements($user_id);
            echo json_encode(['success' => true, 'achievements' => $achievements]);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $achievement->addAchievement($user_id, $data);
            echo json_encode($result);
            break;
        case 'DELETE':
            $achievement_id = $_GET['id'] ?? 0;
            $result = $achievement->deleteAchievement($achievement_id, $user_id);
            echo json_encode($result);
            break;
    }
}

function handleGaps($skill, $user_id, $method) {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get';
        if ($action === 'analyze') {
            $result = $skill->analyzeSkillGaps($user_id);
            echo json_encode($result);
        } else {
            $gaps = $skill->getSkillGaps($user_id);
            echo json_encode(['success' => true, 'gaps' => $gaps]);
        }
    }
}

function handleRecommendations($recommendation, $user_id, $method) {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get';
        if ($action === 'generate') {
            $result = $recommendation->generateAIRecommendations($user_id);
            echo json_encode(['success' => true, 'recommendations' => $result]);
        } else {
            $type = $_GET['type'] ?? null;
            $recs = $recommendation->getRecommendations($user_id, $type);
            echo json_encode(['success' => true, 'recommendations' => $recs]);
        }
    }
}

function handleStats($user, $user_id) {
    $stats = $user->getUserStats($user_id);
    echo json_encode(['success' => true, 'stats' => $stats]);
}
?>