<?php
// Tasks List page for Tutors
$page_title = 'My Tasks';
include '../../header.php';
?>

<div class="container">
    <h2>My Tasks</h2>
    <div class="widget">
        <a href="request.php" class="btn btn-primary mb-3">Request New Task</a>
        <div class="list-group">
            <div class="list-group-item">
                <h5>Help with Python Debugging</h5>
                <p>Student: John Doe • Status: In Progress • Deadline: 2023-10-15</p>
                <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                <a href="#" class="btn btn-sm btn-outline-success">Mark Complete</a>
            </div>
            <div class="list-group-item">
                <h5>AI Model Training</h5>
                <p>Student: Jane Smith • Status: Pending • Deadline: 2023-10-20</p>
                <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                <a href="#" class="btn btn-sm btn-outline-warning">Accept</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>