<?php
// API Integrations Page
// pages/integrations.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check integration status
$stmt = $conn->prepare("SELECT linkedin_connected FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$linkedin_connected = $user['linkedin_connected'] ?? 0;

?>

<style>
.integrations-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
}

.integrations-header {
    text-align: center;
    margin-bottom: 40px;
}

.integrations-header h1 {
    font-size: 36px;
    color: #333;
    margin-bottom: 10px;
}

.integrations-header p {
    font-size: 18px;
    color: #666;
}

.integrations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.integration-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.integration-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.integration-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.integration-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 30px;
}

.integration-icon.linkedin {
    background: #0077b5;
    color: white;
}

.integration-icon.coursera {
    background: #0056d2;
    color: white;
}

.integration-icon.udemy {
    background: #a435f0;
    color: white;
}

.integration-info h3 {
    font-size: 22px;
    margin-bottom: 5px;
}

.integration-info .status {
    font-size: 14px;
    padding: 4px 12px;
    border-radius: 12px;
    display: inline-block;
}

.status.connected {
    background: #d4edda;
    color: #155724;
}

.status.not-connected {
    background: #f8d7da;
    color: #721c24;
}

.integration-description {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.integration-features {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.integration-features h4 {
    font-size: 14px;
    color: #667eea;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.integration-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.integration-features li {
    padding: 8px 0;
    color: #333;
    display: flex;
    align-items: center;
}

.integration-features li::before {
    content: '✓';
    color: #10b981;
    font-weight: bold;
    margin-right: 10px;
}

.integration-actions {
    display: flex;
    gap: 10px;
}

.btn-connect {
    flex: 1;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-connect:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-disconnect {
    flex: 1;
    padding: 12px 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-disconnect:hover {
    background: #dc2626;
}

.btn-sync {
    padding: 12px 24px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-sync:hover {
    background: #059669;
}

.course-recommendations {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.course-recommendations h2 {
    color: #333;
    margin-bottom: 20px;
}

.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.course-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid #667eea;
    transition: all 0.3s;
}

.course-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.course-card h4 {
    color: #333;
    margin-bottom: 10px;
}

.course-card p {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.course-rating {
    color: #f59e0b;
    font-weight: 600;
}

.course-difficulty {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    background: #e0e0e0;
    color: #333;
}

.btn-enroll {
    width: 100%;
    padding: 10px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-enroll:hover {
    background: #5568d3;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>

<div class="integrations-page">
    <div class="integrations-header">
        <h1>🔗 Connect Your Accounts</h1>
        <p>Enhance your experience by connecting external platforms</p>
    </div>

    <div class="integrations-grid">
        <!-- LinkedIn Integration -->
        <div class="integration-card">
            <div class="integration-header">
                <div class="integration-icon linkedin">in</div>
                <div class="integration-info">
                    <h3>LinkedIn</h3>
                    <span class="status <?php echo $linkedin_connected ? 'connected' : 'not-connected'; ?>">
                        <?php echo $linkedin_connected ? 'Connected' : 'Not Connected'; ?>
                    </span>
                </div>
            </div>
            
            <p class="integration-description">
                Import your professional profile, work experience, and skills from LinkedIn to auto-populate your profile.
            </p>
            
            <div class="integration-features">
                <h4>Features</h4>
                <ul>
                    <li>Auto-import work experience</li>
                    <li>Sync professional skills</li>
                    <li>Update profile information</li>
                    <li>Connect with network</li>
                </ul>
            </div>
            
            <div class="integration-actions">
                <?php if ($linkedin_connected): ?>
                    <button class="btn-sync" onclick="syncLinkedIn()">Sync Now</button>
                    <button class="btn-disconnect" onclick="disconnectLinkedIn()">Disconnect</button>
                <?php else: ?>
                    <button class="btn-connect" onclick="connectLinkedIn()">Connect LinkedIn</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coursera Integration -->
        <div class="integration-card">
            <div class="integration-header">
                <div class="integration-icon coursera">C</div>
                <div class="integration-info">
                    <h3>Coursera</h3>
                    <span class="status connected">Active</span>
                </div>
            </div>
            
            <p class="integration-description">
                Get personalized course recommendations from Coursera's catalog based on your skill gaps and career goals.
            </p>
            
            <div class="integration-features">
                <h4>Features</h4>
                <ul>
                    <li>AI-powered recommendations</li>
                    <li>Skill-gap based courses</li>
                    <li>Career pathway courses</li>
                    <li>Progress tracking</li>
                </ul>
            </div>
            
            <div class="integration-actions">
                <button class="btn-connect" onclick="loadCourseRecommendations()">Load Recommendations</button>
            </div>
        </div>

        <!-- Udemy Integration -->
        <div class="integration-card">
            <div class="integration-header">
                <div class="integration-icon udemy">U</div>
                <div class="integration-info">
                    <h3>Udemy</h3>
                    <span class="status connected">Active</span>
                </div>
            </div>
            
            <p class="integration-description">
                Discover affordable courses from Udemy's extensive library tailored to your learning needs.
            </p>
            
            <div class="integration-features">
                <h4>Features</h4>
                <ul>
                    <li>Budget-friendly options</li>
                    <li>Practical skill courses</li>
                    <li>Lifetime access</li>
                    <li>Certificate of completion</li>
                </ul>
            </div>
            
            <div class="integration-actions">
                <button class="btn-connect" onclick="loadUdemyCourses()">Browse Courses</button>
            </div>
        </div>
    </div>

    <!-- Course Recommendations Section -->
    <div class="course-recommendations">
        <h2>📚 Recommended Courses for You</h2>
        <div id="courseRecommendations" class="course-grid">
            <div class="loading">Click "Load Recommendations" to see personalized course suggestions</div>
        </div>
    </div>
</div>

<script>
async function connectLinkedIn() {
    try {
        const response = await fetch('api/linkedin.php?action=auth-url', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.auth_url) {
            window.location.href = data.auth_url;
        } else {
            alert('Failed to generate LinkedIn authorization URL');
        }
    } catch (error) {
        console.error('LinkedIn connect error:', error);
        alert('Error connecting to LinkedIn');
    }
}

async function disconnectLinkedIn() {
    if (!confirm('Are you sure you want to disconnect LinkedIn?')) return;
    
    try {
        const response = await fetch('api/linkedin.php?action=disconnect', {
            method: 'POST',
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            alert('LinkedIn disconnected successfully');
            location.reload();
        }
    } catch (error) {
        console.error('LinkedIn disconnect error:', error);
    }
}

async function syncLinkedIn() {
    try {
        const response = await fetch('api/linkedin.php?action=sync', {
            method: 'POST',
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            alert('LinkedIn profile synced successfully!');
        }
    } catch (error) {
        console.error('LinkedIn sync error:', error);
    }
}

async function loadCourseRecommendations() {
    const container = document.getElementById('courseRecommendations');
    container.innerHTML = '<div class="loading">Loading recommendations...</div>';
    
    try {
        const response = await fetch('api/coursera.php?action=recommendations', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.courses) {
            displayCourses(data.courses);
        } else {
            container.innerHTML = '<div class="loading">No recommendations available</div>';
        }
    } catch (error) {
        console.error('Load courses error:', error);
        container.innerHTML = '<div class="loading">Error loading recommendations</div>';
    }
}

function displayCourses(courses) {
    const container = document.getElementById('courseRecommendations');
    
    if (courses.length === 0) {
        container.innerHTML = '<div class="loading">No courses found</div>';
        return;
    }
    
    container.innerHTML = courses.map(course => `
        <div class="course-card">
            <h4>${course.name}</h4>
            <p>${course.description}</p>
            <div class="course-meta">
                <span class="course-rating">⭐ ${course.rating}</span>
                <span class="course-difficulty">${course.difficulty}</span>
            </div>
            <a href="${course.url}" target="_blank" class="btn-enroll">View Course</a>
        </div>
    `).join('');
}

async function loadUdemyCourses() {
    alert('Udemy integration coming soon! This will load courses from Udemy API.');
}
</script>