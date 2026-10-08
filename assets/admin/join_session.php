<?php
// Admin Join Session page
$page_title = 'Join Session - Admin';
include '../header.php';
?>

<div class="container">
    <h2>Join Active Session - Admin</h2>
    <div class="widget">
        <h4>Select Session to Join</h4>
        <div class="list-group">
            <a href="video.php" class="list-group-item list-group-item-action">
                <h5>Machine Learning Basics</h5>
                <p>Tutor: AI Expert • Students: 2 • Status: Active</p>
            </a>
            <a href="video.php" class="list-group-item list-group-item-action">
                <h5>Blockchain Fundamentals</h5>
                <p>Tutor: Crypto Guru • Students: 1 • Status: Active</p>
            </a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>