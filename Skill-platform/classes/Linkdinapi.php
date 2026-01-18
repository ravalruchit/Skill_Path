<?php
// File: classes/LinkedInAPI.php
// LinkedIn API Integration for Profile Import

class LinkedInAPI {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $db;
    private $conn;
    
    public function __construct() {
        // LinkedIn OAuth credentials
        $this->client_id = getenv('LINKEDIN_CLIENT_ID') ?: 'your_client_id';
        $this->client_secret = getenv('LINKEDIN_CLIENT_SECRET') ?: 'your_client_secret';
        $this->redirect_uri = 'http://localhost/skill-platform/api/linkedin-callback.php';
        
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Generate LinkedIn OAuth authorization URL
     */
    public function getAuthorizationUrl($state = null) {
        if (!$state) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['linkedin_state'] = $state;
        }
        
        $params = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => $state,
            'scope' => 'r_liteprofile r_emailaddress'
        ];
        
        return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($code) {
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';
        
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirect_uri,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Get user profile from LinkedIn
     */
    public function getUserProfile($access_token) {
        $url = 'https://api.linkedin.com/v2/me?projection=(id,localizedFirstName,localizedLastName,profilePicture(displayImage~:playableStreams))';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Connection: Keep-Alive'
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
     * Get user email from LinkedIn
     */
    public function getUserEmail($access_token) {
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Connection: Keep-Alive'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['elements'][0]['handle~']['emailAddress'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Import LinkedIn profile data to user account
     */
    public function importProfile($user_id, $access_token) {
        $profile = $this->getUserProfile($access_token);
        $email = $this->getUserEmail($access_token);
        
        if (!$profile) {
            return ['success' => false, 'message' => 'Failed to fetch LinkedIn profile'];
        }
        
        $full_name = ($profile['localizedFirstName'] ?? '') . ' ' . ($profile['localizedLastName'] ?? '');
        
        // Update user profile
        $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, linkedin_connected = 1 WHERE user_id = ?");
        $stmt->execute([trim($full_name), $user_id]);
        
        // Store LinkedIn data
        $stmt = $this->conn->prepare("
            INSERT INTO linkedin_profiles (user_id, linkedin_id, access_token, profile_data, connected_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE access_token = ?, profile_data = ?, connected_at = NOW()
        ");
        
        $linkedin_id = $profile['id'] ?? '';
        $profile_json = json_encode($profile);
        
        $stmt->execute([
            $user_id, 
            $linkedin_id, 
            $access_token, 
            $profile_json,
            $access_token,
            $profile_json
        ]);
        
        return [
            'success' => true,
            'message' => 'LinkedIn profile imported successfully',
            'profile' => [
                'name' => $full_name,
                'email' => $email,
                'linkedin_id' => $linkedin_id
            ]
        ];
    }
    
    /**
     * Disconnect LinkedIn account
     */
    public function disconnect($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM linkedin_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $stmt = $this->conn->prepare("UPDATE users SET linkedin_connected = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        return ['success' => true, 'message' => 'LinkedIn account disconnected'];
    }
    
    /**
     * Check if user has connected LinkedIn
     */
    public function isConnected($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM linkedin_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
?>