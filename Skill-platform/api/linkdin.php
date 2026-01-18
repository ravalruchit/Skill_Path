<?php
// File: api/linkedin.php
// LinkedIn API Endpoint

header('Content-Type: application/json');
session_start();

require_once '../classes/Database.php';
require_once '../classes/LinkedInAPI.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$linkedin = new LinkedInAPI();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'auth-url':
        // Generate LinkedIn authorization URL
        $auth_url = $linkedin->getAuthorizationUrl();
        echo json_encode([
            'success' => true,
            'auth_url' => $auth_url
        ]);
        break;
        
    case 'callback':
        // Handle OAuth callback
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        
        if (!$code || !$state || $state !== ($_SESSION['linkedin_state'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid callback']);
            exit();
        }
        
        // Exchange code for access token
        $access_token = $linkedin->getAccessToken($code);
        
        if (!$access_token) {
            echo json_encode(['success' => false, 'message' => 'Failed to get access token']);
            exit();
        }
        
        // Import profile
        $result = $linkedin->importProfile($user_id, $access_token);
        echo json_encode($result);
        break;
        
    case 'sync':
        // Sync LinkedIn profile
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        // Get stored access token
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT access_token FROM linkedin_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $linkedin_profile = $stmt->fetch();
        
        if (!$linkedin_profile) {
            echo json_encode(['success' => false, 'message' => 'LinkedIn not connected']);
            exit();
        }
        
        $result = $linkedin->importProfile($user_id, $linkedin_profile['access_token']);
        echo json_encode($result);
        break;
        
    case 'disconnect':
        // Disconnect LinkedIn
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        $result = $linkedin->disconnect($user_id);
        echo json_encode($result);
        break;
        
    case 'status':
        // Check connection status
        $connected = $linkedin->isConnected($user_id);
        echo json_encode([
            'success' => true,
            'connected' => $connected
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>