// File: js/admin.js
// Admin Dashboard JavaScript

const API_BASE = window.location.origin + '/skill-platform/api';

// Check admin auth on page load
document.addEventListener('DOMContentLoaded', async () => {
    await checkAdminAuth();
    await loadAdminStats();
    setupAdminNavigation();
    setupAdminLogout();
});

async function checkAdminAuth() {
    try {
        const response = await fetch(`${API_BASE}/auth.php?action=check`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (!data.logged_in || !data.is_admin) {
            window.location.href = 'login.html';
            return;
        }
        
        // Update admin display
        document.getElementById('adminName').textContent = data.full_name;
        const initials = data.full_name.split(' ').map(n => n[0]).join('');
        document.querySelector('.admin-avatar').textContent = initials;
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'login.html';
    }
}

async function loadAdminStats() {
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=stats`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.stats) {
            document.getElementById('totalUsers').textContent = data.stats.total_users || 0;
            document.getElementById('activeUsers').textContent = data.stats.active_users || 0;
            document.getElementById('totalSkillsAdmin').textContent = data.stats.total_skills || 0;
            document.getElementById('totalCoursesAdmin').textContent = data.stats.total_courses || 0;
            
            document.getElementById('userChange').textContent = `+${data.stats.new_users_month || 0} this month`;
            document.getElementById('activeChange').textContent = `${data.stats.active_users || 0} today`;
        }
    } catch (error) {
        console.error('Load stats error:', error);
    }
}

function setupAdminNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', async (e) => {
            e.preventDefault();
            const tab = item.dataset.tab;
            
            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Show corresponding tab
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tab}-tab`).classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Admin Dashboard',
                'users': 'User Management',
                'analytics': 'Platform Analytics',
                'skills': 'Skills Framework',
                'settings': 'Platform Settings'
            };
            document.getElementById('adminPageTitle').textContent = titles[tab] || 'Admin Panel';
            
            // Load tab-specific data
            await loadTabData(tab);
        });
    });
}

async function loadTabData(tab) {
    switch(tab) {
        case 'dashboard':
            await loadDashboardCharts();
            await loadRecentActivity();
            break;
        case 'users':
            await loadUsers();
            break;
        case 'analytics':
            await loadAnalytics();
            break;
        case 'skills':
            await loadSkillsFramework();
            break;
    }
}

async function loadUsers() {
    const search = document.getElementById('userSearch')?.value || '';
    const sector = document.getElementById('sectorFilter')?.value || '';
    
    try {
        const response = await fetch(
            `${API_BASE}/admin.php?endpoint=users&search=${search}&sector=${sector}`,
            { credentials: 'include' }
        );
        const data = await response.json();
        
        if (data.success && data.users) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = data.users.map(user => `
                <tr>
                    <td>${user.user_id}</td>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td><span class="badge ${user.sector_focus}">${formatSector(user.sector_focus)}</span></td>
                    <td>${user.career_goal || 'Not set'}</td>
                    <td>${user.skills_count || 0}</td>
                    <td>${user.courses_count || 0}</td>
                    <td>${formatDate(user.created_at)}</td>
                    <td>${formatDate(user.last_login) || 'Never'}</td>
                    <td>
                        <button class="action-btn view" onclick="viewUser(${user.user_id})">View</button>
                        <button class="action-btn delete" onclick="deleteUser(${user.user_id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Load users error:', error);
    }
}

// Add event listeners for search and filter
if (document.getElementById('userSearch')) {
    document.getElementById('userSearch').addEventListener('input', debounce(loadUsers, 500));
}
if (document.getElementById('sectorFilter')) {
    document.getElementById('sectorFilter').addEventListener('change', loadUsers);
}

async function loadDashboardCharts() {
    try {
        // User Growth Chart
        const growthResponse = await fetch(`${API_BASE}/admin.php?endpoint=user-growth`, {
            credentials: 'include'
        });
        const growthData = await growthResponse.json();
        
        if (growthData.success && growthData.growth) {
            createUserGrowthChart(growthData.growth);
        }
        
        // Sector Distribution Chart
        const sectorResponse = await fetch(`${API_BASE}/admin.php?endpoint=sector-distribution`, {
            credentials: 'include'
        });
        const sectorData = await sectorResponse.json();
        
        if (sectorData.success && sectorData.distribution) {
            createSectorChart(sectorData.distribution);
        }
    } catch (error) {
        console.error('Load charts error:', error);
    }
}

function createUserGrowthChart(data) {
    const ctx = document.getElementById('userGrowthChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => formatDate(d.date)),
            datasets: [{
                label: 'New Users',
                data: data.map(d => d.count),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createSectorChart(data) {
    const ctx = document.getElementById('sectorChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => formatSector(d.sector_focus)),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [
                    '#6366f1',
                    '#10b981',
                    '#f59e0b'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

async function loadAnalytics() {
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=analytics`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.analytics) {
            createSkillsBySectorChart(data.analytics.skills_by_sector);
            createTopSkillsChart(data.analytics.top_skills);
            createCompletionChart(data.analytics.course_completion);
        }
    } catch (error) {
        console.error('Load analytics error:', error);
    }
}

function createSkillsBySectorChart(data) {
    const ctx = document.getElementById('skillsBySectorChart');
    if (!ctx || !data) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => formatSector(d.sector)),
            datasets: [{
                label: 'Skills Count',
                data: data.map(d => d.count),
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function createTopSkillsChart(data) {
    const ctx = document.getElementById('topSkillsChart');
    if (!ctx || !data) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.skill_name),
            datasets: [{
                label: 'Users with Skill',
                data: data.map(d => d.count),
                backgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y'
        }
    });
}

function createCompletionChart(data) {
    const ctx = document.getElementById('completionChart');
    if (!ctx || !data) return;
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(d => d.completion_status.replace('_', ' ')),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: ['#10b981', '#f59e0b', '#6366f1']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

async function loadRecentActivity() {
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=recent-activity`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.activity) {
            const container = document.getElementById('recentActivityList');
            container.innerHTML = data.activity.map(act => `
                <div class="activity-item">
                    <div class="activity-info">
                        <div class="activity-user">${act.full_name}</div>
                        <div class="activity-action">${act.activity}</div>
                    </div>
                    <div class="activity-time">${formatDate(act.activity_time)}</div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Load activity error:', error);
    }
}

async function loadSkillsFramework() {
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=skills-framework`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.skills) {
            const tbody = document.getElementById('skillsTableBody');
            tbody.innerHTML = data.skills.map(skill => `
                <tr>
                    <td>${skill.skill_name}</td>
                    <td><span class="badge ${skill.sector}">${formatSector(skill.sector)}</span></td>
                    <td>${skill.category}</td>
                    <td><span class="badge ${skill.importance_level}">${skill.importance_level}</span></td>
                    <td>${skill.description || 'N/A'}</td>
                    <td>
                        <button class="action-btn view" onclick="editSkill(${skill.framework_id})">Edit</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Load skills error:', error);
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=users&id=${userId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            alert('User deleted successfully');
            await loadUsers();
            await loadAdminStats();
        } else {
            alert('Failed to delete user');
        }
    } catch (error) {
        console.error('Delete user error:', error);
        alert('Error deleting user');
    }
}

function viewUser(userId) {
    // Implement view user details
    alert(`View user ${userId} - Coming soon`);
}

function setupAdminLogout() {
    document.getElementById('adminLogoutBtn').addEventListener('click', async () => {
        try {
            await fetch(`${API_BASE}/auth.php?action=logout`, {
                credentials: 'include'
            });
            sessionStorage.clear();
            window.location.href = 'login.html';
        } catch (error) {
            console.error('Logout error:', error);
        }
    });
}

async function refreshData() {
    const activeTab = document.querySelector('.nav-item.active').dataset.tab;
    await loadAdminStats();
    await loadTabData(activeTab);
}

// Utility Functions
function formatDate(dateString) {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatSector(sector) {
    const sectors = {
        'healthcare': 'Healthcare',
        'agriculture': 'Agriculture',
        'urban_planning': 'Urban Planning'
    };
    return sectors[sector] || sector;
}

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