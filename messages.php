<?php
// Start session first - Fix session path for XAMPP
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}

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

// Only set session save path if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($session_path);
    session_start();
} elseif (!isset($_SESSION)) {
    // If session is already active but $_SESSION is not set, start it anyway
    session_start();
}

// Check user authentication BEFORE including header
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$page_title = 'Messages';
include 'header.php';

// Get current user
$current_user_id = $_SESSION['user_id'];

// Fetch conversations (recent messages grouped by conversation)
$conversations = fetchAll("
    SELECT
        CASE
            WHEN sender_id = ? THEN receiver_id
            ELSE sender_id
        END as other_user_id,
        u.username as other_username,
        m.content as last_message,
        m.created_at as last_message_time,
        COUNT(CASE WHEN m.receiver_id = ? AND m.read_at IS NULL THEN 1 END) as unread_count
    FROM messages m
    JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_user_id, u.username
    ORDER BY last_message_time DESC
", [$current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id]);
?>

<div class="container">
    <div class="row">
        <div class="col-lg-4">
            <!-- Message Threads -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Conversations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (count($conversations) > 0): ?>
                            <?php foreach ($conversations as $conv): ?>
                                <a href="#" class="list-group-item list-group-item-action message-thread"
                                   onclick="openConversation(<?php echo $conv['other_user_id']; ?>, '<?php echo htmlspecialchars($conv['other_username']); ?>')"
                                   data-user-id="<?php echo $conv['other_user_id']; ?>"
                                   data-username="<?php echo htmlspecialchars($conv['other_username']); ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold"><?php echo htmlspecialchars($conv['other_username']); ?></div>
                                            <div class="text-muted small text-truncate"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?></div>
                                            <div class="text-muted small"><?php echo date('M j, H:i', strtotime($conv['last_message_time'])); ?></div>
                                        </div>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-4 text-muted">
                                <i class="fas fa-envelope fa-3x mb-3"></i>
                                <p>No conversations yet</p>
                                <p class="small">Start a new conversation to get help</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="fas fa-plus me-2"></i>New Message
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Message View -->
            <div class="card" id="messageView">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="conversationTitle">Select a conversation</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" id="markAsRead">
                            <i class="fas fa-check"></i> Mark Read
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteConversation">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body" id="messageContent" style="height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <p>Select a conversation to view messages</p>
                    </div>
                </div>
                <div class="card-footer" id="messageInput" style="display: none;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="messageText" placeholder="Type your message..." maxlength="500">
                        <button class="btn btn-primary" id="sendMessage">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block" id="charCount">0/500 characters</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="recipientSelect" class="form-label">To:</label>
                    <select class="form-select" id="recipientSelect">
                        <option value="">Select recipient...</option>
                        <option value="admin">Admin Support</option>
                        <!-- Dynamic options will be loaded -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="messageSubject" class="form-label">Subject:</label>
                    <input type="text" class="form-control" id="messageSubject" placeholder="Message subject" maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="newMessageText" class="form-label">Message:</label>
                    <textarea class="form-control" id="newMessageText" rows="4" placeholder="Type your message..." maxlength="500"></textarea>
                    <small class="text-muted" id="newCharCount">0/500 characters</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendNewMessage">Send Message</button>
            </div>
        </div>
    </div>
</div>

<style>
.message-thread {
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.message-thread:hover {
    background-color: #f8f9fa;
}

.message-thread.active {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.message-item {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
    max-width: 70%;
}

.message-item.sent {
    background-color: #007bff;
    color: white;
    margin-left: auto;
    text-align: right;
}

.message-item.received {
    background-color: #f8f9fa;
    color: #333;
}

.message-meta {
    font-size: 0.8rem;
    opacity: 0.7;
    margin-top: 5px;
}

.unread-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    margin-left: auto;
}
</style>

<script>
let currentConversation = null;

// Load message threads (already loaded via PHP, but refresh function)
function loadMessageThreads() {
    // Reload the page to refresh conversations
    location.reload();
}

// Open conversation
function openConversation(userId, username) {
    currentConversation = { userId: parseInt(userId), username: username };

    // Update UI
    document.querySelectorAll('.message-thread').forEach(thread => {
        thread.classList.remove('active');
    });
    event.currentTarget.classList.add('active');

    document.getElementById('conversationTitle').textContent = username;
    document.getElementById('messageInput').style.display = 'block';

    // Load messages
    loadConversationMessages(userId);
}

// Load conversation messages
function loadConversationMessages(userId) {
    fetch(`/api/messages.php?action=get_conversation&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const contentDiv = document.getElementById('messageContent');
            contentDiv.innerHTML = '';

            if (!data.success || data.messages.length === 0) {
                contentDiv.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>No messages in this conversation</p>
                        <p class="small">Send a message to start the conversation</p>
                    </div>
                `;
                return;
            }

            data.messages.forEach(msg => {
                const messageElement = document.createElement('div');
                messageElement.className = `message-item ${msg.sender_id == <?php echo $current_user_id; ?> ? 'sent' : 'received'}`;

                messageElement.innerHTML = `
                    <div>${msg.content}</div>
                    <div class="message-meta">
                        ${new Date(msg.created_at).toLocaleString()}
                        ${msg.sender_id == <?php echo $current_user_id; ?> ? '<i class="fas fa-check ms-1"></i>' : ''}
                    </div>
                `;

                contentDiv.appendChild(messageElement);
            });

            // Scroll to bottom
            contentDiv.scrollTop = contentDiv.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

// Send message
document.getElementById('sendMessage').addEventListener('click', function() {
    const messageText = document.getElementById('messageText').value.trim();

    if (!messageText || !currentConversation) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('receiver_id', currentConversation.userId);
    formData.append('content', messageText);

    fetch('/api/messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            document.getElementById('messageText').value = '';
            // Reload conversation
            loadConversationMessages(currentConversation.userId);
            loadMessageThreads();
        } else {
            alert('Failed to send message: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('An error occurred while sending the message.');
    });
});

// Character count for message input
document.getElementById('messageText').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('charCount').textContent = `${count}/500 characters`;
});

// Send new message
document.getElementById('sendNewMessage').addEventListener('click', function() {
    const recipient = document.getElementById('recipientSelect').value;
    const subject = document.getElementById('messageSubject').value.trim();
    const content = document.getElementById('newMessageText').value.trim();

    if (!recipient || !content.trim()) {
        alert('Please fill in all required fields.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('receiver_id', recipient);
    formData.append('subject', subject);
    formData.append('content', content);

    fetch('/api/messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newMessageModal'));
            modal.hide();

            // Clear form
            document.getElementById('messageSubject').value = '';
            document.getElementById('newMessageText').value = '';

            // Reload threads
            loadMessageThreads();

            alert('Message sent successfully!');
        } else {
            alert('Failed to send message: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('An error occurred while sending the message.');
    });
});

// Character count for new message
document.getElementById('newMessageText').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('newCharCount').textContent = `${count}/500 characters`;
});

// Mark as read
document.getElementById('markAsRead').addEventListener('click', function() {
    if (!currentConversation) return;

    const formData = new FormData();
    formData.append('action', 'mark_read');
    formData.append('user_id', currentConversation.userId);

    fetch('/api/messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadMessageThreads();
            alert('All messages marked as read!');
        }
    });
});

// Delete conversation
document.getElementById('deleteConversation').addEventListener('click', function() {
    if (!currentConversation) return;

    if (confirm('Are you sure you want to delete this conversation?')) {
        const formData = new FormData();
        formData.append('action', 'delete_conversation');
        formData.append('user_id', currentConversation.userId);

        fetch('/api/messages.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentConversation = null;
                document.getElementById('messageInput').style.display = 'none';
                document.getElementById('messageContent').innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <p>Select a conversation to view messages</p>
                    </div>
                `;
                loadMessageThreads();
                alert('Conversation deleted!');
            }
        });
    }
});

// Load recipients for new message
function loadRecipients() {
    fetch('/api/messages.php?action=get_users')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('recipientSelect');

                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.username;
                    select.appendChild(option);
                });
            }
        });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadMessageThreads();
    loadRecipients();
});
</script>

<?php include 'footer.php'; ?>