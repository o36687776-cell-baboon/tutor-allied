<?php
// View Tutorial page
$page_title = 'View Tutorial';
include '../../header.php';

$id = $_GET['id'] ?? 1;

// Mock tutorial data
$tutorial = [
    'id' => $id,
    'title' => 'Machine Learning Basics',
    'publisher' => 'AI Expert',
    'duration' => '45 min',
    'rating' => 4.5,
    'views' => 1250,
    'transcript' => 'This tutorial covers the fundamentals of machine learning algorithms and their applications. We start with supervised learning, then move to unsupervised learning, and finally discuss practical implementations.',
    'notes' => 'Key points covered: supervised vs unsupervised learning, neural networks, overfitting prevention, model evaluation metrics.'
];
?>

<div class="container">
    <h2><?php echo $tutorial['title']; ?></h2>
    <div class="row">
        <div class="col-md-8">
            <div class="widget">
                <h4>Video Player</h4>
                <div style="background: #000; height: 400px; display: flex; align-items: center; justify-content: center; color: white;">
                    <div style="text-align: center;">
                        <i class="fas fa-play-circle fa-3x mb-3"></i>
                        <p>Video Tutorial Playback</p>
                        <p>(Demo - Video would play here)</p>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Publisher:</strong> <?php echo $tutorial['publisher']; ?><br>
                    <strong>Duration:</strong> <?php echo $tutorial['duration']; ?><br>
                    <strong>Views:</strong> <?php echo $tutorial['views']; ?><br>
                    <strong>Rating:</strong>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="color: <?php echo $i <= $tutorial['rating'] ? '#ffc107' : '#e4e5e9'; ?>"></i>
                    <?php endfor; ?>
                    (<?php echo $tutorial['rating']; ?>)
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="widget">
                <h4>Transcript</h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <p><?php echo $tutorial['transcript']; ?></p>
                </div>
            </div>
            <div class="widget mt-3">
                <h4>Session Notes</h4>
                <div style="max-height: 200px; overflow-y: auto;">
                    <p><?php echo $tutorial['notes']; ?></p>
                </div>
            </div>
            <div class="widget mt-3">
                <h4>Rate This Tutorial</h4>
                <div class="mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star rating-star" data-rating="<?php echo $i; ?>" style="cursor: pointer; color: #e4e5e9;"></i>
                    <?php endfor; ?>
                </div>
                <button id="submitRating" class="btn btn-primary">Submit Rating</button>
            </div>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary">Back to Tutorials</a>
</div>

<script>
let selectedRating = 0;

document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = this.dataset.rating;
        document.querySelectorAll('.rating-star').forEach(s => {
            s.style.color = s.dataset.rating <= selectedRating ? '#ffc107' : '#e4e5e9';
        });
    });
});

document.getElementById('submitRating').addEventListener('click', function() {
    if (selectedRating > 0) {
        alert(`Thank you for rating ${selectedRating} stars!`);
        // In real implementation, send to server
    } else {
        alert('Please select a rating first.');
    }
});
</script>

<?php include '../../footer.php'; ?>