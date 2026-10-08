<?php
// Blog View page
$page_title = 'Blog Post';
include '../header.php';

$id = $_GET['id'] ?? 0;

// Fetch blog from database
$blog = fetchOne("SELECT b.*, u.username as author_name FROM blogs b LEFT JOIN users u ON b.user_id = u.id WHERE b.id = ? AND b.status = 'published'", [$id]);

if (!$blog) {
    // Blog not found or not published
    header('Location: index.php');
    exit;
}

// Update view count (if we add this field later)
// executeUpdate("UPDATE blogs SET views = views + 1 WHERE id = ?", [$id]);

$post = [
    'title' => $blog['title'],
    'author' => $blog['author_name'] ?? 'Anonymous',
    'date' => date('M j, Y', strtotime($blog['created_at'])),
    'content' => $blog['content'],
    'tags' => $blog['tags'] ? explode(',', $blog['tags']) : []
];
?>

<div class="container">
    <article>
        <header class="mb-4">
            <h1><?php echo $post['title']; ?></h1>
            <div class="text-muted">
                <span>By <?php echo $post['author']; ?></span> |
                <span><?php echo $post['date']; ?></span>
            </div>
        </header>

        <div class="blog-content">
            <?php echo nl2br($post['content']); ?>
        </div>

        <div class="mt-4">
            <h5>Tags:</h5>
            <?php foreach ($post['tags'] as $tag): ?>
                <span class="badge bg-secondary me-1"><?php echo $tag; ?></span>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Back to Blog Catalogue</a>
        </div>
    </article>
</div>

<style>
.blog-content {
    line-height: 1.6;
    font-size: 1.1em;
}
</style>

<?php include '../footer.php'; ?>