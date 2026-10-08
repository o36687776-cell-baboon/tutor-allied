<?php
// My Publications page for Tutors
$page_title = 'My Publications';
include '../../header.php';
?>

<div class="container">
    <h2>My Publications</h2>
    <div class="widget">
        <a href="create.php" class="btn btn-primary mb-3">Create New Publication</a>
        <div class="list-group">
            <div class="list-group-item">
                <h5>Introduction to Machine Learning</h5>
                <p>Published on 2023-10-01 • 150 views • 25 likes</p>
                <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
            </div>
            <div class="list-group-item">
                <h5>Blockchain Fundamentals</h5>
                <p>Published on 2023-09-15 • 200 views • 30 likes</p>
                <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>