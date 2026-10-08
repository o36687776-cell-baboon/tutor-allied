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
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_pi_wallet (pi_wallet_address),
    INDEX idx_subscription_tier (subscription_tier),
    INDEX idx_user_role (user_role),
    INDEX idx_is_active (is_active)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_pi_payment_id (pi_payment_id),
    INDEX idx_payment_type (payment_type)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tier (tier),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
);

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
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_instructor_id (instructor_id),
    INDEX idx_category (category),
    INDEX idx_level (level),
    INDEX idx_status (status),
    FULLTEXT idx_title_description (title, description)
);

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
    UNIQUE KEY unique_enrollment (user_id, course_id),
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_progress (progress_percentage),
    INDEX idx_enrolled_at (enrolled_at)
);

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
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_id (course_id),
    INDEX idx_order_index (order_index)
);

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
    UNIQUE KEY unique_progress (enrollment_id, module_id),
    INDEX idx_enrollment_id (enrollment_id),
    INDEX idx_module_id (module_id),
    INDEX idx_completed (completed)
);

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
    issued_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_status (status)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_visibility (visibility),
    INDEX idx_category (category),
    INDEX idx_published_at (published_at),
    FULLTEXT idx_title_content (title, content)
);

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
    FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE,
    INDEX idx_blog_id (blog_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (blog_id, user_id),
    INDEX idx_blog_id (blog_id),
    INDEX idx_user_id (user_id)
);

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_order_index (order_index),
    INDEX idx_is_active (is_active)
);

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
    FOREIGN KEY (accepted_answer_id) REFERENCES forum_answers(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_visibility (visibility),
    INDEX idx_last_activity_at (last_activity_at),
    FULLTEXT idx_title_content (title, content)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_question_id (question_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_accepted (is_accepted),
    INDEX idx_votes_count (votes_count)
);

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
    UNIQUE KEY unique_answer_vote (user_id, answer_id),
    INDEX idx_user_id (user_id),
    INDEX idx_vote_type (vote_type)
);

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
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_sender_id (sender_id),
    INDEX idx_receiver_id (receiver_id),
    INDEX idx_message_type (message_type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS message_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    participants JSON NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_last_message_at (last_message_at)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at),
    INDEX idx_points_earned (points_earned)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_level (level),
    INDEX idx_category (category),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

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
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_active (is_active)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at)
);

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
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_file_type (file_type),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public),
    INDEX idx_expires_at (expires_at)
);

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
    FOREIGN KEY (assigned_tutor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_assigned_tutor_id (assigned_tutor_id),
    INDEX idx_subject (subject),
    INDEX idx_status (status),
    INDEX idx_urgency (urgency),
    FULLTEXT idx_topic_description (topic, description)
);

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
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_request_id (request_id),
    INDEX idx_tutor_id (tutor_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at)
);

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
    FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_rater_id (rater_id),
    INDEX idx_rated_user_id (rated_user_id),
    INDEX idx_rating_type (rating_type),
    INDEX idx_related_id (related_id),
    INDEX idx_is_verified (is_verified)
);

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_badge_type (badge_type),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
);

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
-- INITIAL DATA SEEDING
-- ===========================================

-- Insert default admin user (password should be hashed in production)
INSERT IGNORE INTO users (username, email, user_role, is_active) VALUES
('admin', 'admin@ai-academy.com', 'admin', TRUE);

-- Insert default forum categories
INSERT IGNORE INTO forum_categories (name, slug, description, icon, color, order_index) VALUES
('General Discussion', 'general', 'General topics and discussions', 'fas fa-comments', '#007bff', 1),
('Pi Network', 'pi-network', 'Pi Network related discussions', 'fas fa-circle', '#ffc107', 2),
('AI & Machine Learning', 'ai-ml', 'Artificial Intelligence and ML topics', 'fas fa-brain', '#28a745', 3),
('Web Development', 'web-dev', 'Web development discussions', 'fas fa-code', '#dc3545', 4),
('Blockchain', 'blockchain', 'Blockchain technology discussions', 'fas fa-link', '#6f42c1', 5);

-- Insert default system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'AI-ACADEMY', 'string', 'Website name', TRUE),
('site_description', 'Learn AI to Build Pi Ecosystem Apps', 'string', 'Website description', TRUE),
('pi_network_sandbox', 'true', 'boolean', 'Pi Network sandbox mode', FALSE),
('max_upload_size', '10485760', 'integer', 'Maximum file upload size in bytes', FALSE),
('session_timeout', '3600', 'integer', 'User session timeout in seconds', FALSE),
('maintenance_mode', 'false', 'boolean', 'Site maintenance mode', TRUE);

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('open', 'closed', 'answered') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_accepted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES forum_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_users_pi_wallet ON users(pi_wallet_address);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_user_activity_user_id ON user_activity(user_id);
-- Video Classroom Tables
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

-- Indexes for video classroom performance
CREATE INDEX idx_video_rooms_tutor_id ON video_rooms(tutor_id);
CREATE INDEX idx_video_rooms_status ON video_rooms(status);
CREATE INDEX idx_room_participants_room_id ON room_participants(room_id);
CREATE INDEX idx_room_participants_user_id ON room_participants(user_id);
CREATE INDEX idx_room_messages_room_id ON room_messages(room_id);
CREATE INDEX idx_room_recordings_room_id ON room_recordings(room_id);
CREATE INDEX idx_certifications_user_id ON certifications(user_id);