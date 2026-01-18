// Enhanced Dashboard JavaScript with Full AI/ML Integration
const API_BASE = 'http://localhost/dashboard/Skill-platform/api';

// State Management
const state = {
    searchOpen: false,
    notificationsOpen: false,
    sidebarOpen: true,
    currentUser: null,
    notifications: [
        { id: 1, title: 'New Course Recommendation', message: 'Advanced React Development added to your recommendations', time: '2 hours ago', read: false, type: 'info' },
        { id: 2, title: 'Skill Verified', message: 'Your JavaScript skill has been verified', time: '5 hours ago', read: false, type: 'success' },
        { id: 3, title: 'Goal Progress', message: 'You\'re 75% towards your career goal!', time: '1 day ago', read: true, type: 'warning' }
    ]
};

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    await loadUserData();
    await loadStats();
    setupNavigation();
    setupLogout();
    setupSearch();
    setupNotifications();
    setupMobileMenu();
    setupAddButtons();
    setupProfileForm();
    renderNotifications();
});

// Authentication Check
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}/auth.php?action=check`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (!data.logged_in) {
            window.location.href = 'login.html';
            return;
        }
        
        state.currentUser = data;
        const initials = data.full_name.split(' ').map(n => n[0]).join('');
        document.getElementById('userInitials').textContent = initials;
        
        showWelcomeMessage(data.full_name);
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'login.html';
    }
}

// Show Welcome Message
function showWelcomeMessage(name) {
    const firstName = name.split(' ')[0];
    showToast(`Welcome back, ${firstName}!`, 'success');
}

// Setup Search Functionality
function setupSearch() {
    const searchBtn = document.querySelector('.icon-btn:has(svg path[d*="21 21"])');
    const searchBox = document.createElement('div');
    searchBox.className = 'search-box';
    searchBox.innerHTML = `
        <input type="text" placeholder="Search skills, courses, projects..." id="globalSearch">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>
    `;
    
    if (searchBtn) {
        searchBtn.parentElement.insertBefore(searchBox, searchBtn.nextSibling);
        
        searchBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            state.searchOpen = !state.searchOpen;
            searchBox.classList.toggle('active', state.searchOpen);
            
            if (state.searchOpen) {
                searchBox.querySelector('input').focus();
                searchBtn.classList.add('active');
            } else {
                searchBtn.classList.remove('active');
            }
        });
        
        const searchInput = searchBox.querySelector('input');
        searchInput.addEventListener('input', debounce(handleSearch, 300));
        
        document.addEventListener('click', (e) => {
            if (!searchBox.contains(e.target) && !searchBtn.contains(e.target)) {
                state.searchOpen = false;
                searchBox.classList.remove('active');
                searchBtn.classList.remove('active');
            }
        });
    }
}

// Handle Search
async function handleSearch(e) {
    const query = e.target.value.trim().toLowerCase();
    
    if (query.length < 2) return;
    
    try {
        const [skills, courses, projects] = await Promise.all([
            fetch(`${API_BASE}/dashboard.php?endpoint=skills`, { credentials: 'include' }).then(r => r.json()),
            fetch(`${API_BASE}/dashboard.php?endpoint=courses`, { credentials: 'include' }).then(r => r.json()),
            fetch(`${API_BASE}/dashboard.php?endpoint=projects`, { credentials: 'include' }).then(r => r.json())
        ]);
        
        const results = [];
        
        if (skills.success) {
            skills.skills.filter(s => s.skill_name.toLowerCase().includes(query))
                .forEach(s => results.push({ type: 'skill', data: s }));
        }
        
        if (courses.success) {
            courses.courses.filter(c => c.course_name.toLowerCase().includes(query))
                .forEach(c => results.push({ type: 'course', data: c }));
        }
        
        if (projects.success) {
            projects.projects.filter(p => p.project_name.toLowerCase().includes(query))
                .forEach(p => results.push({ type: 'project', data: p }));
        }
        
        displaySearchResults(results, query);
    } catch (error) {
        console.error('Search error:', error);
    }
}

// Display Search Results
function displaySearchResults(results, query) {
    if (results.length === 0) {
        showToast(`No results found for "${query}"`, 'info');
        return;
    }
    
    showToast(`Found ${results.length} result${results.length > 1 ? 's' : ''} for "${query}"`, 'success');
    
    if (results[0].type === 'skill') {
        switchTab('skills');
    } else if (results[0].type === 'course') {
        switchTab('courses');
    } else if (results[0].type === 'project') {
        switchTab('projects');
    }
}

// Setup Notifications
function setupNotifications() {
    const notifBtn = document.querySelector('.icon-btn:has(svg path[d*="18 8"])');
    
    if (notifBtn) {
        const unreadCount = state.notifications.filter(n => !n.read).length;
        if (unreadCount > 0) {
            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.textContent = unreadCount;
            notifBtn.appendChild(badge);
        }
        
        const dropdown = document.createElement('div');
        dropdown.className = 'notifications-dropdown';
        dropdown.innerHTML = `
            <div class="notifications-header">
                <h3>Notifications</h3>
                <button class="mark-read-btn" onclick="markAllRead()">Mark all read</button>
            </div>
            <div class="notifications-list" id="notificationsList"></div>
        `;
        
        notifBtn.parentElement.style.position = 'relative';
        notifBtn.parentElement.appendChild(dropdown);
        
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            state.notificationsOpen = !state.notificationsOpen;
            dropdown.classList.toggle('active', state.notificationsOpen);
            notifBtn.classList.toggle('active', state.notificationsOpen);
        });
        
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                state.notificationsOpen = false;
                dropdown.classList.remove('active');
                notifBtn.classList.remove('active');
            }
        });
    }
}

// Render Notifications
function renderNotifications() {
    const list = document.getElementById('notificationsList');
    if (!list) return;
    
    list.innerHTML = state.notifications.map(notif => `
        <div class="notification-item ${notif.read ? '' : 'unread'}" onclick="markAsRead(${notif.id})">
            <div class="notification-icon ${notif.type}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20">
                    ${notif.type === 'success' ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>' : 
                      notif.type === 'warning' ? '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>' :
                      '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>'}
                </svg>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notif.title}</div>
                <div class="notification-message">${notif.message}</div>
                <div class="notification-time">${notif.time}</div>
            </div>
        </div>
    `).join('');
}

// Mark Notification as Read
function markAsRead(id) {
    const notif = state.notifications.find(n => n.id === id);
    if (notif) {
        notif.read = true;
        renderNotifications();
        updateNotificationBadge();
    }
}

// Mark All as Read
function markAllRead() {
    state.notifications.forEach(n => n.read = true);
    renderNotifications();
    updateNotificationBadge();
    showToast('All notifications marked as read', 'success');
}

// Update Notification Badge
function updateNotificationBadge() {
    const badge = document.querySelector('.icon-btn .badge');
    const unreadCount = state.notifications.filter(n => !n.read).length;
    
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
        } else {
            badge.remove();
        }
    }
}

// Setup Mobile Menu
function setupMobileMenu() {
    const header = document.querySelector('.top-header h1');
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="24" height="24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
    
    header.insertBefore(menuToggle, header.firstChild);
    
    menuToggle.addEventListener('click', () => {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('expanded');
    });
    
    if (window.innerWidth <= 1024) {
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    }
}

// Load User Data
async function loadUserData() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=profile`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.profile) {
            document.getElementById('profile_full_name').value = data.profile.full_name || '';
            document.getElementById('profile_career_goal').value = data.profile.career_goal || '';
            document.getElementById('profile_sector').value = data.profile.sector_focus || 'healthcare';
        }
    } catch (error) {
        console.error('Load user data error:', error);
    }
}

// Load Stats with Animation
async function loadStats() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=stats`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.stats) {
            animateNumber('totalSkills', 0, data.stats.total_skills || 0);
            animateNumber('totalCourses', 0, data.stats.total_courses || 0);
            animateNumber('totalProjects', 0, data.stats.total_projects || 0);
            animateNumber('totalGaps', 0, data.stats.skill_gaps || 0);
        }
    } catch (error) {
        console.error('Load stats error:', error);
    }
}

// Animate Number
function animateNumber(elementId, start, end, duration = 1000) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Setup Navigation
function setupNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = item.dataset.tab;
            
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tab}-tab`).classList.add('active');
            
            const titles = {
                'overview': 'Dashboard Overview',
                'skills': 'Your Skills',
                'courses': 'Your Courses',
                'projects': 'Your Projects',
                'recommendations': 'AI Recommendations',
                'gaps': 'Skill Gap Analysis',
                'profile': 'Profile Settings'
            };
            document.getElementById('pageTitle').textContent = titles[tab] || 'Dashboard';
            
            loadTabData(tab);
            
            if (window.innerWidth <= 1024) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        });
    });
}

// Switch Tab Programmatically
function switchTab(tab) {
    const navItem = document.querySelector(`.nav-item[data-tab="${tab}"]`);
    if (navItem) {
        navItem.click();
    }
}

// Load Tab Data
async function loadTabData(tab) {
    switch(tab) {
        case 'overview':
            await loadAIInsights();
            break;
        case 'skills':
            await loadSkills();
            break;
        case 'courses':
            await loadCourses();
            break;
        case 'projects':
            await loadProjects();
            break;
        case 'recommendations':
            await loadRecommendations();
            break;
        case 'gaps':
            await loadGaps();
            break;
    }
}

// Load AI Insights for Overview
async function loadAIInsights() {
    const container = document.getElementById('aiInsights');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-state">Analyzing your profile with AI...</div>';
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=recommendations&action=generate`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.recommendations && data.recommendations.length > 0) {
            const topRecommendations = data.recommendations.slice(0, 3);
            
            container.innerHTML = `
                <div class="ai-insights-grid">
                    ${topRecommendations.map(rec => `
                        <div class="insight-card" style="animation: fadeIn 0.5s ease forwards; opacity: 0;">
                            <div class="insight-header">
                                <div class="insight-icon ${rec.recommendation_type}">
                                    ${getRecommendationIcon(rec.recommendation_type)}
                                </div>
                                <span class="insight-score">${Math.round(rec.relevance_score)}%</span>
                            </div>
                            <h3>${rec.title}</h3>
                            <p>${rec.description}</p>
                            <div class="insight-footer">
                                <span class="insight-source">${rec.source}</span>
                                <button class="btn-small" onclick="exploreRecommendation(${rec.recommendation_id})">Explore</button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button class="btn-primary" style="margin-top: 1rem;" onclick="switchTab('recommendations')">
                    View All Recommendations
                </button>
            `;
            
            container.querySelectorAll('.insight-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p>Add your skills to get AI-powered recommendations</p>
                    <button class="btn-primary" onclick="switchTab('skills')">Add Skills</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Load AI insights error:', error);
        container.innerHTML = '<div class="error-state">Failed to load AI insights. Please try again.</div>';
    }
}

function getRecommendationIcon(type) {
    const icons = {
        'course': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        'project': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>',
        'skill': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
        'certification': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
    };
    return icons[type] || icons['course'];
}

function exploreRecommendation(id) {
    showToast('Opening recommendation details...', 'info');
    switchTab('recommendations');
}

// Load Skills
async function loadSkills() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=skills`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('skillsList');
        
        if (data.success && data.skills.length > 0) {
            container.innerHTML = data.skills.map(skill => `
                <div class="skill-card" style="animation: fadeIn 0.5s ease forwards; opacity: 0;">
                    <div class="skill-header">
                        <div class="skill-name">${skill.skill_name}</div>
                        <span class="skill-level ${skill.proficiency_level}">${skill.proficiency_level}</span>
                    </div>
                    ${skill.category ? `<div class="skill-category">${skill.category}</div>` : ''}
                    <div class="skill-actions">
                        <button class="btn-small" onclick="editSkill(${skill.skill_id})">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Edit
                        </button>
                        <button class="btn-small" onclick="deleteSkill(${skill.skill_id})" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            `).join('');
            
            container.querySelectorAll('.skill-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p>No skills added yet. Start by adding your first skill!</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Load skills error:', error);
        showToast('Error loading skills', 'error');
    }
}

async function loadCourses() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=courses`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('coursesList');
        
        if (data.success && data.courses.length > 0) {
            container.innerHTML = data.courses.map((course, index) => `
                <div class="card" style="margin-bottom: 1rem; animation: fadeIn 0.5s ease forwards ${index * 0.1}s; opacity: 0;">
                    <h3>${course.course_name}</h3>
                    <p><strong>Institution:</strong> ${course.institution || 'N/A'}</p>
                    <p><strong>Status:</strong> <span class="skill-level ${course.completion_status}">${course.completion_status.replace('_', ' ')}</span></p>
                    ${course.grade ? `<p><strong>Grade:</strong> ${course.grade}</p>` : ''}
                    <div class="skill-actions" style="margin-top: 1rem;">
                        <button class="btn-small" onclick="editCourse(${course.course_id})">Edit</button>
                        <button class="btn-small" onclick="deleteCourse(${course.course_id})" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Delete</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No courses added yet</p></div>';
        }
    } catch (error) {
        console.error('Load courses error:', error);
        showToast('Error loading courses', 'error');
    }
}

async function loadProjects() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=projects`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('projectsList');
        
        if (data.success && data.projects.length > 0) {
            container.innerHTML = data.projects.map((project, index) => `
                <div class="card" style="margin-bottom: 1rem; animation: fadeIn 0.5s ease forwards ${index * 0.1}s; opacity: 0;">
                    <h3>${project.project_name}</h3>
                    <p>${project.description || 'No description'}</p>
                    ${project.technologies_used ? `<p><strong>Technologies:</strong> ${project.technologies_used}</p>` : ''}
                    ${project.project_url ? `<p><strong>URL:</strong> <a href="${project.project_url}" target="_blank">${project.project_url}</a></p>` : ''}
                    <div class="skill-actions" style="margin-top: 1rem;">
                        <button class="btn-small" onclick="editProject(${project.project_id})">Edit</button>
                        <button class="btn-small" onclick="deleteProject(${project.project_id})" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Delete</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No projects added yet</p></div>';
        }
    } catch (error) {
        console.error('Load projects error:', error);
        showToast('Error loading projects', 'error');
    }
}

// Load AI-Powered Recommendations
async function loadRecommendations() {
    const container = document.getElementById('recommendationsList');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-state">Generating AI recommendations...</div>';
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=recommendations&action=generate`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.recommendations && data.recommendations.length > 0) {
            container.innerHTML = data.recommendations.map((rec, index) => `
                <div class="recommendation-card" style="animation: fadeIn 0.5s ease forwards ${index * 0.1}s; opacity: 0;">
                    <div class="rec-header">
                        <div class="rec-type ${rec.recommendation_type}">
                            ${getRecommendationIcon(rec.recommendation_type)}
                            <span>${rec.recommendation_type}</span>
                        </div>
                        <div class="rec-score">${Math.round(rec.relevance_score)}% Match</div>
                    </div>
                    <h3>${rec.title}</h3>
                    <p>${rec.description}</p>
                    <div class="rec-footer">
                        <span class="rec-source">Source: ${rec.source}</span>
                        <button class="btn-primary" onclick="enrollInRecommendation('${rec.title}', '${rec.recommendation_type}')">
                            ${rec.recommendation_type === 'course' ? 'Enroll Now' : 'Start Now'}
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p>No recommendations available. Add your skills first!</p>
                    <button class="btn-primary" onclick="switchTab('skills')">Add Skills</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Load recommendations error:', error);
        container.innerHTML = '<div class="error-state">Failed to load recommendations. Please try again.</div>';
    }
}

// CONTINUATION OF ENHANCED DASHBOARD JS

function enrollInRecommendation(title, type) {
    showToast(`Opening ${title}...`, 'info');
    // This would typically open the course/project/certification
}

// Load Skill Gaps with AI Analysis
async function loadGaps() {
    const container = document.getElementById('gapsList');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-state">Analyzing skill gaps with AI...</div>';
    
    try {
        // First analyze gaps
        await fetch(`${API_BASE}/dashboard.php?endpoint=gaps&action=analyze`, {
            credentials: 'include'
        });
        
        // Then fetch the gaps
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=gaps`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.gaps && data.gaps.length > 0) {
            container.innerHTML = `
                <div class="gaps-summary">
                    <div class="summary-card critical">
                        <div class="summary-number">${data.gaps.filter(g => g.gap_severity === 'critical').length}</div>
                        <div class="summary-label">Critical Gaps</div>
                    </div>
                    <div class="summary-card high">
                        <div class="summary-number">${data.gaps.filter(g => g.gap_severity === 'high').length}</div>
                        <div class="summary-label">High Priority</div>
                    </div>
                    <div class="summary-card medium">
                        <div class="summary-number">${data.gaps.filter(g => g.gap_severity === 'medium').length}</div>
                        <div class="summary-label">Medium Priority</div>
                    </div>
                </div>
                <div class="gaps-list">
                    ${data.gaps.map((gap, index) => `
                        <div class="gap-card ${gap.gap_severity}" style="animation: fadeIn 0.5s ease forwards ${index * 0.1}s; opacity: 0;">
                            <div class="gap-header">
                                <h3>${gap.skill_name}</h3>
                                <span class="gap-severity-badge ${gap.gap_severity}">${gap.gap_severity}</span>
                            </div>
                            <div class="gap-levels">
                                <div class="level-info">
                                    <span class="level-label">Current:</span>
                                    <span class="level-value ${gap.current_level}">${gap.current_level}</span>
                                </div>
                                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                                <div class="level-info">
                                    <span class="level-label">Required:</span>
                                    <span class="level-value ${gap.required_level}">${gap.required_level}</span>
                                </div>
                            </div>
                            <div class="gap-actions">
                                <button class="btn-primary" onclick="findLearningResources('${gap.skill_name}', '${gap.gap_severity}')">
                                    Find Learning Resources
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <p>No skill gaps found! You're on track with your career goals.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Load gaps error:', error);
        container.innerHTML = '<div class="error-state">Failed to analyze skill gaps. Please try again.</div>';
    }
}

// Find Learning Resources for Skill Gaps
async function findLearningResources(skillName, severity) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Learning Resources for ${skillName}</h2>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading-state">Finding best resources...</div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Fetch AI-powered learning resources
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=recommendations&action=generate`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.recommendations) {
            const skillResources = data.recommendations.filter(rec => 
                rec.title.toLowerCase().includes(skillName.toLowerCase()) ||
                rec.description.toLowerCase().includes(skillName.toLowerCase())
            );
            
            const modalBody = modal.querySelector('.modal-body');
            
            if (skillResources.length > 0) {
                modalBody.innerHTML = `
                    <p class="resource-intro">Here are the top AI-recommended resources to help you master <strong>${skillName}</strong>:</p>
                    <div class="resources-grid">
                        ${skillResources.map(resource => `
                            <div class="resource-card">
                                <div class="resource-header">
                                    <div class="resource-type ${resource.recommendation_type}">
                                        ${getRecommendationIcon(resource.recommendation_type)}
                                        <span>${resource.recommendation_type}</span>
                                    </div>
                                    <div class="resource-match">${Math.round(resource.relevance_score)}% Match</div>
                                </div>
                                <h3>${resource.title}</h3>
                                <p>${resource.description}</p>
                                <div class="resource-footer">
                                    <span class="resource-source">${resource.source}</span>
                                    <button class="btn-primary" onclick="window.open('https://www.coursera.org/search?query=${encodeURIComponent(skillName)}', '_blank')">
                                        Start Learning
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="additional-resources">
                        <h3>Additional Learning Platforms:</h3>
                        <div class="platform-links">
                            <a href="https://www.coursera.org/search?query=${encodeURIComponent(skillName)}" target="_blank" class="platform-link coursera">
                                <span>Coursera</span>
                            </a>
                            <a href="https://www.udemy.com/courses/search/?q=${encodeURIComponent(skillName)}" target="_blank" class="platform-link udemy">
                                <span>Udemy</span>
                            </a>
                            <a href="https://www.edx.org/search?q=${encodeURIComponent(skillName)}" target="_blank" class="platform-link edx">
                                <span>edX</span>
                            </a>
                            <a href="https://www.youtube.com/results?search_query=${encodeURIComponent(skillName + ' tutorial')}" target="_blank" class="platform-link youtube">
                                <span>YouTube</span>
                            </a>
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <p>We're searching for the best resources for <strong>${skillName}</strong>:</p>
                    <div class="platform-links" style="margin-top: 1.5rem;">
                        <a href="https://www.coursera.org/search?query=${encodeURIComponent(skillName)}" target="_blank" class="platform-link coursera">
                            <span>Search on Coursera</span>
                        </a>
                        <a href="https://www.udemy.com/courses/search/?q=${encodeURIComponent(skillName)}" target="_blank" class="platform-link udemy">
                            <span>Search on Udemy</span>
                        </a>
                        <a href="https://www.edx.org/search?q=${encodeURIComponent(skillName)}" target="_blank" class="platform-link edx">
                            <span>Search on edX</span>
                        </a>
                        <a href="https://www.linkedin.com/learning/search?keywords=${encodeURIComponent(skillName)}" target="_blank" class="platform-link linkedin">
                            <span>Search on LinkedIn Learning</span>
                        </a>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error fetching resources:', error);
        modal.querySelector('.modal-body').innerHTML = '<div class="error-state">Failed to load resources. Please try again.</div>';
    }
}

// Analyze Gaps Button Handler
async function analyzeGaps() {
    showToast('Analyzing your skill gaps...', 'info');
    await loadGaps();
    showToast('Analysis complete!', 'success');
}

// Generate Recommendations Button Handler
async function generateRecommendations() {
    showToast('Generating AI recommendations...', 'info');
    await loadRecommendations();
    showToast('Recommendations updated!', 'success');
}

// Setup Add Buttons
function setupAddButtons() {
    // Add Skill Modal
    window.openSkillModal = function() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Skill</h2>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
                </div>
                <form class="modal-form" onsubmit="submitSkill(event)">
                    <div class="form-group">
                        <label>Skill Name</label>
                        <input type="text" name="skill_name" required placeholder="e.g., JavaScript">
                    </div>
                    <div class="form-group">
                        <label>Proficiency Level</label>
                        <select name="proficiency_level" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" placeholder="e.g., Programming">
                    </div>
                    <button type="submit" class="btn-primary">Add Skill</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    };
    
    // Add Course Modal
    window.openCourseModal = function() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Course</h2>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
                </div>
                <form class="modal-form" onsubmit="submitCourse(event)">
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="course_name" required>
                    </div>
                    <div class="form-group">
                        <label>Institution</label>
                        <input type="text" name="institution">
                    </div>
                    <div class="form-group">
                        <label>Completion Status</label>
                        <select name="completion_status">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="planned">Planned</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Add Course</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    };
    
    // Add Project Modal
    window.openProjectModal = function() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Project</h2>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
                </div>
                <form class="modal-form" onsubmit="submitProject(event)">
                    <div class="form-group">
                        <label>Project Name</label>
                        <input type="text" name="project_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Technologies Used</label>
                        <input type="text" name="technologies_used" placeholder="e.g., React, Node.js, MongoDB">
                    </div>
                    <div class="form-group">
                        <label>Project URL</label>
                        <input type="url" name="project_url" placeholder="https://...">
                    </div>
                    <button type="submit" class="btn-primary">Add Project</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    };
}

// Submit Handlers
window.submitSkill = async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=skills`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Skill added successfully!', 'success');
            e.target.closest('.modal-overlay').remove();
            await loadSkills();
            await loadStats();
        } else {
            showToast('Failed to add skill', 'error');
        }
    } catch (error) {
        console.error('Error adding skill:', error);
        showToast('Error adding skill', 'error');
    }
};

window.submitCourse = async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=courses`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Course added successfully!', 'success');
            e.target.closest('.modal-overlay').remove();
            await loadCourses();
            await loadStats();
        } else {
            showToast('Failed to add course', 'error');
        }
    } catch (error) {
        console.error('Error adding course:', error);
        showToast('Error adding course', 'error');
    }
};

window.submitProject = async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=projects`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Project added successfully!', 'success');
            e.target.closest('.modal-overlay').remove();
            await loadProjects();
            await loadStats();
        } else {
            showToast('Failed to add project', 'error');
        }
    } catch (error) {
        console.error('Error adding project:', error);
        showToast('Error adding project', 'error');
    }
};

// Edit/Delete Handlers
window.editSkill = function(id) {
    showToast('Edit skill feature - Coming soon!', 'info');
};

window.deleteSkill = async function(id) {
    if (!confirm('Are you sure you want to delete this skill?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=skills&id=${id}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Skill deleted successfully!', 'success');
            await loadSkills();
            await loadStats();
        } else {
            showToast('Failed to delete skill', 'error');
        }
    } catch (error) {
        console.error('Error deleting skill:', error);
        showToast('Error deleting skill', 'error');
    }
};

window.editCourse = function(id) {
    showToast('Edit course feature - Coming soon!', 'info');
};

window.deleteCourse = async function(id) {
    if (!confirm('Are you sure you want to delete this course?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=courses&id=${id}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Course deleted successfully!', 'success');
            await loadCourses();
            await loadStats();
        } else {
            showToast('Failed to delete course', 'error');
        }
    } catch (error) {
        console.error('Error deleting course:', error);
        showToast('Error deleting course', 'error');
    }
};

window.editProject = function(id) {
    showToast('Edit project feature - Coming soon!', 'info');
};

window.deleteProject = async function(id) {
    if (!confirm('Are you sure you want to delete this project?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=projects&id=${id}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Project deleted successfully!', 'success');
            await loadProjects();
            await loadStats();
        } else {
            showToast('Failed to delete project', 'error');
        }
    } catch (error) {
        console.error('Error deleting project:', error);
        showToast('Error deleting project', 'error');
    }
};

// Setup Profile Form
function setupProfileForm() {
    const form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const data = {
                full_name: document.getElementById('profile_full_name').value,
                career_goal: document.getElementById('profile_career_goal').value,
                sector_focus: document.getElementById('profile_sector').value
            };
            
            try {
                const response = await fetch(`${API_BASE}/dashboard.php?endpoint=profile`, {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Profile updated successfully!', 'success');
                } else {
                    showToast('Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Profile update error:', error);
                showToast('Error updating profile', 'error');
            }
        });
    }
}

// Setup Logout
function setupLogout() {
    document.getElementById('logoutBtn').addEventListener('click', async () => {
        if (confirm('Are you sure you want to logout?')) {
            try {
                await fetch(`${API_BASE}/auth.php?action=logout`, {
                    credentials: 'include'
                });
                showToast('Logged out successfully', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1000);
            } catch (error) {
                console.error('Logout error:', error);
                showToast('Error logging out', 'error');
            }
        }
    });
}

// Toast Notification System
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideInUp 0.3s ease, slideOutDown 0.3s ease 2.7s;
        font-weight: 500;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Utility: Debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .platform-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .platform-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .platform-link.coursera {
        background: #0056D2;
        color: white;
    }
    
    .platform-link.udemy {
        background: #A435F0;
        color: white;
    }
    
    .platform-link.edx {
        background: #02262B;
        color: white;
    }
    
    .platform-link.youtube {
        background: #FF0000;
        color: white;
    }
    
    .platform-link.linkedin {
        background: #0077B5;
        color: white;
    }
    
    .platform-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
`;
document.head.appendChild(style);

console.log('✅ Enhanced Dashboard with AI/ML Features Loaded Successfully!');