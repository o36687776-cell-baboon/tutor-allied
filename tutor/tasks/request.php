<?php
// Request Task page for Tutors
$page_title = 'Request Task';
include '../../header.php';
?>

<div class="container">
    <h2>Request New Task</h2>
    <div class="widget">
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <select class="form-control" id="subject" name="subject">
                    <option>AI</option>
                    <option>Blockchain</option>
                    <option>Software Development</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="date" class="form-control" id="deadline" name="deadline">
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</div>

<?php include '../../footer.php'; ?>