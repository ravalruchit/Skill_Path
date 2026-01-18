<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/LinkedInAPI.php';

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (!$code || $state !== ($_SESSION['linkedin_state'] ?? '')) {
    header('Location: /dashboard.html?error=linkedin_auth_failed');
    exit();
}

$linkedin = new LinkedInAPI();
$access_token = $linkedin->getAccessToken($code);

if ($access_token && isset($_SESSION['user_id'])) {
    $result = $linkedin->importProfile($_SESSION['user_id'], $access_token);
    
    if ($result['success']) {
        header('Location: /dashboard.html?page=integrations&success=linkedin_connected');
    } else {
        header('Location: /dashboard.html?page=integrations&error=import_failed');
    }
} else {
    header('Location: /dashboard.html?error=linkedin_auth_failed');
}
exit();
?>