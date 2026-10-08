<?php
// Start session first
if (!isset($_SESSION)) {
    session_start();
}

// Check user authentication BEFORE including header
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$page_title = 'Messages';
include 'header.php';
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
                    <div class="list-group list-group-flush" id="messageThreads">
                        <!-- Message threads will be loaded here -->
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
// Mock message data
let currentConversation = null;
let messages = JSON.parse(localStorage.getItem('userMessages') || '[]');

// Load message threads
function loadMessageThreads() {
    const threadsContainer = document.getElementById('messageThreads');
    const userMessages = {};

    // Group messages by conversation
    messages.forEach(msg => {
        const conversationId = msg.from === 'user' ? msg.to : msg.from;
        if (!userMessages[conversationId]) {
            userMessages[conversationId] = [];
        }
        userMessages[conversationId].push(msg);
    });

    threadsContainer.innerHTML = '';

    Object.keys(userMessages).forEach(conversationId => {
        const conversationMessages = userMessages[conversationId];
        const lastMessage = conversationMessages[conversationMessages.length - 1];
        const unreadCount = conversationMessages.filter(m => !m.read && m.to === 'user').length;

        const threadElement = document.createElement('div');
        threadElement.className = 'list-group-item list-group-item-action message-thread';
        threadElement.onclick = () => openConversation(conversationId);

        threadElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="fw-bold">${conversationId === 'admin' ? 'Admin Support' : conversationId}</div>
                    <div class="text-muted small text-truncate">${lastMessage.subject || lastMessage.content}</div>
                    <div class="text-muted small">${new Date(lastMessage.timestamp).toLocaleDateString()}</div>
                </div>
                ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
            </div>
        `;

        threadsContainer.appendChild(threadElement);
    });

    // If no conversations, show empty state
    if (Object.keys(userMessages).length === 0) {
        threadsContainer.innerHTML = `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-envelope fa-3x mb-3"></i>
                <p>No conversations yet</p>
                <p class="small">Start a new conversation to get help</p>
            </div>
        `;
    }
}

// Open conversation
function openConversation(conversationId) {
    currentConversation = conversationId;

    // Update UI
    document.querySelectorAll('.message-thread').forEach(thread => {
        thread.classList.remove('active');
    });
    event.currentTarget.classList.add('active');

    document.getElementById('conversationTitle').textContent = conversationId === 'admin' ? 'Admin Support' : conversationId;
    document.getElementById('messageInput').style.display = 'block';

    // Load messages
    loadConversationMessages(conversationId);
}

// Load conversation messages
function loadConversationMessages(conversationId) {
    const contentDiv = document.getElementById('messageContent');
    const conversationMessages = messages.filter(msg =>
        (msg.from === conversationId && msg.to === 'user') ||
        (msg.from === 'user' && msg.to === conversationId)
    );

    contentDiv.innerHTML = '';

    if (conversationMessages.length === 0) {
        contentDiv.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>No messages in this conversation</p>
                <p class="small">Send a message to start the conversation</p>
            </div>
        `;
        return;
    }

    conversationMessages.forEach(msg => {
        const messageElement = document.createElement('div');
        messageElement.className = `message-item ${msg.from === 'user' ? 'sent' : 'received'}`;

        messageElement.innerHTML = `
            <div>${msg.content}</div>
            <div class="message-meta">
                ${new Date(msg.timestamp).toLocaleString()}
                ${msg.from === 'user' ? '<i class="fas fa-check ms-1"></i>' : ''}
            </div>
        `;

        contentDiv.appendChild(messageElement);
    });

    // Scroll to bottom
    contentDiv.scrollTop = contentDiv.scrollHeight;

    // Mark as read
    conversationMessages.forEach(msg => {
        if (msg.to === 'user' && !msg.read) {
            msg.read = true;
        }
    });
    localStorage.setItem('userMessages', JSON.stringify(messages));
    loadMessageThreads(); // Refresh thread list
}

// Send message
document.getElementById('sendMessage').addEventListener('click', function() {
    const messageText = document.getElementById('messageText').value.trim();

    if (!messageText || !currentConversation) {
        return;
    }

    const newMessage = {
        id: Date.now(),
        from: 'user',
        to: currentConversation,
        content: messageText,
        timestamp: new Date().toISOString(),
        read: false
    };

    messages.push(newMessage);
    localStorage.setItem('userMessages', JSON.stringify(messages));

    // Clear input
    document.getElementById('messageText').value = '';

    // Reload conversation
    loadConversationMessages(currentConversation);
    loadMessageThreads();
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

    const newMessage = {
        id: Date.now(),
        from: 'user',
        to: recipient,
        subject: subject,
        content: content,
        timestamp: new Date().toISOString(),
        read: false
    };

    messages.push(newMessage);
    localStorage.setItem('userMessages', JSON.stringify(messages));

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newMessageModal'));
    modal.hide();

    // Clear form
    document.getElementById('messageSubject').value = '';
    document.getElementById('newMessageText').value = '';

    // Reload threads
    loadMessageThreads();

    alert('Message sent successfully!');
});

// Character count for new message
document.getElementById('newMessageText').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('newCharCount').textContent = `${count}/500 characters`;
});

// Mark as read
document.getElementById('markAsRead').addEventListener('click', function() {
    if (!currentConversation) return;

    messages.forEach(msg => {
        if (msg.to === 'user' && !msg.read) {
            msg.read = true;
        }
    });
    localStorage.setItem('userMessages', JSON.stringify(messages));
    loadMessageThreads();
    alert('All messages marked as read!');
});

// Delete conversation
document.getElementById('deleteConversation').addEventListener('click', function() {
    if (!currentConversation) return;

    if (confirm('Are you sure you want to delete this conversation?')) {
        messages = messages.filter(msg =>
            !((msg.from === currentConversation && msg.to === 'user') ||
              (msg.from === 'user' && msg.to === currentConversation))
        );
        localStorage.setItem('userMessages', JSON.stringify(messages));

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

// Load recipients for new message
function loadRecipients() {
    const select = document.getElementById('recipientSelect');

    // Add some mock recipients
    const mockRecipients = [
        {value: 'admin', text: 'Admin Support'},
        {value: 'support_team', text: 'Support Team'},
        {value: 'tutor_john', text: 'Tutor John'},
        {value: 'tutor_sarah', text: 'Tutor Sarah'}
    ];

    mockRecipients.forEach(recipient => {
        const option = document.createElement('option');
        option.value = recipient.value;
        option.textContent = recipient.text;
        select.appendChild(option);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadMessageThreads();
    loadRecipients();
});
</script>

<?php include 'footer.php'; ?>