<?php
// Library Details page
$page_title = 'Publication Details';
include '../header.php';

$id = $_GET['id'] ?? 1;

// Mock data for different publications
$publications = [
    1 => [
        'title' => 'AI Fundamentals',
        'author' => 'Dr. AI Expert',
        'published' => '2023-10-01',
        'category' => 'AI',
        'description' => 'This is a comprehensive guide to AI fundamentals, covering machine learning, neural networks, and practical applications.',
        'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        'toc' => ['Introduction to AI', 'Machine Learning Basics', 'Neural Networks', 'Practical Applications']
    ],
    2 => [
        'title' => 'Blockchain Explained',
        'author' => 'Crypto Guru',
        'published' => '2023-09-15',
        'category' => 'Blockchain',
        'description' => 'A complete video series explaining blockchain technology and its real-world applications.',
        'content' => 'Blockchain technology explained from basics to advanced concepts including smart contracts and decentralized applications.',
        'toc' => ['What is Blockchain?', 'How Blocks Work', 'Cryptography in Blockchain', 'Smart Contracts']
    ],
    3 => [
        'title' => 'Python Notes',
        'author' => 'Code Master',
        'published' => '2023-11-01',
        'category' => 'Software Development',
        'description' => 'Comprehensive study notes for Python programming language.',
        'content' => 'Complete Python notes covering syntax, data structures, object-oriented programming, and best practices.',
        'toc' => ['Python Basics', 'Data Structures', 'Functions', 'OOP Concepts', 'File Handling']
    ]
];

$pub = $publications[$id] ?? $publications[1];
?>

<div class="container">
    <h2>Publication Details</h2>
    <div class="widget">
        <h3><?php echo $pub['title']; ?></h3>
        <p><strong>Author:</strong> <?php echo $pub['author']; ?></p>
        <p><strong>Published:</strong> <?php echo $pub['published']; ?></p>
        <p><strong>Category:</strong> <?php echo $pub['category']; ?></p>
        <div class="mt-3">
            <p><?php echo $pub['description']; ?></p>
            <p><?php echo $pub['content']; ?></p>
        </div>
        <div class="mt-3">
            <h5>Table of Contents</h5>
            <ul>
                <?php foreach ($pub['toc'] as $item): ?>
                    <li><?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="catalogue.php" class="btn btn-primary">Back to Catalogue</a>
    </div>
</div>

<?php include '../footer.php'; ?>