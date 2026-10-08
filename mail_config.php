HE<?php
/**
 * Zoho SMTP Mail Configuration
 * Handles all email sending for AI-ACADEMY
 */

// Zoho SMTP Configuration
define('SMTP_HOST', 'smtp.zoho.com');
define('SMTP_PORT', 587); // TLS
define('SMTP_USERNAME', 'noreply@ai-academy.com'); // Replace with your Zoho email
define('SMTP_PASSWORD', 'your_zoho_password_here'); // Replace with your Zoho password
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'noreply@ai-academy.com');
define('SMTP_FROM_NAME', 'AI-ACADEMY');

// Email templates and settings
define('SITE_NAME', 'AI-ACADEMY');
define('SITE_URL', 'https://ai-academy.com');
define('SUPPORT_EMAIL', 'support@ai-academy.com');

/**
 * Mail Helper Class
 * Uses PHP's built-in mail function with SMTP
 */
class Mailer {
    private static $smtp_config = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'encryption' => SMTP_ENCRYPTION
    ];

    /**
     * Send email using Zoho SMTP
     */
    public static function send($to, $subject, $message, $headers = []) {
        // Set default headers
        $default_headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . SUPPORT_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];

        // Merge with custom headers
        $all_headers = array_merge($default_headers, $headers);
        $headers_string = implode("\r\n", $all_headers);

        // Send email
        $result = mail($to, $subject, $message, $headers_string);

        // Log email sending
        if ($result) {
            error_log("Email sent successfully to: $to, Subject: $subject");
        } else {
            error_log("Failed to send email to: $to, Subject: $subject");
        }

        return $result;
    }

    /**
     * Send HTML email with template
     */
    public static function sendHTML($to, $subject, $html_content, $text_content = null) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8'
        ];

        if ($text_content) {
            // Create multipart message
            $boundary = md5(time());
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

            $message = "--$boundary\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $message .= $text_content . "\r\n\r\n";
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $message .= $html_content . "\r\n\r\n";
            $message .= "--$boundary--";
        } else {
            $message = $html_content;
        }

        return self::send($to, $subject, $message, $headers);
    }

    /**
     * Get HTML email template
     */
    public static function getHTMLTemplate($content, $title = null) {
        $title = $title ?: SITE_NAME;
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px 20px; text-align: center; }
                .content { padding: 30px 20px; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .btn:hover { background-color: #0056b3; }
                .highlight { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . SITE_NAME . "</h1>
                    <p>Master AI, Software Development & Blockchain</p>
                </div>
                <div class='content'>
                    {$content}
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                    <p><a href='" . SITE_URL . "'>" . SITE_URL . "</a> | <a href='mailto:" . SUPPORT_EMAIL . "'>Contact Support</a></p>
                    <p>This email was sent to you because you are a member of " . SITE_NAME . ".</p>
                </div>
            </div>
        </body>
        </html>";
    }
}

/**
 * Email Templates
 */
class EmailTemplates {


    /**
     * Welcome Email for New Users
     */
    public static function welcomeUser($username, $email) {
        $content = "
            <h2>Welcome to " . SITE_NAME . "!</h2>
            <p>Hello {$username},</p>
            <p>Welcome to " . SITE_NAME . "! We're excited to have you join our community of learners and educators.</p>
            <div class='highlight'>
                <h3>What you can do:</h3>
                <ul>
                    <li>📚 Access hundreds of courses on AI, Blockchain, and Software Development</li>
                    <li>👨‍🏫 Learn from expert tutors in the Pi Network ecosystem</li>
                    <li>💰 Earn Pi coins while you learn</li>
                    <li>🎓 Get certified in cutting-edge technologies</li>
                    <li>🤝 Connect with fellow learners and professionals</li>
                </ul>
            </div>
            <p><a href='" . SITE_URL . "/learnershub/' class='btn'>Start Learning Now</a></p>
            <p>If you have any questions, feel free to contact our support team at <a href='mailto:" . SUPPORT_EMAIL . "'>" . SUPPORT_EMAIL . "</a></p>
            <p>Happy learning!<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'Welcome to ' . SITE_NAME);
    }

    /**
     * Course Enrollment Confirmation
     */
    public static function courseEnrollment($username, $course_title, $course_price) {
        $content = "
            <h2>Course Enrollment Confirmed!</h2>
            <p>Hello {$username},</p>
            <p>Great news! You have successfully enrolled in the course:</p>
            <div class='highlight'>
                <h3>{$course_title}</h3>
                " . ($course_price > 0 ? "<p><strong>Payment:</strong> {$course_price} π (Pi coins)</p>" : "<p><strong>Price:</strong> FREE</p>") . "
            </div>
            <p>You can now access your course materials in your <a href='" . SITE_URL . "/learnershub/'>Learners Hub</a>.</p>
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Start with the course introduction</li>
                <li>Complete lessons at your own pace</li>
                <li>Earn Pi coins as you progress</li>
                <li>Get your certificate upon completion</li>
            </ul>
            <p><a href='" . SITE_URL . "/learnershub/' class='btn'>Go to My Courses</a></p>
            <p>Best regards,<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'Course Enrollment Confirmed');
    }

    /**
     * Course Approval Notification (to Tutor)
     */
    public static function courseApproved($username, $course_title) {
        $content = "
            <h2>Course Approved!</h2>
            <p>Hello {$username},</p>
            <p>Great news! Your course has been approved and is now live on " . SITE_NAME . ":</p>
            <div class='highlight'>
                <h3>{$course_title}</h3>
                <p>Your course is now available for student enrollment!</p>
            </div>
            <p><strong>What happens next:</strong></p>
            <ul>
                <li>Students can now enroll in your course</li>
                <li>You'll earn Pi coins from course sales</li>
                <li>Track your earnings in the tutor dashboard</li>
                <li>Receive notifications for new enrollments</li>
            </ul>
            <p><a href='" . SITE_URL . "/tutor/' class='btn'>View My Courses</a></p>
            <p>Congratulations on becoming a published instructor!</p>
            <p>Best regards,<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'Course Approved');
    }

    /**
     * Course Rejection Notification (to Tutor)
     */
    public static function courseRejected($username, $course_title, $reason) {
        $content = "
            <h2>Course Review Feedback</h2>
            <p>Hello {$username},</p>
            <p>Thank you for submitting your course for review. After careful consideration, we need some revisions before we can approve it:</p>
            <div class='highlight'>
                <h3>{$course_title}</h3>
                <p><strong>Feedback:</strong></p>
                <p>{$reason}</p>
            </div>
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Make the requested revisions to your course</li>
                <li>Resubmit your course for approval</li>
                <li>Contact support if you need clarification</li>
            </ul>
            <p><a href='" . SITE_URL . "/tutor/courses/create.php' class='btn'>Edit Course</a></p>
            <p>We appreciate your effort in creating quality educational content. We're here to help you succeed!</p>
            <p>Best regards,<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'Course Review Feedback');
    }

    /**
     * Tutoring Session Request (to Tutor)
     */
    public static function tutoringRequest($tutor_name, $student_name, $subject, $message) {
        $content = "
            <h2>New Tutoring Session Request</h2>
            <p>Hello {$tutor_name},</p>
            <p>You have received a new tutoring session request:</p>
            <div class='highlight'>
                <h3>Session Details</h3>
                <p><strong>Student:</strong> {$student_name}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>{$message}</p>
            </div>
            <p>Please review the request and contact the student to arrange the session details and payment.</p>
            <p><a href='" . SITE_URL . "/tutor/sessions.php' class='btn'>View All Requests</a></p>
            <p>Happy teaching!</p>
            <p>Best regards,<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'New Tutoring Request');
    }

    /**
     * Certificate Earned Notification
     */
    public static function certificateEarned($username, $course_title, $certificate_url) {
        $content = "
            <h2>🎉 Congratulations! Certificate Earned</h2>
            <p>Hello {$username},</p>
            <p>Congratulations on completing your course! You have earned an official certificate:</p>
            <div class='highlight'>
                <h3>Certificate Details</h3>
                <p><strong>Course:</strong> {$course_title}</p>
                <p><strong>Completion Date:</strong> " . date('F j, Y') . "</p>
                <p><strong>Certificate ID:</strong> " . strtoupper(substr(md5(time()), 0, 8)) . "</p>
            </div>
            <p><a href='{$certificate_url}' class='btn'>View & Print Certificate</a></p>
            <p><strong>Share your achievement:</strong></p>
            <ul>
                <li>Add it to your LinkedIn profile</li>
                <li>Include it in your resume</li>
                <li>Showcase your new skills to employers</li>
            </ul>
            <p>Keep learning and earning more certificates!</p>
            <p>Best regards,<br>The " . SITE_NAME . " Team</p>
        ";
        return Mailer::getHTMLTemplate($content, 'Certificate Earned!');
    }
}

/**
 * Send welcome email to new user
 */
function sendWelcomeEmail($user_id) {
    $user = getUserById($user_id);
    if ($user && $user['email']) {
        $template = EmailTemplates::welcomeUser($user['username'], $user['email']);
        return Mailer::sendHTML($user['email'], 'Welcome to ' . SITE_NAME . '!', $template);
    }
    return false;
}


/**
 * Send course enrollment confirmation
 */
function sendEnrollmentConfirmation($user_id, $course_id) {
    $user = getUserById($user_id);
    $course = fetchOne("SELECT title, price_pi FROM courses WHERE id = ?", [$course_id]);

    if ($user && $course && $user['email']) {
        $template = EmailTemplates::courseEnrollment($user['username'], $course['title'], $course['price_pi']);
        return Mailer::sendHTML($user['email'], 'Course Enrollment Confirmed - ' . SITE_NAME, $template);
    }
    return false;
}

/**
 * Send course approval notification to tutor
 */
function sendCourseApprovalNotification($course_id, $approved = true, $reason = '') {
    $course = fetchOne("
        SELECT c.*, u.username, u.email
        FROM courses c
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE c.id = ?
    ", [$course_id]);

    if ($course && $course['email']) {
        if ($approved) {
            $template = EmailTemplates::courseApproved($course['username'], $course['title']);
            $subject = 'Course Approved - ' . SITE_NAME;
        } else {
            $template = EmailTemplates::courseRejected($course['username'], $course['title'], $reason);
            $subject = 'Course Review Feedback - ' . SITE_NAME;
        }
        return Mailer::sendHTML($course['email'], $subject, $template);
    }
    return false;
}

/**
 * Send tutoring request notification to tutor
 */
function sendTutoringRequestNotification($tutor_id, $student_id, $subject, $message) {
    $tutor = getUserById($tutor_id);
    $student = getUserById($student_id);

    if ($tutor && $student && $tutor['email']) {
        $template = EmailTemplates::tutoringRequest($tutor['username'], $student['username'], $subject, $message);
        return Mailer::sendHTML($tutor['email'], 'New Tutoring Request - ' . SITE_NAME, $template);
    }
    return false;
}

/**
 * Send certificate earned notification
 */
function sendCertificateNotification($user_id, $course_title, $certificate_url) {
    $user = getUserById($user_id);
    if ($user && $user['email']) {
        $template = EmailTemplates::certificateEarned($user['username'], $course_title, $certificate_url);
        return Mailer::sendHTML($user['email'], 'Certificate Earned! - ' . SITE_NAME, $template);
    }
    return false;
}
?>