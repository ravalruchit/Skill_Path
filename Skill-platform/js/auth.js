// File: js/auth.js
// Fixed Authentication Logic with Debug Info

const API_BASE = 'https://techeys.onrender.com/api/auth.php';

console.log('Auth.js loaded. API_BASE:', API_BASE);

// Login Form Handler
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    console.log('Login form found');
    
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Login form submitted');
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const errorMsg = document.getElementById('errorMessage');
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        
        console.log('Login attempt for:', email);
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';
        errorMsg.style.display = 'none';
        
        const loginUrl = `${API_BASE}/auth.php?action=login`;
        console.log('Login URL:', loginUrl);
        
        try {
            console.log('Sending login request...');
            
            const response = await fetch(loginUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                console.log('Login successful!');
                if (data.user && data.user.is_admin) {
                    console.log('Redirecting to admin...');
                    window.location.href = 'admin.html';
                } else {
                    console.log('Redirecting to dashboard...');
                    window.location.href = 'dashboard.html';
                }
            } else {
                console.log('Login failed:', data.message);
                errorMsg.textContent = data.message || 'Invalid email or password';
                errorMsg.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        } catch (error) {
            console.error('Login error:', error);
            console.error('Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            errorMsg.innerHTML = `
                <strong>Connection Error!</strong><br><br>
                <strong>Details:</strong> ${error.message}<br><br>
                <strong>Troubleshooting:</strong><br>
                1. Open <a href="${loginUrl}" target="_blank">this link</a> in new tab<br>
                2. If you see JSON response, API works<br>
                3. If you see error, check Apache is running<br>
                4. Check browser console (F12) for details<br><br>
                <strong>Expected URL:</strong><br>
                ${loginUrl}
            `;
            errorMsg.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
        }
    });
}

// Register Form Handler
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    console.log('Register form found');
    
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Register form submitted');
        
        const formData = {
            full_name: document.getElementById('full_name').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            career_goal: document.getElementById('career_goal').value.trim(),
            sector: document.getElementById('sector').value
        };
        
        console.log('Register attempt for:', formData.email);
        
        const errorMsg = document.getElementById('errorMessage');
        const successMsg = document.getElementById('successMessage');
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        
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
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Account...';
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
        
        const registerUrl = `${API_BASE}/auth.php?action=register`;
        console.log('Register URL:', registerUrl);
        
        try {
            console.log('Sending register request...');
            
            const response = await fetch(registerUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            console.log('Response status:', response.status);
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                console.log('Registration successful!');
                successMsg.textContent = 'Account created successfully! Redirecting...';
                successMsg.style.display = 'block';
                errorMsg.style.display = 'none';
                
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                console.log('Registration failed:', data.message);
                errorMsg.textContent = data.message || 'Registration failed';
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        } catch (error) {
            console.error('Registration error:', error);
            console.error('Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            errorMsg.innerHTML = `
                <strong>Connection Error!</strong><br><br>
                <strong>Details:</strong> ${error.message}<br><br>
                <strong>Troubleshooting:</strong><br>
                1. Open <a href="${registerUrl}" target="_blank">this link</a> in new tab<br>
                2. Make sure Apache & MySQL are running<br>
                3. Check browser console (F12) for details<br><br>
                <strong>Expected URL:</strong><br>
                ${registerUrl}
            `;
            errorMsg.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        }
    });
}

// Check if user is already logged in
async function checkAuth() {
    const checkUrl = `${API_BASE}/auth.php?action=check`;
    console.log('Checking auth at:', checkUrl);
    
    try {
        const response = await fetch(checkUrl, {
            credentials: 'include'
        });
        
        const data = await response.json();
        console.log('Auth check response:', data);
        
        if (data.logged_in) {
            console.log('User is logged in');
            if (window.location.pathname.includes('login.html') || 
                window.location.pathname.includes('register.html')) {
                if (data.is_admin) {
                    console.log('Redirecting to admin...');
                    window.location.href = 'admin.html';
                } else {
                    console.log('Redirecting to dashboard...');
                    window.location.href = 'dashboard.html';
                }
            }
        } else {''
            console.log('User is not logged in');
        }
    } catch (error) {
        console.error('Auth check error:', error);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAuth);
} else {
    checkAuth();
}

console.log('Auth.js fully initialized');
