<?php
// Start session first - Fix session path for XAMPP
$session_path = __DIR__ . '/../sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}

// Only set session save path if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($session_path);
    session_start();
} elseif (!isset($_SESSION)) {
    // If session is already active but $_SESSION is not set, start it anyway
    session_start();
}

// Community Rating Page - Allow anonymous rating but protect user-specific features
$page_title = 'Community Ratings';
include '../header.php';

// Check if user is authenticated for user-specific features
// Allow demo mode users
$is_demo_mode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
$is_authenticated = isset($_SESSION['user_id']) || $is_demo_mode;
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <h2>Community Product Ratings</h2>
            <p class="lead">Test, review, and rate products and services from our community</p>

            <!-- Rating Stats - Only show for authenticated users -->
            <?php if ($is_authenticated): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Your Rating Activity</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-primary" id="userReviews">0</h3>
                                <p class="mb-0">Reviews Given</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-success" id="userAvgRating">0.0</h3>
                                <p class="mb-0">Avg Rating</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-warning" id="helpfulVotes">0</h3>
                                <p class="mb-0">Helpful Votes</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-info" id="ranking">New</h3>
                                <p class="mb-0">Reviewer Rank</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Anonymous Rating Notice -->
            <div class="card mb-4 border-info">
                <div class="card-body text-center">
                    <i class="fas fa-user-secret fa-3x text-info mb-3"></i>
                    <h5 class="text-info">Anonymous Rating</h5>
                    <p class="mb-3">You can rate products anonymously, but to track your reviews and earn badges, please sign in.</p>
                    <a href="<?php echo BASE_PATH; ?>/auth/?redirect=<?php echo BASE_PATH; ?>/rating-badge/community.php" class="btn btn-info">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Track Reviews
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Products to Rate -->
            <div id="productsContainer">
                <!-- Products will be loaded here -->
            </div>

            <!-- Load More -->
            <div class="text-center mt-4">
                <button id="loadMoreProducts" class="btn btn-outline-primary">Load More Products</button>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recent Reviews - Only show for authenticated users -->
            <?php if ($is_authenticated): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Your Recent Reviews</h5>
                </div>
                <div class="card-body">
                    <div id="recentReviews">
                        <p class="text-muted">No reviews yet. Start rating products!</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Anonymous Rating Benefits -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Benefits of Signing In</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Track your reviews</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Earn reviewer badges</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>View review history</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Get personalized recommendations</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rating Guidelines -->
            <div class="card">
                <div class="card-header">
                    <h5>Rating Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>⭐ 1 star: Poor quality</li>
                        <li>⭐⭐ 2 stars: Below average</li>
                        <li>⭐⭐⭐ 3 stars: Average</li>
                        <li>⭐⭐⭐⭐ 4 stars: Good</li>
                        <li>⭐⭐⭐⭐⭐ 5 stars: Excellent</li>
                    </ul>
                    <hr>
                    <p class="small mb-0">
                        <strong>Tips:</strong><br>
                        • Be honest and constructive<br>
                        • Focus on features and usability<br>
                        • Include specific examples<br>
                        • Respect other users
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="productToRate" class="mb-3"></div>
                <div class="rating-stars mb-3">
                    <label class="form-label">Your Rating:</label>
                    <div class="stars" id="ratingStars">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reviewTitle" class="form-label">Review Title</label>
                    <input type="text" class="form-control" id="reviewTitle" placeholder="Summarize your review">
                </div>
                <div class="mb-3">
                    <label for="reviewText" class="form-label">Your Review</label>
                    <textarea class="form-control" id="reviewText" rows="4" placeholder="Share your experience with this product..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pros:</label>
                    <input type="text" class="form-control" id="reviewPros" placeholder="What did you like? (comma separated)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Cons:</label>
                    <input type="text" class="form-control" id="reviewCons" placeholder="What could be improved? (comma separated)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitRating">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
}

.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.rating-stars {
    font-size: 1.5rem;
}

.stars i {
    color: #ddd;
    cursor: pointer;
    margin: 0 2px;
    transition: color 0.3s ease;
}

.stars i:hover,
.stars i.active {
    color: #ffc107;
}

.review-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.review-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.product-rating {
    color: #ffc107;
    font-size: 0.9rem;
}

.product-stats {
    font-size: 0.8rem;
    color: #666;
}
</style>

<script>
// Sample products data
const sampleProducts = [
    {
        id: 1,
        name: "AI Code Assistant Pro",
        description: "Advanced AI-powered code completion and debugging tool",
        category: "Development Tools",
        image: "https://via.placeholder.com/100x100?text=AI+Tool",
        rating: 4.2,
        reviews: 45,
        price: "29 π/month"
    },
    {
        id: 2,
        name: "Blockchain Wallet Secure",
        description: "Multi-currency blockchain wallet with advanced security features",
        category: "Finance",
        image: "https://via.placeholder.com/100x100?text=Wallet",
        rating: 4.7,
        reviews: 128,
        price: "Free + Premium"
    },
    {
        id: 3,
        name: "DataViz Pro Dashboard",
        description: "Interactive data visualization and analytics dashboard",
        category: "Business Intelligence",
        image: "https://via.placeholder.com/100x100?text=Dashboard",
        rating: 4.0,
        reviews: 67,
        price: "49 π/month"
    },
    {
        id: 4,
        name: "Mobile App Builder",
        description: "No-code mobile app development platform",
        category: "Development Tools",
        image: "https://via.placeholder.com/100x100?text=App+Builder",
        rating: 3.8,
        reviews: 89,
        price: "99 π/month"
    },
    {
        id: 5,
        name: "Cloud Storage Plus",
        description: "Secure cloud storage with AI-powered file organization",
        category: "Cloud Services",
        image: "https://via.placeholder.com/100x100?text=Cloud",
        rating: 4.5,
        reviews: 203,
        price: "9.99 π/month"
    }
];

let currentProductId = null;
let visibleProducts = 3;

// Load products
function loadProducts() {
    const container = document.getElementById('productsContainer');

    for (let i = (visibleProducts - 3); i < visibleProducts && i < sampleProducts.length; i++) {
        const product = sampleProducts[i];
        const productCard = document.createElement('div');
        productCard.className = 'card product-card mb-3';
        productCard.onclick = () => openRatingModal(product.id);

        productCard.innerHTML = `
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="${product.image}" alt="${product.name}" class="img-fluid rounded">
                    </div>
                    <div class="col-md-7">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text text-muted">${product.description}</p>
                        <span class="badge bg-primary">${product.category}</span>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="product-rating mb-2">
                            ${'★'.repeat(Math.floor(product.rating))}${'☆'.repeat(5 - Math.floor(product.rating))}
                            <span class="ms-1">${product.rating}</span>
                        </div>
                        <div class="product-stats">
                            ${product.reviews} reviews<br>
                            ${product.price}
                        </div>
                        <button class="btn btn-primary btn-sm mt-2">Rate This Product</button>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(productCard);
    }

    // Update load more button
    const loadMoreBtn = document.getElementById('loadMoreProducts');
    if (visibleProducts >= sampleProducts.length) {
        loadMoreBtn.style.display = 'none';
    }
}

// Load user stats - Only for authenticated users
function loadUserStats() {
    <?php if ($is_authenticated): ?>
    const userReviews = JSON.parse(localStorage.getItem('userReviews') || '[]');
    const totalRating = userReviews.reduce((sum, review) => sum + review.rating, 0);
    const avgRating = userReviews.length > 0 ? (totalRating / userReviews.length).toFixed(1) : '0.0';

    document.getElementById('userReviews').textContent = userReviews.length;
    document.getElementById('userAvgRating').textContent = avgRating;
    document.getElementById('helpfulVotes').textContent = Math.floor(Math.random() * 50); // Mock data

    // Ranking based on activity
    const ranking = userReviews.length > 10 ? 'Expert' : userReviews.length > 5 ? 'Advanced' : userReviews.length > 0 ? 'Regular' : 'New';
    document.getElementById('ranking').textContent = ranking;
    <?php endif; ?>
}

// Load recent reviews - Only for authenticated users
function loadRecentReviews() {
    <?php if ($is_authenticated): ?>
    const userReviews = JSON.parse(localStorage.getItem('userReviews') || '[]');
    const container = document.getElementById('recentReviews');

    if (userReviews.length === 0) {
        container.innerHTML = '<p class="text-muted">No reviews yet. Start rating products!</p>';
        return;
    }

    container.innerHTML = '';
    userReviews.slice(-3).reverse().forEach(review => {
        const reviewElement = document.createElement('div');
        reviewElement.className = 'review-item';
        reviewElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${review.productName}</h6>
                    <div class="rating-stars mb-1">
                        ${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}
                    </div>
                    <p class="small mb-1">${review.title}</p>
                    <small class="text-muted">${new Date(review.date).toLocaleDateString()}</small>
                </div>
            </div>
        `;
        container.appendChild(reviewElement);
    });
    <?php endif; ?>
}

// Open rating modal
function openRatingModal(productId) {
    currentProductId = productId;
    const product = sampleProducts.find(p => p.id === productId);

    document.getElementById('productToRate').innerHTML = `
        <div class="text-center mb-3">
            <img src="${product.image}" alt="${product.name}" class="img-fluid rounded mb-2" style="max-width: 80px;">
            <h5>${product.name}</h5>
            <p class="text-muted">${product.description}</p>
        </div>
    `;

    // Reset form
    document.getElementById('reviewTitle').value = '';
    document.getElementById('reviewText').value = '';
    document.getElementById('reviewPros').value = '';
    document.getElementById('reviewCons').value = '';

    // Reset stars
    document.querySelectorAll('#ratingStars i').forEach(star => {
        star.className = 'far fa-star';
    });

    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}

// Handle star rating
document.getElementById('ratingStars').addEventListener('click', function(e) {
    if (e.target.tagName === 'I') {
        const rating = parseInt(e.target.dataset.rating);
        document.querySelectorAll('#ratingStars i').forEach((star, index) => {
            star.className = index < rating ? 'fas fa-star' : 'far fa-star';
        });
    }
});

// Submit rating
document.getElementById('submitRating').addEventListener('click', function() {
    const rating = document.querySelectorAll('#ratingStars .fas').length;
    const title = document.getElementById('reviewTitle').value;
    const text = document.getElementById('reviewText').value;
    const pros = document.getElementById('reviewPros').value;
    const cons = document.getElementById('reviewCons').value;

    if (rating === 0) {
        alert('Please select a rating!');
        return;
    }

    if (!title.trim() || !text.trim()) {
        alert('Please fill in the review title and text!');
        return;
    }

    const product = sampleProducts.find(p => p.id === currentProductId);
    const review = {
        productId: currentProductId,
        productName: product.name,
        rating: rating,
        title: title,
        text: text,
        pros: pros,
        cons: cons,
        date: new Date().toISOString(),
        helpful: 0
    };

    <?php if ($is_authenticated): ?>
    review.userId = '<?php echo $_SESSION['pi_user_id'] ?? session_id(); ?>';
    review.authenticated = true;
    <?php else: ?>
    review.userId = 'anonymous_' + Date.now();
    review.authenticated = false;
    <?php endif; ?>

    // Save review - use different storage for anonymous vs authenticated
    <?php if ($is_authenticated): ?>
    const userReviews = JSON.parse(localStorage.getItem('userReviews') || '[]');
    userReviews.push(review);
    localStorage.setItem('userReviews', JSON.stringify(userReviews));

    // Update stats for authenticated users
    loadUserStats();
    loadRecentReviews();
    <?php else: ?>
    // For anonymous users, store in general reviews
    const allReviews = JSON.parse(localStorage.getItem('allReviews') || '[]');
    allReviews.push(review);
    localStorage.setItem('allReviews', JSON.stringify(allReviews));
    <?php endif; ?>

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
    modal.hide();

    <?php if ($is_authenticated): ?>
    alert('Review submitted successfully! You can track it in your profile.');
    <?php else: ?>
    alert('Review submitted successfully! Sign in to track your reviews and earn badges.');
    <?php endif; ?>
});

// Load more products
document.getElementById('loadMoreProducts').addEventListener('click', function() {
    visibleProducts += 3;
    loadProducts();
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    <?php if ($is_authenticated): ?>
    loadUserStats();
    loadRecentReviews();
    <?php endif; ?>
});
</script>

<?php include '../footer.php'; ?>