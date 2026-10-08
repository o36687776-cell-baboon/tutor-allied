<?php
// Ask Question page for AI-ACADEMY Forums
$page_title = 'Ask a Question';
include '../header.php';
?>

<div class="container">
    <h2>Ask a Question</h2>
    <div class="widget">
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Question Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Question Details</label>
                <textarea class="form-control rich-editor" id="content" name="content" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="tags" class="form-label">Tags</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g. AI, Python">
            </div>
            <button type="submit" class="btn btn-primary">Submit Question</button>
        </form>
    </div>
</div>

<script src="../rich-editor.js"></script>

<?php include '../footer.php'; ?>