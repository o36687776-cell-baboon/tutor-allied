# 🚀 AI-ACADEMY LMS

<div align="center">

**Welcome to AI-ACADEMY** - *Where Learning Meets Innovation!*

[![Pi Network](https://img.shields.io/badge/Pi%20Network-Integrated-blue)](https://minepi.com/)
[![Mobile Optimized](https://img.shields.io/badge/Mobile-Optimized-green)](https://minepi.com/)
[![PHP](https://img.shields.io/badge/PHP-8.x-purple)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-orange)](https://mysql.com/)

*Master AI, Software Development, and Blockchain with Expert Tutors*

[🌐 Live Demo](#) • [📱 Mobile Access](#mobile-access) • [📖 Documentation](#documentation)

</div>

---

## ✨ Overview

**AI-ACADEMY** is a cutting-edge Learning Management System (LMS) built specifically for the Pi Network ecosystem. Our platform empowers learners worldwide to master AI, Software Development, and Blockchain technologies through an engaging, gamified experience with seamless Pi Network integration.

### 🎯 Key Highlights

- **🔐 Pi Network Authentication** - Secure, decentralized login system
- **📱 Mobile-First Design** - Optimized for Pi Browser and all mobile devices
- **🎮 Gamified Learning** - Engagement algorithms with ranks, badges, and Pi rewards
- **👥 Role-Based Access** - Tailored experiences for Learners, Tutors, and Admins
- **💰 Pi Economy Integration** - Earn, spend, and trade with Pi coins
- **🌍 Global Accessibility** - Multi-language support and worldwide access

---

## 🚀 Features

### Core Learning Platform
- **📚 Comprehensive Library** - E-books, videos, tutorials, and interactive simulations
- **👨‍🏫 Expert Tutors** - Connect with industry professionals worldwide
- **💬 Interactive Forums** - Stack Overflow-style Q&A community
- **📝 Blog Platform** - Medium-inspired article publishing system
- **🎯 Task Management** - Request and complete learning challenges
- **📊 Progress Tracking** - Detailed analytics and learning paths

### Pi Network Integration
- **🔑 Decentralized Auth** - Passwordless login via Pi Browser
- **💳 Pi Wallet** - Integrated wallet management and verification
- **💰 Pi Payments** - Seamless transactions for premium features
- **🏆 Achievement System** - Earn Pi coins through learning milestones
- **🎪 Premium Features** - Unlock advanced content with Pi payments

### Mobile & Accessibility
- **📱 Pi Browser Optimized** - Native Pi Network mobile experience
- **🎨 Responsive Design** - Perfect on all devices and screen sizes
- **♿ Accessibility First** - WCAG compliant with screen reader support
- **🌙 Dark Mode Ready** - Eye-friendly learning environment
- **⚡ Performance Optimized** - Fast loading on mobile networks

### Administration & Analytics
- **👑 Admin Dashboard** - Comprehensive system management
- **📈 Real-time Analytics** - User engagement and performance metrics
- **🔧 Content Management** - Easy publishing and moderation tools
- **👥 User Management** - Role assignment and permission control
- **📊 Activity Logs** - Detailed audit trails and reporting

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.x** - Server-side scripting with modern features
- **MySQL 8.x** - Robust database with advanced features
- **PDO** - Secure database abstraction layer
- **Composer** - Dependency management

### Frontend
- **HTML5** - Semantic markup and accessibility
- **CSS3** - Custom properties, flexbox, and grid
- **JavaScript (ES6+)** - Modern interactive features
- **Bootstrap 5** - Responsive component library
- **Font Awesome 6** - Comprehensive icon library

### Pi Network Integration
- **Pi Network SDK** - Official Pi Network JavaScript SDK
- **Pi Browser API** - Mobile app integration
- **Pi Payments API** - Secure transaction processing
- **Pi Auth API** - Decentralized authentication

### Development & Deployment
- **XAMPP** - Local development environment
- **Git** - Version control and collaboration
- **VS Code** - Recommended IDE with extensions
- **Chrome DevTools** - Debugging and testing

---

## 📦 Installation & Setup

### Prerequisites
- **XAMPP** (Apache 2.4+, MySQL 8.x, PHP 8.x)
- **Pi Browser** (for full Pi Network integration)
- **Git** (optional, for cloning)
- **Composer** (optional, for Pi SDK)

### 🚀 Quick Start

#### 1. Download & Setup
```bash
# Clone the repository
git clone https://github.com/yourusername/ai-academy.git

# Or download ZIP and extract to:
# C:\xampp\htdocs\homework-help\
```

#### 2. Database Setup
```sql
# Import database schema
# Open phpMyAdmin at http://localhost/phpmyadmin/
# Create database: ai_academy
# Import: db_schema.sql
```

#### 3. Configuration
```php
# Update db.php with your credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_academy');
define('DB_USER', 'root');
define('DB_PASS', '');
```

#### 4. Pi Network Setup
```php
# Update pi_config.php
define('PI_APP_API_KEY', 'your_pi_app_key');
define('PI_APP_SECRET', 'your_pi_app_secret');
define('PI_NETWORK_SANDBOX', true); # Set to false for production
```

#### 5. Start Services
```bash
# Start XAMPP Control Panel
# Start Apache & MySQL services
```

#### 6. Access Application
- **Local:** `http://localhost/`
- **Network:** `http://192.168.100.26/` (replace with your IP)
- **Pi Browser:** Open in Pi Browser for full integration

### 🔧 Production Deployment

#### Hosting Requirements
- **PHP 8.0+** with required extensions
- **MySQL 8.0+** or MariaDB 10.5+
- **SSL Certificate** (required for Pi Network)
- **HTTPS Only** (mandatory for Pi payments)

#### Deployment Steps
```bash
# 1. Upload files to hosting
scp -r . user@yourhost:/var/www/html/

# 2. Set permissions
chmod -R 755 /var/www/html/
chmod -R 777 /var/www/html/sessions/
chmod -R 777 /var/www/html/logs/

# 3. Configure Apache/Nginx
# Enable mod_rewrite
# Set DocumentRoot to project root

# 4. SSL Configuration
# Obtain SSL certificate (Let's Encrypt recommended)
# Configure HTTPS redirects
```

---

## 📱 Mobile Access

### Pi Browser (Recommended)
1. **Download Pi Browser** from [minepi.com/browser](https://minepi.com/browser)
2. **Open AI-ACADEMY** using your local/network URL
3. **Authenticate** with Pi Network (one-click login)
4. **Enjoy** full mobile-optimized experience

### Other Mobile Browsers
- **Chrome Mobile:** Full feature support
- **Safari iOS:** Optimized for iOS devices
- **Firefox Mobile:** Standard web experience
- **Samsung Internet:** Android optimized

### Network Access URLs
```
Local Computer:    http://localhost/
Network Devices:   http://192.168.100.26/
Pi Browser:        Open in Pi Browser app
```

---

## 👥 User Roles & Permissions

### 🎓 Learner/Student
- **Browse Library** - Access all learning materials
- **Join Live Sessions** - Participate in tutor-led classes
- **Request Tasks** - Submit learning challenges
- **Earn Pi Coins** - Complete tasks and achievements
- **Track Progress** - View learning analytics
- **Community Access** - Forums and blog participation

### 👨‍🏫 Tutor/Instructor
- **Create Content** - Publish tutorials and courses
- **Accept Tasks** - Help students with challenges
- **Manage Sessions** - Schedule and conduct live classes
- **Track Earnings** - Monitor Pi coin earnings
- **Student Management** - View student progress
- **Content Analytics** - Track content performance

### 👑 Administrator
- **User Management** - Create, edit, and manage users
- **Content Moderation** - Approve/reject submissions
- **System Analytics** - View comprehensive reports
- **Payment Processing** - Manage Pi transactions
- **Security Monitoring** - Audit logs and access control
- **System Configuration** - Platform settings and maintenance

---

## 📁 Project Structure

```
homework-help/
├── 📄 index.php                 # Landing page with Pi integration
├── 📄 db.php                    # Database connection & configuration
├── 📄 pi_config.php             # Pi Network API configuration
├── 📄 header.php                # Mobile-optimized navigation header
├── 📄 footer.php                # Site footer with links
├── 📄 role_handler.php          # User role management system
├── 📄 logger.php                # Activity logging system
├── 📄 admin_logger.php          # Admin-specific logging
├── 📄 db_schema.sql             # Database schema & initial data
├── 📄 README.md                 # This documentation
│
├── 🔐 auth/                     # Authentication system
│   ├── 📄 admin_login.php       # Admin authentication
│   ├── 📄 admin_process.php     # Admin login processing
│   ├── 📄 admin_logout.php      # Admin logout
│   ├── 📄 register.php          # User registration with roles
│   └── 📄 process_register.php  # Registration processing
│
├── 👑 admin/                    # Admin dashboard & management
│   ├── 📄 index.php            # Admin dashboard (terminal theme)
│   ├── 📄 logs.php             # Activity logs viewer
│   ├── 📄 video.php            # Video session management
│   └── 📄 join_session.php     # Session management
│
├── 👨‍🏫 tutor/                  # Tutor dashboard & tools
│   ├── 📄 index.php            # Tutor main dashboard
│   ├── 📄 video.php            # Video session tools
│   ├── 📁 publications/        # Content publishing system
│   │   ├── 📄 list.php         # Publication management
│   │   └── 📄 create.php       # Content creation
│   └── 📁 tasks/               # Task management
│       ├── 📄 list.php         # Available tasks
│       └── 📄 request.php      # Task request system
│
├── 🎓 learnershub/             # Student learning portal
│   ├── 📄 index.php            # Student dashboard
│   └── 📄 video.php            # Video learning tools
│
├── 📚 library/                 # Learning resource library
│   ├── 📄 catalogue.php        # Resource catalog
│   ├── 📄 details.php          # Resource details
│   └── 📁 tutorials/           # Tutorial management
│       ├── 📄 index.php        # Tutorial listing
│       └── 📄 view.php         # Tutorial viewer
│
├── 💬 forums/                  # Community discussion forums
│   ├── 📄 index.php            # Forum listing
│   ├── 📄 question.php         # Individual forum threads
│   └── 📄 ask.php              # Create new forum post
│
├── 📝 blog-catalogue/          # Blog/article platform
│   ├── 📄 index.php            # Blog listing
│   └── 📄 view.php             # Individual blog posts
│
├── 💰 api/                     # API endpoints
│   ├── 📄 notifications.php    # Notification system
│   ├── 📄 engagement.php       # User engagement tracking
│   ├── 📄 create-payment.php   # Pi payment processing
│   └── 📄 test-feature.php     # Feature testing
│
├── 🎨 assets/                  # Static assets
│   ├── 📁 css/                 # Stylesheets
│   │   └── 📄 global.css       # Mobile-optimized global styles
│   └── 📁 js/                  # JavaScript files
│       └── 📄 global.js        # Global JavaScript utilities
│
├── 📊 logs/                    # System logs
│   ├── 📄 blog.json           # Blog activity logs
│   └── 📄 video_session.json  # Video session logs
│
├── 🏆 rating-badge/           # Achievement & rating system
│   ├── 📄 index.php           # Rating dashboard
│   ├── 📄 community.php       # Community ratings
│   └── 📄 leaderboard.php     # Achievement rankings
│
├── ⚙️ sessions/               # PHP session storage
└── 📋 temp/                   # Temporary files
```

---

## 🔧 Configuration

### Environment Variables
```php
# db.php - Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_academy');
define('DB_USER', 'root');
define('DB_PASS', '');

# pi_config.php - Pi Network Configuration
define('PI_APP_API_KEY', 'your_pi_app_key');
define('PI_APP_SECRET', 'your_pi_app_secret');
define('PI_NETWORK_SANDBOX', true);
```

### File Permissions
```bash
# Set correct permissions for security
chmod 644 *.php        # PHP files
chmod 755 sessions/    # Session directory
chmod 755 logs/        # Log directory
chmod 777 temp/        # Temporary files
```

---

## 🐛 Troubleshooting

### Common Issues

#### Pi Browser Login Issues
```bash
# Check Pi Network configuration
# Verify PI_APP_API_KEY and PI_APP_SECRET
# Ensure HTTPS is enabled for Pi payments
# Check Pi Browser version compatibility
```

#### Mobile Display Problems
```bash
# Clear browser cache
# Check viewport meta tags
# Verify CSS media queries
# Test on different devices
```

#### Database Connection Errors
```bash
# Verify MySQL service is running
# Check database credentials in db.php
# Ensure database exists and is accessible
# Check MySQL user permissions
```

#### Permission Errors
```bash
# Set correct file permissions
chmod -R 755 /path/to/project/
chmod -R 777 sessions/
chmod -R 777 logs/
```

### Debug Mode
```php
# Enable debug mode in pi_config.php
define('DEBUG_MODE', true);

// Check logs in logs/ directory
// View browser developer console
// Check PHP error logs
```

---

## 🤝 Contributing

We welcome contributions! Here's how to get started:

### Development Setup
1. **Fork** the repository
2. **Clone** your fork: `git clone https://github.com/yourusername/ai-academy.git`
3. **Create** feature branch: `git checkout -b feature/amazing-feature`
4. **Install** dependencies: `composer install`
5. **Start** development server: XAMPP Apache + MySQL

### Code Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **CSS**: BEM methodology
- **Git**: Conventional commit messages

### Pull Request Process
1. **Test** your changes thoroughly
2. **Update** documentation if needed
3. **Ensure** all tests pass
4. **Submit** pull request with detailed description
5. **Wait** for review and approval

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 AI-ACADEMY

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.
```

---

## 📞 Support & Community

### Get Help
- **📧 Email:** support@ai-academy.com
- **💬 Forums:** [Community Forums](http://192.168.100.26/forums/)
- **📖 Documentation:** [Wiki](https://github.com/yourusername/ai-academy/wiki)
- **🐛 Bug Reports:** [Issues](https://github.com/yourusername/ai-academy/issues)

### Community
- **🌐 Website:** [ai-academy.com](https://ai-academy.com)
- **📱 Pi Browser:** Optimized for Pi Network
- **💰 Pi Network:** [minepi.com](https://minepi.com)
- **📱 Mobile App:** Download Pi Browser for best experience

### Social Media
- **Twitter:** [@AI_Academy](https://twitter.com/AI_Academy)
- **LinkedIn:** [AI-ACADEMY](https://linkedin.com/company/ai-academy)
- **Discord:** [AI-ACADEMY Community](https://discord.gg/ai-academy)

---

## 🙏 Acknowledgments

- **Pi Network Team** - For the revolutionary blockchain platform
- **Bootstrap Team** - For the amazing CSS framework
- **Font Awesome** - For the comprehensive icon library
- **PHP Community** - For the robust server-side language
- **Open Source Community** - For the countless libraries and tools

---

## 🎯 Roadmap

### Version 2.0 (Coming Soon)
- [ ] **AI-Powered Learning** - Personalized learning paths
- [ ] **Live Video Sessions** - Real-time tutoring
- [ ] **NFT Certificates** - Blockchain-verified credentials
- [ ] **Multi-language Support** - Global accessibility
- [ ] **Mobile App** - Native iOS/Android apps

### Version 1.5 (Current)
- [x] **Pi Network Integration** - Full Pi ecosystem support
- [x] **Mobile Optimization** - Pi Browser compatibility
- [x] **Gamification System** - Achievement and reward system
- [x] **Admin Dashboard** - Comprehensive management tools
- [x] **Role-Based Access** - Multi-user permission system

---

<div align="center">

**Made with ❤️ for the Pi Network Community**

*Empowering learners worldwide through decentralized education*

---

**🚀 Ready to start learning? [Join AI-ACADEMY Today!](http://192.168.100.26/)**

</div>