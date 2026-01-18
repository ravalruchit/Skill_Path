<?php
// Enhanced Dashboard Page with Advanced Visualizations
// pages/enhanced-dashboard.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];

// Get user profile
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get skill progression data
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM user_skills 
    WHERE user_id = ? 
    GROUP BY month 
    ORDER BY month ASC 
    LIMIT 12
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skill_progression = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get skills by proficiency
$stmt = $conn->prepare("
    SELECT proficiency_level, COUNT(*) as count 
    FROM user_skills 
    WHERE user_id = ? 
    GROUP BY proficiency_level
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skills_by_level = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get career pathway progress
$stmt = $conn->prepare("SELECT * FROM career_pathways WHERE user_id = ? ORDER BY pathway_id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$career_pathway = $stmt->get_result()->fetch_assoc();

// Get recent achievements
$stmt = $conn->prepare("
    SELECT * FROM (
        SELECT 'skill' as type, skill_name as title, created_at, proficiency_level as details FROM user_skills WHERE user_id = ?
        UNION ALL
        SELECT 'course' as type, course_name as title, created_at, completion_status as details FROM courses WHERE user_id = ?
        UNION ALL
        SELECT 'project' as type, project_name as title, created_at, sector as details FROM projects WHERE user_id = ?
    ) as activities 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$recent_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<style>
.enhanced-dashboard {
    padding: 20px;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.dashboard-header h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.dashboard-header p {
    font-size: 18px;
    opacity: 0.9;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card-enhanced {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #667eea;
    transition: transform 0.3s;
}

.stat-card-enhanced:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.stat-card-enhanced h3 {
    color: #667eea;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.stat-card-enhanced .value {
    font-size: 36px;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}

.stat-card-enhanced .change {
    color: #10b981;
    font-size: 14px;
}

.visualization-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 25px;
}

.visualization-section h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 22px;
}

.chart-container {
    position: relative;
    height: 350px;
}

.career-pathway-visual {
    background: linear-gradient(to right, #f8f9fa, #ffffff);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.pathway-timeline {
    position: relative;
    padding: 20px 0;
}

.pathway-step {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    position: relative;
}

.pathway-step::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -30px;
    width: 2px;
    background: #e0e0e0;
}

.pathway-step:last-child::before {
    display: none;
}

.step-marker {
    width: 40px;
    height: 40px;
    background: #667eea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    z-index: 1;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
}

.step-marker.completed {
    background: #10b981;
}

.step-marker.current {
    background: #f59e0b;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.step-content {
    margin-left: 20px;
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.step-content h3 {
    color: #333;
    margin-bottom: 8px;
}

.step-content p {
    color: #666;
    margin-bottom: 10px;
}

.step-progress {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 10px;
}

.step-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.5s;
}

.activity-timeline {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.activity-item {
    display: flex;
    padding: 15px;
    border-left: 3px solid #667eea;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: #667eea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
}

.activity-details {
    flex: 1;
}

.activity-details h4 {
    color: #333;
    margin-bottom: 5px;
}

.activity-details p {
    color: #666;
    font-size: 14px;
}

.activity-time {
    color: #999;
    font-size: 12px;
}

.skills-radar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .skills-radar {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="enhanced-dashboard">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        <p>Track your progress and continue your journey to <?php echo htmlspecialchars($user['career_goal'] ?: 'career success'); ?></p>
    </div>

    <div class="dashboard-grid">
        <div class="stat-card-enhanced">
            <h3>Total Skills</h3>
            <div class="value" id="totalSkillsCount">0</div>
            <div class="change">↑ Growing this month</div>
        </div>
        
        <div class="stat-card-enhanced">
            <h3>Courses Completed</h3>
            <div class="value" id="completedCoursesCount">0</div>
            <div class="change">Keep learning!</div>
        </div>
        
        <div class="stat-card-enhanced">
            <h3>Projects</h3>
            <div class="value" id="totalProjectsCount">0</div>
            <div class="change">Building portfolio</div>
        </div>
        
        <div class="stat-card-enhanced">
            <h3>Career Match</h3>
            <div class="value"><?php echo $career_pathway ? intval($career_pathway['match_percentage']) : 0; ?>%</div>
            <div class="change">On track!</div>
        </div>
    </div>

    <!-- Skill Progression Chart -->
    <div class="visualization-section">
        <h2>📈 Skill Progression Over Time</h2>
        <div class="chart-container">
            <canvas id="skillProgressionChart"></canvas>
        </div>
    </div>

    <!-- Skills Distribution -->
    <div class="visualization-section">
        <h2>🎯 Skills by Proficiency Level</h2>
        <div class="skills-radar">
            <div class="chart-container" style="height: 300px;">
                <canvas id="skillsDistributionChart"></canvas>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="sectorSkillsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Career Pathway Visualization -->
    <div class="visualization-section career-pathway-visual">
        <h2>🚀 Your Career Pathway</h2>
        <div class="pathway-timeline">
            <div class="pathway-step">
                <div class="step-marker completed">1</div>
                <div class="step-content">
                    <h3>Foundation Skills</h3>
                    <p>Basic skills in your chosen sector</p>
                    <div class="step-progress">
                        <div class="step-progress-bar" style="width: 100%"></div>
                    </div>
                    <small>Completed</small>
                </div>
            </div>
            
            <div class="pathway-step">
                <div class="step-marker current">2</div>
                <div class="step-content">
                    <h3>Intermediate Development</h3>
                    <p>Expanding your knowledge and practical skills</p>
                    <div class="step-progress">
                        <div class="step-progress-bar" style="width: 65%"></div>
                    </div>
                    <small>65% Complete</small>
                </div>
            </div>
            
            <div class="pathway-step">
                <div class="step-marker">3</div>
                <div class="step-content">
                    <h3>Advanced Expertise</h3>
                    <p>Specialized skills and certifications</p>
                    <div class="step-progress">
                        <div class="step-progress-bar" style="width: 30%"></div>
                    </div>
                    <small>30% Complete</small>
                </div>
            </div>
            
            <div class="pathway-step">
                <div class="step-marker">4</div>
                <div class="step-content">
                    <h3>Career Goal Achievement</h3>
                    <p><?php echo htmlspecialchars($user['career_goal'] ?: 'Professional Excellence'); ?></p>
                    <div class="step-progress">
                        <div class="step-progress-bar" style="width: 0%"></div>
                    </div>
                    <small>Not Started</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-timeline">
        <h2>📋 Recent Activity</h2>
        <?php foreach ($recent_activities as $activity): ?>
        <div class="activity-item">
            <div class="activity-icon">
                <?php if ($activity['type'] === 'skill'): ?>📚<?php endif; ?>
                <?php if ($activity['type'] === 'course'): ?>🎓<?php endif; ?>
                <?php if ($activity['type'] === 'project'): ?>💼<?php endif; ?>
            </div>
            <div class="activity-details">
                <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                <p>
                    <?php if ($activity['type'] === 'skill'): ?>
                        Added new skill - <?php echo htmlspecialchars($activity['details']); ?> level
                    <?php elseif ($activity['type'] === 'course'): ?>
                        Course status: <?php echo htmlspecialchars($activity['details']); ?>
                    <?php else: ?>
                        New project in <?php echo htmlspecialchars($activity['details']); ?> sector
                    <?php endif; ?>
                </p>
                <div class="activity-time"><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Load stats
async function loadDashboardStats() {
    try {
        const response = await fetch('api/dashboard.php?endpoint=stats', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.stats) {
            document.getElementById('totalSkillsCount').textContent = data.stats.total_skills || 0;
            document.getElementById('completedCoursesCount').textContent = data.stats.total_courses || 0;
            document.getElementById('totalProjectsCount').textContent = data.stats.total_projects || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Skill Progression Chart
const progressionData = <?php echo json_encode($skill_progression); ?>;
const ctx1 = document.getElementById('skillProgressionChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: progressionData.map(d => d.month),
        datasets: [{
            label: 'Skills Acquired',
            data: progressionData.map(d => d.count),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Skills Distribution Chart
const skillsLevelData = <?php echo json_encode($skills_by_level); ?>;
const ctx2 = document.getElementById('skillsDistributionChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: skillsLevelData.map(d => d.proficiency_level.charAt(0).toUpperCase() + d.proficiency_level.slice(1)),
        datasets: [{
            data: skillsLevelData.map(d => d.count),
            backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Sector Skills Chart
const ctx3 = document.getElementById('sectorSkillsChart').getContext('2d');
new Chart(ctx3, {
    type: 'radar',
    data: {
        labels: ['Healthcare', 'Agriculture', 'Urban Planning', 'Technical', 'Soft Skills'],
        datasets: [{
            label: 'Your Skills',
            data: [8, 6, 7, 9, 7],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.2)',
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#667eea'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                beginAtZero: true,
                max: 10
            }
        }
    }
});

// Load stats on page load
loadDashboardStats();
</script>