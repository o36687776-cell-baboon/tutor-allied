<?php
// Create Publication page for Tutors
$page_title = 'Create Publication';
include '../../header.php';
?>

<div class="container">
    <h2>Create New Publication</h2>
    <div class="widget">
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Publication Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control rich-editor" id="content" name="content" rows="10" required></textarea>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-control" id="category" name="category">
                    <option>AI</option>
                    <option>Blockchain</option>
                    <option>Software Development</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Publish</button>
        </form>
    </div>
</div>

<script src="../../rich-editor.js"></script>

<?php include '../../footer.php'; ?>