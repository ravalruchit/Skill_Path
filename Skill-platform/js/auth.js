// File: js/auth.js
// Fixed Authentication Logic

const API_BASE = window.location.origin + '/skill-platform/api';

// Login Form Handler
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const errorMsg = document.getElementById('errorMessage');
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';
        errorMsg.style.display = 'none';
        
        try {
            const response = await fetch(`${API_BASE}/auth.php?action=login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Check if admin
                if (data.user.is_admin) {
                    window.location.href = 'admin.html';
                } else {
                    window.location.href = 'dashboard.html';
                }
            } else {
                errorMsg.textContent = data.message || 'Invalid email or password';
                errorMsg.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        } catch (error) {
            console.error('Login error:', error);
            errorMsg.textContent = 'Connection error. Please check if the server is running.';
            errorMsg.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
        }
    });
}

// Register Form Handler
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            full_name: document.getElementById('full_name').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            career_goal: document.getElementById('career_goal').value.trim(),
            sector: document.getElementById('sector').value
        };
        
        const errorMsg = document.getElementById('errorMessage');
        const successMsg = document.getElementById('successMessage');
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        
        // Validation
        if (formData.password.length < 8) {
            errorMsg.textContent = 'Password must be at least 8 characters';
            errorMsg.style.display = 'block';
            successMsg.style.display = 'none';
            return;
        }
        
        if (!formData.full_name || !formData.email) {
            errorMsg.textContent = 'Please fill in all required fields';
            errorMsg.style.display = 'block';
            successMsg.style.display = 'none';
            return;
        }
        
        // Disable button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Account...';
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
        
        try {
            const response = await fetch(`${API_BASE}/auth.php?action=register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                successMsg.textContent = 'Account created successfully! Redirecting...';
                successMsg.style.display = 'block';
                errorMsg.style.display = 'none';
                
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                errorMsg.textContent = data.message || 'Registration failed';
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        } catch (error) {
            console.error('Registration error:', error);
            errorMsg.textContent = 'Connection error. Please check if the server is running.';
            errorMsg.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        }
    });
}

// Check if user is already logged in
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}/auth.php?action=check`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.logged_in) {
            if (window.location.pathname.includes('login.html') || 
                window.location.pathname.includes('register.html')) {
                if (data.is_admin) {
                    window.location.href = 'admin.html';
                } else {
                    window.location.href = 'dashboard.html';
                }
            }
        }
    } catch (error) {
        console.error('Auth check error:', error);
    }
}

// Run auth check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAuth);
} else {
    checkAuth();
}