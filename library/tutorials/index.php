<?php
// Tutorials Index page
$page_title = 'Video Tutorials';
include '../../header.php';
include '../../logger.php';

// Mock tutorials data
$tutorials = [
    [
        'id' => 1,
        'title' => 'Machine Learning Basics',
        'publisher' => 'AI Expert',
        'duration' => '45 min',
        'rating' => 4.5,
        'views' => 1250,
        'transcript' => 'This tutorial covers the fundamentals of machine learning...',
        'notes' => 'Key points: supervised learning, unsupervised learning...'
    ],
    [
        'id' => 2,
        'title' => 'Blockchain Fundamentals',
        'publisher' => 'Crypto Guru',
        'duration' => '60 min',
        'rating' => 4.8,
        'views' => 890,
        'transcript' => 'Understanding blockchain technology and its applications...',
        'notes' => 'Important concepts: decentralization, consensus mechanisms...'
    ]
];
?>

<div class="container">
    <h2>Video Tutorials</h2>
    <div class="row">
        <?php foreach ($tutorials as $tutorial): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $tutorial['title']; ?></h5>
                    <p class="card-text">
                        <strong>Publisher:</strong> <?php echo $tutorial['publisher']; ?><br>
                        <strong>Duration:</strong> <?php echo $tutorial['duration']; ?><br>
                        <strong>Views:</strong> <?php echo $tutorial['views']; ?><br>
                        <strong>Rating:</strong>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" style="color: <?php echo $i <= $tutorial['rating'] ? '#ffc107' : '#e4e5e9'; ?>"></i>
                        <?php endfor; ?>
                        (<?php echo $tutorial['rating']; ?>)
                    </p>
                    <div class="mb-2">
                        <strong>Transcript Preview:</strong><br>
                        <?php echo substr($tutorial['transcript'], 0, 100) . '...'; ?>
                    </div>
                    <div class="mb-2">
                        <strong>Notes:</strong><br>
                        <?php echo substr($tutorial['notes'], 0, 100) . '...'; ?>
                    </div>
                    <a href="view.php?id=<?php echo $tutorial['id']; ?>" class="btn btn-primary">Watch Tutorial</a>
                    <button class="btn btn-outline-secondary rate-btn" data-id="<?php echo $tutorial['id']; ?>">Rate</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.querySelectorAll('.rate-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = prompt('Rate this tutorial (1-5):');
        if (rating >= 1 && rating <= 5) {
            alert('Thank you for rating!');
            // In real implementation, send to server
        }
    });
});
</script>

<?php include '../../footer.php'; ?>