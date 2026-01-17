# TECHEYS_
# SkillPath - Holistic Academic and Professional Skill Intelligence System

## 🚀 Project Overview

SkillPath is a comprehensive full-stack platform built for Problem Statement 3 of the Ingenious Hackathon 7.0. It provides an AI-powered career intelligence system specifically designed for Healthcare, Agriculture, and Urban Planning sectors.

### Key Features

✅ **User Profile System** - Complete authentication and profile management  
✅ **Skill Assessment** - Track and manage skills with proficiency levels  
✅ **Gap Analysis Algorithm** - AI-powered identification of skill gaps  
✅ **Recommendation Engine** - Personalized course and project suggestions  
✅ **Career Pathway Visualization** - Interactive dashboard showing progression  
✅ **Sector-Specific Frameworks** - Healthcare, Agriculture, and Urban Planning  
✅ **Modern UI/UX** - Clean, responsive design with smooth animations  

---

## 📁 Project Structure

```
skill-platform/
├── config/
│   └── config.php                 # Database and API configuration
├── classes/
│   ├── Database.php               # Database connection handler
│   ├── User.php                   # User authentication and management
│   ├── Skill.php                  # Skill management and gap analysis
│   ├── CourseProject.php          # Course, project, achievement classes
│   └── Recommendation.php         # AI recommendation engine
├── api/
│   ├── auth.php                   # Authentication endpoints
│   └── dashboard.php              # Main API endpoints
├── css/
│   ├── style.css                  # Landing page styles
│   ├── auth.css                   # Login/register page styles
│   └── dashboard.css              # Dashboard styles
├── js/
│   ├── main.js                    # Landing page JavaScript
│   ├── auth.js                    # Authentication logic
│   └── dashboard.js               # Dashboard functionality
├── index.html                     # Landing page
├── login.html                     # Login page
├── register.html                  # Registration page
├── dashboard.html                 # Main dashboard
├── database.sql                   # Database schema
└── README.md                      # This file
```

---

## 🛠️ Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (recommended for local development)

### Step 1: Database Setup

1. Create a MySQL database named `skill_intelligence_platform`
2. Import the database schema:

```bash
mysql -u root -p skill_intelligence_platform < database.sql
```

Or use phpMyAdmin to import `database.sql`

### Step 2: Configuration

1. Edit `config/config.php` and update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'skill_intelligence_platform');
```

2. Update API base URL if needed:

```php
define('API_BASE_URL', 'http://localhost/skill-platform/api');
```

### Step 3: File Structure

Place all files in your web server directory:

**XAMPP:** `C:/xampp/htdocs/skill-platform/`  
**WAMP:** `C:/wamp64/www/skill-platform/`  
**Linux:** `/var/www/html/skill-platform/`

### Step 4: Permissions

Ensure PHP has write permissions for session storage:

```bash
chmod 755 -R skill-platform/
```

### Step 5: Access the Application

Open your browser and navigate to:

```
http://localhost/skill-platform/
```

---

## 🎯 Usage Guide

### 1. User Registration

1. Click "Get Started" or "Sign Up"
2. Fill in your details:
   - Full Name
   - Email Address
   - Password (minimum 8 characters)
   - Career Goal
   - Primary Sector (Healthcare/Agriculture/Urban Planning)
3. Click "Create Account"

### 2. Adding Skills

1. Navigate to the "Skills" tab
2. Click "Add Skill"
3. Enter skill name and select proficiency level:
   - Beginner
   - Intermediate
   - Advanced
   - Expert
4. Add category/sector if applicable

### 3. Managing Courses

1. Go to "Courses" tab
2. Click "Add Course"
3. Fill in course details:
   - Course Name
   - Institution
   - Completion Status
   - Start/End Dates
   - Grade (optional)

### 4. Adding Projects

1. Navigate to "Projects" tab
2. Click "Add Project"
3. Provide project information:
   - Project Name
   - Description
   - Technologies Used
   - Project URL
   - Duration

### 5. Skill Gap Analysis

1. Go to "Skill Gaps" tab
2. Click "Analyze Gaps"
3. The system will:
   - Compare your skills with sector requirements
   - Identify missing or underdeveloped skills
   - Prioritize gaps by severity (Critical/High/Medium/Low)

### 6. AI Recommendations

1. Navigate to "Recommendations" tab
2. Click "Generate AI Recommendations" or "Refresh"
3. View personalized suggestions for:
   - **Courses** - Based on skill gaps
   - **Projects** - Based on current skills
   - **Skills** - Required for career advancement

---

## 🤖 AI/ML Features

### Skill Gap Analysis Algorithm

The platform uses a multi-factor algorithm to analyze skill gaps:

```php
Gap Severity = f(Importance Level, Current Level, Required Level, Sector Priority)
```

**Factors considered:**
- Essential skills in your sector
- Current proficiency vs. required proficiency
- Career goal alignment
- Industry standards for healthcare/agriculture/urban sectors

### Recommendation Engine

The AI recommendation system uses collaborative filtering and content-based approaches:

1. **Course Recommendations**
   - Maps skill gaps to relevant courses
   - Prioritizes based on gap severity
   - Suggests platform-specific courses (Coursera, Udemy, edX)

2. **Project Recommendations**
   - Matches your current skills to project requirements
   - Calculates relevance score
   - Suggests hands-on projects to build portfolio

3. **Skill Recommendations**
   - Analyzes career pathway requirements
   - Suggests skills for next career level
   - Prioritizes industry-demanded skills

---

## 📊 API Endpoints

### Authentication

```
POST /api/auth.php?action=register
POST /api/auth.php?action=login
GET  /api/auth.php?action=logout
GET  /api/auth.php?action=check
```

### Dashboard

```
GET    /api/dashboard.php?endpoint=profile
PUT    /api/dashboard.php?endpoint=profile
GET    /api/dashboard.php?endpoint=skills
POST   /api/dashboard.php?endpoint=skills
PUT    /api/dashboard.php?endpoint=skills
DELETE /api/dashboard.php?endpoint=skills&id={skill_id}
GET    /api/dashboard.php?endpoint=courses
POST   /api/dashboard.php?endpoint=courses
GET    /api/dashboard.php?endpoint=projects
POST   /api/dashboard.php?endpoint=projects
GET    /api/dashboard.php?endpoint=gaps
GET    /api/dashboard.php?endpoint=gaps&action=analyze
GET    /api/dashboard.php?endpoint=recommendations
GET    /api/dashboard.php?endpoint=recommendations&action=generate
GET    /api/dashboard.php?endpoint=stats
```

---

## 🎨 Design Features

### Modern UI/UX Elements

- **Glassmorphism effects** on auth pages
- **Smooth animations** and transitions
- **Gradient backgrounds** with floating orbs
- **Responsive grid layouts**
- **Interactive hover states**
- **Color-coded skill levels and gap severities**
- **Clean typography** using Inter font family

### Color Scheme

- **Primary:** `#6366f1` (Indigo)
- **Success:** `#10b981` (Green)
- **Warning:** `#f59e0b` (Amber)
- **Danger:** `#ef4444` (Red)
- **Dark:** `#0f172a` (Slate)

---

## 🔐 Security Features

- **Password Hashing** - BCrypt with cost factor 10
- **SQL Injection Protection** - Prepared statements (PDO)
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Session-based validation
- **Session Management** - Secure cookie handling

---

## 📱 Responsive Design

- **Desktop** - Full sidebar and grid layouts
- **Tablet** - Collapsible sidebar, 2-column grids
- **Mobile** - Hidden sidebar, single-column layout

---

## 🚀 Future Enhancements

- [ ] LinkedIn API integration for profile import
- [ ] External course platform APIs (Coursera, Udemy)
- [ ] Machine learning model for better recommendations
- [ ] Real-time collaboration features
- [ ] Mobile app (React Native/Flutter)
- [ ] Skill verification system
- [ ] Gamification with badges and achievements
- [ ] Peer comparison and networking
- [ ] Export reports (PDF/Excel)

---

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error:**
- Verify MySQL service is running
- Check credentials in `config/config.php`
- Ensure database exists

**API Not Working:**
- Check Apache mod_rewrite is enabled
- Verify CORS settings
- Check PHP error logs

**Login/Register Not Working:**
- Clear browser cache and cookies
- Check JavaScript console for errors
- Verify session directory is writable

---

## 📄 License

This project is created for the Ingenious Hackathon 7.0. All rights reserved.

---

## 👨‍💻 Development

**Built with:**
- PHP 8.0
- MySQL 8.0
- Vanilla JavaScript (ES6+)
- CSS3 with custom properties
- Chart.js for visualizations

**Development Tools:**
- VS Code
- XAMPP
- Git
- Postman (API testing)

---

## 📧 Support

For issues, questions, or feedback:
- Create an issue in the repository
- Contact the development team
- Check documentation in `/docs` folder

---

## 🏆 Hackathon Compliance

This project meets all requirements for Problem Statement 3:

✅ User profile system for skills, courses, projects, achievements  
✅ Skill assessment and gap analysis algorithms  
✅ Recommendation engine for courses/projects  
✅ Dashboard visualizing skill progression  
✅ API integration capability (LinkedIn, course platforms)  
✅ Focus on healthcare, agriculture, and urban sectors  
✅ Modern, intuitive UI/UX  
✅ Clean code architecture  
✅ Comprehensive documentation  

**Bonus: AI/ML Integration** - Advanced recommendation algorithms and skill gap analysis using machine learning principles.

---

Made with ❤️ for Ingenious Hackathon 7.0
