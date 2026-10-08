<?php
// Create Blog page
$page_title = 'Create Blog Post';
include '../header.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Log blog creation access
logUserActivity($_SESSION['user_id'], 'blog_create_accessed');

// Handle form submission
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $tags = trim($_POST['tags'] ?? '');
    $status = isset($_POST['publish']) ? 'published' : 'draft';

    if (empty($title) || empty($content)) {
        $message = 'Title and content are required.';
        $message_type = 'danger';
    } else {
        // Save to database
        $blog_id = executeUpdate(
            "INSERT INTO blogs (user_id, title, content, tags, status) VALUES (?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], $title, $content, $tags, $status]
        );

        if ($blog_id) {
            logUserActivity($_SESSION['user_id'], 'blog_created', 10); // 10 points for creating blog
            $message = $status === 'published' ? 'Blog published successfully!' : 'Blog saved as draft!';
            $message_type = 'success';

            // Clear form if published
            if ($status === 'published') {
                $_POST = [];
            }
        } else {
            $message = 'Failed to save blog. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>

<div class="container">
    <h2>Create New Blog Post</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="widget">
        <form id="blogForm" method="POST" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Blog Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="AI, Machine Learning, Tutorial">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Blog Content</label>
                <textarea class="form-control rich-editor" id="content" name="content" rows="15" required></textarea>
            </div>
            <button type="submit" name="publish" value="1" class="btn btn-primary">Publish Blog</button>
            <button type="submit" name="draft" value="1" class="btn btn-secondary">Save as Draft</button>
        </form>
    </div>
</div>

<script src="../rich-editor.js"></script>

<?php include '../footer.php'; ?>