<?php
// File: api/admin.php
// Admin API Endpoints

session_start();
require_once '../config/config.php';
require_once '../classes/Database.php';

setCorsHeaders();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

switch($endpoint) {
    case 'stats':
        getAdminStats($conn);
        break;
    case 'users':
        if ($method === 'GET') {
            getAllUsers($conn);
        } elseif ($method === 'DELETE') {
            deleteUser($conn);
        }
        break;
    case 'analytics':
        getAnalytics($conn);
        break;
    case 'user-growth':
        getUserGrowth($conn);
        break;
    case 'sector-distribution':
        getSectorDistribution($conn);
        break;
    case 'skills-framework':
        getSkillsFramework($conn);
        break;
    case 'recent-activity':
        getRecentActivity($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid endpoint']);
}

function getAdminStats($conn) {
    $stats = [];
    
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Active users (logged in last 7 days)
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND is_admin = FALSE");
    $stats['active_users'] = $stmt->fetch()['count'];
    
    // New users this month
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND is_admin = FALSE");
    $stats['new_users_month'] = $stmt->fetch()['count'];
    
    // Total skills
    $stmt = $conn->query("SELECT COUNT(*) as count FROM user_skills");
    $stats['total_skills'] = $stmt->fetch()['count'];
    
    // Total courses
    $stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
    $stats['total_courses'] = $stmt->fetch()['count'];
    
    // Total projects
    $stmt = $conn->query("SELECT COUNT(*) as count FROM projects");
    $stats['total_projects'] = $stmt->fetch()['count'];
    
    // Users by sector
    $stmt = $conn->query("SELECT sector_focus, COUNT(*) as count FROM users WHERE is_admin = FALSE GROUP BY sector_focus");
    $stats['sector_distribution'] = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function getAllUsers($conn) {
    $search = $_GET['search'] ?? '';
    $sector = $_GET['sector'] ?? '';
    
    $sql = "SELECT u.user_id, u.email, u.full_name, u.sector_focus, u.career_goal, u.created_at, u.last_login,
            (SELECT COUNT(*) FROM user_skills WHERE user_id = u.user_id) as skills_count,
            (SELECT COUNT(*) FROM courses WHERE user_id = u.user_id) as courses_count
            FROM users u
            WHERE u.is_admin = FALSE";
    
    if ($search) {
        $sql .= " AND (u.full_name LIKE :search OR u.email LIKE :search)";
    }
    
    if ($sector) {
        $sql .= " AND u.sector_focus = :sector";
    }
    
    $sql .= " ORDER BY u.created_at DESC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    
    if ($search) {
        $stmt->bindValue(':search', "%$search%");
    }
    if ($sector) {
        $stmt->bindValue(':sector', $sector);
    }
    
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'users' => $users]);
}

function deleteUser($conn) {
    $user_id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND is_admin = FALSE");
    
    if ($stmt->execute([$user_id])) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
}

function getAnalytics($conn) {
    $analytics = [];
    
    // Skills by sector
    $stmt = $conn->query("
        SELECT sf.sector, COUNT(*) as count 
        FROM user_skills us 
        JOIN users u ON us.user_id = u.user_id 
        JOIN skill_framework sf ON us.skill_name = sf.skill_name AND sf.sector = u.sector_focus
        WHERE u.is_admin = FALSE
        GROUP BY sf.sector
    ");
    $analytics['skills_by_sector'] = $stmt->fetchAll();
    
    // Top skills
    $stmt = $conn->query("
        SELECT skill_name, COUNT(*) as count 
        FROM user_skills 
        GROUP BY skill_name 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $analytics['top_skills'] = $stmt->fetchAll();
    
    // Course completion rates
    $stmt = $conn->query("
        SELECT completion_status, COUNT(*) as count 
        FROM courses 
        GROUP BY completion_status
    ");
    $analytics['course_completion'] = $stmt->fetchAll();
    
    // Skills by proficiency
    $stmt = $conn->query("
        SELECT proficiency_level, COUNT(*) as count 
        FROM user_skills 
        GROUP BY proficiency_level
    ");
    $analytics['skills_proficiency'] = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'analytics' => $analytics]);
}

function getUserGrowth($conn) {
    $stmt = $conn->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_admin = FALSE
        GROUP BY DATE(created_at) 
        ORDER BY date
    ");
    
    $growth = $stmt->fetchAll();
    echo json_encode(['success' => true, 'growth' => $growth]);
}

function getSectorDistribution($conn) {
    $stmt = $conn->query("
        SELECT sector_focus, COUNT(*) as count 
        FROM users 
        WHERE is_admin = FALSE
        GROUP BY sector_focus
    ");
    
    $distribution = $stmt->fetchAll();
    echo json_encode(['success' => true, 'distribution' => $distribution]);
}

function getSkillsFramework($conn) {
    $stmt = $conn->query("SELECT * FROM skill_framework ORDER BY sector, importance_level DESC");
    $skills = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'skills' => $skills]);
}

function getRecentActivity($conn) {
    $stmt = $conn->query("
        SELECT u.full_name, u.email, 'Joined' as activity, u.created_at as activity_time
        FROM users u
        WHERE u.is_admin = FALSE
        UNION ALL
        SELECT u.full_name, u.email, CONCAT('Added skill: ', us.skill_name) as activity, us.created_at as activity_time
        FROM user_skills us
        JOIN users u ON us.user_id = u.user_id
        UNION ALL
        SELECT u.full_name, u.email, CONCAT('Enrolled in: ', c.course_name) as activity, c.created_at as activity_time
        FROM courses c
        JOIN users u ON c.user_id = u.user_id
        ORDER BY activity_time DESC
        LIMIT 20
    ");
    
    $activity = $stmt->fetchAll();
    echo json_encode(['success' => true, 'activity' => $activity]);
}
?>