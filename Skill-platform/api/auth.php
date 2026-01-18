<?php
// File: api/auth.php
// Authentication API Endpoints - Fixed Session Handling

// Start session FIRST, before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

setCorsHeaders();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$user = new User();

// Handle different HTTP methods
switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $_GET['action'] ?? '';
        
        if ($action === 'register') {
            handleRegister($user, $data);
        } elseif ($action === 'login') {
            handleLogin($user, $data);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
        
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'logout') {
            handleLogout();
        } elseif ($action === 'check') {
            checkSession();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleRegister($user, $data) {
    if (!isset($data['email'], $data['password'], $data['full_name'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Validate password
    if (strlen($data['password']) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }
    
    $result = $user->register(
        $data['email'],
        $data['password'],
        $data['full_name'],
        $data['career_goal'] ?? '',
        $data['sector'] ?? 'healthcare'
    );
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['full_name'] = $data['full_name'];
        $_SESSION['is_admin'] = false;
        
        // Update last login
        $user->updateLastLogin($result['user_id']);
        
        // Return success with user info
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'user_id' => $result['user_id'],
                'email' => $data['email'],
                'full_name' => $data['full_name'],
                'is_admin' => false
            ]
        ]);
    } else {
        echo json_encode($result);
    }
}

function handleLogin($user, $data) {
    if (!isset($data['email'], $data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Missing credentials']);
        return;
    }
    
    $result = $user->login($data['email'], $data['password']);
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user']['user_id'];
        $_SESSION['email'] = $result['user']['email'];
        $_SESSION['full_name'] = $result['user']['full_name'];
        $_SESSION['is_admin'] = $result['user']['is_admin'] ?? false;
        
        // Update last login
        $user->updateLastLogin($result['user']['user_id']);
        
        // Return is_admin status
        $result['user']['is_admin'] = $_SESSION['is_admin'];
        
        echo json_encode($result);
    } else {
        echo json_encode($result);
    }
}

function handleLogout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function checkSession() {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'is_admin' => $_SESSION['is_admin'] ?? false
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}
?>