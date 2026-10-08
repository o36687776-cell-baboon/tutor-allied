/**
 * Video Room WebRTC Client
 * Handles peer-to-peer video connections using WebSocket signaling
 */

class VideoRoom {
    constructor(roomId, userId, userRole = 'learner', isDemo = false) {
        this.roomId = roomId;
        this.userId = userId;
        this.userRole = userRole;
        this.isDemo = isDemo;
        this.ws = null;
        this.localStream = null;
        this.peers = new Map();
        this.isMuted = false;
        this.isVideoEnabled = true;

        // ICE servers for STUN/TURN
        this.iceServers = [
            { urls: 'stun:stun.l.google.com:19302' },
            // Add TURN servers when configured
        ];

        this.init();
    }

    async init() {
        try {
            // Show loading message
            this.showStatus('Initializing video room...');

            // Check if we're in a secure context (required for getUserMedia)
            if (!this.isSecureContext()) {
                throw new Error('Video features require HTTPS. Please ensure you\'re accessing the site over a secure connection.');
            }

            // Get user media
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });

            // Setup local video
            this.setupLocalVideo();

            if (this.isDemo) {
                // Demo mode: skip WebSocket connection
                this.showStatus('Demo mode: Video room initialized successfully');
                this.setupDemoMode();
            } else {
                // Connect to signaling server
                this.connectToSignaling();
                this.showStatus('Video room initialized successfully');
            }

        } catch (error) {
            console.error('Failed to initialize video room:', error);
            this.handleMediaError(error);
        }
    }

    connectToSignaling() {
        // Use secure WebSocket for production, fallback to local for development
        const isProduction = window.location.protocol === 'https:';
        const wsProtocol = isProduction ? 'wss:' : 'ws:';
        const wsHost = isProduction ? window.location.host : 'localhost:8080';
        const wsUrl = `${wsProtocol}//${wsHost}`;
        this.showStatus('Connecting to video server...');

        try {
            this.ws = new WebSocket(wsUrl);

            this.ws.onopen = () => {
                console.log('Connected to signaling server');
                this.showStatus('Connected to video server');
                this.joinRoom();
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleSignalingMessage(data);
                } catch (e) {
                    console.error('Failed to parse WebSocket message:', e);
                }
            };

            this.ws.onclose = (event) => {
                console.log('Disconnected from signaling server:', event.code, event.reason);
                this.showStatus('Disconnected from video server');
                // Try to reconnect after 5 seconds
                setTimeout(() => {
                    if (!this.ws || this.ws.readyState === WebSocket.CLOSED) {
                        console.log('Attempting to reconnect...');
                        this.connectToSignaling();
                    }
                }, 5000);
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showError('Failed to connect to video server. Please check your connection.');
            };

            // Connection timeout
            setTimeout(() => {
                if (this.ws.readyState === WebSocket.CONNECTING) {
                    this.ws.close();
                    this.showError('Connection to video server timed out. Server may not be running.');
                }
            }, 10000);

        } catch (error) {
            console.error('Failed to create WebSocket connection:', error);
            this.showError('Failed to initialize video connection. Please refresh the page.');
        }
    }

    joinRoom() {
        this.sendSignalingMessage({
            type: 'join',
            roomId: this.roomId,
            userId: this.userId,
            userRole: this.userRole
        });
    }

    sendSignalingMessage(message) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(message));
        }
    }

    handleSignalingMessage(data) {
        switch (data.type) {
            case 'room_joined':
                this.onRoomJoined(data.participants);
                break;
            case 'user_joined':
                this.onUserJoined(data);
                break;
            case 'user_left':
                this.onUserLeft(data);
                break;
            case 'offer':
                this.handleOffer(data);
                break;
            case 'answer':
                this.handleAnswer(data);
                break;
            case 'candidate':
                this.handleCandidate(data);
                break;
            case 'chat':
                this.onChatMessage(data);
                break;
            case 'participant_update':
                this.onParticipantUpdate(data);
                break;
            case 'error':
                this.showError(data.message);
                break;
        }
    }

    onRoomJoined(participants) {
        console.log('Joined room with participants:', participants);

        // Create peer connections for existing participants
        participants.forEach(participant => {
            if (participant.userId !== this.userId) {
                this.createPeerConnection(participant.userId);
            }
        });
    }

    onUserJoined(data) {
        console.log('User joined:', data.userId);
        if (data.userId !== this.userId) {
            this.createPeerConnection(data.userId);
        }
        this.updateParticipantsList(data.participants);
    }

    onUserLeft(data) {
        console.log('User left:', data.userId);
        if (this.peers.has(data.userId)) {
            this.peers.get(data.userId).conn.close();
            this.peers.delete(data.userId);
        }
        this.updateParticipantsList(data.participants);
        this.removeRemoteVideo(data.userId);
    }

    createPeerConnection(peerId) {
        const peerConnection = new RTCPeerConnection({
            iceServers: this.iceServers
        });

        this.peers.set(peerId, {
            conn: peerConnection,
            stream: null
        });

        // Add local stream tracks
        this.localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, this.localStream);
        });

        // Handle ICE candidates
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.sendSignalingMessage({
                    type: 'candidate',
                    roomId: this.roomId,
                    targetUserId: peerId,
                    candidate: event.candidate
                });
            }
        };

        // Handle remote stream
        peerConnection.ontrack = (event) => {
            const remoteStream = event.streams[0];
            this.peers.get(peerId).stream = remoteStream;
            this.addRemoteVideo(peerId, remoteStream);
        };

        // Create offer if we're the initiator
        if (this.shouldCreateOffer(peerId)) {
            this.createOffer(peerId);
        }
    }

    shouldCreateOffer(peerId) {
        // Simple logic: create offer if our userId is smaller
        return this.userId < peerId;
    }

    async createOffer(peerId) {
        const peer = this.peers.get(peerId);
        if (!peer) return;

        try {
            const offer = await peer.conn.createOffer();
            await peer.conn.setLocalDescription(offer);

            this.sendSignalingMessage({
                type: 'offer',
                roomId: this.roomId,
                targetUserId: peerId,
                offer: offer
            });
        } catch (error) {
            console.error('Error creating offer:', error);
        }
    }

    async handleOffer(data) {
        const peerId = data.from;
        let peer = this.peers.get(peerId);

        if (!peer) {
            this.createPeerConnection(peerId);
            peer = this.peers.get(peerId);
        }

        try {
            await peer.conn.setRemoteDescription(new RTCSessionDescription(data.data.offer));
            const answer = await peer.conn.createAnswer();
            await peer.conn.setLocalDescription(answer);

            this.sendSignalingMessage({
                type: 'answer',
                roomId: this.roomId,
                targetUserId: peerId,
                answer: answer
            });
        } catch (error) {
            console.error('Error handling offer:', error);
        }
    }

    async handleAnswer(data) {
        const peerId = data.from;
        const peer = this.peers.get(peerId);

        if (peer) {
            try {
                await peer.conn.setRemoteDescription(new RTCSessionDescription(data.data.answer));
            } catch (error) {
                console.error('Error handling answer:', error);
            }
        }
    }

    async handleCandidate(data) {
        const peerId = data.from;
        const peer = this.peers.get(peerId);

        if (peer) {
            try {
                await peer.conn.addIceCandidate(new RTCIceCandidate(data.data.candidate));
            } catch (error) {
                console.error('Error handling candidate:', error);
            }
        }
    }

    setupLocalVideo() {
        const localVideo = document.getElementById('local-video');
        if (localVideo) {
            localVideo.srcObject = this.localStream;
        }
    }

    addRemoteVideo(peerId, stream) {
        const remoteVideos = document.getElementById('remote-videos');
        if (!remoteVideos) return;

        let videoElement = document.getElementById(`remote-video-${peerId}`);
        if (!videoElement) {
            videoElement = document.createElement('video');
            videoElement.id = `remote-video-${peerId}`;
            videoElement.autoplay = true;
            videoElement.playsinline = true;
            videoElement.className = 'remote-video';
            remoteVideos.appendChild(videoElement);
        }

        videoElement.srcObject = stream;
    }

    removeRemoteVideo(peerId) {
        const videoElement = document.getElementById(`remote-video-${peerId}`);
        if (videoElement) {
            videoElement.remove();
        }
    }

    updateParticipantsList(participants) {
        const participantsList = document.getElementById('participants-list');
        if (!participantsList) return;

        participantsList.innerHTML = '';
        participants.forEach(participant => {
            const li = document.createElement('li');
            li.textContent = `User ${participant.userId} (${participant.userRole})`;
            participantsList.appendChild(li);
        });
    }

    onChatMessage(data) {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;

        const messageDiv = document.createElement('div');
        messageDiv.textContent = `User ${data.userId}: ${data.message}`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    onParticipantUpdate(data) {
        console.log('Participant update:', data);
        // Update UI based on participant state changes
    }

    sendChatMessage(message) {
        this.sendSignalingMessage({
            type: 'chat',
            roomId: this.roomId,
            message: message
        });
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        this.localStream.getAudioTracks().forEach(track => {
            track.enabled = !this.isMuted;
        });

        const type = this.isMuted ? 'mute' : 'unmute';
        this.sendSignalingMessage({
            type: type,
            roomId: this.roomId
        });

        return this.isMuted;
    }

    toggleVideo() {
        this.isVideoEnabled = !this.isVideoEnabled;
        this.localStream.getVideoTracks().forEach(track => {
            track.enabled = this.isVideoEnabled;
        });

        const type = this.isVideoEnabled ? 'video_on' : 'video_off';
        this.sendSignalingMessage({
            type: type,
            roomId: this.roomId
        });

        return this.isVideoEnabled;
    }

    leaveRoom() {
        // Close all peer connections
        this.peers.forEach(peer => {
            peer.conn.close();
        });
        this.peers.clear();

        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
        }

        if (!this.isDemo) {
            // Send leave message
            this.sendSignalingMessage({
                type: 'leave',
                roomId: this.roomId,
                userId: this.userId
            });

            // Close WebSocket
            if (this.ws) {
                this.ws.close();
            }
        }
    }

    showStatus(message) {
        console.log('Video Room Status:', message);
        // Update status display if available
        const statusElement = document.getElementById('status') || document.querySelector('.status');
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    setupDemoMode() {
        console.log('Setting up demo mode...');

        // Add mock remote video placeholder
        const remoteVideos = document.getElementById('remote-videos');
        if (remoteVideos) {
            remoteVideos.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100 text-white">
                    <div class="text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-50"></i>
                        <p>Demo Mode: Tutor video will appear here</p>
                        <small class="text-muted">Connect to a real session to see live video</small>
                    </div>
                </div>
            `;
        }

        // Mock chat functionality
        this.mockChatMessages = [
            { user: 'Tutor', message: 'Welcome to the demo session!', time: new Date() },
            { user: 'Tutor', message: 'This is how the chat works during live sessions.', time: new Date() }
        ];

        // Display initial mock messages
        this.displayMockChatMessages();

        // Simulate periodic messages
        setInterval(() => {
            if (Math.random() < 0.3) { // 30% chance every 10 seconds
                this.addMockChatMessage();
            }
        }, 10000);
    }

    displayMockChatMessages() {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;

        chatMessages.innerHTML = '';
        this.mockChatMessages.forEach(msg => {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'chat-message other';
            msgDiv.innerHTML = `<strong>${msg.user}:</strong> ${msg.message}`;
            chatMessages.appendChild(msgDiv);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    addMockChatMessage() {
        const messages = [
            'Great question! Let me explain that concept.',
            'You can use the whiteboard to draw diagrams.',
            'Feel free to ask questions anytime.',
            'Let\'s move on to the next topic.',
            'Take notes in the notes section on the right.'
        ];

        const randomMessage = messages[Math.floor(Math.random() * messages.length)];
        this.mockChatMessages.push({
            user: 'Tutor',
            message: randomMessage,
            time: new Date()
        });

        // Keep only last 10 messages
        if (this.mockChatMessages.length > 10) {
            this.mockChatMessages.shift();
        }

        this.displayMockChatMessages();
    }

    sendChatMessage(message) {
        if (this.isDemo) {
            // Add user message to chat
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                const msgDiv = document.createElement('div');
                msgDiv.className = 'chat-message own';
                msgDiv.innerHTML = `<strong>You:</strong> ${message}`;
                chatMessages.appendChild(msgDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Simulate tutor response after a delay
            setTimeout(() => {
                this.addMockChatMessage();
            }, 2000 + Math.random() * 3000); // 2-5 seconds delay
        } else {
            this.sendSignalingMessage({
                type: 'chat',
                roomId: this.roomId,
                message: message
            });
        }
    }

    isSecureContext() {
        return window.location.protocol === 'https:' ||
               window.location.hostname === 'localhost' ||
               window.location.hostname === '127.0.0.1';
    }

    handleMediaError(error) {
        console.error('Media access error:', error);

        let userMessage = 'Failed to access camera/microphone. ';
        let technicalDetails = '';

        switch (error.name) {
            case 'NotAllowedError':
                userMessage += 'Please allow camera and microphone access when prompted.';
                break;
            case 'NotFoundError':
                userMessage += 'No camera or microphone found. Please connect a camera and microphone.';
                break;
            case 'NotReadableError':
                userMessage += 'Camera or microphone is already in use by another application.';
                break;
            case 'OverconstrainedError':
                userMessage += 'Camera/microphone does not meet requirements.';
                break;
            case 'SecurityError':
                userMessage += 'Camera/microphone access is blocked. Please check your browser settings and ensure you\'re using HTTPS.';
                technicalDetails = 'This usually happens when accessing the site over HTTP instead of HTTPS.';
                break;
            case 'AbortError':
                userMessage += 'Camera/microphone access was interrupted.';
                break;
            default:
                if (error.message.includes('HTTPS')) {
                    userMessage += 'Video features require HTTPS. Please ensure you\'re accessing the site over a secure connection.';
                    technicalDetails = 'Modern browsers require HTTPS for camera/microphone access.';
                } else {
                    userMessage += 'Please check your camera/microphone permissions and try again.';
                }
        }

        this.showError(userMessage, technicalDetails);
    }

    showError(message, technicalDetails = '') {
        console.error('Video Room Error:', message);
        if (technicalDetails) {
            console.error('Technical details:', technicalDetails);
        }

        this.showStatus('Error: ' + message);

        // Show error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        errorDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Video Error:</strong> ${message}
            ${technicalDetails ? '<br><small class="text-muted">' + technicalDetails + '</small>' : ''}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(errorDiv);

        // Auto remove after 8 seconds for errors with technical details
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, technicalDetails ? 8000 : 5000);
    }
}

// Export for use in other scripts
window.VideoRoom = VideoRoom;