<?php
// Enhanced Admin Logging System

class AdminLogger {
    private $log_file;
    private $max_logs = 10000; // Maximum number of logs to keep

    public function __construct($log_file = '../admin_logs.json') {
        $this->log_file = $log_file;
        $this->ensureLogFile();
    }

    private function ensureLogFile() {
        if (!file_exists($this->log_file)) {
            file_put_contents($this->log_file, json_encode([]));
        }
    }

    public function log($action, $details = [], $user = null, $ip = null, $user_agent = null) {
        if (!isset($_SESSION)) {
            session_start();
        }

        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $user ?: ($_SESSION['admin_username'] ?? 'system'),
            'action' => $action,
            'details' => is_string($details) ? $details : json_encode($details),
            'ip' => $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'user_agent' => $user_agent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
            'session_id' => session_id(),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];

        // Load existing logs
        $logs = json_decode(file_get_contents($this->log_file), true) ?: [];

        // Add new log entry
        array_unshift($logs, $log_entry); // Add to beginning for newest first

        // Limit log size
        if (count($logs) > $this->max_logs) {
            $logs = array_slice($logs, 0, $this->max_logs);
        }

        // Save logs
        file_put_contents($this->log_file, json_encode($logs, JSON_PRETTY_PRINT));

        return true;
    }

    public function logSettingsChange($setting_name, $old_value, $new_value) {
        $details = [
            'setting' => $setting_name,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'change_type' => 'settings_update'
        ];

        return $this->log('settings_change', $details);
    }

    public function logUserAction($action_type, $target_user, $details = []) {
        $log_details = array_merge([
            'target_user' => $target_user,
            'action_type' => $action_type
        ], $details);

        return $this->log('user_management', $log_details);
    }

    public function logSystemAction($action_type, $details = []) {
        $log_details = array_merge([
            'system_action' => $action_type
        ], $details);

        return $this->log('system_change', $log_details);
    }

    public function logSecurityEvent($event_type, $details = []) {
        $log_details = array_merge([
            'security_event' => $event_type,
            'severity' => $details['severity'] ?? 'medium'
        ], $details);

        return $this->log('security_event', $log_details);
    }

    public function logDatabaseAction($query_type, $table_name, $details = []) {
        $log_details = array_merge([
            'query_type' => $query_type,
            'table' => $table_name
        ], $details);

        return $this->log('database_action', $log_details);
    }

    public function logFileAction($action_type, $file_path, $details = []) {
        $log_details = array_merge([
            'file_action' => $action_type,
            'file_path' => $file_path
        ], $details);

        return $this->log('file_management', $log_details);
    }

    public function getLogs($limit = null, $filter = []) {
        $logs = json_decode(file_get_contents($this->log_file), true) ?: [];

        // Apply filters
        if (!empty($filter)) {
            $logs = array_filter($logs, function($log) use ($filter) {
                foreach ($filter as $key => $value) {
                    if (isset($log[$key]) && $log[$key] !== $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Apply limit
        if ($limit) {
            $logs = array_slice($logs, 0, $limit);
        }

        return $logs;
    }

    public function clearOldLogs($days = 30) {
        $logs = json_decode(file_get_contents($this->log_file), true) ?: [];
        $cutoff_date = strtotime("-{$days} days");

        $filtered_logs = array_filter($logs, function($log) use ($cutoff_date) {
            return strtotime($log['timestamp']) >= $cutoff_date;
        });

        file_put_contents($this->log_file, json_encode(array_values($filtered_logs), JSON_PRETTY_PRINT));

        return count($logs) - count($filtered_logs);
    }

    public function exportLogs($format = 'json', $filter = []) {
        $logs = $this->getLogs(null, $filter);

        switch ($format) {
            case 'csv':
                return $this->exportAsCSV($logs);
            case 'json':
            default:
                return json_encode($logs, JSON_PRETTY_PRINT);
        }
    }

    private function exportAsCSV($logs) {
        if (empty($logs)) {
            return '';
        }

        $headers = array_keys($logs[0]);
        $csv = implode(',', array_map(function($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\n";

        foreach ($logs as $log) {
            $row = [];
            foreach ($headers as $header) {
                $value = $log[$header] ?? '';
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $row[] = '"' . str_replace('"', '""', $value) . '"';
            }
            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }
}

// Global logger instance
$admin_logger = new AdminLogger();

// Helper functions for easy logging
function log_admin_action($action, $details = []) {
    global $admin_logger;
    return $admin_logger->log($action, $details);
}

function log_settings_change($setting, $old_value, $new_value) {
    global $admin_logger;
    return $admin_logger->logSettingsChange($setting, $old_value, $new_value);
}

function log_user_management($action, $target_user, $details = []) {
    global $admin_logger;
    return $admin_logger->logUserAction($action, $target_user, $details);
}

function log_system_change($action, $details = []) {
    global $admin_logger;
    return $admin_logger->logSystemAction($action, $details);
}

function log_security_event($event, $details = []) {
    global $admin_logger;
    return $admin_logger->logSecurityEvent($event, $details);
}

function log_database_action($query_type, $table, $details = []) {
    global $admin_logger;
    return $admin_logger->logDatabaseAction($query_type, $table, $details);
}

function log_file_action($action, $file_path, $details = []) {
    global $admin_logger;
    return $admin_logger->logFileAction($action, $file_path, $details);
}
?>