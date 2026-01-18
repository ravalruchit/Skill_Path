// File: js/admin.js
// Complete Admin Dashboard JavaScript - All Features Included

const API_BASE = 'http://localhost/dashboard/Skill-platform/api';

document.addEventListener('DOMContentLoaded', async () => {
    await checkAdminAuth();
    await loadAdminStats();
    setupAdminNavigation();
    setupAdminLogout();
    addNotificationStyles();
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
            
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tab}-tab`).classList.add('active');
            
            const titles = {
                'dashboard': 'Admin Dashboard',
                'users': 'User Management',
                'analytics': 'Platform Analytics',
                'skills': 'Skills Framework',
                'settings': 'Platform Settings'
            };
            document.getElementById('adminPageTitle').textContent = titles[tab] || 'Admin Panel';
            
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

if (document.getElementById('userSearch')) {
    document.getElementById('userSearch').addEventListener('input', debounce(loadUsers, 500));
}
if (document.getElementById('sectorFilter')) {
    document.getElementById('sectorFilter').addEventListener('change', loadUsers);
}

async function loadDashboardCharts() {
    try {
        // Load User Growth
        const growthResponse = await fetch(`${API_BASE}/admin.php?endpoint=user-growth`, {
            credentials: 'include'
        });
        const growthData = await growthResponse.json();
        
        if (growthData.success && growthData.growth && growthData.growth.length > 0) {
            createUserGrowthChart(growthData.growth);
        }
        
        // Load Sector Distribution
        const sectorResponse = await fetch(`${API_BASE}/admin.php?endpoint=sector-distribution`, {
            credentials: 'include'
        });
        const sectorData = await sectorResponse.json();
        
        if (sectorData.success && sectorData.distribution && sectorData.distribution.length > 0) {
            createSectorChart(sectorData.distribution);
        }
    } catch (error) {
        console.error('Load charts error:', error);
    }
}

function createUserGrowthChart(data) {
    const ctx = document.getElementById('userGrowthChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.userGrowthChartInstance) {
        window.userGrowthChartInstance.destroy();
    }
    
    window.userGrowthChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => formatDate(d.date)),
            datasets: [{
                label: 'New Users',
                data: data.map(d => d.count),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

function createSectorChart(data) {
    const ctx = document.getElementById('sectorChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.sectorChartInstance) {
        window.sectorChartInstance.destroy();
    }
    
    window.sectorChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => formatSector(d.sector_focus)),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [
                    '#6366f1',
                    '#10b981',
                    '#f59e0b'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
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
            if (data.analytics.skills_by_sector) {
                createSkillsBySectorChart(data.analytics.skills_by_sector);
            }
            if (data.analytics.top_skills) {
                createTopSkillsChart(data.analytics.top_skills);
            }
            if (data.analytics.course_completion) {
                createCompletionChart(data.analytics.course_completion);
            }
        }
    } catch (error) {
        console.error('Load analytics error:', error);
    }
}

function createSkillsBySectorChart(data) {
    const ctx = document.getElementById('skillsBySectorChart');
    if (!ctx || !data || data.length === 0) return;
    
    if (window.skillsBySectorChartInstance) {
        window.skillsBySectorChartInstance.destroy();
    }
    
    window.skillsBySectorChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => formatSector(d.sector)),
            datasets: [{
                label: 'Skills Count',
                data: data.map(d => d.count),
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.8,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function createTopSkillsChart(data) {
    const ctx = document.getElementById('topSkillsChart');
    if (!ctx || !data || data.length === 0) return;
    
    if (window.topSkillsChartInstance) {
        window.topSkillsChartInstance.destroy();
    }
    
    window.topSkillsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.skill_name),
            datasets: [{
                label: 'Users with Skill',
                data: data.map(d => d.count),
                backgroundColor: '#6366f1',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.2,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function createCompletionChart(data) {
    const ctx = document.getElementById('completionChart');
    if (!ctx || !data || data.length === 0) return;
    
    if (window.completionChartInstance) {
        window.completionChartInstance.destroy();
    }
    
    window.completionChartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(d => d.completion_status.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: ['#10b981', '#f59e0b', '#6366f1'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
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
            if (data.activity.length > 0) {
                container.innerHTML = data.activity.map(act => `
                    <div class="activity-item">
                        <div class="activity-info">
                            <div class="activity-user">${act.full_name}</div>
                            <div class="activity-action">${act.activity}</div>
                        </div>
                        <div class="activity-time">${formatDate(act.activity_time)}</div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="empty-state"><p>No recent activity</p></div>';
            }
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
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin.php?endpoint=users&id=${userId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification('User deleted successfully', 'success');
            await loadUsers();
            await loadAdminStats();
        } else {
            showNotification('Failed to delete user', 'error');
        }
    } catch (error) {
        console.error('Delete user error:', error);
        showNotification('Error deleting user', 'error');
    }
}

function viewUser(userId) {
    showNotification(`View user details (ID: ${userId}) - Feature coming soon!`, 'info');
}

function editSkill(frameworkId) {
    showNotification(`Edit skill framework (ID: ${frameworkId}) - Feature coming soon!`, 'info');
}

function addFrameworkSkill() {
    showNotification('Add framework skill - Feature coming soon!', 'info');
}

function setupAdminLogout() {
    document.getElementById('adminLogoutBtn').addEventListener('click', async () => {
        if (!confirm('Are you sure you want to logout?')) return;
        
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
    const btn = event?.target;
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }
    
    const activeTab = document.querySelector('.nav-item.active')?.dataset.tab || 'dashboard';
    
    try {
        await loadAdminStats();
        await loadTabData(activeTab);
        showNotification('Data refreshed successfully', 'success');
    } catch (error) {
        console.error('Refresh error:', error);
        showNotification('Failed to refresh data', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }
}

// ============================================
// EXPORT FUNCTIONS
// ============================================

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    
    // Create overlay if it doesn't exist
    let overlay = document.getElementById('exportOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'exportOverlay';
        overlay.className = 'export-overlay';
        overlay.onclick = closeExportMenu;
        document.body.appendChild(overlay);
    }
    
    menu.classList.toggle('active');
    overlay.classList.toggle('active');
}

function closeExportMenu() {
    const menu = document.getElementById('exportMenu');
    const overlay = document.getElementById('exportOverlay');
    
    if (menu) menu.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
}

async function exportData(type) {
    closeExportMenu();
    
    // Show loading state
    const exportBtn = event?.target?.closest('.btn-export, .btn-export-small');
    if (exportBtn) {
        exportBtn.classList.add('loading');
        exportBtn.disabled = true;
    }
    
    try {
        // Create export URL
        const exportUrl = `${API_BASE}/export.php?type=${type}`;
        
        // Open in new window to trigger download
        window.open(exportUrl, '_blank');
        
        // Show success message
        setTimeout(() => {
            showNotification(`Exporting ${formatExportType(type)}...`, 'success');
            
            if (exportBtn) {
                exportBtn.classList.remove('loading');
                exportBtn.disabled = false;
            }
        }, 500);
        
    } catch (error) {
        console.error('Export error:', error);
        showNotification('Export failed. Please try again.', 'error');
        
        if (exportBtn) {
            exportBtn.classList.remove('loading');
            exportBtn.disabled = false;
        }
    }
}

function formatExportType(type) {
    const types = {
        'users': 'Users Data',
        'skills': 'Skills Data',
        'courses': 'Courses Data',
        'projects': 'Projects Data',
        'analytics': 'Analytics Report',
        'all': 'Complete Platform Data'
    };
    return types[type] || type;
}

function exportCurrentTab() {
    const activeTab = document.querySelector('.nav-item.active')?.dataset.tab;
    
    const tabExportMap = {
        'users': 'users',
        'analytics': 'analytics',
        'skills': 'skills'
    };
    
    const exportType = tabExportMap[activeTab] || 'all';
    exportData(exportType);
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Add icon based on type
    const icons = {
        'success': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        'error': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        'info': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };
    
    notification.innerHTML = `
        ${icons[type]}
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function addNotificationStyles() {
    const notificationStyles = document.createElement('style');
    notificationStyles.id = 'notification-styles';
    notificationStyles.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 300px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        }
        
        .notification svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .notification span {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .notification button {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .notification button:hover {
            background: #f1f5f9;
        }
        
        .notification-success {
            border-left: 4px solid #10b981;
        }
        
        .notification-success svg {
            color: #10b981;
        }
        
        .notification-error {
            border-left: 4px solid #ef4444;
        }
        
        .notification-error svg {
            color: #ef4444;
        }
        
        .notification-info {
            border-left: 4px solid #6366f1;
        }
        
        .notification-info svg {
            color: #6366f1;
        }
        
        .notification.fade-out {
            animation: slideOutRight 0.3s ease;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        @media (max-width: 640px) {
            .notification {
                left: 20px;
                right: 20px;
                min-width: auto;
            }
        }
    `;
    
    if (!document.getElementById('notification-styles')) {
        document.head.appendChild(notificationStyles);
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

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

// ============================================
// EVENT LISTENERS
// ============================================

// Close export menu when clicking outside
document.addEventListener('click', (e) => {
    const exportDropdown = document.querySelector('.export-dropdown');
    const exportMenu = document.getElementById('exportMenu');
    
    if (exportDropdown && exportMenu && !exportDropdown.contains(e.target)) {
        closeExportMenu();
    }
});

// Keyboard shortcut for export (Ctrl+E or Cmd+E)
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        exportCurrentTab();
    }
});

// Console welcome message
console.log('%c🎉 Admin Panel Loaded Successfully!', 'color: #6366f1; font-size: 16px; font-weight: bold;');
console.log('%c📊 Export shortcut: Ctrl+E or Cmd+E', 'color: #10b981; font-size: 12px;');
console.log('%c🔄 Refresh: Click refresh button or reload page', 'color: #10b981; font-size: 12px;');