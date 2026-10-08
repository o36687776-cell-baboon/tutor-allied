<?php
// Video Session page for Learners
$page_title = 'Live Video Session';
include '../header.php';
include '../logger.php';

// Get room ID from URL or create demo room
$room_id = $_GET['room'] ?? 'demo-room-' . session_id();
$lesson_title = $_GET['lesson'] ?? 'Machine Learning Basics';
$lesson_description = $_GET['description'] ?? 'Learn the fundamentals of machine learning algorithms and their applications.';

// Check if this is demo mode (room starts with 'demo-')
$is_demo = strpos($room_id, 'demo-') === 0;

// Log session access
logEvent('video_session', ['user' => 'Student', 'action' => 'accessed', 'lesson' => $lesson_title, 'room' => $room_id]);
?>

<!-- Video Classroom Styles -->
<style>
.video-classroom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding-top: 20px;
}

.video-main-panel {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    margin-bottom: 20px;
}

.video-sidebar {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    height: fit-content;
}

.video-controls {
    background: rgba(0,0,0,0.8);
    border-radius: 25px;
    padding: 10px 20px;
    margin-top: 15px;
}

.control-btn {
    border-radius: 50px;
    width: 50px;
    height: 50px;
    margin: 0 5px;
    border: none;
    font-size: 18px;
    transition: all 0.3s ease;
}

.control-btn:hover {
    transform: scale(1.1);
}

.video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.video-tile {
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    aspect-ratio: 16/9;
}

.video-tile video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-label {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
}

.chat-messages {
    height: 300px;
    overflow-y: auto;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}

.chat-message {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    max-width: 80%;
}

.chat-message.own {
    background: #007bff;
    color: white;
    margin-left: auto;
}

.chat-message.other {
    background: #e9ecef;
    color: #333;
}

.participants-list {
    max-height: 200px;
    overflow-y: auto;
}

.participant-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
}

.participant-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 10px;
}

.participant-status.online {
    background: #28a745;
}

.whiteboard-container {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-top: 15px;
}

.whiteboard-tools {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}

.session-info {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
}

.session-status {
    padding: 10px 15px;
    border-radius: 25px;
    font-weight: bold;
    text-align: center;
    margin-top: 10px;
}

.status-waiting {
    background: #ffc107;
    color: #000;
}

.status-joined {
    background: #28a745;
    color: white;
}

.status-left {
    background: #dc3545;
    color: white;
}
</style>

<div class="video-classroom">
    <div class="container-fluid">
        <!-- Session Info Header -->
        <div class="session-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="fas fa-video me-2"></i><?php echo htmlspecialchars($lesson_title); ?></h2>
                    <p class="mb-0 opacity-90"><?php echo htmlspecialchars($lesson_description); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <div id="sessionStatus" class="session-status status-waiting">
                        <i class="fas fa-clock me-2"></i>Waiting for Tutor
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Video Panel -->
            <div class="col-lg-8">
                <div class="video-main-panel p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0"><i class="fas fa-desktop me-2"></i>Live Session</h4>
                        <div>
                            <button id="minimizeBtn" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="fas fa-compress"></i>
                            </button>
                            <button id="fullscreenBtn" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Video Grid -->
                    <div class="video-grid">
                        <div class="video-tile">
                            <video id="local-video" autoplay muted playsinline></video>
                            <div class="video-label">
                                <i class="fas fa-user me-1"></i>You (Student)
                                <span id="localStatus" class="ms-2">
                                    <i class="fas fa-microphone text-success"></i>
                                    <i class="fas fa-video text-success ms-1"></i>
                                </span>
                            </div>
                        </div>
                        <div id="remote-videos" class="video-tile">
                            <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                <div class="text-center">
                                    <i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-50"></i>
                                    <p>Waiting for tutor to start...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Controls -->
                    <div class="video-controls d-flex justify-content-center">
                        <button id="muteBtn" class="control-btn btn btn-success" title="Toggle Microphone">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button id="videoBtn" class="control-btn btn btn-primary" title="Toggle Camera">
                            <i class="fas fa-video"></i>
                        </button>
                        <button id="raiseHandBtn" class="control-btn btn btn-warning" title="Raise Hand">
                            <i class="fas fa-hand-paper"></i>
                        </button>
                        <button id="joinButton" class="control-btn btn btn-success" title="Join Session">
                            <i class="fas fa-sign-in-alt"></i>
                        </button>
                        <button id="leaveButton" class="control-btn btn btn-danger" title="Leave Session">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Whiteboard Section -->
                <div class="video-main-panel p-4">
                    <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i>Interactive Whiteboard</h5>
                    <div class="whiteboard-container">
                        <div class="whiteboard-tools">
                            <button id="clearBoard" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash me-1"></i>Clear
                            </button>
                            <div class="d-flex align-items-center">
                                <label class="me-2 text-sm">Color:</label>
                                <input type="color" id="colorPicker" value="#000000" class="form-control form-control-sm" style="width: 50px;">
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2 text-sm">Size:</label>
                                <input type="range" id="brushSize" min="1" max="20" value="3" class="form-range" style="width: 80px;">
                            </div>
                        </div>
                        <canvas id="whiteboard" width="100%" height="300" style="border: 2px solid #dee2e6; border-radius: 8px; cursor: crosshair;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Chat Panel -->
                <div class="video-sidebar p-3 mb-3">
                    <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Class Chat</h6>
                    <div id="chatMessages" class="chat-messages"></div>
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control" placeholder="Ask questions..." maxlength="500">
                        <button id="sendChat" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <!-- Participants Panel -->
                <div class="video-sidebar p-3 mb-3">
                    <h6 class="mb-3"><i class="fas fa-users me-2"></i>Classmates (<span id="participantCount">1</span>)</h6>
                    <div id="participantsList" class="participants-list">
                        <div class="participant-item">
                            <div class="participant-status online"></div>
                            <div>
                                <div class="fw-bold">You (Student)</div>
                                <small class="text-muted">Joined</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Notes -->
                <div class="video-sidebar p-3 mb-3">
                    <h6 class="mb-3"><i class="fas fa-sticky-note me-2"></i>My Notes</h6>
                    <textarea id="notes" class="form-control" rows="6" placeholder="Take notes during the lesson..."></textarea>
                    <button id="saveNotes" class="btn btn-outline-primary btn-sm mt-2 w-100">
                        <i class="fas fa-save me-1"></i>Save Notes
                    </button>
                </div>

                <!-- Learning Resources -->
                <div class="video-sidebar p-3">
                    <h6 class="mb-3"><i class="fas fa-book me-2"></i>Resources</h6>
                    <div class="d-grid gap-2">
                        <button id="downloadMaterials" class="btn btn-outline-info">
                            <i class="fas fa-download me-2"></i>Download Materials
                        </button>
                        <button id="viewSlides" class="btn btn-outline-success">
                            <i class="fas fa-file-pdf me-2"></i>View Slides
                        </button>
                        <button id="askQuestion" class="btn btn-outline-warning">
                            <i class="fas fa-question-circle me-2"></i>Ask Question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/video-room.js"></script>
<script>
let videoRoom;
let handRaised = false;

// DOM Elements
const sessionStatus = document.getElementById('sessionStatus');
const joinButton = document.getElementById('joinButton');
const leaveButton = document.getElementById('leaveButton');
const muteBtn = document.getElementById('muteBtn');
const videoBtn = document.getElementById('videoBtn');
const raiseHandBtn = document.getElementById('raiseHandBtn');
const minimizeBtn = document.getElementById('minimizeBtn');
const fullscreenBtn = document.getElementById('fullscreenBtn');
const chatInput = document.getElementById('chatInput');
const sendChat = document.getElementById('sendChat');
const chatMessages = document.getElementById('chatMessages');
const saveNotes = document.getElementById('saveNotes');
const notes = document.getElementById('notes');
const whiteboard = document.getElementById('whiteboard');
const clearBoard = document.getElementById('clearBoard');
const colorPicker = document.getElementById('colorPicker');
const brushSize = document.getElementById('brushSize');
const participantsList = document.getElementById('participantsList');
const participantCount = document.getElementById('participantCount');

// Initialize video room
const roomId = '<?php echo $room_id; ?>';
const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] ? $_SESSION['user_id'] : 'null'); ?>;
const userRole = 'learner';
const isDemo = <?php echo $is_demo ? 'true' : 'false'; ?>;

// Check if user is authenticated (skip for demo mode)
if (userId === 'null' && !isDemo) {
    alert('You must be logged in to use video sessions. Redirecting to login...');
    window.location.href = '../auth/';
}

// Event Listeners
if (joinButton) joinButton.addEventListener('click', joinSession);
if (leaveButton) leaveButton.addEventListener('click', leaveSession);
if (muteBtn) muteBtn.addEventListener('click', toggleMute);
if (videoBtn) videoBtn.addEventListener('click', toggleVideo);
if (raiseHandBtn) raiseHandBtn.addEventListener('click', toggleRaiseHand);
if (minimizeBtn) minimizeBtn.addEventListener('click', minimizeVideo);
if (fullscreenBtn) fullscreenBtn.addEventListener('click', toggleFullscreen);

if (sendChat) sendChat.addEventListener('click', sendMessage);
if (chatInput) chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

if (saveNotes) saveNotes.addEventListener('click', saveNotesFunc);
if (clearBoard) clearBoard.addEventListener('click', clearWhiteboard);

// Additional button listeners for learner
document.getElementById('downloadMaterials')?.addEventListener('click', downloadMaterials);
document.getElementById('viewSlides')?.addEventListener('click', viewSlides);
document.getElementById('askQuestion')?.addEventListener('click', askQuestion);

// Whiteboard functionality
let isDrawing = false;
let ctx = whiteboard.getContext('2d');
ctx.lineWidth = brushSize.value;
ctx.lineCap = 'round';
ctx.strokeStyle = colorPicker.value;

whiteboard.addEventListener('mousedown', startDrawing);
whiteboard.addEventListener('mousemove', draw);
whiteboard.addEventListener('mouseup', stopDrawing);
whiteboard.addEventListener('mouseout', stopDrawing);

colorPicker.addEventListener('change', () => ctx.strokeStyle = colorPicker.value);
brushSize.addEventListener('change', () => ctx.lineWidth = brushSize.value);

// Functions
function joinSession() {
    console.log('Joining session...');
    if (!videoRoom) {
        try {
            videoRoom = new VideoRoom(roomId, userId, userRole, isDemo);
            updateSessionStatus('joined', isDemo ? 'Demo Session Started' : 'Joined Session');
            joinButton.disabled = true;
            leaveButton.disabled = false;

            if (!isDemo) {
                // Log join event (commented out for testing)
                // fetch('../log_event.php?type=video_session&action=join&user=Student&lesson=<?php echo urlencode($lesson_title); ?>&room=' + roomId);
            }

            console.log('Joined session successfully');
        } catch (error) {
            console.error('Failed to join session:', error);
            alert('Failed to join session: ' + error.message);
        }
    }
}

function leaveSession() {
    if (videoRoom) {
        videoRoom.leaveRoom();
        videoRoom = null;
        updateSessionStatus('left', isDemo ? 'Demo Session Ended' : 'Left Session');
        joinButton.disabled = false;
        leaveButton.disabled = true;

        if (!isDemo) {
            // Log leave event
            fetch('../log_event.php?type=video_session&action=leave&user=Student&lesson=<?php echo urlencode($lesson_title); ?>&room=' + roomId);
        }
    }
}

function toggleMute() {
    if (videoRoom) {
        const muted = videoRoom.toggleMute();
        const icon = muteBtn.querySelector('i');
        const localStatus = document.getElementById('localStatus');

        if (muted) {
            muteBtn.className = 'control-btn btn btn-danger';
            icon.className = 'fas fa-microphone-slash';
            if (localStatus) localStatus.innerHTML = localStatus.innerHTML.replace('fa-microphone text-success', 'fa-microphone-slash text-danger');
        } else {
            muteBtn.className = 'control-btn btn btn-success';
            icon.className = 'fas fa-microphone';
            if (localStatus) localStatus.innerHTML = localStatus.innerHTML.replace('fa-microphone-slash text-danger', 'fa-microphone text-success');
        }
    }
}

function toggleVideo() {
    if (videoRoom) {
        const videoOff = videoRoom.toggleVideo();
        const icon = videoBtn.querySelector('i');
        const localStatus = document.getElementById('localStatus');

        if (videoOff) {
            videoBtn.className = 'control-btn btn btn-secondary';
            icon.className = 'fas fa-video-slash';
            if (localStatus) localStatus.innerHTML = localStatus.innerHTML.replace('fa-video text-success', 'fa-video-slash text-warning');
        } else {
            videoBtn.className = 'control-btn btn btn-primary';
            icon.className = 'fas fa-video';
            if (localStatus) localStatus.innerHTML = localStatus.innerHTML.replace('fa-video-slash text-warning', 'fa-video text-success');
        }
    }
}

function toggleRaiseHand() {
    handRaised = !handRaised;
    const icon = raiseHandBtn.querySelector('i');

    if (handRaised) {
        raiseHandBtn.className = 'control-btn btn btn-warning';
        icon.className = 'fas fa-hand-paper';
        // Send raise hand signal
        if (videoRoom) {
            videoRoom.sendSignalingMessage({
                type: 'raise_hand',
                roomId: roomId
            });
        }
    } else {
        raiseHandBtn.className = 'control-btn btn btn-outline-warning';
        icon.className = 'fas fa-hand-paper';
    }
}

function minimizeVideo() {
    const videoPanel = document.querySelector('.col-lg-8');
    const sidebar = document.querySelector('.col-lg-4');

    if (videoPanel.classList.contains('col-lg-8')) {
        videoPanel.classList.remove('col-lg-8');
        videoPanel.classList.add('col-lg-10');
        sidebar.classList.remove('col-lg-4');
        sidebar.classList.add('col-lg-2');
        minimizeBtn.innerHTML = '<i class="fas fa-expand"></i>';
    } else {
        videoPanel.classList.remove('col-lg-10');
        videoPanel.classList.add('col-lg-8');
        sidebar.classList.remove('col-lg-2');
        sidebar.classList.add('col-lg-4');
        minimizeBtn.innerHTML = '<i class="fas fa-compress"></i>';
    }
}

function toggleFullscreen() {
    const videoPanel = document.querySelector('.video-main-panel');
    if (!document.fullscreenElement) {
        videoPanel.requestFullscreen();
        fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
    } else {
        document.exitFullscreen();
        fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
    }
}

function sendMessage() {
    const message = chatInput.value.trim();
    if (message) {
        if (videoRoom) {
            videoRoom.sendChatMessage(message);
        }

        // Add message to chat
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-message own';
        msgDiv.innerHTML = `<strong>You:</strong> ${message}`;
        chatMessages.appendChild(msgDiv);
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function saveNotesFunc() {
    const notesContent = notes.value;
    localStorage.setItem('lessonNotes_' + roomId, notesContent);

    // Visual feedback
    const btn = document.getElementById('saveNotes');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

function updateSessionStatus(status, message) {
    sessionStatus.className = `session-status status-${status}`;
    sessionStatus.innerHTML = `<i class="fas fa-${status === 'joined' ? 'check-circle' : status === 'left' ? 'times-circle' : 'clock'} me-2"></i>${message}`;
}

function clearWhiteboard() {
    ctx.clearRect(0, 0, whiteboard.width, whiteboard.height);
}

// Whiteboard drawing functions
function startDrawing(e) {
    isDrawing = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
}

function draw(e) {
    if (!isDrawing) return;
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
}

function stopDrawing() {
    isDrawing = false;
}

// Resource functions
function downloadMaterials() {
    alert('Download feature coming soon! Materials will be available for download.');
}

function viewSlides() {
    alert('Slides will be displayed in the whiteboard area during the session.');
}

function askQuestion() {
    if (chatInput) {
        chatInput.focus();
        chatInput.placeholder = 'Ask your question here...';
    }
}

// Load saved notes on page load
window.addEventListener('load', () => {
    console.log('Learner video page loaded');
    console.log('Room ID:', roomId);
    console.log('User ID:', userId);
    console.log('User Role:', userRole);
    console.log('Demo Mode:', isDemo);
    console.log('Protocol:', window.location.protocol);
    console.log('Hostname:', window.location.hostname);
    console.log('Is Secure Context:', window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');

    const savedNotes = localStorage.getItem('lessonNotes_' + roomId);
    if (savedNotes) {
        notes.value = savedNotes;
    }

    // Update UI for demo mode
    if (isDemo) {
        document.getElementById('sessionStatus').className = 'session-status status-waiting';
        document.getElementById('sessionStatus').innerHTML = '<i class="fas fa-play-circle me-2"></i>Demo Mode Ready';
        document.getElementById('joinButton').innerHTML = '<i class="fas fa-play"></i>';
        document.getElementById('joinButton').title = 'Start Demo Session';

        // Add HTTPS warning for demo mode on HTTP
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'alert alert-warning mt-3';
            warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>HTTPS Required:</strong> Video features work best over HTTPS. Some features may not work properly on HTTP connections.';
            document.querySelector('.session-info').appendChild(warningDiv);
        }
    }

    // Test button functionality
    console.log('Testing button connections...');
    console.log('Join button:', joinButton);
    console.log('Leave button:', leaveButton);
    console.log('Mute button:', muteBtn);
    console.log('Video button:', videoBtn);
});

// Handle window resize for whiteboard
window.addEventListener('resize', () => {
    // Adjust whiteboard canvas size if needed
    const container = whiteboard.parentElement;
    whiteboard.width = container.offsetWidth - 30;
});
</script>
<script src="../rich-editor.js"></script>

<?php include '../footer.php'; ?>