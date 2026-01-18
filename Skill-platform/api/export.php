<?php
// File: api/export.php
// Export Data to Excel - Admin Only

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../classes/Database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$exportType = $_GET['type'] ?? '';

switch($exportType) {
    case 'users':
        exportUsers($conn);
        break;
    case 'skills':
        exportSkills($conn);
        break;
    case 'courses':
        exportCourses($conn);
        break;
    case 'projects':
        exportProjects($conn);
        break;
    case 'analytics':
        exportAnalytics($conn);
        break;
    case 'all':
        exportAllData($conn);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid export type']);
}

function exportUsers($conn) {
    $sql = "SELECT u.user_id, u.email, u.full_name, u.sector_focus, u.career_goal, 
            u.created_at, u.last_login,
            (SELECT COUNT(*) FROM user_skills WHERE user_id = u.user_id) as skills_count,
            (SELECT COUNT(*) FROM courses WHERE user_id = u.user_id) as courses_count,
            (SELECT COUNT(*) FROM projects WHERE user_id = u.user_id) as projects_count
            FROM users u
            WHERE u.is_admin = FALSE
            ORDER BY u.created_at DESC";
    
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll();
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output Excel file
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>User ID</th>';
    echo '<th>Full Name</th>';
    echo '<th>Email</th>';
    echo '<th>Sector Focus</th>';
    echo '<th>Career Goal</th>';
    echo '<th>Skills Count</th>';
    echo '<th>Courses Count</th>';
    echo '<th>Projects Count</th>';
    echo '<th>Registration Date</th>';
    echo '<th>Last Login</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($user['user_id']) . '</td>';
        echo '<td>' . htmlspecialchars($user['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $user['sector_focus']))) . '</td>';
        echo '<td>' . htmlspecialchars($user['career_goal']) . '</td>';
        echo '<td>' . htmlspecialchars($user['skills_count']) . '</td>';
        echo '<td>' . htmlspecialchars($user['courses_count']) . '</td>';
        echo '<td>' . htmlspecialchars($user['projects_count']) . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))) . '</td>';
        echo '<td>' . htmlspecialchars($user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
}

function exportSkills($conn) {
    $sql = "SELECT us.skill_id, u.full_name, u.email, us.skill_name, 
            us.proficiency_level, us.category, us.verified, us.created_at
            FROM user_skills us
            JOIN users u ON us.user_id = u.user_id
            ORDER BY us.created_at DESC";
    
    $stmt = $conn->query($sql);
    $skills = $stmt->fetchAll();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="skills_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Skill ID</th>';
    echo '<th>User Name</th>';
    echo '<th>Email</th>';
    echo '<th>Skill Name</th>';
    echo '<th>Proficiency Level</th>';
    echo '<th>Category</th>';
    echo '<th>Verified</th>';
    echo '<th>Added Date</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($skills as $skill) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($skill['skill_id']) . '</td>';
        echo '<td>' . htmlspecialchars($skill['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($skill['email']) . '</td>';
        echo '<td>' . htmlspecialchars($skill['skill_name']) . '</td>';
        echo '<td>' . htmlspecialchars(ucfirst($skill['proficiency_level'])) . '</td>';
        echo '<td>' . htmlspecialchars($skill['category']) . '</td>';
        echo '<td>' . ($skill['verified'] ? 'Yes' : 'No') . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($skill['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
}

function exportCourses($conn) {
    $sql = "SELECT c.course_id, u.full_name, u.email, c.course_name, 
            c.institution, c.completion_status, c.start_date, c.end_date, 
            c.grade, c.sector, c.created_at
            FROM courses c
            JOIN users u ON c.user_id = u.user_id
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->query($sql);
    $courses = $stmt->fetchAll();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="courses_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Course ID</th>';
    echo '<th>User Name</th>';
    echo '<th>Email</th>';
    echo '<th>Course Name</th>';
    echo '<th>Institution</th>';
    echo '<th>Status</th>';
    echo '<th>Start Date</th>';
    echo '<th>End Date</th>';
    echo '<th>Grade</th>';
    echo '<th>Sector</th>';
    echo '<th>Added Date</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($courses as $course) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($course['course_id']) . '</td>';
        echo '<td>' . htmlspecialchars($course['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($course['email']) . '</td>';
        echo '<td>' . htmlspecialchars($course['course_name']) . '</td>';
        echo '<td>' . htmlspecialchars($course['institution']) . '</td>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $course['completion_status']))) . '</td>';
        echo '<td>' . htmlspecialchars($course['start_date'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($course['end_date'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($course['grade'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $course['sector']))) . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($course['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
}

function exportProjects($conn) {
    $sql = "SELECT p.project_id, u.full_name, u.email, p.project_name, 
            p.description, p.technologies_used, p.project_url, 
            p.start_date, p.end_date, p.sector, p.created_at
            FROM projects p
            JOIN users u ON p.user_id = u.user_id
            ORDER BY p.created_at DESC";
    
    $stmt = $conn->query($sql);
    $projects = $stmt->fetchAll();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Project ID</th>';
    echo '<th>User Name</th>';
    echo '<th>Email</th>';
    echo '<th>Project Name</th>';
    echo '<th>Description</th>';
    echo '<th>Technologies</th>';
    echo '<th>Project URL</th>';
    echo '<th>Start Date</th>';
    echo '<th>End Date</th>';
    echo '<th>Sector</th>';
    echo '<th>Added Date</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($projects as $project) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($project['project_id']) . '</td>';
        echo '<td>' . htmlspecialchars($project['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($project['email']) . '</td>';
        echo '<td>' . htmlspecialchars($project['project_name']) . '</td>';
        echo '<td>' . htmlspecialchars($project['description']) . '</td>';
        echo '<td>' . htmlspecialchars($project['technologies_used']) . '</td>';
        echo '<td>' . htmlspecialchars($project['project_url'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($project['start_date'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($project['end_date'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $project['sector']))) . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($project['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
}

function exportAnalytics($conn) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="analytics_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    
    // Platform Statistics
    echo '<h2>Platform Statistics</h2>';
    echo '<table border="1">';
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE");
    $totalUsers = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND is_admin = FALSE");
    $activeUsers = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM user_skills");
    $totalSkills = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
    $totalCourses = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM projects");
    $totalProjects = $stmt->fetch()['count'];
    
    echo '<tr><td><strong>Total Users</strong></td><td>' . $totalUsers . '</td></tr>';
    echo '<tr><td><strong>Active Users (Last 7 Days)</strong></td><td>' . $activeUsers . '</td></tr>';
    echo '<tr><td><strong>Total Skills</strong></td><td>' . $totalSkills . '</td></tr>';
    echo '<tr><td><strong>Total Courses</strong></td><td>' . $totalCourses . '</td></tr>';
    echo '<tr><td><strong>Total Projects</strong></td><td>' . $totalProjects . '</td></tr>';
    echo '</table><br><br>';
    
    // Users by Sector
    echo '<h2>Users by Sector</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Sector</th><th>User Count</th>';
    echo '</tr>';
    
    $stmt = $conn->query("SELECT sector_focus, COUNT(*) as count FROM users WHERE is_admin = FALSE GROUP BY sector_focus");
    $sectors = $stmt->fetchAll();
    
    foreach ($sectors as $sector) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $sector['sector_focus']))) . '</td>';
        echo '<td>' . $sector['count'] . '</td>';
        echo '</tr>';
    }
    echo '</table><br><br>';
    
    // Top Skills
    echo '<h2>Top 20 Skills</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Skill Name</th><th>User Count</th>';
    echo '</tr>';
    
    $stmt = $conn->query("SELECT skill_name, COUNT(*) as count FROM user_skills GROUP BY skill_name ORDER BY count DESC LIMIT 20");
    $topSkills = $stmt->fetchAll();
    
    foreach ($topSkills as $skill) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($skill['skill_name']) . '</td>';
        echo '<td>' . $skill['count'] . '</td>';
        echo '</tr>';
    }
    echo '</table><br><br>';
    
    // Course Completion Statistics
    echo '<h2>Course Completion Statistics</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    echo '<th>Status</th><th>Count</th>';
    echo '</tr>';
    
    $stmt = $conn->query("SELECT completion_status, COUNT(*) as count FROM courses GROUP BY completion_status");
    $completionStats = $stmt->fetchAll();
    
    foreach ($completionStats as $stat) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $stat['completion_status']))) . '</td>';
        echo '<td>' . $stat['count'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</body>';
    echo '</html>';
}

function exportAllData($conn) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="complete_data_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    
    // Export all data sections
    echo '<h1>Complete Platform Data Export - ' . date('Y-m-d H:i:s') . '</h1><br>';
    
    // Users
    echo '<h2>Users</h2>';
    $sql = "SELECT user_id, email, full_name, sector_focus, career_goal, created_at, last_login FROM users WHERE is_admin = FALSE";
    outputTable($conn, $sql);
    echo '<br><br>';
    
    // Skills
    echo '<h2>User Skills</h2>';
    $sql = "SELECT us.skill_id, u.full_name, us.skill_name, us.proficiency_level, us.category FROM user_skills us JOIN users u ON us.user_id = u.user_id";
    outputTable($conn, $sql);
    echo '<br><br>';
    
    // Courses
    echo '<h2>Courses</h2>';
    $sql = "SELECT c.course_id, u.full_name, c.course_name, c.institution, c.completion_status, c.grade FROM courses c JOIN users u ON c.user_id = u.user_id";
    outputTable($conn, $sql);
    echo '<br><br>';
    
    // Projects
    echo '<h2>Projects</h2>';
    $sql = "SELECT p.project_id, u.full_name, p.project_name, p.technologies_used FROM projects p JOIN users u ON p.user_id = u.user_id";
    outputTable($conn, $sql);
    
    echo '</body>';
    echo '</html>';
}

function outputTable($conn, $sql) {
    $stmt = $conn->query($sql);
    $data = $stmt->fetchAll();
    
    if (empty($data)) {
        echo '<p>No data available</p>';
        return;
    }
    
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #6366f1; color: white; font-weight: bold;">';
    
    foreach (array_keys($data[0]) as $header) {
        echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
    }
    
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell ?: 'N/A') . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
}
?>