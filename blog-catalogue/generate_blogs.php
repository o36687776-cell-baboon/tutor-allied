<?php
// Generate 100 SEO-optimized blog articles for the blog catalogue

function slugify($s) {
    return strtolower(trim(preg_replace('/[^a-z0-9\-]/', '', preg_replace('/\s+/', '-', $s)), '-'));
}

function randChoice($arr) {
    return $arr[array_rand($arr)];
}

function uniqueTitle($seed, $i) {
    $variations = ['A practical guide', 'Explained', 'Beginner\'s guide', 'What you need to know', 'Top tips', 'Step-by-step'];
    return $seed . ': ' . $variations[$i % count($variations)];
}

function generateMeta($title, $keywords) {
    $desc = $title . ' — Explore this detailed guide covering key ideas, steps, and best practices. Learn actionable tips and real-life examples.';
    return ['metaDesc' => $desc, 'metaKeywords' => $keywords];
}

function getImageUrl($seed) {
    $seedLower = strtolower($seed);
    if (strpos($seedLower, 'ai') !== false) {
        return 'https://source.unsplash.com/800x400/?artificial,intelligence,technology';
    } elseif (strpos($seedLower, 'blockchain') !== false) {
        return 'https://source.unsplash.com/800x400/?blockchain,crypto,currency';
    } elseif (strpos($seedLower, 'pi network') !== false) {
        return 'https://source.unsplash.com/800x400/?cryptocurrency,pi,network';
    } elseif (strpos($seedLower, 'online learning') !== false || strpos($seedLower, 'study') !== false) {
        return 'https://source.unsplash.com/800x400/?online,learning,education';
    } elseif (strpos($seedLower, 'tutor') !== false) {
        return 'https://source.unsplash.com/800x400/?tutor,teaching,education';
    } else {
        return 'https://source.unsplash.com/800x400/?education,learning';
    }
}

function generateArticleBody($seed, $keywords) {
    $intro = "Introduction\n\n" . $seed . " is an important topic for learners and tutors alike. In this article we explore why " . strtolower($seed) . " matters, concrete steps to get started, and practical advice you can use immediately.";
    $paragraphs = [$intro];

    // Add relevant image
    $imageUrl = getImageUrl($seed);
    $paragraphs[] = '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($seed) . '" style="max-width:100%; height:auto;">';

    $sections = [
        'Why it matters',
        'Getting started',
        'Tools and resources',
        'Common mistakes to avoid',
        'A step-by-step plan',
        'Case study and examples',
        'Conclusion and next steps'
    ];

    foreach ($sections as $s) {
        $para = $s . ": \n\nThis section covers " . strtolower($s) . " for " . strtolower($seed) . ". ";
        $para .= "Students and tutors frequently ask about " . randChoice(explode(',', $keywords)) . ". ";
        $para .= "Practical tips: \n";
        for ($i = 0; $i < 6; $i++) {
            $para .= "Tip " . ($i + 1) . " — " . randChoice(['set clear goals', 'practice deliberately', 'use peer feedback', 'build small projects', 'track progress', 'use revision schedules']) . ". \n";
        }
        $paragraphs[] = $para;
    }

    $body = implode("\n\n", $paragraphs);

    // Pad to ~1000 words
    while (str_word_count($body) < 1000) {
        $body .= "\n\n" . randChoice($paragraphs) . " " . randChoice(['Further reading is recommended.', 'Try applying the steps today.', 'Document your progress in a learning journal.']);
    }

    return $body;
}

$topics = [
    'AI and blockchain in education',
    'Pi Network for students',
    'How to start learning AI',
    'Best online study tips',
    'How to verify Pi wallet for coursework',
    'Career paths after AI courses',
    'How to use Pi in micro-payments for tutors',
    'Building AI projects for beginners',
    'Ethics of AI in education',
    'Tutor tips for remote teaching'
];

$keywords = 'AI education, Pi Network, blockchain, online learning';
$categories = ['AI', 'Blockchain', 'Software Development', 'Tutorials', 'General'];

$articles = [];
for ($i = 0; $i < 100; $i++) {
    $seed = $topics[$i % count($topics)] . ($i >= count($topics) ? ' (part ' . floor($i / count($topics)) + 1 . ')' : '');
    $title = uniqueTitle($seed, $i);
    $slug = slugify($title) . '-' . ($i + 1);
    $meta = generateMeta($title, $keywords);
    $body = generateArticleBody($seed, $keywords);
    $excerpt = implode(' ', array_slice(explode("\n\n", $body), 0, 2));

    $article = [
        'id' => time() + $i,
        'title' => $title,
        'category' => randChoice($categories),
        'tags' => $keywords,
        'content' => $body,
        'excerpt' => substr($excerpt, 0, 200),
        'author' => 'AI Content Generator',
        'date' => date('Y-m-d'),
        'status' => 'approved',
        'slug' => $slug
    ];
    $articles[] = $article;
}

// Read existing blogs
$existing = [];
if (file_exists('user_blogs.json')) {
    $existing = json_decode(file_get_contents('user_blogs.json'), true) ?: [];
}

// Append new articles
$allBlogs = array_merge($existing, $articles);

// Write back
file_put_contents('user_blogs.json', json_encode($allBlogs, JSON_PRETTY_PRINT));

echo "Generated and published 100 blog articles to user_blogs.json\n";
?>