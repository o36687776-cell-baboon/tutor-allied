-- AI-ACADEMY Production Database Backup
-- Generated: 2025-10-10
-- Version: 2.0 - Production Ready
-- Description: Complete LMS with Pi Network integration and video classrooms

-- ===========================================
-- DATABASE SETUP
-- ===========================================

CREATE DATABASE IF NOT EXISTS ai_academy
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ai_academy;

-- ===========================================
-- COMPLETE SCHEMA
-- ===========================================

-- AI-ACADEMY Production Database Schema
-- Comprehensive LMS with Pi Network Integration and Video Classrooms
-- Version: 2.0 - Production Ready
-- Created: 2025-10-10

-- ===========================================
-- CORE USER MANAGEMENT
-- ===========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    pi_wallet_address VARCHAR(100) UNIQUE,
    wallet_verified BOOLEAN DEFAULT FALSE,
    subscription_tier ENUM('free', 'premium_basic', 'premium_pro') DEFAULT 'free',
    subscription_expires DATETIME NULL,
    total_pi_earned DECIMAL(15,2) DEFAULT 0.00,
    total_pi_spent DECIMAL(15,2) DEFAULT 0.00,
    user_role ENUM('guest', 'student', 'tutor', 'admin') DEFAULT 'student',
    auth_type ENUM('pi', 'traditional') DEFAULT 'pi',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    phone VARCHAR(20),
    full_name VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create indexes for users table separately (avoiding duplicates with UNIQUE constraints)
CREATE INDEX idx_users_user_role ON users(user_role);
CREATE INDEX idx_users_is_active ON users(is_active);
-- Note: username, email, and pi_wallet_address already have UNIQUE constraints (implicit indexes)
-- Note: subscription_tier is covered by composite index idx_users_subscription_expires

-- ===========================================
-- PAYMENT & SUBSCRIPTION SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'PI',
    status ENUM('pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    pi_payment_id VARCHAR(100) UNIQUE,
    payment_type ENUM('verification', 'subscription', 'tutoring', 'content') DEFAULT 'subscription',
    description TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    refunded_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_payments_payment_type ON payments(payment_type);
-- Note: pi_payment_id already has UNIQUE constraint (implicit index)
-- Note: user_id and status are covered by composite index idx_payments_user_status

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tier ENUM('free', 'premium_basic', 'premium_pro') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'PI',
    status ENUM('active', 'cancelled', 'expired', 'pending') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_tier ON subscriptions(tier);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_expires_at ON subscriptions(expires_at);

-- ===========================================
-- LEARNING MANAGEMENT SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructor_id INT NOT NULL,
    category VARCHAR(100),
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration_hours INT,
    price DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'PI',
    max_students INT DEFAULT 50,
    enrolled_count INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    thumbnail_url VARCHAR(255),
    video_url VARCHAR(255),
    materials_url VARCHAR(255),
    tags JSON,
    prerequisites TEXT,
    learning_objectives TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_courses_instructor_id ON courses(instructor_id);
CREATE INDEX idx_courses_category ON courses(category);
CREATE INDEX idx_courses_level ON courses(level);
CREATE INDEX idx_courses_status ON courses(status);
CREATE FULLTEXT INDEX idx_courses_title_description ON courses(title, description);

CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    last_accessed_at TIMESTAMP NULL,
    certificate_issued BOOLEAN DEFAULT FALSE,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    payment_status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'PI',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
);

CREATE INDEX idx_course_enrollments_course_id ON course_enrollments(course_id);
CREATE INDEX idx_course_enrollments_progress ON course_enrollments(progress_percentage);
CREATE INDEX idx_course_enrollments_enrolled_at ON course_enrollments(enrolled_at);
-- Note: user_id is covered by composite index idx_course_enrollments_user_progress

CREATE TABLE IF NOT EXISTS course_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    order_index INT NOT NULL,
    duration_minutes INT,
    content_type ENUM('video', 'text', 'quiz', 'assignment', 'live_session') DEFAULT 'video',
    content_url VARCHAR(500),
    is_free BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE INDEX idx_course_modules_course_id ON course_modules(course_id);
CREATE INDEX idx_course_modules_order_index ON course_modules(order_index);

CREATE TABLE IF NOT EXISTS course_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    module_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    time_spent_minutes INT DEFAULT 0,
    score DECIMAL(5,2) NULL,
    completed_at TIMESTAMP NULL,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (enrollment_id, module_id)
);

CREATE INDEX idx_course_progress_enrollment_id ON course_progress(enrollment_id);
CREATE INDEX idx_course_progress_module_id ON course_progress(module_id);
CREATE INDEX idx_course_progress_completed ON course_progress(completed);

CREATE TABLE IF NOT EXISTS certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_id INT NOT NULL,
    certificate_number VARCHAR(50) UNIQUE,
    issued_date DATE NOT NULL,
    expires_date DATE NULL,
    certificate_hash VARCHAR(64) UNIQUE,
    verification_url VARCHAR(255),
    grade VARCHAR(10),
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    issued_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_certifications_user_id ON certifications(user_id);
CREATE INDEX idx_certifications_course_id ON certifications(course_id);
CREATE INDEX idx_certifications_status ON certifications(status);
-- Note: certificate_number and certificate_hash already have UNIQUE constraints (implicit indexes)

-- ===========================================
-- CONTENT MANAGEMENT SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    visibility ENUM('public', 'private', 'premium') DEFAULT 'public',
    category VARCHAR(100),
    tags JSON,
    views_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_blogs_user_id ON blogs(user_id);
CREATE INDEX idx_blogs_status ON blogs(status);
CREATE INDEX idx_blogs_visibility ON blogs(visibility);
CREATE INDEX idx_blogs_category ON blogs(category);
CREATE INDEX idx_blogs_published_at ON blogs(published_at);
CREATE FULLTEXT INDEX idx_blogs_title_content ON blogs(title, content);

CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE
);

CREATE INDEX idx_blog_comments_blog_id ON blog_comments(blog_id);
CREATE INDEX idx_blog_comments_user_id ON blog_comments(user_id);
CREATE INDEX idx_blog_comments_status ON blog_comments(status);

CREATE TABLE IF NOT EXISTS blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (blog_id, user_id)
);

CREATE INDEX idx_blog_likes_blog_id ON blog_likes(blog_id);
CREATE INDEX idx_blog_likes_user_id ON blog_likes(user_id);

-- ===========================================
-- DISCUSSION FORUMS
-- ===========================================

CREATE TABLE IF NOT EXISTS forum_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(20),
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_forum_categories_slug ON forum_categories(slug);
CREATE INDEX idx_forum_categories_order_index ON forum_categories(order_index);
CREATE INDEX idx_forum_categories_is_active ON forum_categories(is_active);

CREATE TABLE IF NOT EXISTS forum_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content LONGTEXT NOT NULL,
    status ENUM('open', 'closed', 'answered', 'pinned') DEFAULT 'open',
    visibility ENUM('public', 'private', 'premium') DEFAULT 'public',
    tags JSON,
    views_count INT DEFAULT 0,
    answers_count INT DEFAULT 0,
    votes_count INT DEFAULT 0,
    accepted_answer_id INT NULL,
    last_activity_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (accepted_answer_id) REFERENCES forum_answers(id) ON DELETE SET NULL
);

CREATE INDEX idx_forum_questions_user_id ON forum_questions(user_id);
CREATE INDEX idx_forum_questions_category_id ON forum_questions(category_id);
CREATE INDEX idx_forum_questions_status ON forum_questions(status);
CREATE INDEX idx_forum_questions_visibility ON forum_questions(visibility);
CREATE INDEX idx_forum_questions_last_activity_at ON forum_questions(last_activity_at);
CREATE FULLTEXT INDEX idx_forum_questions_title_content ON forum_questions(title, content);

CREATE TABLE IF NOT EXISTS forum_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    content LONGTEXT NOT NULL,
    is_accepted BOOLEAN DEFAULT FALSE,
    votes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES forum_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_forum_answers_question_id ON forum_answers(question_id);
CREATE INDEX idx_forum_answers_user_id ON forum_answers(user_id);
CREATE INDEX idx_forum_answers_is_accepted ON forum_answers(is_accepted);
CREATE INDEX idx_forum_answers_votes_count ON forum_answers(votes_count);

CREATE TABLE IF NOT EXISTS forum_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NULL,
    answer_id INT NULL,
    vote_type ENUM('up', 'down') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES forum_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES forum_answers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_question_vote (user_id, question_id),
    UNIQUE KEY unique_answer_vote (user_id, answer_id)
);

CREATE INDEX idx_forum_votes_user_id ON forum_votes(user_id);
CREATE INDEX idx_forum_votes_vote_type ON forum_votes(vote_type);

-- ===========================================
-- MESSAGING SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    content LONGTEXT NOT NULL,
    message_type ENUM('direct', 'system', 'notification') DEFAULT 'direct',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    parent_message_id INT NULL,
    attachments JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL
);

CREATE INDEX idx_messages_sender_id ON messages(sender_id);
CREATE INDEX idx_messages_receiver_id ON messages(receiver_id);
CREATE INDEX idx_messages_message_type ON messages(message_type);
CREATE INDEX idx_messages_is_read ON messages(is_read);
CREATE INDEX idx_messages_created_at ON messages(created_at);

CREATE TABLE IF NOT EXISTS message_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    participants JSON NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_message_threads_last_message_at ON message_threads(last_message_at);

-- ===========================================
-- VIDEO CLASSROOM SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS video_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    tutor_id INT NOT NULL,
    max_participants INT DEFAULT 50,
    is_locked BOOLEAN DEFAULT FALSE,
    start_at DATETIME NULL,
    end_at DATETIME NULL,
    status ENUM('scheduled', 'active', 'ended') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_video_rooms_tutor_id ON video_rooms(tutor_id);
CREATE INDEX idx_video_rooms_status ON video_rooms(status);
CREATE INDEX idx_video_rooms_start_at ON video_rooms(start_at);
-- Note: uuid already has UNIQUE constraint (implicit index)

CREATE TABLE IF NOT EXISTS room_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    role ENUM('tutor', 'learner', 'moderator') DEFAULT 'learner',
    is_muted BOOLEAN DEFAULT FALSE,
    is_video_enabled BOOLEAN DEFAULT TRUE,
    is_hand_raised BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (room_id) REFERENCES video_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_room_participants_room_id ON room_participants(room_id);
CREATE INDEX idx_room_participants_user_id ON room_participants(user_id);
CREATE INDEX idx_room_participants_role ON room_participants(role);

CREATE TABLE IF NOT EXISTS room_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    participant_id INT,
    storage_path VARCHAR(500),
    duration_seconds INT,
    file_size_bytes BIGINT,
    recording_type ENUM('screen', 'camera', 'mixed') DEFAULT 'mixed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES video_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES room_participants(id) ON DELETE SET NULL
);

CREATE INDEX idx_room_recordings_room_id ON room_recordings(room_id);
CREATE INDEX idx_room_recordings_recording_type ON room_recordings(recording_type);

CREATE TABLE IF NOT EXISTS room_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    message_type ENUM('chat', 'system', 'poll', 'raise_hand') DEFAULT 'chat',
    is_private BOOLEAN DEFAULT FALSE,
    recipient_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES video_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_room_messages_room_id ON room_messages(room_id);
CREATE INDEX idx_room_messages_user_id ON room_messages(user_id);
CREATE INDEX idx_room_messages_message_type ON room_messages(message_type);

-- ===========================================
-- ACTIVITY LOGGING & ANALYTICS
-- ===========================================

CREATE TABLE IF NOT EXISTS user_activity (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128),
    activity_type VARCHAR(100) NOT NULL,
    activity_data JSON,
    points_earned INT DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer_url VARCHAR(500),
    page_url VARCHAR(500),
    duration_seconds INT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_activity_user_id ON user_activity(user_id);
CREATE INDEX idx_user_activity_activity_type ON user_activity(activity_type);
CREATE INDEX idx_user_activity_session_id ON user_activity(session_id);
CREATE INDEX idx_user_activity_created_at ON user_activity(created_at);
CREATE INDEX idx_user_activity_points_earned ON user_activity(points_earned);

CREATE TABLE IF NOT EXISTS system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
    category VARCHAR(50),
    message TEXT NOT NULL,
    context JSON,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_system_logs_level ON system_logs(level);
CREATE INDEX idx_system_logs_category ON system_logs(category);
CREATE INDEX idx_system_logs_user_id ON system_logs(user_id);
CREATE INDEX idx_system_logs_created_at ON system_logs(created_at);

-- ===========================================
-- ADMINISTRATION & SYSTEM MANAGEMENT
-- ===========================================

CREATE TABLE IF NOT EXISTS admin_access_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON,
    max_uses INT DEFAULT 1,
    uses_count INT DEFAULT 0,
    created_by INT,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    used_by JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_admin_access_codes_expires_at ON admin_access_codes(expires_at);
CREATE INDEX idx_admin_access_codes_is_active ON admin_access_codes(is_active);
-- Note: code already has UNIQUE constraint (implicit index)

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'system') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    action_url VARCHAR(500),
    action_text VARCHAR(100),
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_expires_at ON notifications(expires_at);

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_system_settings_is_public ON system_settings(is_public);
-- Note: setting_key already has UNIQUE constraint (implicit index)

CREATE TABLE IF NOT EXISTS file_uploads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_url VARCHAR(500),
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100),
    file_type ENUM('image', 'video', 'document', 'audio', 'archive', 'other') DEFAULT 'other',
    category VARCHAR(50),
    is_public BOOLEAN DEFAULT FALSE,
    downloads_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_file_uploads_user_id ON file_uploads(user_id);
CREATE INDEX idx_file_uploads_file_type ON file_uploads(file_type);
CREATE INDEX idx_file_uploads_category ON file_uploads(category);
CREATE INDEX idx_file_uploads_is_public ON file_uploads(is_public);
CREATE INDEX idx_file_uploads_expires_at ON file_uploads(expires_at);

-- ===========================================
-- TUTORING & TASK MANAGEMENT
-- ===========================================

CREATE TABLE IF NOT EXISTS tutoring_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    description TEXT,
    urgency ENUM('low', 'medium', 'high') DEFAULT 'medium',
    preferred_budget DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'PI',
    status ENUM('open', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    assigned_tutor_id INT NULL,
    deadline TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_tutor_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tutoring_requests_student_id ON tutoring_requests(student_id);
CREATE INDEX idx_tutoring_requests_assigned_tutor_id ON tutoring_requests(assigned_tutor_id);
CREATE INDEX idx_tutoring_requests_subject ON tutoring_requests(subject);
CREATE INDEX idx_tutoring_requests_status ON tutoring_requests(status);
CREATE INDEX idx_tutoring_requests_urgency ON tutoring_requests(urgency);
CREATE FULLTEXT INDEX idx_tutoring_requests_topic_description ON tutoring_requests(topic, description);

CREATE TABLE IF NOT EXISTS tutoring_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    tutor_id INT NOT NULL,
    student_id INT NOT NULL,
    scheduled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    duration_minutes INT,
    session_type ENUM('video', 'chat', 'screen_share') DEFAULT 'video',
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    recording_url VARCHAR(500),
    amount DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'PI',
    payment_status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES tutoring_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_tutoring_sessions_request_id ON tutoring_sessions(request_id);
CREATE INDEX idx_tutoring_sessions_tutor_id ON tutoring_sessions(tutor_id);
CREATE INDEX idx_tutoring_sessions_student_id ON tutoring_sessions(student_id);
CREATE INDEX idx_tutoring_sessions_status ON tutoring_sessions(status);
CREATE INDEX idx_tutoring_sessions_scheduled_at ON tutoring_sessions(scheduled_at);

-- ===========================================
-- TRUST & REPUTATION SYSTEM
-- ===========================================

CREATE TABLE IF NOT EXISTS user_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rater_id INT NOT NULL,
    rated_user_id INT NOT NULL,
    rating_type ENUM('tutor', 'student', 'content', 'platform') DEFAULT 'tutor',
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    related_id INT NULL, -- course_id, session_id, etc.
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rater_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_ratings_rater_id ON user_ratings(rater_id);
CREATE INDEX idx_user_ratings_rated_user_id ON user_ratings(rated_user_id);
CREATE INDEX idx_user_ratings_rating_type ON user_ratings(rating_type);
CREATE INDEX idx_user_ratings_related_id ON user_ratings(related_id);
CREATE INDEX idx_user_ratings_is_verified ON user_ratings(is_verified);

CREATE TABLE IF NOT EXISTS trust_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_type VARCHAR(50) NOT NULL,
    badge_level INT DEFAULT 1,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    criteria_met JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_trust_badges_user_id ON trust_badges(user_id);
CREATE INDEX idx_trust_badges_badge_type ON trust_badges(badge_type);
CREATE INDEX idx_trust_badges_is_active ON trust_badges(is_active);
CREATE INDEX idx_trust_badges_expires_at ON trust_badges(expires_at);

-- ===========================================
-- PERFORMANCE INDEXES
-- ===========================================

-- Composite indexes for common queries
CREATE INDEX idx_users_role_active ON users(user_role, is_active);
CREATE INDEX idx_users_subscription_expires ON users(subscription_tier, subscription_expires);
CREATE INDEX idx_payments_user_status ON payments(user_id, status);
CREATE INDEX idx_course_enrollments_user_progress ON course_enrollments(user_id, progress_percentage);
CREATE INDEX idx_forum_questions_category_status ON forum_questions(category_id, status);
CREATE INDEX idx_messages_sender_receiver ON messages(sender_id, receiver_id, created_at);
CREATE INDEX idx_user_activity_user_type_date ON user_activity(user_id, activity_type, created_at);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_blogs_search ON blogs(title, content, excerpt);
CREATE FULLTEXT INDEX idx_forum_questions_search ON forum_questions(title, content);
CREATE FULLTEXT INDEX idx_courses_search ON courses(title, description);

-- ===========================================
-- SAMPLE DATA FOR TESTING
-- ===========================================

-- Insert default admin user
INSERT IGNORE INTO users (id, username, email, user_role, is_active, subscription_tier, wallet_verified) VALUES
(1, 'admin', 'admin@ai-academy.com', 'admin', TRUE, 'premium_pro', TRUE);

-- Insert sample tutor
INSERT IGNORE INTO users (id, username, email, full_name, user_role, is_active, subscription_tier, wallet_verified, bio) VALUES
(2, 'ai_expert', 'tutor@ai-academy.com', 'AI Expert', 'tutor', TRUE, 'premium_pro', TRUE, 'Experienced AI and Pi Network developer with 5+ years of teaching experience.');

-- Insert sample student
INSERT IGNORE INTO users (id, username, email, full_name, user_role, is_active, subscription_tier, wallet_verified) VALUES
(3, 'student1', 'student@ai-academy.com', 'John Student', 'student', TRUE, 'premium_basic', TRUE);

-- Insert forum categories
INSERT IGNORE INTO forum_categories (id, name, slug, description, icon, color, order_index) VALUES
(1, 'General Discussion', 'general', 'General topics and discussions', 'fas fa-comments', '#007bff', 1),
(2, 'Pi Network', 'pi-network', 'Pi Network related discussions', 'fas fa-circle', '#ffc107', 2),
(3, 'AI & Machine Learning', 'ai-ml', 'Artificial Intelligence and ML topics', 'fas fa-brain', '#28a745', 3),
(4, 'Web Development', 'web-dev', 'Web development discussions', 'fas fa-code', '#dc3545', 4),
(5, 'Blockchain', 'blockchain', 'Blockchain technology discussions', 'fas fa-link', '#6f42c1', 5);

-- Insert sample course
INSERT IGNORE INTO courses (id, title, description, instructor_id, category, level, duration_hours, price, status, published_at) VALUES
(1, 'Introduction to Pi Network Development', 'Learn how to build applications for the Pi Network ecosystem using modern web technologies.', 2, 'Blockchain', 'beginner', 10, 50.00, 'published', NOW());

-- Insert sample blog post
INSERT IGNORE INTO blogs (id, user_id, title, slug, content, excerpt, status, published_at) VALUES
(1, 2, 'Getting Started with Pi Network Apps', 'getting-started-pi-network-apps', '<p>The Pi Network represents a revolutionary approach to cryptocurrency and decentralized applications...</p>', 'Learn the basics of building applications for the Pi Network ecosystem.', 'published', NOW());

-- Insert system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'AI-ACADEMY', 'string', 'Website name', TRUE),
('site_description', 'Learn AI to Build Pi Ecosystem Apps', 'string', 'Website description', TRUE),
('pi_network_sandbox', 'true', 'boolean', 'Pi Network sandbox mode', FALSE),
('max_upload_size', '10485760', 'integer', 'Maximum file upload size in bytes', FALSE),
('session_timeout', '3600', 'integer', 'User session timeout in seconds', FALSE),
('maintenance_mode', 'false', 'boolean', 'Site maintenance mode', TRUE);

-- ===========================================
-- DATA INTEGRITY CONSTRAINTS
-- ===========================================

-- Ensure subscription tiers have valid expiration dates
DELIMITER //
CREATE TRIGGER check_subscription_expires
    BEFORE INSERT ON users
    FOR EACH ROW
BEGIN
    IF NEW.subscription_tier != 'free' AND NEW.subscription_expires IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Subscription expiration date required for premium tiers';
    END IF;
END;
//
DELIMITER ;

-- Auto-update course enrolled count
DELIMITER //
CREATE TRIGGER update_course_enrolled_count
    AFTER INSERT ON course_enrollments
    FOR EACH ROW
BEGIN
    UPDATE courses SET enrolled_count = enrolled_count + 1 WHERE id = NEW.course_id;
END;
//
CREATE TRIGGER decrease_course_enrolled_count
    AFTER DELETE ON course_enrollments
    FOR EACH ROW
BEGIN
    UPDATE courses SET enrolled_count = enrolled_count - 1 WHERE id = OLD.course_id;
END;
//
DELIMITER ;

-- ===========================================
-- BACKUP COMPLETED
-- ===========================================

-- This backup includes:
-- ✅ Complete database schema with all tables
-- ✅ Proper indexes for performance
-- ✅ Foreign key constraints for data integrity
-- ✅ Sample data for testing
-- ✅ Triggers for automated updates
-- ✅ Production-ready configuration
