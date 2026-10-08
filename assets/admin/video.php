<?php
// Admin Video Session page
$page_title = 'Admin Video Session';
include '../header.php';
include '../logger.php';

// Log admin session access
logEvent('video_session', ['user' => 'Admin', 'action' => 'accessed', 'lesson' => 'Admin Session']);
?>

<div class="container">
    <h2>Admin Video Session Control</h2>
    <div class="widget">
        <div class="row">
            <div class="col-md-6">
                <h4>Admin Video</h4>
                <video id="localVideo" autoplay muted style="width:100%;"></video>
                <div class="mt-2">
                    <button id="startButton" class="btn btn-primary btn-sm">Start Camera</button>
                    <button id="stopButton" class="btn btn-danger btn-sm">Stop Camera</button>
                    <button id="micToggle" class="btn btn-info btn-sm">Mic On</button>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Session Feed</h4>
                <video id="remoteVideo" autoplay style="width:100%;"></video>
                <p id="status" class="mt-2">Ready to monitor sessions.</p>
            </div>
        </div>
        <div class="mt-3">
            <button id="monitorButton" class="btn btn-warning">Monitor Active Session</button>
            <button id="endAllButton" class="btn btn-danger">End All Sessions</button>
        </div>
    </div>

    <!-- Participant Management -->
    <div class="widget mt-4">
        <h3>Participant Management</h3>
        <div id="participants">
            <div class="d-flex justify-content-between align-items-center border p-2 mb-2">
                <span>Student: John Doe</span>
                <div>
                    <button class="btn btn-success btn-sm approve-btn">Approve</button>
                    <button class="btn btn-danger btn-sm decline-btn">Decline</button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center border p-2 mb-2">
                <span>Student: Jane Smith</span>
                <div>
                    <button class="btn btn-success btn-sm approve-btn">Approve</button>
                    <button class="btn btn-danger btn-sm decline-btn">Decline</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let localStream;

const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const startButton = document.getElementById('startButton');
const stopButton = document.getElementById('stopButton');
const micToggle = document.getElementById('micToggle');
const monitorButton = document.getElementById('monitorButton');
const endAllButton = document.getElementById('endAllButton');
const status = document.getElementById('status');

startButton.addEventListener('click', startCamera);
stopButton.addEventListener('click', stopCamera);
micToggle.addEventListener('click', toggleMic);
monitorButton.addEventListener('click', monitorSession);
endAllButton.addEventListener('click', endAllSessions);

// Participant management
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.d-flex').style.backgroundColor = '#d4edda';
        this.disabled = true;
        this.nextElementSibling.disabled = true;
        alert('Participant approved');
    });
});

document.querySelectorAll('.decline-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.d-flex').style.backgroundColor = '#f8d7da';
        this.disabled = true;
        this.previousElementSibling.disabled = true;
        alert('Participant declined');
    });
});

async function startCamera() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        status.textContent = 'Camera started. Monitoring mode.';
    } catch (error) {
        console.error('Error accessing camera:', error);
        status.textContent = 'Error accessing camera.';
    }
}

function stopCamera() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localVideo.srcObject = null;
        status.textContent = 'Camera stopped.';
    }
}

function toggleMic() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            micToggle.textContent = audioTrack.enabled ? 'Mic On' : 'Mic Off';
        }
    }
}

function monitorSession() {
    status.textContent = 'Monitoring active session...';
    // Simulate monitoring
    setTimeout(() => {
        remoteVideo.srcObject = localStream; // Echo for demo
        status.textContent = 'Connected to session feed.';
    }, 2000);
}

function endAllSessions() {
    if (confirm('Are you sure you want to end all sessions?')) {
        status.textContent = 'Ending all sessions...';
        // Log admin action
        fetch('log_event.php?type=video_session&action=end_all&user=Admin&lesson=All Sessions');
        setTimeout(() => {
            status.textContent = 'All sessions ended.';
        }, 1000);
    }
}
</script>

<?php include '../footer.php'; ?>