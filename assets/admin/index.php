<?php
// Dynamic base path detection for different deployment environments
if (!defined('BASE_PATH')) {
    // Get the directory of the current script
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    // Get the parent directory (deployment subdirectory)
    $base_path = dirname($script_dir);

    // If at root level, use empty string; otherwise use the subdirectory path
    if ($base_path === '/' || $base_path === '\\' || $base_path === '.') {
        $base_path = '';
    }

    define('BASE_PATH', $base_path);
}

// Start session FIRST before any output
session_start();

error_log("Admin index.php accessed at " . date('Y-m-d H:i:s') . " by " . $_SERVER['REMOTE_ADDR']);
// Admin Dashboard for AI-ACADEMY
$page_title = 'Admin Dashboard';

// Admin Authentication Check
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/admin/');
    exit;
}

// Admin session timeout (30 minutes)
$session_timeout = 30 * 60; // 30 minutes in seconds
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > $session_timeout) {
    session_destroy();
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/admin/&timeout=1');
    exit;
}

include '../logger.php';
include '../admin_logger.php';

// Check for login success notification
$showLoginSuccess = isset($_GET['login']) && $_GET['login'] === 'success';

// Mock data for dashboard
$user_count = 1000; // Query: SELECT COUNT(*) FROM users
$tutor_count = 50;
$publication_count = 200;
$forum_posts = 500;

// Get logs
$video_logs = getLogs('video_session');
?>

<div class="nerd-container">
    <!-- Matrix Rain Background -->
    <div id="matrix-bg"></div>

    <!-- Terminal Header -->
    <div class="terminal-header">
        <div class="terminal-title">
            <i class="fas fa-terminal"></i>
            <span class="typing-effect" data-text="ADMIN@AI-ACADEMY:~#"></span>
        </div>
        <div class="terminal-controls">
            <div class="control-btn red"></div>
            <div class="control-btn yellow"></div>
            <div class="control-btn green"></div>
        </div>
    </div>

    <!-- Login Success Notification -->
    <?php if ($showLoginSuccess): ?>
    <div class="login-success-notification">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Login Successful!</strong> Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>.
            You have successfully authenticated to the admin dashboard.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Command Line Interface -->
    <div class="cli-interface">
        <div class="cli-output">
            <div class="cli-line">
                <span class="cli-prompt">admin@ai-academy:~$</span>
                <span class="cli-command">whoami</span>
            </div>
            <div class="cli-line">
                <span class="cli-output-text"><?php echo $_SESSION['admin_username']; ?></span>
            </div>
            <div class="cli-line">
                <span class="cli-prompt">admin@ai-academy:~$</span>
                <span class="cli-command">uptime</span>
            </div>
            <div class="cli-line">
                <span class="cli-output-text">Session: <?php echo gmdate("H:i:s", $session_timeout - (time() - $_SESSION['admin_login_time'])); ?> remaining</span>
            </div>
        </div>
    </div>

    <!-- System Status Bar -->
    <div class="system-status">
        <div class="status-item">
            <i class="fas fa-server"></i>
            <span>SYSTEM STATUS: ONLINE</span>
        </div>
        <div class="status-item">
            <i class="fas fa-shield-alt"></i>
            <span>SECURITY: ACTIVE</span>
        </div>
        <div class="status-item">
            <i class="fas fa-database"></i>
            <span>DATABASE: CONNECTED</span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="nerd-btn" onclick="window.location.href='logs.php'">
            <i class="fas fa-file-code"></i>
            <span>VIEW LOGS</span>
        </button>
        <button class="nerd-btn" onclick="showCodeGenerator()">
            <i class="fas fa-code"></i>
            <span>CODE GENERATOR</span>
        </button>
        <button class="nerd-btn" onclick="showRoleSwitcher()">
            <i class="fas fa-exchange-alt"></i>
            <span>ROLE SWITCHER</span>
        </button>
        <button class="nerd-btn" onclick="window.location.href='content_approval.php'">
            <i class="fas fa-check-circle"></i>
            <span>CONTENT APPROVAL</span>
        </button>
        <button class="nerd-btn" onclick="manageTutors()">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>MANAGE TUTORS</span>
        </button>
        <button class="nerd-btn" onclick="manageLibrary()">
            <i class="fas fa-book"></i>
            <span>MANAGE LIBRARY</span>
        </button>
        <button class="nerd-btn" onclick="manageRatings()">
            <i class="fas fa-star"></i>
            <span>MANAGE RATINGS</span>
        </button>
        <button class="nerd-btn" onclick="manageMessages()">
            <i class="fas fa-envelope"></i>
            <span>MANAGE MESSAGES</span>
        </button>
        <button class="nerd-btn" onclick="managePremium()">
            <i class="fas fa-crown"></i>
            <span>MANAGE PREMIUM</span>
        </button>
        <button class="nerd-btn" onclick="switchToLearner()">
            <i class="fas fa-graduation-cap"></i>
            <span>LEARNER MODE</span>
        </button>
        <button class="nerd-btn" onclick="switchToTutor()">
            <i class="fas fa-user-graduate"></i>
            <span>TUTOR MODE</span>
        </button>
        <button class="nerd-btn" onclick="window.location.href='../admin_setup.php'">
            <i class="fas fa-key"></i>
            <span>ACCESS CODES</span>
        </button>
        <button class="nerd-btn logout-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-power-off"></i>
            <span>LOGOUT</span>
        </button>
    </div>
    <!-- System Metrics Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="metric-data">
                <div class="metric-value"><?php echo $user_count; ?></div>
                <div class="metric-label">TOTAL_USERS</div>
            </div>
            <div class="metric-glow"></div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="metric-data">
                <div class="metric-value"><?php echo $tutor_count; ?></div>
                <div class="metric-label">ACTIVE_TUTORS</div>
            </div>
            <div class="metric-glow"></div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="metric-data">
                <div class="metric-value"><?php echo $publication_count; ?></div>
                <div class="metric-label">PUBLICATIONS</div>
            </div>
            <div class="metric-glow"></div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="metric-data">
                <div class="metric-value"><?php echo $forum_posts; ?></div>
                <div class="metric-label">FORUM_POSTS</div>
            </div>
            <div class="metric-glow"></div>
        </div>
    </div>

    <!-- System Log Terminal -->
    <div class="system-log">
        <div class="log-header">
            <i class="fas fa-terminal"></i>
            <span>SYSTEM LOG - RECENT ACTIVITIES</span>
        </div>
        <div class="log-entries">
            <div class="log-entry">
                <span class="log-timestamp">[<?php echo date('H:i:s'); ?>]</span>
                <span class="log-level info">INFO</span>
                <span class="log-message">New user registered: John Doe</span>
            </div>
            <div class="log-entry">
                <span class="log-timestamp">[<?php echo date('H:i:s', time()-300); ?>]</span>
                <span class="log-level success">SUCCESS</span>
                <span class="log-message">Publication approved: AI Basics</span>
            </div>
            <div class="log-entry">
                <span class="log-timestamp">[<?php echo date('H:i:s', time()-600); ?>]</span>
                <span class="log-level warning">WARNING</span>
                <span class="log-message">Forum post flagged for review</span>
            </div>
        </div>
    </div>

</div>

<script>
// Tutorial management actions
document.querySelectorAll('.btn-warning').forEach(btn => {
    if (btn.textContent === 'Edit') {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const title = row.cells[0].textContent;
            alert(`Edit tutorial: ${title}`);
        });
    }
});

document.querySelectorAll('.btn-danger').forEach(btn => {
    if (btn.textContent === 'Delete') {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const title = row.cells[0].textContent;
            if (confirm(`Are you sure you want to delete "${title}"?`)) {
                row.remove();
                alert('Tutorial deleted successfully');
            }
        });
    }
    if (btn.textContent === 'Reject') {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const title = row.cells[0].textContent;
            if (confirm(`Reject tutorial: ${title}?`)) {
                row.cells[3].innerHTML = '<span class="badge bg-danger">Rejected</span>';
                this.style.display = 'none';
                this.previousElementSibling.style.display = 'none';
                alert('Tutorial rejected');
            }
        });
    }
});

document.querySelectorAll('.btn-success').forEach(btn => {
    if (btn.textContent === 'Approve') {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const title = row.cells[0].textContent;
            if (confirm(`Approve tutorial: ${title}?`)) {
                row.cells[3].innerHTML = '<span class="badge bg-success">Approved</span>';
                this.style.display = 'none';
                this.nextElementSibling.style.display = 'none';
                alert('Tutorial approved and published');
            }
        });
    }
});

// Load blog submissions
function loadBlogSubmissions() {
    const submissions = JSON.parse(localStorage.getItem('userBlogs') || '[]');
    const container = document.getElementById('blogSubmissions');

    if (submissions.length > 0) {
        submissions.forEach((blog, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${blog.title}</td>
                <td>${blog.author}</td>
                <td>${blog.category}</td>
                <td><span class="badge bg-warning">${blog.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-success approve-blog" data-index="${index}">Approve</button>
                    <button class="btn btn-sm btn-danger reject-blog" data-index="${index}">Reject</button>
                    <button class="btn btn-sm btn-info view-blog" data-index="${index}">View</button>
                </td>
            `;
            container.appendChild(row);
        });

        // Add event listeners for blog actions
        document.querySelectorAll('.approve-blog').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;
                if (confirm('Approve this blog post?')) {
                    submissions[index].status = 'approved';
                    localStorage.setItem('userBlogs', JSON.stringify(submissions));
                    this.closest('td').previousElementSibling.innerHTML = '<span class="badge bg-success">approved</span>';
                    this.style.display = 'none';
                    this.nextElementSibling.style.display = 'none';
                    alert('Blog approved and published!');
                }
            });
        });

        document.querySelectorAll('.reject-blog').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;
                if (confirm('Reject this blog post?')) {
                    submissions[index].status = 'rejected';
                    localStorage.setItem('userBlogs', JSON.stringify(submissions));
                    this.closest('td').previousElementSibling.innerHTML = '<span class="badge bg-danger">rejected</span>';
                    this.style.display = 'none';
                    this.previousElementSibling.style.display = 'none';
                    alert('Blog rejected.');
                }
            });
        });

        document.querySelectorAll('.view-blog').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;
                const blog = submissions[index];
                alert(`Title: ${blog.title}\nAuthor: ${blog.author}\nContent Preview: ${blog.content.substring(0, 200)}...`);
            });
        });
    } else {
        container.innerHTML = '<tr><td colspan="5" class="text-center">No blog submissions pending.</td></tr>';
    }
}

// Rating system management
document.getElementById('refreshRatings').addEventListener('click', function() {
    // Simulate refreshing rating data
    const currentRating = parseFloat(document.getElementById('currentRating').textContent);
    const newRating = (currentRating + (Math.random() - 0.5) * 0.2).toFixed(1);
    document.getElementById('currentRating').textContent = Math.max(1, Math.min(5, newRating));

    const currentReviews = parseInt(document.getElementById('totalReviews').textContent.replace(',', ''));
    const newReviews = currentReviews + Math.floor(Math.random() * 10);
    document.getElementById('totalReviews').textContent = newReviews.toLocaleString();

    alert('Rating data refreshed successfully!');
});

document.getElementById('exportRatings').addEventListener('click', function() {
    const ratingData = {
        overall_rating: document.getElementById('currentRating').textContent,
        total_reviews: document.getElementById('totalReviews').textContent,
        export_date: new Date().toISOString(),
        exported_by: 'Admin'
    };

    // Create and download JSON file
    const dataStr = JSON.stringify(ratingData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);

    const link = document.createElement('a');
    link.href = url;
    link.download = 'ai-academy-ratings-export.json';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert('Rating report exported successfully!');
});

// System Control Functions
document.getElementById('restartServer').addEventListener('click', function() {
    if (confirm('Are you sure you want to restart the server? This will temporarily disconnect all users.')) {
        this.innerHTML = '<i class="fas fa-sync-alt fa-spin me-2"></i>Restarting...';

        // Log system restart action
        console.log('System restart initiated by admin');

        setTimeout(() => {
            this.innerHTML = 'Restart Server';
            alert('Server restarted successfully!');

            // Log successful restart
            console.log('System restart completed');
        }, 3000);
    }
});

document.getElementById('clearCache').addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clearing...';
    setTimeout(() => {
        this.innerHTML = 'Clear Cache';
        alert('Cache cleared successfully!');
    }, 2000);
});

document.getElementById('backupData').addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Backing up...';
    setTimeout(() => {
        this.innerHTML = 'Backup Data';
        alert('Data backup completed successfully!');
    }, 3000);
});

// Database Console Functions
document.getElementById('executeQuery').addEventListener('click', function() {
    const query = document.getElementById('sqlQuery').value.trim();
    const resultDiv = document.getElementById('queryResult');

    if (!query) {
        resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a SQL query.</div>';
        return;
    }

    resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Executing query...</div>';

    // Log database query execution
    console.log('Database query executed:', query.substring(0, 100) + (query.length > 100 ? '...' : ''));

    setTimeout(() => {
        // Mock query execution
        if (query.toLowerCase().includes('select')) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">Query executed successfully!</div>
                <table class="table table-sm">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td>John Doe</td><td>john@example.com</td></tr>
                        <tr><td>2</td><td>Jane Smith</td><td>jane@example.com</td></tr>
                    </tbody>
                </table>
            `;

            // Log successful SELECT query
            console.log('SELECT query completed successfully');
        } else {
            resultDiv.innerHTML = '<div class="alert alert-success">Query executed successfully! Rows affected: 1</div>';

            // Log other query types
            console.log('Database modification query completed');
        }
    }, 1500);
});

document.getElementById('showTables').addEventListener('click', function() {
    const resultDiv = document.getElementById('queryResult');
    resultDiv.innerHTML = `
        <div class="alert alert-info">Database Tables:</div>
        <ul class="list-group">
            <li class="list-group-item">users</li>
            <li class="list-group-item">courses</li>
            <li class="list-group-item">enrollments</li>
            <li class="list-group-item">reviews</li>
            <li class="list-group-item">messages</li>
        </ul>
    `;
});

document.getElementById('exportDB').addEventListener('click', function() {
    const dbData = {
        tables: ['users', 'courses', 'enrollments', 'reviews', 'messages'],
        export_date: new Date().toISOString(),
        exported_by: 'Admin'
    };

    const dataStr = JSON.stringify(dbData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);

    const link = document.createElement('a');
    link.href = url;
    link.download = 'database-export.json';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert('Database exported successfully!');
});

// Traffic & Transactions Functions
document.getElementById('refreshTraffic').addEventListener('click', function() {
    const trafficLog = document.getElementById('trafficLog');
    const transactionLog = document.getElementById('transactionLog');

    // Add new mock entries
    const newTraffic = document.createElement('div');
    newTraffic.className = 'border p-2 mb-1 small';
    newTraffic.innerHTML = `<strong>192.168.1.10${Math.floor(Math.random() * 9)}</strong> - GET /admin/ - 200 OK - ${Math.floor(Math.random() * 5) + 1}.${Math.floor(Math.random() * 9)}KB - Just now`;

    const newTransaction = document.createElement('div');
    newTransaction.className = 'border p-2 mb-1 small';
    newTransaction.innerHTML = `<span class="badge bg-success">Payment</span> User${Math.floor(Math.random() * 1000)} - ${Math.floor(Math.random() * 100)}.99 π - Course: Tutorial - Just now`;

    trafficLog.insertBefore(newTraffic, trafficLog.firstChild);
    transactionLog.insertBefore(newTransaction, transactionLog.firstChild);

    // Remove old entries if too many
    while (trafficLog.children.length > 10) {
        trafficLog.removeChild(trafficLog.lastChild);
    }
    while (transactionLog.children.length > 10) {
        transactionLog.removeChild(transactionLog.lastChild);
    }

    alert('Traffic logs refreshed!');
});

// User Session Management
document.getElementById('refreshSessions').addEventListener('click', function() {
    const sessionTable = document.getElementById('sessionTable');

    // Update last activity times
    const rows = sessionTable.querySelectorAll('tr');
    rows.forEach(row => {
        const lastActivityCell = row.cells[4];
        if (lastActivityCell) {
            const timeOptions = ['Just now', '1 min ago', '2 min ago', '5 min ago', '10 min ago'];
            lastActivityCell.textContent = timeOptions[Math.floor(Math.random() * timeOptions.length)];
        }
    });

    alert('Session data refreshed!');
});

document.getElementById('exportSessions').addEventListener('click', function() {
    const sessionData = [
        {user_id: 1001, username: 'john_doe', role: 'student', ip: '192.168.1.100', last_activity: '2 min ago'},
        {user_id: 1002, username: 'sarah_tutor', role: 'tutor', ip: '192.168.1.101', last_activity: '5 min ago'}
    ];

    const dataStr = JSON.stringify(sessionData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);

    const link = document.createElement('a');
    link.href = url;
    link.download = 'user-sessions-export.json';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert('Session data exported successfully!');
});

// Add event listeners for session action buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-info') && e.target.textContent === 'View Details') {
        const row = e.target.closest('tr');
        const userId = row.cells[0].textContent;
        const username = row.cells[1].textContent;
        alert(`User Details:\nID: ${userId}\nUsername: ${username}\nRole: ${row.cells[2].textContent}\nIP: ${row.cells[3].textContent}`);
    }

    if (e.target.classList.contains('btn-warning') && e.target.textContent === 'Send Message') {
        const row = e.target.closest('tr');
        const username = row.cells[1].textContent;
        document.getElementById('messageRecipient').value = 'specific';
        document.getElementById('specificUser').value = username;
        document.getElementById('specificUserField').style.display = 'block';
        document.getElementById('messageSubject').focus();
    }

    if (e.target.classList.contains('btn-danger') && e.target.textContent === 'Terminate') {
        const row = e.target.closest('tr');
        const username = row.cells[1].textContent;
        if (confirm(`Terminate session for ${username}?`)) {
            row.remove();
            alert('Session terminated successfully!');
        }
    }
});

// Core Page Editor Functions
document.getElementById('loadPage').addEventListener('click', function() {
    const pageSelector = document.getElementById('pageSelector');
    const pageContent = document.getElementById('pageContent');
    const selectedPage = pageSelector.value;

    if (!selectedPage) {
        alert('Please select a page to edit.');
        return;
    }

    // Mock page content loading
    const mockContent = {
        index: '<h1>Welcome to AI-ACADEMY</h1><p>This is the home page content...</p>',
        header: '<nav><ul><li>Home</li><li>About</li></ul></nav>',
        footer: '<footer><p>&copy; 2024 AI-ACADEMY</p></footer>',
        about: '<h1>About Us</h1><p>Learn about our mission...</p>',
        contacts: '<h1>Contact</h1><p>Get in touch with us...</p>',
        learnershub: '<h1>Learners Hub</h1><p>Your learning dashboard...</p>',
        tutor: '<h1>Tutor Dashboard</h1><p>Manage your teaching...</p>'
    };

    pageContent.value = mockContent[selectedPage] || 'Page content not found.';
});

document.getElementById('savePage').addEventListener('click', function() {
    const pageContent = document.getElementById('pageContent').value;
    if (!pageContent.trim()) {
        alert('Please load a page first.');
        return;
    }

    // Mock save functionality
    alert('Page changes saved successfully!');
});

document.getElementById('previewPage').addEventListener('click', function() {
    const pageContent = document.getElementById('pageContent').value;
    if (!pageContent.trim()) {
        alert('Please load a page first.');
        return;
    }

    // Open preview in new window
    const previewWindow = window.open('', '_blank');
    previewWindow.document.write(`
        <html>
        <head><title>Page Preview</title></head>
        <body>
            ${pageContent}
        </body>
        </html>
    `);
});

// Messaging System Functions
document.getElementById('messageRecipient').addEventListener('change', function() {
    const specificField = document.getElementById('specificUserField');
    specificField.style.display = this.value === 'specific' ? 'block' : 'none';
});

document.getElementById('sendMessage').addEventListener('click', function() {
    const recipient = document.getElementById('messageRecipient').value;
    const subject = document.getElementById('messageSubject').value;
    const content = document.getElementById('messageContent').value;

    if (!recipient || !subject.trim() || !content.trim()) {
        alert('Please fill in all message fields.');
        return;
    }

    // Mock message sending
    const messageHistory = document.getElementById('messageHistory');
    const newMessage = document.createElement('div');
    newMessage.className = 'border p-2 mb-2';
    newMessage.innerHTML = `
        <strong>To: ${recipient === 'specific' ? document.getElementById('specificUser').value : recipient.replace('_', ' ')}</strong><br>
        <small>Subject: ${subject}</small><br>
        <small class="text-muted">Sent just now</small>
    `;

    messageHistory.insertBefore(newMessage, messageHistory.firstChild);

    // Clear form
    document.getElementById('messageSubject').value = '';
    document.getElementById('messageContent').value = '';

    alert('Message sent successfully!');
});

document.getElementById('refreshMessages').addEventListener('click', function() {
    alert('Message history refreshed!');
});

document.getElementById('viewAllMessages').addEventListener('click', function() {
    // Mock showing all messages
    const allMessages = JSON.parse(localStorage.getItem('userMessages') || '[]');
    const adminMessages = allMessages.filter(msg => msg.to === 'admin' || msg.from === 'admin');

    if (adminMessages.length === 0) {
        alert('No messages found.');
        return;
    }

    let messageList = 'All Messages:\n\n';
    adminMessages.forEach((msg, index) => {
        messageList += `${index + 1}. From: ${msg.from} | To: ${msg.to} | Subject: ${msg.subject || 'N/A'}\n`;
        messageList += `   Content: ${msg.content}\n`;
        messageList += `   Time: ${new Date(msg.timestamp).toLocaleString()}\n\n`;
    });

    alert(messageList);
});

// Settings Management Functions
document.getElementById('saveSettings').addEventListener('click', function() {
    const settings = {
        siteTitle: document.getElementById('siteTitle').value,
        siteDescription: document.getElementById('siteDescription').value,
        contactEmail: document.getElementById('contactEmail').value,
        contactPhone: document.getElementById('contactPhone').value,
        maxUploadSize: document.getElementById('maxUploadSize').value,
        sessionTimeout: document.getElementById('sessionTimeout').value,
        maintenanceMode: document.getElementById('maintenanceMode').value,
        debugMode: document.getElementById('debugMode').value
    };

    // Get old settings for comparison
    const oldSettings = JSON.parse(localStorage.getItem('adminSettings') || '{}');

    // Save to localStorage (in real app, this would be saved to database)
    localStorage.setItem('adminSettings', JSON.stringify(settings));

    // Log settings changes
    const changedSettings = [];
    Object.keys(settings).forEach(key => {
        if (oldSettings[key] !== settings[key]) {
            changedSettings.push(`${key}: "${oldSettings[key] || 'N/A'}" → "${settings[key]}"`);
        }
    });

    if (changedSettings.length > 0) {
        // In a real application, this would be sent to the server for logging
        console.log('Settings changed:', changedSettings);
        // You could make an AJAX call here to log the changes server-side
    }

    alert('Settings saved successfully!');
});

document.getElementById('resetSettings').addEventListener('click', function() {
    if (confirm('Reset all settings to defaults?')) {
        document.getElementById('siteTitle').value = 'AI-ACADEMY';
        document.getElementById('siteDescription').value = 'Master AI, Software Development, and Blockchain';
        document.getElementById('contactEmail').value = 'info@homework-help.online';
        document.getElementById('contactPhone').value = '+254 758 318 070';
        document.getElementById('maxUploadSize').value = '10';
        document.getElementById('sessionTimeout').value = '60';
        document.getElementById('maintenanceMode').value = 'false';
        document.getElementById('debugMode').value = 'false';

        alert('Settings reset to defaults!');
    }
});

// Load saved settings on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBlogSubmissions();

    // Load saved settings
    const savedSettings = JSON.parse(localStorage.getItem('adminSettings'));
    if (savedSettings) {
        Object.keys(savedSettings).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = savedSettings[key];
            }
        });
    }
});
</script>

<style>
/* ===== NERD THEME STYLES ===== */
:root {
    --matrix-green: #00ff41;
    --matrix-dark: #0a0a0a;
    --matrix-darker: #050505;
    --neon-blue: #00ffff;
    --neon-pink: #ff00ff;
    --neon-orange: #ff6600;
    --terminal-bg: #1a1a1a;
    --terminal-border: #333;
}

.nerd-container {
    position: relative;
    min-height: 100vh;
    background: var(--matrix-dark);
    color: var(--matrix-green);
    font-family: 'Courier New', monospace;
    overflow-x: hidden;
}

/* ===== MATRIX RAIN BACKGROUND ===== */
#matrix-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    opacity: 0.1;
}

.matrix-rain {
    position: absolute;
    color: var(--matrix-green);
    font-size: 14px;
    font-weight: bold;
    text-shadow: 0 0 5px var(--matrix-green);
    animation: matrix-fall linear infinite;
}

@keyframes matrix-fall {
    0% { transform: translateY(-100vh); opacity: 1; }
    100% { transform: translateY(100vh); opacity: 0; }
}

/* ===== TERMINAL HEADER ===== */
.terminal-header {
    background: var(--terminal-bg);
    border: 2px solid var(--terminal-border);
    border-radius: 8px 8px 0 0;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
}

.terminal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--matrix-green);
    font-size: 18px;
    font-weight: bold;
}

.terminal-title i {
    color: var(--neon-blue);
}

.typing-effect::after {
    content: '|';
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.terminal-controls {
    display: flex;
    gap: 8px;
}

.control-btn {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.control-btn.red { background: #ff5f57; }
.control-btn.yellow { background: #ffbd2e; }
.control-btn.green { background: #28ca42; }

/* ===== CLI INTERFACE ===== */
.cli-interface {
    background: var(--terminal-bg);
    border: 2px solid var(--terminal-border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    font-family: 'Courier New', monospace;
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
}

.cli-output {
    max-height: 200px;
    overflow-y: auto;
}

.cli-line {
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cli-prompt {
    color: var(--neon-blue);
    font-weight: bold;
}

.cli-command {
    color: var(--neon-orange);
}

.cli-output-text {
    color: var(--matrix-green);
}

/* ===== SYSTEM STATUS ===== */
.system-status {
    display: flex;
    justify-content: space-around;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.status-item {
    background: var(--terminal-bg);
    border: 1px solid var(--terminal-border);
    border-radius: 8px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 0 10px rgba(0, 255, 65, 0.1);
    transition: all 0.3s ease;
}

.status-item:hover {
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
    transform: translateY(-2px);
}

.status-item i {
    color: var(--neon-blue);
    font-size: 20px;
}

.status-item span {
    color: var(--matrix-green);
    font-weight: bold;
    font-size: 14px;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 40px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.nerd-btn {
    background: linear-gradient(45deg, var(--terminal-bg), var(--matrix-darker));
    border: 2px solid var(--matrix-green);
    color: var(--matrix-green);
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    box-shadow: 0 0 10px rgba(0, 255, 65, 0.2);
    min-height: 80px;
    justify-content: center;
}

.nerd-btn:hover {
    background: linear-gradient(45deg, var(--matrix-green), var(--neon-blue));
    color: var(--matrix-dark);
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.5);
    transform: translateY(-3px);
}

.nerd-btn i {
    font-size: 20px;
}

.nerd-btn span {
    font-size: 10px;
}

.logout-btn {
    border-color: #ff4444;
    color: #ff4444;
}

.logout-btn:hover {
    background: linear-gradient(45deg, #ff4444, #ff6666);
    color: white;
}

/* ===== METRICS GRID ===== */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.metric-card {
    background: var(--terminal-bg);
    border: 2px solid var(--terminal-border);
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.1);
    transition: all 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 30px rgba(0, 255, 65, 0.3);
}

.metric-icon {
    font-size: 48px;
    color: var(--neon-blue);
    margin-bottom: 15px;
    text-shadow: 0 0 10px var(--neon-blue);
}

.metric-data {
    position: relative;
    z-index: 2;
}

.metric-value {
    font-size: 48px;
    font-weight: bold;
    color: var(--matrix-green);
    margin-bottom: 10px;
    text-shadow: 0 0 15px var(--matrix-green);
    font-family: 'Courier New', monospace;
}

.metric-label {
    font-size: 14px;
    color: var(--neon-orange);
    font-weight: bold;
    letter-spacing: 1px;
    font-family: 'Courier New', monospace;
}

.metric-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(0, 255, 65, 0.05), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.metric-card:hover .metric-glow {
    opacity: 1;
}

/* ===== SYSTEM LOG ===== */
.system-log {
    background: var(--terminal-bg);
    border: 2px solid var(--terminal-border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
}

.log-header {
    background: var(--matrix-darker);
    padding: 15px 20px;
    border-bottom: 1px solid var(--terminal-border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-header i {
    color: var(--neon-blue);
}

.log-header span {
    color: var(--matrix-green);
    font-weight: bold;
    font-family: 'Courier New', monospace;
}

.log-entries {
    max-height: 300px;
    overflow-y: auto;
}

.log-entry {
    padding: 10px 20px;
    border-bottom: 1px solid var(--terminal-border);
    display: flex;
    align-items: center;
    gap: 15px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-timestamp {
    color: var(--neon-orange);
    font-weight: bold;
}

.log-level {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.log-level.info {
    background: var(--neon-blue);
    color: var(--matrix-dark);
}

.log-level.success {
    background: var(--matrix-green);
    color: var(--matrix-dark);
}

.log-level.warning {
    background: var(--neon-orange);
    color: var(--matrix-dark);
}

.log-message {
    color: var(--matrix-green);
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .terminal-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .system-status {
        flex-direction: column;
        align-items: center;
    }

    .action-buttons {
        flex-direction: column;
        align-items: center;
    }

    .metrics-grid {
        grid-template-columns: 1fr;
    }

    .metric-card {
        margin: 0 20px;
    }
}

/* ===== LOGIN SUCCESS NOTIFICATION ===== */
.login-success-notification {
    margin: 20px;
    z-index: 1000;
}

.login-success-notification .alert-success {
    background: linear-gradient(135deg, var(--matrix-green), var(--neon-blue));
    border: 2px solid var(--matrix-green);
    color: var(--matrix-dark);
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.4);
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

.login-success-notification .alert-success .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.login-success-notification .alert-success .btn-close:hover {
    opacity: 1;
}

/* ===== ANIMATIONS ===== */
@keyframes glow {
    0%, 100% { text-shadow: 0 0 5px var(--matrix-green); }
    50% { text-shadow: 0 0 20px var(--matrix-green), 0 0 30px var(--matrix-green); }
}

.metric-value {
    animation: glow 2s ease-in-out infinite;
}

/* ===== SCROLLBAR STYLING ===== */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--matrix-darker);
}

::-webkit-scrollbar-thumb {
    background: var(--matrix-green);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--neon-blue);
}

/* ===== FOOTER STYLING FIX ===== */
footer {
    background: #212529 !important;
    color: #ffffff !important;
    border-top: 2px solid var(--matrix-green) !important;
    margin-top: 50px !important;
}

footer .container {
    background: transparent !important;
}

footer h5 {
    color: var(--matrix-green) !important;
    border-bottom: 1px solid var(--matrix-green);
    padding-bottom: 8px;
    margin-bottom: 15px;
}

footer a {
    color: #ffffff !important;
    text-decoration: none !important;
    transition: color 0.3s ease !important;
}

footer a:hover {
    color: var(--neon-blue) !important;
}

footer .text-muted {
    color: #adb5bd !important;
}

footer hr {
    border-color: var(--matrix-green) !important;
    opacity: 0.3;
}

footer .fas.fa-heart {
    color: #ff4444 !important;
}

/* Footer form styling */
footer .dropdown-menu {
    background: var(--terminal-bg) !important;
    border: 1px solid var(--terminal-border) !important;
    box-shadow: 0 4px 20px rgba(0, 255, 65, 0.2) !important;
}

footer .form-control {
    background: var(--matrix-darker) !important;
    border: 1px solid var(--terminal-border) !important;
    color: var(--matrix-green) !important;
}

footer .form-control:focus {
    background: var(--matrix-darker) !important;
    border-color: var(--neon-blue) !important;
    color: var(--matrix-green) !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 255, 255, 0.25) !important;
}

footer .btn {
    background: var(--matrix-green) !important;
    border: 1px solid var(--matrix-green) !important;
    color: var(--matrix-dark) !important;
}

footer .btn:hover {
    background: var(--neon-blue) !important;
    border-color: var(--neon-blue) !important;
    color: white !important;
}

footer .alert {
    background: var(--terminal-bg) !important;
    border: 1px solid var(--matrix-green) !important;
    color: var(--matrix-green) !important;
}
</style>

<script>
// Matrix Rain Effect
function createMatrixRain() {
    const matrixBg = document.getElementById('matrix-bg');
    const characters = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    for (let i = 0; i < 50; i++) {
        const rainDrop = document.createElement('div');
        rainDrop.className = 'matrix-rain';
        rainDrop.textContent = characters[Math.floor(Math.random() * characters.length)];
        rainDrop.style.left = Math.random() * 100 + '%';
        rainDrop.style.animationDuration = (Math.random() * 3 + 2) + 's';
        rainDrop.style.animationDelay = Math.random() * 2 + 's';
        matrixBg.appendChild(rainDrop);

        // Remove and recreate rain drops
        setTimeout(() => {
            rainDrop.remove();
        }, 5000);
    }
}

// Typing effect for terminal title
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.textContent = '';

    function type() {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }

    type();
}

// Admin Feature Functions
function showCodeGenerator() {
    alert('Code Generator Dashboard - Feature coming soon!\n\nThis will allow admins to generate code snippets, templates, and automation scripts.');
}

function showRoleSwitcher() {
    const roles = ['Admin', 'Tutor', 'Learner'];
    const currentRole = 'Admin';
    const newRole = prompt(`Current Role: ${currentRole}\n\nSwitch to role:\n1. Tutor\n2. Learner\n\nEnter number (1-2) or cancel:`);

    if (newRole === '1') {
        alert('Switching to Tutor mode...');
        window.location.href = '../tutor/';
    } else if (newRole === '2') {
        alert('Switching to Learner mode...');
        window.location.href = '../learnershub/';
    }
}

function manageTutors() {
    alert('Tutor Management Dashboard\n\nFeatures:\n- View all tutors\n- Approve/reject tutor applications\n- Manage tutor profiles\n- Monitor tutor performance\n- Assign courses to tutors');
}

function manageLibrary() {
    alert('Library Management Dashboard\n\nFeatures:\n- Add/edit courses and tutorials\n- Organize content by categories\n- Manage learning paths\n- Track content usage statistics\n- Approve user-generated content');
}

function manageRatings() {
    alert('Ratings Management Dashboard\n\nFeatures:\n- View all ratings and reviews\n- Moderate inappropriate content\n- Generate rating reports\n- Manage rating badges\n- Analyze user feedback');
}

function manageMessages() {
    alert('Messages Management Dashboard\n\nFeatures:\n- View all user messages\n- Send system announcements\n- Moderate message content\n- Manage message templates\n- Track message delivery');
}

function managePremium() {
    alert('Premium Management Dashboard\n\nFeatures:\n- Manage premium subscriptions\n- Set pricing and plans\n- Track premium user analytics\n- Manage premium content access\n- Process premium payments');
}

function switchToLearner() {
    alert('Switching to Learner Mode...\n\nYou will be redirected to the Learners Hub where you can:\n- Browse courses\n- Join live sessions\n- Track progress\n- Access learning materials');
    window.location.href = '../learnershub/';
}

function switchToTutor() {
    alert('Switching to Tutor Mode...\n\nYou will be redirected to the Tutor Dashboard where you can:\n- Manage your courses\n- View student progress\n- Schedule sessions\n- Access tutor earnings');
    window.location.href = '../tutor/';
}

// Initialize effects
document.addEventListener('DOMContentLoaded', function() {
    // Start matrix rain
    createMatrixRain();
    setInterval(createMatrixRain, 5000);

    // Typing effect
    const typingElement = document.querySelector('.typing-effect');
    if (typingElement) {
        typeWriter(typingElement, typingElement.dataset.text, 150);
    }

    // Add hover effects to metric cards
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<?php include '../footer.php'; ?>