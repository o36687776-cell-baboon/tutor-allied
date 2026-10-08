<?php
// Start session first
if (!isset($_SESSION)) {
    session_start();
}

// Rating Badge Generator for AI-ACADEMY
$page_title = 'Forum Rating Badge';
include '../header.php';

// Mock rating data - in real implementation, this would come from database
$ratingData = [
    'overall_rating' => 4.7,
    'total_reviews' => 1250,
    'rating_distribution' => [
        5 => 850,
        4 => 280,
        3 => 90,
        2 => 20,
        1 => 10
    ],
    'recent_reviews' => [
        ['rating' => 5, 'comment' => 'Excellent platform for learning!', 'date' => '2023-10-15'],
        ['rating' => 4, 'comment' => 'Great tutors and resources', 'date' => '2023-10-14'],
        ['rating' => 5, 'comment' => 'Highly recommended for developers', 'date' => '2023-10-13']
    ]
];

// Calculate percentages
$total = array_sum($ratingData['rating_distribution']);
$percentages = [];
foreach ($ratingData['rating_distribution'] as $stars => $count) {
    $percentages[$stars] = round(($count / $total) * 100);
}
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>AI-ACADEMY Forum Rating Badge</h2>
                    <p class="lead mb-0">Generate a trust badge to display community ratings on your website</p>
                </div>
                <div>
                    <a href="community.php" class="btn btn-outline-primary">Rate Products</a>
                </div>
            </div>

            <!-- Rating Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Community Ratings Overview</h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="rating-display mb-3">
                                <div class="rating-number"><?php echo $ratingData['overall_rating']; ?></div>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?php echo $i <= $ratingData['overall_rating'] ? '#ffc107' : '#e9ecef'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-count"><?php echo $ratingData['total_reviews']; ?> reviews</div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="rating-breakdown">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <div class="rating-bar mb-2">
                                        <span class="rating-label"><?php echo $i; ?> ★</span>
                                        <div class="progress" style="flex: 1; margin: 0 10px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $percentages[$i]; ?>%"></div>
                                        </div>
                                        <span class="rating-percent"><?php echo $percentages[$i]; ?>%</span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badge Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Badge Preview</h4>
                </div>
                <div class="card-body">
                    <div class="badge-preview-container">
                        <div id="ratingBadge" class="rating-badge">
                            <div class="badge-header">
                                <i class="fas fa-star text-warning"></i>
                                <span class="badge-title">AI-ACADEMY Rating</span>
                            </div>
                            <div class="badge-rating">
                                <span class="rating-score"><?php echo $ratingData['overall_rating']; ?></span>
                                <div class="rating-stars-small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?php echo $i <= $ratingData['overall_rating'] ? '#ffc107' : '#e9ecef'; ?>; font-size: 12px;"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="badge-footer">
                                <span class="review-count"><?php echo $ratingData['total_reviews']; ?> reviews</span>
                                <span class="powered-by">Powered by AI-ACADEMY</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customization Options -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Customize Your Badge</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Badge Style</label>
                                <select id="badgeStyle" class="form-select">
                                    <option value="default">Default</option>
                                    <option value="minimal">Minimal</option>
                                    <option value="compact">Compact</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Theme Color</label>
                                <select id="badgeTheme" class="form-select">
                                    <option value="blue">Blue</option>
                                    <option value="green">Green</option>
                                    <option value="purple">Purple</option>
                                    <option value="orange">Orange</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button id="previewBadge" class="btn btn-secondary">Preview Changes</button>
                </div>
            </div>

            <!-- Embed Code Generator -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Your Embed Code</h4>
                    <div>
                        <button id="regenerateCode" class="btn btn-outline-primary btn-sm me-2">Regenerate</button>
                        <button id="copyCode" class="btn btn-primary btn-sm">Copy Code</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="embedCodeContainer">
                        <textarea id="embedCode" class="form-control" rows="8" readonly></textarea>
                    </div>
                    <div id="copyMessage" class="mt-2 d-none alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Code copied to clipboard!
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Copy this code and paste it into your website's HTML where you want the badge to appear.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recent Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Recent Reviews</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($ratingData['recent_reviews'] as $review): ?>
                        <div class="review-item mb-3 pb-3 border-bottom">
                            <div class="review-rating mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $i <= $review['rating'] ? '#ffc107' : '#e9ecef'; ?>; font-size: 12px;"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-2"><?php echo $review['date']; ?></small>
                            </div>
                            <p class="review-text mb-0"><?php echo $review['comment']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Usage Instructions -->
            <div class="card">
                <div class="card-header">
                    <h5>How to Use</h5>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Customize your badge appearance</li>
                        <li>Copy the embed code</li>
                        <li>Paste it into your website's HTML</li>
                        <li>The badge will display your ratings automatically</li>
                    </ol>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i>
                        Badge updates automatically with new reviews
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-display {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    color: white;
}

.rating-number {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.rating-stars {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.rating-count {
    font-size: 0.9rem;
    opacity: 0.9;
}

.rating-breakdown {
    padding: 20px;
}

.rating-bar {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.rating-label {
    width: 30px;
    text-align: center;
}

.rating-percent {
    width: 40px;
    text-align: right;
}

.badge-preview-container {
    display: flex;
    justify-content: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.rating-badge {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    width: 200px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.badge-header {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 0.9rem;
    font-weight: bold;
}

.badge-header i {
    margin-right: 5px;
}

.badge-rating {
    margin-bottom: 10px;
}

.rating-score {
    font-size: 2rem;
    font-weight: bold;
    color: #ffc107;
    display: block;
}

.rating-stars-small {
    margin-top: 5px;
}

.badge-footer {
    font-size: 0.8rem;
    color: #6c757d;
    border-top: 1px solid #dee2e6;
    padding-top: 10px;
    margin-top: 10px;
}

.review-item {
    font-size: 0.9rem;
}

.review-rating {
    color: #ffc107;
}
</style>

<script>
// Global variables for customization
let currentStyle = 'default';
let currentTheme = 'blue';

// Generate embed code based on current settings
function generateEmbedCode() {
    const style = document.getElementById('badgeStyle').value || 'default';
    const theme = document.getElementById('badgeTheme').value || 'blue';

    // Theme colors
    const themeColors = {
        blue: '#007bff',
        green: '#28a745',
        purple: '#6f42c1',
        orange: '#fd7e14'
    };

    const color = themeColors[theme];

    const embedCode = `<div id="ai-academy-rating-badge"></div>
<script>
(function() {
    const badge = document.getElementById('ai-academy-rating-badge');
    badge.innerHTML = \`
        <div style="background: white; border: 2px solid ${color}; border-radius: 12px; padding: 20px; width: 220px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, ${color}, ${color}dd);"></div>
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 1rem; font-weight: bold; color: ${color};">
                <span style="font-size: 1.2rem; margin-right: 8px;">⭐</span>
                <span>AI-ACADEMY Rating</span>
            </div>
            <div style="margin-bottom: 15px;">
                <span style="font-size: 2.5rem; font-weight: bold; color: ${color}; display: block; line-height: 1;">${<?php echo $ratingData['overall_rating']; ?>}</span>
                <div style="margin-top: 8px; font-size: 1.1rem; color: #666;">
                    ★★★★★
                </div>
            </div>
            <div style="font-size: 0.9rem; color: #666; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                <div style="font-weight: bold; margin-bottom: 5px;">${<?php echo $ratingData['total_reviews']; ?>} reviews</div>
                <div style="font-size: 0.8rem; color: #999;">Powered by AI-ACADEMY</div>
            </div>
        </div>
    \`;
})();
</script>`;

    document.getElementById('embedCode').value = embedCode;
    return embedCode;
}

// Copy to clipboard with modern API
document.getElementById('copyCode').addEventListener('click', async function() {
    const code = document.getElementById('embedCode').value;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(code);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }

        const message = document.getElementById('copyMessage');
        message.classList.remove('d-none');
        setTimeout(() => message.classList.add('d-none'), 3000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy code. Please select and copy manually.');
    }
});

// Regenerate code
document.getElementById('regenerateCode').addEventListener('click', function() {
    generateEmbedCode();
    // Add visual feedback
    this.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i>Regenerated!';
    setTimeout(() => {
        this.innerHTML = 'Regenerate';
    }, 1500);
});

// Live preview updates
document.getElementById('badgeStyle').addEventListener('change', function() {
    updatePreview();
    generateEmbedCode();
});

document.getElementById('badgeTheme').addEventListener('change', function() {
    updatePreview();
    generateEmbedCode();
});

// Update preview badge
function updatePreview() {
    const style = document.getElementById('badgeStyle').value;
    const theme = document.getElementById('badgeTheme').value;

    const badge = document.getElementById('ratingBadge');

    // Theme colors
    const themeColors = {
        blue: '#007bff',
        green: '#28a745',
        purple: '#6f42c1',
        orange: '#fd7e14'
    };

    const color = themeColors[theme];

    // Apply styles
    badge.style.borderColor = color;
    badge.style.borderWidth = '2px';
    badge.style.borderRadius = '12px';
    badge.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';

    const headerIcon = badge.querySelector('.badge-header i');
    if (headerIcon) {
        headerIcon.style.color = color;
    }

    const ratingScore = badge.querySelector('.badge-rating .rating-score');
    if (ratingScore) {
        ratingScore.style.color = color;
    }
}

// Make form fields functional
document.addEventListener('DOMContentLoaded', function() {
    console.log('Rating Badge Generator loaded');

    // Generate initial code
    generateEmbedCode();
    console.log('Initial embed code generated');

    // Make all inputs functional
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (!input.hasAttribute('readonly') && input.id !== 'embedCode') {
            input.addEventListener('input', function() {
                console.log(`${this.id} changed to: ${this.value}`);
                // Regenerate code when inputs change
                if (this.tagName === 'SELECT') {
                    generateEmbedCode();
                    updatePreview();
                }
            });
            input.addEventListener('change', function() {
                console.log(`${this.id} changed to: ${this.value}`);
            });
        }
    });

    // Test all buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            console.log(`Button clicked: ${this.id || this.textContent.trim()}`);
        });
    });

    // Initialize preview
    updatePreview();
    console.log('Preview initialized');

    // Test functionality
    setTimeout(() => {
        console.log('Testing functionality...');
        console.log('Badge style select:', document.getElementById('badgeStyle'));
        console.log('Badge theme select:', document.getElementById('badgeTheme'));
        console.log('Embed code textarea:', document.getElementById('embedCode'));
        console.log('Copy button:', document.getElementById('copyCode'));
        console.log('Regenerate button:', document.getElementById('regenerateCode'));
    }, 1000);
});
</script>

<?php include '../footer.php'; ?>