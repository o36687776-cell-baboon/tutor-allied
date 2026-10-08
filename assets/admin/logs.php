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

// Start session first
if (!isset($_SESSION)) {
    session_start();
}

// Admin Authentication Check
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . BASE_PATH . '/admin/');
    exit;
}

// Admin Logs Page
$page_title = 'Admin Activity Logs';
include '../header.php';

// Load admin logs
$admin_logs = [];
$log_file = '../admin_logs.json';
if (file_exists($log_file)) {
    $admin_logs = json_decode(file_get_contents($log_file), true) ?: [];
}

// Filter logs based on GET parameters
$filter_type = $_GET['type'] ?? 'all';
$filter_date = $_GET['date'] ?? 'all';
$filter_user = $_GET['user'] ?? 'all';

// Apply filters
$filtered_logs = array_filter($admin_logs, function($log) use ($filter_type, $filter_date, $filter_user) {
    // Type filter
    if ($filter_type !== 'all' && (!isset($log['action']) || $log['action'] !== $filter_type)) {
        return false;
    }

    // Date filter
    if ($filter_date !== 'all') {
        $log_date = date('Y-m-d', strtotime($log['timestamp']));
        if ($filter_date === 'today' && $log_date !== date('Y-m-d')) {
            return false;
        }
        if ($filter_date === 'week' && strtotime($log_date) < strtotime('-7 days')) {
            return false;
        }
        if ($filter_date === 'month' && strtotime($log_date) < strtotime('-30 days')) {
            return false;
        }
    }

    // User filter
    if ($filter_user !== 'all' && (!isset($log['user']) || $log['user'] !== $filter_user)) {
        return false;
    }

    return true;
});

// Sort logs by timestamp (newest first)
usort($filtered_logs, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

// Get unique users for filter dropdown
$unique_users = array_unique(array_column($admin_logs, 'user'));
$unique_actions = array_unique(array_column($admin_logs, 'action'));

// Statistics
$total_logs = count($admin_logs);
$today_logs = count(array_filter($admin_logs, function($log) {
    return date('Y-m-d', strtotime($log['timestamp'])) === date('Y-m-d');
}));
$week_logs = count(array_filter($admin_logs, function($log) {
    return strtotime($log['timestamp']) >= strtotime('-7 days');
}));
$month_logs = count(array_filter($admin_logs, function($log) {
    return strtotime($log['timestamp']) >= strtotime('-30 days');
}));
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Admin Activity Logs</h2>
            <small class="text-muted">Detailed audit trail of all administrative activities</small>
        </div>
        <div>
            <button id="exportLogs" class="btn btn-success me-2">
                <i class="fas fa-download me-1"></i>Export Logs
            </button>
            <button id="clearOldLogs" class="btn btn-warning">
                <i class="fas fa-trash me-1"></i>Clear Old Logs
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?php echo $total_logs; ?></h4>
                    <p class="mb-0">Total Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success"><?php echo $today_logs; ?></h4>
                    <p class="mb-0">Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info"><?php echo $week_logs; ?></h4>
                    <p class="mb-0">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning"><?php echo $month_logs; ?></h4>
                    <p class="mb-0">This Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Logs</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Action Type</label>
                    <select name="type" class="form-select">
                        <option value="all">All Actions</option>
                        <?php foreach ($unique_actions as $action): ?>
                            <option value="<?php echo $action; ?>" <?php echo $filter_type === $action ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $action)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select name="date" class="form-select">
                        <option value="all">All Time</option>
                        <option value="today" <?php echo $filter_date === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $filter_date === 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $filter_date === 'month' ? 'selected' : ''; ?>>This Month</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Admin User</label>
                    <select name="user" class="form-select">
                        <option value="all">All Users</option>
                        <?php foreach ($unique_users as $user): ?>
                            <option value="<?php echo $user; ?>" <?php echo $filter_user === $user ? 'selected' : ''; ?>>
                                <?php echo $user; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="logs.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Activity Logs (<?php echo count($filtered_logs); ?> entries)</h5>
            <div class="input-group" style="width: 300px;">
                <input type="text" id="logSearch" class="form-control" placeholder="Search logs...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="logsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Timestamp</th>
                            <th>Admin User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filtered_logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No logs found matching the current filters.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filtered_logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo date('M d, Y', strtotime($log['timestamp'])); ?></div>
                                        <small class="text-muted"><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $log['user'] ?? 'System'; ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $action_badges = [
                                            'admin_login' => 'success',
                                            'admin_logout' => 'secondary',
                                            'settings_change' => 'warning',
                                            'user_management' => 'info',
                                            'system_change' => 'danger',
                                            'security_event' => 'dark'
                                        ];
                                        $badge_class = $action_badges[$log['action']] ?? 'light';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $log['action'] ?? 'unknown')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="log-details">
                                            <?php if (isset($log['details'])): ?>
                                                <?php echo nl2br(htmlspecialchars($log['details'])); ?>
                                            <?php elseif (isset($log['data'])): ?>
                                                <pre class="mb-0 small"><?php echo json_encode($log['data'], JSON_PRETTY_PRINT); ?></pre>
                                            <?php else: ?>
                                                <em class="text-muted">No additional details</em>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <code><?php echo $log['ip'] ?? 'N/A'; ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?php echo $log['user_agent'] ?? ''; ?>">
                                            <?php echo substr($log['user_agent'] ?? 'N/A', 0, 50) . (strlen($log['user_agent'] ?? '') > 50 ? '...' : ''); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.log-details {
    max-width: 300px;
    overflow: hidden;
}

.log-details pre {
    background: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    max-height: 100px;
    overflow-y: auto;
}

.table th {
    border-top: none;
    position: sticky;
    top: 0;
    background: #343a40;
    z-index: 10;
}

.card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: none;
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}
</style>

<script>
// Search functionality
document.getElementById('logSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#logsTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('searchBtn').addEventListener('click', function() {
    document.getElementById('logSearch').focus();
});

// Export logs functionality
document.getElementById('exportLogs').addEventListener('click', function() {
    const logsData = <?php echo json_encode($filtered_logs); ?>;
    const csvContent = convertToCSV(logsData);

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `admin_logs_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert('Logs exported successfully!');
});

// Clear old logs functionality
document.getElementById('clearOldLogs').addEventListener('click', function() {
    if (confirm('Are you sure you want to clear logs older than 30 days? This action cannot be undone.')) {
        // This would typically be handled by a server-side script
        alert('Old logs cleared successfully! (This is a demo - actual clearing would require server-side processing)');
    }
});

// Convert logs to CSV
function convertToCSV(logs) {
    const headers = ['Timestamp', 'User', 'Action', 'Details', 'IP Address', 'User Agent'];
    let csv = headers.join(',') + '\n';

    logs.forEach(log => {
        const row = [
            log.timestamp,
            log.user || 'System',
            log.action || 'unknown',
            JSON.stringify(log.details || log.data || {}).replace(/"/g, '""'),
            log.ip || 'N/A',
            (log.user_agent || 'N/A').replace(/"/g, '""')
        ];
        csv += row.map(field => `"${field}"`).join(',') + '\n';
    });

    return csv;
}

// Auto-refresh logs every 30 seconds
setInterval(() => {
    if (document.visibilityState === 'visible') {
        // In a real application, you might want to refresh the logs here
        console.log('Logs auto-refresh would happen here');
    }
}, 30000);
</script>

<?php include '../footer.php'; ?>