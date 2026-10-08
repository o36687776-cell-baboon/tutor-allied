<?php
// Simple logging system for AI-ACADEMY
function logEvent($type, $data) {
    $logFile = __DIR__ . '/logs/' . $type . '.json';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'data' => $data
    ];

    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    $logs[] = $entry;
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}

function getLogs($type) {
    $logFile = __DIR__ . '/logs/' . $type . '.json';
    if (file_exists($logFile)) {
        return json_decode(file_get_contents($logFile), true) ?: [];
    }
    return [];
}
?>