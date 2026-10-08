<?php
/**
 * WebSocket Signaling Server for Video Classrooms
 * Uses Ratchet for WebSocket implementation
 * Handles WebRTC signaling for peer-to-peer video connections
 */

require_once __DIR__ . '/composer/vendor/autoload.php';
require_once 'db.php';
require_once 'pi_config.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SignalingServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data) {
            return;
        }

        switch ($data['type']) {
            case 'join':
                $this->handleJoin($from, $data);
                break;
            case 'offer':
            case 'answer':
            case 'candidate':
                $this->handleSignaling($from, $data);
                break;
            case 'leave':
                $this->handleLeave($from, $data);
                break;
            case 'chat':
                $this->handleChat($from, $data);
                break;
            case 'mute':
            case 'unmute':
            case 'video_on':
            case 'video_off':
                $this->handleParticipantUpdate($from, $data);
                break;
        }
    }

    protected function handleJoin(ConnectionInterface $conn, $data) {
        $roomId = $data['roomId'];
        $userId = $data['userId'] ?? null;
        $userRole = $data['userRole'] ?? 'learner';

        // Verify room exists and user has access
        if (!$this->verifyRoomAccess($roomId, $userId)) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Access denied or room not found'
            ]));
            return;
        }

        // Add to room
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }

        $this->rooms[$roomId][$conn->resourceId] = [
            'conn' => $conn,
            'userId' => $userId,
            'userRole' => $userRole,
            'joined_at' => time()
        ];

        // Update database
        $this->updateParticipantStatus($roomId, $userId, 'joined');

        // Notify others in room
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'userId' => $userId,
            'userRole' => $userRole,
            'participants' => $this->getRoomParticipants($roomId)
        ], $conn);

        // Send current participants to new user
        $conn->send(json_encode([
            'type' => 'room_joined',
            'participants' => $this->getRoomParticipants($roomId)
        ]));
    }

    protected function handleSignaling(ConnectionInterface $from, $data) {
        $roomId = $data['roomId'];
        $targetUserId = $data['targetUserId'] ?? null;

        if (!isset($this->rooms[$roomId])) {
            return;
        }

        $message = [
            'type' => $data['type'],
            'from' => $this->getUserIdFromConn($from, $roomId),
            'data' => $data
        ];

        if ($targetUserId) {
            // Send to specific user
            $this->sendToUser($roomId, $targetUserId, $message);
        } else {
            // Broadcast to room (excluding sender)
            $this->broadcastToRoom($roomId, $message, $from);
        }
    }

    protected function handleLeave(ConnectionInterface $conn, $data) {
        $roomId = $data['roomId'];
        $userId = $data['userId'] ?? null;

        if (isset($this->rooms[$roomId][$conn->resourceId])) {
            unset($this->rooms[$roomId][$conn->resourceId]);

            // Update database
            $this->updateParticipantStatus($roomId, $userId, 'left');

            // Notify others
            $this->broadcastToRoom($roomId, [
                'type' => 'user_left',
                'userId' => $userId,
                'participants' => $this->getRoomParticipants($roomId)
            ]);
        }
    }

    protected function handleChat(ConnectionInterface $from, $data) {
        $roomId = $data['roomId'];
        $message = $data['message'];
        $userId = $this->getUserIdFromConn($from, $roomId);

        if (!$userId) return;

        // Save to database
        $this->saveChatMessage($roomId, $userId, $message);

        // Broadcast to room
        $this->broadcastToRoom($roomId, [
            'type' => 'chat',
            'userId' => $userId,
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    protected function handleParticipantUpdate(ConnectionInterface $from, $data) {
        $roomId = $data['roomId'];
        $userId = $this->getUserIdFromConn($from, $roomId);

        if (!$userId) return;

        // Update database
        $this->updateParticipantState($roomId, $userId, $data['type']);

        // Broadcast to room
        $this->broadcastToRoom($roomId, [
            'type' => 'participant_update',
            'userId' => $userId,
            'update' => $data['type']
        ], $from);
    }

    protected function broadcastToRoom($roomId, $message, $exclude = null) {
        if (!isset($this->rooms[$roomId])) return;

        foreach ($this->rooms[$roomId] as $participant) {
            if ($exclude && $participant['conn'] === $exclude) continue;
            $participant['conn']->send(json_encode($message));
        }
    }

    protected function sendToUser($roomId, $userId, $message) {
        if (!isset($this->rooms[$roomId])) return;

        foreach ($this->rooms[$roomId] as $participant) {
            if ($participant['userId'] == $userId) {
                $participant['conn']->send(json_encode($message));
                break;
            }
        }
    }

    protected function getUserIdFromConn(ConnectionInterface $conn, $roomId) {
        if (!isset($this->rooms[$roomId][$conn->resourceId])) return null;
        return $this->rooms[$roomId][$conn->resourceId]['userId'];
    }

    protected function getRoomParticipants($roomId) {
        if (!isset($this->rooms[$roomId])) return [];

        $participants = [];
        foreach ($this->rooms[$roomId] as $participant) {
            $participants[] = [
                'userId' => $participant['userId'],
                'userRole' => $participant['userRole'],
                'joined_at' => $participant['joined_at']
            ];
        }
        return $participants;
    }

    protected function verifyRoomAccess($roomId, $userId) {
        // Check if room exists and is active
        $room = fetchOne("SELECT * FROM video_rooms WHERE uuid = ? AND status = 'active'", [$roomId]);
        if (!$room) return false;

        // For now, allow access if room exists (add more validation as needed)
        return true;
    }

    protected function updateParticipantStatus($roomId, $userId, $status) {
        if (!$userId) return;

        $room = fetchOne("SELECT id FROM video_rooms WHERE uuid = ?", [$roomId]);
        if (!$room) return;

        if ($status === 'joined') {
            // Insert or update participant
            executeUpdate(
                "INSERT INTO room_participants (room_id, user_id, joined_at) VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE joined_at = NOW(), left_at = NULL",
                [$room['id'], $userId]
            );
        } elseif ($status === 'left') {
            executeUpdate(
                "UPDATE room_participants SET left_at = NOW() WHERE room_id = ? AND user_id = ? AND left_at IS NULL",
                [$room['id'], $userId]
            );
        }
    }

    protected function updateParticipantState($roomId, $userId, $state) {
        $room = fetchOne("SELECT id FROM video_rooms WHERE uuid = ?", [$roomId]);
        if (!$room) return;

        $column = '';
        $value = null;

        switch ($state) {
            case 'mute': $column = 'is_muted'; $value = true; break;
            case 'unmute': $column = 'is_muted'; $value = false; break;
            case 'video_on': $column = 'is_video_enabled'; $value = true; break;
            case 'video_off': $column = 'is_video_enabled'; $value = false; break;
        }

        if ($column) {
            executeUpdate(
                "UPDATE room_participants SET $column = ? WHERE room_id = ? AND user_id = ?",
                [$value, $room['id'], $userId]
            );
        }
    }

    protected function saveChatMessage($roomId, $userId, $message) {
        $room = fetchOne("SELECT id FROM video_rooms WHERE uuid = ?", [$roomId]);
        if (!$room) return;

        executeUpdate(
            "INSERT INTO room_messages (room_id, user_id, content, message_type) VALUES (?, ?, ?, 'chat')",
            [$room['id'], $userId, $message]
        );
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        // Remove from all rooms
        foreach ($this->rooms as $roomId => $participants) {
            if (isset($participants[$conn->resourceId])) {
                $userId = $participants[$conn->resourceId]['userId'];
                unset($this->rooms[$roomId][$conn->resourceId]);

                // Update database
                $this->updateParticipantStatus($roomId, $userId, 'left');

                // Notify others
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_left',
                    'userId' => $userId,
                    'participants' => $this->getRoomParticipants($roomId)
                ]);
                break;
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Start the server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SignalingServer()
        )
    ),
    8080  // WebSocket port
);

echo "WebSocket Signaling Server started on port 8080\n";
$server->run();
