// File: js/dashboard.js
// Dashboard Functionality

const API_BASE = 'http://localhost/skill-platform/api';

// Check authentication on page load
document.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    await loadUserData();
    await loadStats();
    setupNavigation();
    setupLogout();
});

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
        
        // Update user display
        const initials = data.full_name.split(' ').map(n => n[0]).join('');
        document.getElementById('userInitials').textContent = initials;
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'login.html';
    }
}

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

async function loadStats() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=stats`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.stats) {
            document.getElementById('totalSkills').textContent = data.stats.total_skills || 0;
            document.getElementById('totalCourses').textContent = data.stats.total_courses || 0;
            document.getElementById('totalProjects').textContent = data.stats.total_projects || 0;
            document.getElementById('totalGaps').textContent = data.stats.skill_gaps || 0;
        }
    } catch (error) {
        console.error('Load stats error:', error);
    }
}

function setupNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
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
                'overview': 'Dashboard Overview',
                'skills': 'Your Skills',
                'courses': 'Your Courses',
                'projects': 'Your Projects',
                'recommendations': 'Recommendations',
                'gaps': 'Skill Gap Analysis',
                'profile': 'Profile Settings'
            };
            document.getElementById('pageTitle').textContent = titles[tab] || 'Dashboard';
            
            // Load tab-specific data
            loadTabData(tab);
        });
    });
}

async function loadTabData(tab) {
    switch(tab) {
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

async function loadSkills() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=skills`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('skillsList');
        
        if (data.success && data.skills.length > 0) {
            container.innerHTML = data.skills.map(skill => `
                <div class="skill-card">
                    <div class="skill-header">
                        <div class="skill-name">${skill.skill_name}</div>
                        <span class="skill-level ${skill.proficiency_level}">${skill.proficiency_level}</span>
                    </div>
                    ${skill.category ? `<div class="skill-category">${skill.category}</div>` : ''}
                    <div class="skill-actions">
                        <button class="btn-small" onclick="editSkill(${skill.skill_id})">Edit</button>
                        <button class="btn-small" onclick="deleteSkill(${skill.skill_id})">Delete</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><p>No skills added yet. Start by adding your first skill!</p></div>';
        }
    } catch (error) {
        console.error('Load skills error:', error);
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
            container.innerHTML = data.courses.map(course => `
                <div class="card" style="margin-bottom: 1rem;">
                    <h3>${course.course_name}</h3>
                    <p><strong>Institution:</strong> ${course.institution || 'N/A'}</p>
                    <p><strong>Status:</strong> <span class="badge ${course.completion_status}">${course.completion_status.replace('_', ' ')}</span></p>
                    ${course.grade ? `<p><strong>Grade:</strong> ${course.grade}</p>` : ''}
                    <div class="card-actions">
                        <button class="btn-small" onclick="editCourse(${course.course_id})">Edit</button>
                        <button class="btn-small" onclick="deleteCourse(${course.course_id})">Delete</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No courses added yet</p></div>';
        }
    } catch (error) {
        console.error('Load courses error:', error);
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
            container.innerHTML = data.projects.map(project => `
                <div class="card" style="margin-bottom: 1rem;">
                    <h3>${project.project_name}</h3>
                    <p>${project.description || 'No description'}</p>
                    ${project.technologies_used ? `<p><strong>Technologies:</strong> ${project.technologies_used}</p>` : ''}
                    ${project.project_url ? `<p><strong>URL:</strong> <a href="${project.project_url}" target="_blank">${project.project_url}</a></p>` : ''}
                    <div class="card-actions">
                        <button class="btn-small" onclick="editProject(${project.project_id})">Edit</button>
                        <button class="btn-small" onclick="deleteProject(${project.project_id})">Delete</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No projects added yet</p></div>';
        }
    } catch (error) {
        console.error('Load projects error:', error);
    }
}

async function loadRecommendations() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=recommendations`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('recommendationsList');
        
        if (data.success && data.recommendations.length > 0) {
            container.innerHTML = data.recommendations.map(rec => `
                <div class="card" style="margin-bottom: 1rem;">
                    <div class="recommendation-header">
                        <span class="rec-type ${rec.recommendation_type}">${rec.recommendation_type}</span>
                        <span class="rec-score">${rec.relevance_score}% match</span>
                    </div>
                    <h3>${rec.title}</h3>
                    <p>${rec.description}</p>
                    <small>Source: ${rec.source}</small>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No recommendations yet. Click "Generate AI Recommendations" to get started.</p></div>';
        }
    } catch (error) {
        console.error('Load recommendations error:', error);
    }
}

async function loadGaps() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=gaps`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        const container = document.getElementById('gapsList');
        
        if (data.success && data.gaps.length > 0) {
            container.innerHTML = data.gaps.map(gap => `
                <div class="card gap-card ${gap.gap_severity}" style="margin-bottom: 1rem;">
                    <div class="gap-header">
                        <h3>${gap.skill_name}</h3>
                        <span class="severity-badge ${gap.gap_severity}">${gap.gap_severity}</span>
                    </div>
                    <div class="gap-levels">
                        <div>Current: <strong>${gap.current_level}</strong></div>
                        <div>Required: <strong>${gap.required_level}</strong></div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><p>No skill gaps analyzed. Click "Analyze Gaps" to start.</p></div>';
        }
    } catch (error) {
        console.error('Load gaps error:', error);
    }
}

async function generateRecommendations() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=recommendations&action=generate`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Recommendations generated successfully!');
            await loadRecommendations();
            await loadStats();
        }
    } catch (error) {
        console.error('Generate recommendations error:', error);
        alert('Error generating recommendations');
    }
}

async function analyzeGaps() {
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=gaps&action=analyze`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            alert(`Found ${data.total_gaps} skill gaps`);
            await loadGaps();
            await loadStats();
        }
    } catch (error) {
        console.error('Analyze gaps error:', error);
        alert('Error analyzing skill gaps');
    }
}

function openSkillModal() {
    const modal = document.getElementById('modalContainer');
    modal.innerHTML = `
        <div class="modal-overlay" onclick="closeModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <h2>Add Skill</h2>
                <form id="addSkillForm">
                    <div class="form-group">
                        <label>Skill Name</label>
                        <input type="text" id="skill_name" required>
                    </div>
                    <div class="form-group">
                        <label>Proficiency Level</label>
                        <select id="proficiency_level" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category (Optional)</label>
                        <input type="text" id="category">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-primary">Add Skill</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('addSkillForm').addEventListener('submit', addSkill);
}

async function addSkill(e) {
    e.preventDefault();
    
    const data = {
        skill_name: document.getElementById('skill_name').value,
        proficiency_level: document.getElementById('proficiency_level').value,
        category: document.getElementById('category').value
    };
    
    try {
        const response = await fetch(`${API_BASE}/dashboard.php?endpoint=skills`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            await loadSkills();
            await loadStats();
        }
    } catch (error) {
        console.error('Add skill error:', error);
    }
}

function closeModal() {
    document.getElementById('modalContainer').innerHTML = '';
}

function setupLogout() {
    document.getElementById('logoutBtn').addEventListener('click', async () => {
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

// Profile update
const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const data = {
            full_name: document.getElementById('profile_full_name').value,
            career_goal: document.getElementById('profile_career_goal').value,
            sector_focus: document.getElementById('profile_sector').value
        };
        
        try {
            const response = await fetch(`${API_BASE}/dashboard.php?endpoint=profile`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Profile updated successfully!');
            }
        } catch (error) {
            console.error('Update profile error:', error);
        }
    });
}