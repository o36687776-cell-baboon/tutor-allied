<?php
// Question page for AI-ACADEMY Forums
$page_title = 'Question';
include '../header.php';

$id = $_GET['id'] ?? 1;

// Mock question data
$questions = [
    1 => [
        'title' => 'How does machine learning work?',
        'author' => 'User1',
        'time' => '2 hours ago',
        'content' => 'This is a detailed explanation of the question...',
        'tags' => ['AI', 'Machine Learning']
    ]
];

$question = $questions[$id] ?? $questions[1];
?>

<div class="container">
    <h2>Question Details</h2>
    <div class="widget">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h3 id="question-title"><?php echo $question['title']; ?></h3>
                <p>Asked by <?php echo $question['author']; ?> • <?php echo $question['time']; ?></p>
            </div>
            <button id="edit-question" class="btn btn-sm btn-outline-primary">Edit Question</button>
        </div>
        <div id="question-content-display">
            <p><?php echo $question['content']; ?></p>
        </div>
        <div id="question-content-edit" style="display: none;">
            <textarea class="form-control rich-editor" id="question-content-editor" rows="4"><?php echo $question['content']; ?></textarea>
            <div class="mt-2">
                <button id="save-question" class="btn btn-sm btn-success">Save</button>
                <button id="cancel-question" class="btn btn-sm btn-secondary">Cancel</button>
            </div>
        </div>
        <div class="mt-3">
            <?php foreach ($question['tags'] as $tag): ?>
                <span class="badge bg-primary me-1"><?php echo $tag; ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="widget">
        <h4>Answers (<span id="answer-count">2</span>)</h4>
        <div id="answers-container">
            <!-- Answers will be loaded here -->
        </div>
    </div>

    <div class="widget">
        <h4>Your Answer</h4>
        <form id="answer-form">
            <div class="mb-3">
                <textarea class="form-control rich-editor" id="answer-content" rows="4" placeholder="Provide your answer..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Answer</button>
        </form>
    </div>
</div>

<script src="../rich-editor.js"></script>

<script>
// Load answers from localStorage
function loadAnswers() {
    const answers = JSON.parse(localStorage.getItem('forum_answers_' + <?php echo $id; ?>) || '[]');
    const container = document.getElementById('answers-container');

    if (answers.length === 0) {
        // Load default answers
        answers.push({
            id: 1,
            author: 'User2',
            content: 'Machine learning works by training algorithms on data to make predictions or decisions without being explicitly programmed for each specific task.',
            time: '1 hour ago',
            votes: 10
        });
        answers.push({
            id: 2,
            author: 'User3',
            content: 'Another perspective: ML involves statistical models that learn patterns from data through iterative optimization processes.',
            time: '30 min ago',
            votes: 5
        });
    }

    container.innerHTML = '';
    answers.forEach(answer => {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer mb-3 p-3 border rounded';
        answerDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div id="answer-content-${answer.id}">
                        <p><strong>${answer.author}:</strong> ${answer.content}</p>
                    </div>
                    <div id="answer-edit-${answer.id}" style="display: none;">
                        <textarea class="form-control rich-editor" id="answer-editor-${answer.id}" rows="3">${answer.content}</textarea>
                        <div class="mt-2">
                            <button onclick="saveAnswerEdit(${answer.id})" class="btn btn-sm btn-success">Save</button>
                            <button onclick="cancelAnswerEdit(${answer.id})" class="btn btn-sm btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button onclick="editAnswer(${answer.id})" class="btn btn-sm btn-outline-primary mb-1">Edit</button>
                    <div class="vote-controls">
                        <button onclick="voteAnswer(${answer.id}, 'up')" class="btn btn-sm btn-outline-success">↑</button>
                        <span class="mx-2" id="votes-${answer.id}">${answer.votes}</span>
                        <button onclick="voteAnswer(${answer.id}, 'down')" class="btn btn-sm btn-outline-danger">↓</button>
                    </div>
                </div>
            </div>
            <small>Answered ${answer.time}</small>
        `;
        container.appendChild(answerDiv);
    });

    document.getElementById('answer-count').textContent = answers.length;
    localStorage.setItem('forum_answers_' + <?php echo $id; ?>, JSON.stringify(answers));
}

// Edit question functionality
document.getElementById('edit-question').addEventListener('click', function() {
    document.getElementById('question-content-display').style.display = 'none';
    document.getElementById('question-content-edit').style.display = 'block';
});

document.getElementById('save-question').addEventListener('click', function() {
    const newContent = document.getElementById('question-content-editor').value;
    document.getElementById('question-content-display').innerHTML = '<p>' + newContent + '</p>';
    document.getElementById('question-content-display').style.display = 'block';
    document.getElementById('question-content-edit').style.display = 'none';
});

document.getElementById('cancel-question').addEventListener('click', function() {
    document.getElementById('question-content-display').style.display = 'block';
    document.getElementById('question-content-edit').style.display = 'none';
});

// Answer form submission
document.getElementById('answer-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const content = document.getElementById('answer-content').value;
    if (content.trim()) {
        const answers = JSON.parse(localStorage.getItem('forum_answers_' + <?php echo $id; ?>) || '[]');
        const newAnswer = {
            id: Date.now(),
            author: 'Current User',
            content: content,
            time: 'Just now',
            votes: 0
        };
        answers.push(newAnswer);
        localStorage.setItem('forum_answers_' + <?php echo $id; ?>, JSON.stringify(answers));
        loadAnswers();
        document.getElementById('answer-content').value = '';
        alert('Answer submitted successfully!');
    }
});

// Edit answer functions
function editAnswer(answerId) {
    document.getElementById('answer-content-' + answerId).style.display = 'none';
    document.getElementById('answer-edit-' + answerId).style.display = 'block';
}

function saveAnswerEdit(answerId) {
    const newContent = document.getElementById('answer-editor-' + answerId).value;
    const answers = JSON.parse(localStorage.getItem('forum_answers_' + <?php echo $id; ?>) || '[]');
    const answer = answers.find(a => a.id === answerId);
    if (answer) {
        answer.content = newContent;
        localStorage.setItem('forum_answers_' + <?php echo $id; ?>, JSON.stringify(answers));
        loadAnswers();
    }
}

function cancelAnswerEdit(answerId) {
    document.getElementById('answer-content-' + answerId).style.display = 'block';
    document.getElementById('answer-edit-' + answerId).style.display = 'none';
}

function voteAnswer(answerId, direction) {
    const answers = JSON.parse(localStorage.getItem('forum_answers_' + <?php echo $id; ?>) || '[]');
    const answer = answers.find(a => a.id === answerId);
    if (answer) {
        answer.votes += direction === 'up' ? 1 : -1;
        localStorage.setItem('forum_answers_' + <?php echo $id; ?>, JSON.stringify(answers));
        document.getElementById('votes-' + answerId).textContent = answer.votes;
    }
}

// Load answers on page load
document.addEventListener('DOMContentLoaded', loadAnswers);
</script>

<?php include '../footer.php'; ?>