# 🎯 SkillPath - AI-Powered Career Intelligence Platform

<div align="center">

![SkillPath Logo](https://img.shields.io/badge/SkillPath-v1.0.0-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**Navigate Your Career with AI-Powered Intelligence**

[Features](#-features) • [Installation](#-installation) • [Documentation](#-api-endpoints) • [Contributing](#-contributing)

</div>

---

## 📖 Overview

SkillPath is an intelligent career development platform that leverages **Anthropic's Claude AI** to provide personalized skill assessments, gap analysis, and career recommendations. Designed specifically for Healthcare, Agriculture, and Urban Planning sectors, it helps professionals identify skill gaps and receive tailored learning recommendations.

### 🎯 Key Highlights

- **AI-Powered Recommendations** using Claude Sonnet 4
- **Real-time Skill Gap Analysis**
- **Sector-Specific Career Pathways**
- **Beautiful Admin Dashboard** with analytics
- **Excel Data Export** functionality
- **Interactive Charts** powered by Chart.js

---

## ✨ Features

### 👤 User Features

| Feature | Description |
|---------|-------------|
| 🎓 **Skill Assessment** | AI-powered analysis of your current competencies |
| 📊 **Gap Analysis** | Identify exactly what skills you need for your career goals |
| 🤖 **Smart Recommendations** | Personalized courses, projects, and certifications |
| 📈 **Progress Tracking** | Visual dashboards showing your skill development |
| 🛣️ **Career Pathways** | Multiple trajectories in specialized sectors |
| 🎯 **Goal Setting** | Define and track your career objectives |

### 👨‍💼 Admin Features

| Feature | Description |
|---------|-------------|
| 👥 **User Management** | Comprehensive user analytics and control |
| 📊 **Platform Analytics** | Real-time insights with interactive charts |
| 📥 **Data Export** | Export to Excel (users, skills, courses, analytics) |
| ⚙️ **Skills Framework** | Manage sector-specific skill requirements |
| 🔍 **Search & Filter** | Advanced user and data filtering |
| 📈 **Growth Metrics** | Track platform growth and engagement |

---

## 🛠️ Tech Stack

### Frontend
- HTML5, CSS3 (Custom Properties, Flexbox, Grid)
- Vanilla JavaScript (ES6+)
- Chart.js 4.4.0
- Google Fonts (Inter)

### Backend
- PHP 8.0+
- MySQL 8.0+
- PDO (Database Abstraction)
- Session-based Authentication

### AI Integration
- Anthropic Claude API (Sonnet 4)
- Custom recommendation engine
- Natural language processing

### Development
- Apache 2.4+
- XAMPP/WAMP
- phpMyAdmin

---

## 📦 Prerequisites

Before installation, ensure you have:
```bash
✅ PHP 8.0 or higher
✅ MySQL 8.0 or higher
✅ Apache Web Server
✅ Modern web browser (Chrome, Firefox, Safari, Edge)
```

**Recommended:** XAMPP (includes Apache, MySQL, PHP) - [Download here](https://www.apachefriends.org/)

---

## 🚀 Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/skillpath.git
cd skillpath
```

### Step 2: Install XAMPP

1. Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install to `C:\xampp` (Windows) or `/Applications/XAMPP` (Mac)
3. Start **Apache** and **MySQL** from XAMPP Control Panel

### Step 3: Move Files to htdocs
```bash
# Windows
xcopy /E /I skillpath C:\xampp\htdocs\skillpath

# Mac/Linux
cp -r skillpath /Applications/XAMPP/htdocs/
```

### Step 4: Configure Database Connection

Edit `config/config.php`:
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP password is empty
define('DB_NAME', 'skill_intelligence_platform');

// API Configuration
define('API_BASE_URL', 'http://localhost/skillpath/api');

// Claude AI API Key (Optional)
putenv('ANTHROPIC_API_KEY=your-api-key-here');
```

### Step 5: Update JavaScript API URLs

Update in **3 files**: `js/auth.js`, `js/dashboard.js`, `js/admin.js`:
```javascript
const API_BASE = 'http://localhost/skillpath/api';
```

### Step 6: Create Database

#### Option A: Using phpMyAdmin (Recommended)

1. Open `http://localhost/phpmyadmin`
2. Click **"New"** → Database name: `skill_intelligence_platform`
3. Collation: `utf8mb4_general_ci` → Click **"Create"**
4. Select the database → **"Import"** tab
5. Choose file: `database/skill_intelligence_platform.sql`
6. Click **"Go"**

#### Option B: Using Command Line
```bash
# Navigate to MySQL bin directory
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p

# Create and import database
CREATE DATABASE skill_intelligence_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE skill_intelligence_platform;
source C:/xampp/htdocs/skillpath/database/skill_intelligence_platform.sql;
exit;
```

---

## ▶️ Running the Application

### Start Servers

1. Open **XAMPP Control Panel**
2. Click **"Start"** next to Apache
3. Click **"Start"** next to MySQL
4. Verify both show **green "Running"** status

### Access Application

Open your browser and navigate to:

| Page | URL |
|------|-----|
| 🏠 Landing Page | `http://localhost/skillpath/` |
| 🔐 Login | `http://localhost/skillpath/login.html` |
| ✍️ Register | `http://localhost/skillpath/register.html` |
| 📊 Dashboard | `http://localhost/skillpath/dashboard.html` |
| 👨‍💼 Admin Panel | `http://localhost/skillpath/admin.html` |

---

## 🔑 Test Credentials

### Regular User
```
Email:    user@skillpath.com
Password: password123
```

### Admin Account
```
Email:    admin@skillpath.com
Password: admin123
```

### Create New Account
Visit `http://localhost/skillpath/register.html` and sign up!

---

## 📁 Project Structure
```
skillpath/
│
├── 📁 api/                        # Backend API Endpoints
│   ├── admin.php                  # Admin panel API
│   ├── auth.php                   # Authentication API
│   ├── dashboard.php              # Dashboard data API
│   └── export.php                 # Excel export API
│
├── 📁 classes/                    # PHP Classes
│   ├── Database.php               # DB connection handler
│   ├── User.php                   # User management
│   ├── Skill.php                  # Skill operations
│   ├── CourseProject.php          # Course/Project CRUD
│   ├── Recommendation.php         # Basic recommendations
│   └── AIRecommendation.php       # Claude AI integration
│
├── 📁 config/                     # Configuration
│   └── config.php                 # Main config file
│
├── 📁 css/                        # Stylesheets
│   ├── style.css                  # Landing page
│   ├── auth.css                   # Login/Register
│   ├── dashboard.css              # User dashboard
│   └── admin.css                  # Admin panel
│
├── 📁 js/                         # JavaScript
│   ├── main.js                    # Landing page
│   ├── auth.js                    # Authentication
│   ├── dashboard.js               # Dashboard logic
│   └── admin.js                   # Admin panel
│
├── 📁 database/                   # Database
│   └── skill_intelligence_platform.sql
│
├── 📄 index.html                  # Landing page
├── 📄 login.html                  # Login page
├── 📄 register.html               # Register page
├── 📄 dashboard.html              # User dashboard
├── 📄 admin.html                  # Admin panel
└── 📄 README.md                   # Documentation
```

---

## 🔌 API Endpoints

### Authentication (`api/auth.php`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth.php?action=register` | Register new user |
| POST | `/api/auth.php?action=login` | User login |
| GET | `/api/auth.php?action=logout` | User logout |
| GET | `/api/auth.php?action=check` | Check session |

### Dashboard (`api/dashboard.php`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboard.php?endpoint=profile` | Get user profile |
| PUT | `/api/dashboard.php?endpoint=profile` | Update profile |
| GET | `/api/dashboard.php?endpoint=skills` | Get user skills |
| POST | `/api/dashboard.php?endpoint=skills` | Add new skill |
| DELETE | `/api/dashboard.php?endpoint=skills&id=X` | Delete skill |
| GET | `/api/dashboard.php?endpoint=courses` | Get courses |
| POST | `/api/dashboard.php?endpoint=courses` | Add course |
| GET | `/api/dashboard.php?endpoint=projects` | Get projects |
| POST | `/api/dashboard.php?endpoint=projects` | Add project |
| GET | `/api/dashboard.php?endpoint=recommendations` | AI recommendations |
| GET | `/api/dashboard.php?endpoint=gaps` | Skill gap analysis |
| GET | `/api/dashboard.php?endpoint=stats` | User statistics |

### Admin (`api/admin.php`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin.php?endpoint=stats` | Platform stats |
| GET | `/api/admin.php?endpoint=users` | All users list |
| DELETE | `/api/admin.php?endpoint=users&id=X` | Delete user |
| GET | `/api/admin.php?endpoint=analytics` | Analytics data |
| GET | `/api/admin.php?endpoint=user-growth` | User growth chart |
| GET | `/api/admin.php?endpoint=sector-distribution` | Sector data |
| GET | `/api/admin.php?endpoint=skills-framework` | Skills framework |
| GET | `/api/admin.php?endpoint=recent-activity` | Recent activity |

### Export (`api/export.php`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/export.php?type=users` | Export users to Excel |
| GET | `/api/export.php?type=skills` | Export skills to Excel |
| GET | `/api/export.php?type=courses` | Export courses to Excel |
| GET | `/api/export.php?type=projects` | Export projects to Excel |
| GET | `/api/export.php?type=analytics` | Export analytics report |
| GET | `/api/export.php?type=all` | Export all data |

---

## 🗄️ Database Schema

### Core Tables

#### Users Table
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    career_goal TEXT,
    sector_focus ENUM('healthcare', 'agriculture', 'urban_planning'),
    is_admin BOOLEAN DEFAULT FALSE,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);
```

#### User Skills Table
```sql
CREATE TABLE user_skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert'),
    category VARCHAR(100),
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

#### Skills Framework Table
```sql
CREATE TABLE skill_framework (
    framework_id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(255) NOT NULL,
    sector VARCHAR(100) NOT NULL,
    category VARCHAR(100),
    importance_level ENUM('essential', 'important', 'beneficial'),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Recommendations Table
```sql
CREATE TABLE recommendations (
    recommendation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    recommendation_type ENUM('course', 'project', 'skill', 'certification'),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    relevance_score DECIMAL(5,2),
    source VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

---

## 🔧 Troubleshooting

### Common Issues

#### ❌ Database Connection Failed

**Error:** `Database connection failed: SQLSTATE[HY000] [1045]`

**Solution:**
```php
// Check config/config.php
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default is empty

// Verify MySQL is running in XAMPP Control Panel
```

#### ❌ API 404 Not Found

**Error:** `Cannot GET /api/auth.php`

**Solution:**
```javascript
// Update API_BASE in js/auth.js, js/dashboard.js, js/admin.js
const API_BASE = 'http://localhost/skillpath/api';
// ⚠️ Replace 'skillpath' with your actual folder name
```

#### ❌ Session Not Working

**Error:** `Not authenticated` or redirects to login

**Solution:**
```bash
# Clear browser cookies and cache
# Try Incognito/Private mode
# Check config/config.php session settings
```

#### ❌ Charts Not Displaying

**Error:** Blank charts or loading forever

**Solution:**
```html
<!-- Verify Chart.js is loaded in HTML -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Check browser console (F12) for errors -->
```

#### ❌ AI Recommendations Not Working

**Error:** `Failed to load AI insights`

**Solution:**
```php
// Add Claude API key in config/config.php
putenv('ANTHROPIC_API_KEY=sk-ant-your-key-here');

// Get API key from: https://console.anthropic.com/
// Note: AI features work without key but with limited functionality
```

#### ❌ Export Not Downloading

**Error:** Export button doesn't trigger download

**Solution:**
```bash
# 1. Ensure you're logged in as admin
# 2. Check if export.php file exists in /api/ folder
# 3. Verify admin session: $_SESSION['is_admin'] = true
# 4. Check browser console for errors
```

### Enable Debug Mode
```php
// config/config.php - Enable for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Disable for production
// error_reporting(0);
// ini_set('display_errors', 0);
```

### Check Error Logs
```bash
# Windows XAMPP
C:\xampp\apache\logs\error.log

# Mac/Linux
/Applications/XAMPP/logs/error_log

# View last 50 lines
tail -n 50 error.log
```

---

## 🎨 Customization Guide

### Change Theme Colors

Edit CSS custom properties in any stylesheet:
```css
/* css/style.css, css/dashboard.css, css/admin.css */
:root {
    --primary: #6366f1;      /* Main brand color */
    --primary-dark: #4f46e5; /* Darker shade */
    --success: #10b981;      /* Success messages */
    --warning: #f59e0b;      /* Warnings */
    --danger: #ef4444;       /* Errors */
    --dark: #0f172a;         /* Dark backgrounds */
}
```

### Add New Sector

1. **Update database:**
```sql
-- Add new sector to skill_framework
INSERT INTO skill_framework (skill_name, sector, category, importance_level)
VALUES ('New Skill', 'finance', 'technical', 'essential');
```

2. **Update HTML forms:**
```html
<!-- In register.html and dashboard.html -->
<select id="sector">
    <option value="healthcare">Healthcare</option>
    <option value="agriculture">Agriculture</option>
    <option value="urban_planning">Urban Planning</option>
    <option value="finance">Finance</option> <!-- New -->
</select>
```

3. **Update PHP enums:**
```php
// In classes/User.php and database schema
sector_focus ENUM('healthcare', 'agriculture', 'urban_planning', 'finance')
```

### Customize AI Prompts

Edit `classes/AIRecommendation.php`:
```php
private function buildAIPrompt($profile) {
    $prompt = "You are an AI career advisor specializing in " . $sector;
    $prompt .= "\n\nUser Profile:\n";
    // Add your custom prompt logic here
    return $prompt;
}
```

---

## 🔐 Security Best Practices

### For Production Deployment

#### 1. Secure Database Credentials
```php
// config/config.php
define('DB_PASS', 'Strong_P@ssw0rd!123');
```

#### 2. Enable HTTPS
```php
// config/config.php
ini_set('session.cookie_secure', 1); // Only transmit cookies over HTTPS
```

#### 3. Hide Error Messages
```php
// config/config.php
error_reporting(0);
ini_set('display_errors', 0);
```

#### 4. Add .htaccess Protection
```apache
# .htaccess in root directory
Options -Indexes

# Protect config files
<FilesMatch "^(config\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable HTTPS redirect
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 5. Use Environment Variables
```php
// Use .env file for sensitive data
// Install vlucas/phpdotenv for PHP
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('DB_PASS', $_ENV['DB_PASSWORD']);
```

#### 6. SQL Injection Prevention
```php
// Always use prepared statements (already implemented)
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

---

## 🤝 Contributing

We welcome contributions! Here's how:

### Reporting Bugs

1. Check [existing issues](https://github.com/yourusername/skillpath/issues)
2. Create new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots (if applicable)
   - Environment details (OS, PHP version, browser)

### Feature Requests

1. Open an issue tagged `enhancement`
2. Describe the feature and use case
3. Explain why it would be useful

### Pull Requests
```bash
# 1. Fork the repository
# 2. Create feature branch
git checkout -b feature/AmazingFeature

# 3. Make your changes
git add .
git commit -m 'Add some AmazingFeature'

# 4. Push to branch
git push origin feature/AmazingFeature

# 5. Open Pull Request
```

### Code Style Guidelines

- Follow **PSR-12** coding standard for PHP
- Use **camelCase** for JavaScript variables
- Comment complex logic
- Keep functions small and focused
- Write meaningful commit messages

---

## 📚 Additional Resources

### Documentation
- [PHP Manual](https://www.php.net/manual/en/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Chart.js Documentation](https://www.chartjs.org/docs/)
- [Anthropic Claude API](https://docs.anthropic.com/)

### Tutorials
- [PHP for Beginners](https://www.youtube.com/watch?v=OK_JCtrrv-c)
- [MySQL Tutorial](https://www.mysqltutorial.org/)
- [JavaScript ES6+](https://www.youtube.com/watch?v=NCwa_xi0Uuc)

### Community
- [Stack Overflow - PHP](https://stackoverflow.com/questions/tagged/php)
- [Reddit - r/PHP](https://www.reddit.com/r/PHP/)
- [PHP Discord Server](https://discord.gg/php)

---

## 🗺️ Roadmap

### Version 1.1 (Q2 2025)
- [ ] Mobile responsive design improvements
- [ ] Dark mode theme
- [ ] Email notifications
- [ ] Skill verification badges
- [ ] Advanced filtering options

### Version 1.2 (Q3 2025)
- [ ] LinkedIn profile import
- [ ] Multi-language support (Spanish, French)
- [ ] Video course integration
- [ ] Mentor matching system
- [ ] Resume builder

### Version 2.0 (Q4 2025)
- [ ] Mobile app (React Native)
- [ ] Machine learning skill predictions
- [ ] Virtual career counseling
- [ ] Blockchain-based certifications
- [ ] Industry partnership program

---

## 📝 License

This project is licensed under the **MIT License**.
```
MIT License

Copyright (c) 2025 SkillPath

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 👥 Authors & Contributors

- **Parth Panchal** - *Initial work* - [GitHub](https://github.com/yourusername)

See also the list of [contributors](https://github.com/yourusername/skillpath/contributors) who participated in this project.

---

## 🙏 Acknowledgments

- **[Anthropic](https://www.anthropic.com/)** - Claude AI API
- **[Chart.js](https://www.chartjs.org/)** - Data visualization
- **[Google Fonts](https://fonts.google.com/)** - Inter font family
- **[XAMPP](https://www.apachefriends.org/)** - Development environment
- **[PHP Community](https://www.php.net/)** - Excellent documentation

---

## 📞 Support & Contact

Need help? Reach out:

- 📧 **Email:** support@skillpath.com
- 💬 **Discord:** [Join Community](https://discord.gg/skillpath)
- 🐛 **Issues:** [GitHub Issues](https://github.com/yourusername/skillpath/issues)
- 📖 **Docs:** [Full Documentation](https://docs.skillpath.com)
- 🐦 **Twitter:** [@SkillPathApp](https://twitter.com/SkillPathApp)

---

## ⭐ Show Your Support

Give a ⭐️ if this project helped you!



## 📊 Statistics

![GitHub stars](https://img.shields.io/github/stars/yourusername/skillpath?style=social)
![GitHub forks](https://img.shields.io/github/forks/yourusername/skillpath?style=social)
![GitHub issues](https://img.shields.io/github/issues/yourusername/skillpath)
![GitHub pull requests](https://img.shields.io/github/issues-pr/yourusername/skillpath)


