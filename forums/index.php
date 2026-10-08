<?php
// Forums Index for AI-ACADEMY
$page_title = 'Forums';
include '../header.php';

// Handle demo mode vs real database queries
if ($is_demo_mode) {
    // Demo mode: Provide mock forum questions
    $questions = [
        [
            'id' => 1,
            'title' => 'How to get started with AI development?',
            'author_name' => 'Demo User',
            'answers_count' => 3,
            'views' => 45,
            'tags' => 'AI, Getting Started, Tutorial',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'id' => 2,
            'title' => 'Pi Network smart contract deployment issues',
            'author_name' => 'Demo Developer',
            'answers_count' => 1,
            'views' => 23,
            'tags' => 'Pi Network, Smart Contracts, Deployment',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'id' => 3,
            'title' => 'Best practices for blockchain security',
            'author_name' => 'Demo Expert',
            'answers_count' => 5,
            'views' => 67,
            'tags' => 'Blockchain, Security, Best Practices',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ]
    ];
} else {
    // Real database query
    $questions = fetchAll("SELECT q.*, u.username as author_name, COUNT(a.id) as answers_count FROM forum_questions q LEFT JOIN users u ON q.user_id = u.id LEFT JOIN forum_answers a ON q.id = a.question_id WHERE q.status = 'open' GROUP BY q.id ORDER BY q.created_at DESC LIMIT 50");
}
?>

<div class="container">
    <h2>AI-ACADEMY Forums</h2>

    <!-- Ask Question -->
    <div class="widget">
        <a href="ask.php" class="btn btn-primary btn-lg">Ask a Question</a>
    </div>

    <!-- Search -->
    <div class="widget">
        <div class="input-group">
            <input type="text" id="search-input" class="form-control" placeholder="Search questions...">
            <button id="search-btn" class="btn btn-outline-secondary">Search</button>
        </div>
    </div>

    <!-- Recent Questions -->
    <div class="widget">
        <h3>Recent Questions</h3>
        <div class="list-group">
            <?php if (count($questions) > 0): ?>
                <?php foreach ($questions as $question): ?>
                    <a href="question.php?id=<?php echo $question['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php echo htmlspecialchars($question['title']); ?></h5>
                                <p class="mb-1">
                                    Asked by <?php echo htmlspecialchars($question['author_name'] ?? 'Anonymous'); ?> •
                                    <?php echo $question['answers_count']; ?> answers •
                                    <?php echo $question['views']; ?> views
                                </p>
                                <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($question['created_at'])); ?></small>
                            </div>
                            <div class="text-end">
                                <?php if ($question['tags']): ?>
                                    <?php foreach (explode(',', $question['tags']) as $tag): ?>
                                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item">
                    <p class="mb-0 text-muted">No questions yet. Be the first to ask!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tags -->
    <div class="widget">
        <h3>Popular Tags</h3>
        <div class="d-flex flex-wrap">
            <button onclick="filterByTag('ai')" class="btn btn-sm btn-outline-secondary me-2 mb-2">AI</button>
            <button onclick="filterByTag('blockchain')" class="btn btn-sm btn-outline-secondary me-2 mb-2">Blockchain</button>
            <button onclick="filterByTag('python')" class="btn btn-sm btn-outline-secondary me-2 mb-2">Python</button>
            <button onclick="filterByTag('javascript')" class="btn btn-sm btn-outline-secondary me-2 mb-2">JavaScript</button>
            <button onclick="filterByTag('all')" class="btn btn-sm btn-primary me-2 mb-2">All</button>
        </div>
    </div>
</div>


<?php include '../footer.php'; ?>