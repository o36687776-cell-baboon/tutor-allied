// PI Code Playground - Sololearn-style IDE
function renderIDE(){
  const view = document.getElementById('view');
  view.innerHTML = `
    <h3 class="mono">PI Code Playground</h3>
    <p class="muted-small">Write, run, and debug HTML, CSS, and JavaScript code. Preview your creations in real-time.</p>
    <div class="row">
      <div class="col-md-6">
        <div class="mb-2">
          <select id="languageSelect" class="form-select form-select-sm" onchange="changeLanguage()">
            <option value="html">HTML</option>
            <option value="css">CSS</option>
            <option value="js">JavaScript</option>
          </select>
        </div>
        <textarea id="codeEditor" class="form-control" style="height:300px;font-family:'Courier New', monospace;font-size:14px;resize:none;" placeholder="Write your code here..."></textarea>
        <div class="mt-2 d-flex gap-2">
          <button class="btn btn-sm btn-success" onclick="runCode()">▶ Run Code</button>
          <button class="btn btn-sm btn-warning" onclick="debugCode()">🔍 Debug</button>
          <button class="btn btn-sm btn-outline-secondary" onclick="clearAll()">🗑 Clear All</button>
          <button class="btn btn-sm btn-info" onclick="loadExample()">📚 Load Example</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-2">
          <strong class="mono">Live Preview:</strong>
        </div>
        <iframe id="previewFrame" style="width:100%;height:300px;border:1px solid #333;background:#fff;border-radius:4px;"></iframe>
        <div class="mt-2">
          <strong class="mono">Console Output:</strong>
        </div>
        <div id="consoleOutput" class="terminal mt-1 p-2" style="height:150px;overflow-y:auto;font-size:12px;"></div>
      </div>
    </div>
  `;
  // Initialize
  changeLanguage();
}

function changeLanguage(){
  const lang = document.getElementById('languageSelect').value;
  const editor = document.getElementById('codeEditor');
  const examples = {
    html: `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Web Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to PI Bootstrap!</h1>
        <p>This is a live HTML preview. You can edit the code and see changes instantly.</p>
        <button class="btn" onclick="alert('Hello from PI IDE!')">Click Me</button>
        <div id="dynamic-content"></div>
    </div>

    <script>
        // JavaScript example
        console.log('Page loaded successfully!');
        document.getElementById('dynamic-content').innerHTML = '<p><strong>This content was added dynamically!</strong></p>';
    </script>
</body>
</html>`,
    css: `/* CSS Styling Example */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
    text-align: center;
    backdrop-filter: blur(10px);
}

h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 2.5em;
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

p {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-primary {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(45deg, #f093fb, #f5576c);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(245, 87, 108, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.8s ease-out;
}`,
    js: `// JavaScript Programming Example

// Variables and Data Types
let userName = 'Developer';
const PI = 3.14159;
let isLoggedIn = true;
let userScore = 0;

// Arrays and Objects
let skills = ['HTML', 'CSS', 'JavaScript', 'React'];
let user = {
    name: userName,
    score: userScore,
    skills: skills,
    isActive: isLoggedIn
};

// Functions
function greetUser(name) {
    return \`Hello, \${name}! Welcome to PI Code Playground.\`;
}

function calculateArea(radius) {
    return PI * radius * radius;
}

function updateScore(points) {
    userScore += points;
    user.score = userScore;
    console.log(\`Score updated: \${userScore}\`);
    return userScore;
}

// Event Handlers (for demonstration)
function handleClick() {
    console.log('Button clicked!');
    updateScore(10);
    displayUserInfo();
}

function displayUserInfo() {
    console.log('User Information:');
    console.log(\`Name: \${user.name}\`);
    console.log(\`Score: \${user.score}\`);
    console.log(\`Skills: \${user.skills.join(', ')}\`);
    console.log(\`Active: \${user.isActive}\`);
}

// DOM Manipulation (if running in browser)
if (typeof document !== 'undefined') {
    console.log('Running in browser environment');
}

// Main execution
console.log(greetUser(userName));
console.log(\`Area of circle with radius 5: \${calculateArea(5)}\`);

handleClick();

// Array methods demonstration
let numbers = [1, 2, 3, 4, 5];
let doubled = numbers.map(n => n * 2);
console.log('Original numbers:', numbers);
console.log('Doubled numbers:', doubled);

// Error handling example
try {
    let result = someUndefinedFunction();
} catch (error) {
    console.log('Caught an error:', error.message);
}

// Async example (simplified)
setTimeout(() => {
    console.log('This message appears after 1 second');
}, 1000);

console.log('Script execution completed!');`
  };

  editor.value = examples[lang];
  editor.placeholder = `Write your ${lang.toUpperCase()} code here...`;
}

function runCode(){
  const lang = document.getElementById('languageSelect').value;
  const code = document.getElementById('codeEditor').value;
  const output = document.getElementById('consoleOutput');
  const preview = document.getElementById('previewFrame');

  // Clear previous output
  output.innerHTML = '';

  if (lang === 'html') {
    // Render HTML in iframe
    try {
      const iframe = preview.contentWindow.document;
      iframe.open();
      iframe.write(code);
      iframe.close();
      output.innerHTML = '<span style="color:#8aed92">✓ HTML rendered successfully in preview pane</span>';
    } catch (e) {
      output.innerHTML = '<span style="color:#ff6b6b">✗ Error rendering HTML: ' + e.message + '</span>';
    }
  } else if (lang === 'css') {
    // Apply CSS to a test HTML structure
    try {
      const iframe = preview.contentWindow.document;
      iframe.open();
      iframe.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <style>${code}</style>
        </head>
        <body>
          <div class="test-container">
            <h1>CSS Preview</h1>
            <p>This is a test paragraph to see your CSS styles.</p>
            <button class="test-button">Test Button</button>
            <div class="test-card">
              <h2>Card Title</h2>
              <p>Card content goes here.</p>
            </div>
            <ul class="test-list">
              <li>List item 1</li>
              <li>List item 2</li>
              <li>List item 3</li>
            </ul>
          </div>
        </body>
        </html>
      `);
      iframe.close();
      output.innerHTML = '<span style="color:#8aed92">✓ CSS applied to preview pane</span>';
    } catch (e) {
      output.innerHTML = '<span style="color:#ff6b6b">✗ Error applying CSS: ' + e.message + '</span>';
    }
  } else if (lang === 'js') {
    // Execute JavaScript and capture console output
    const originalLog = console.log;
    const originalError = console.error;
    const originalWarn = console.warn;
    let logs = [];

    console.log = (...args) => {
      logs.push('<span style="color:#8aed92">[LOG]</span> ' + args.join(' '));
      originalLog(...args);
    };
    console.error = (...args) => {
      logs.push('<span style="color:#ff6b6b">[ERROR]</span> ' + args.join(' '));
      originalError(...args);
    };
    console.warn = (...args) => {
      logs.push('<span style="color:#f39c12">[WARN]</span> ' + args.join(' '));
      originalWarn(...args);
    };

    try {
      // Execute the code
      const result = Function(code)();
      console.log = originalLog;
      console.error = originalError;
      console.warn = originalWarn;

      if (logs.length === 0) {
        logs.push('<span style="color:#8aed92">✓ Code executed successfully</span>');
      }

      if (result !== undefined) {
        logs.push('<span style="color:#7fb6ff">[RETURN]</span> ' + JSON.stringify(result, null, 2));
      }

      output.innerHTML = logs.join('<br>');
    } catch (e) {
      console.log = originalLog;
      console.error = originalError;
      console.warn = originalWarn;
      output.innerHTML = '<span style="color:#ff6b6b">✗ Runtime Error: ' + e.message + '</span>';
    }
  }
}

function debugCode(){
  const lang = document.getElementById('languageSelect').value;
  const code = document.getElementById('codeEditor').value;
  const output = document.getElementById('consoleOutput');

  output.innerHTML = '<span style="color:#f39c12">🔍 Analyzing ' + lang.toUpperCase() + ' code...</span><br><br>';

  if (lang === 'html') {
    let issues = [];
    if (!code.includes('<!DOCTYPE html>')) issues.push('Missing DOCTYPE declaration');
    if (!code.includes('<html')) issues.push('Missing opening <html> tag');
    if (!code.includes('</html>')) issues.push('Missing closing </html> tag');
    if (!code.includes('<head>')) issues.push('Missing <head> section');
    if (!code.includes('<body>')) issues.push('Missing <body> section');
    if (code.includes('<script>') && !code.includes('</script>')) issues.push('Unclosed <script> tag');
    if (code.includes('<style>') && !code.includes('</style>')) issues.push('Unclosed <style> tag');

    if (issues.length === 0) {
      output.innerHTML += '<span style="color:#8aed92">✅ HTML structure looks good!</span>';
    } else {
      output.innerHTML += '<span style="color:#ff6b6b">⚠️ Issues found:</span><br>' +
        issues.map(issue => '• ' + issue).join('<br>');
    }
  } else if (lang === 'css') {
    let issues = [];
    const rules = code.split('}');
    rules.forEach(rule => {
      if (rule.trim() && !rule.includes('{')) {
        issues.push('Missing opening brace: ' + rule.trim());
      }
    });

    // Check for common syntax errors
    if (code.includes('{') && !code.includes('}')) {
      issues.push('Unclosed CSS rule block');
    }

    if (issues.length === 0) {
      output.innerHTML += '<span style="color:#8aed92">✅ CSS syntax appears valid!</span>';
    } else {
      output.innerHTML += '<span style="color:#ff6b6b">⚠️ Issues found:</span><br>' +
        issues.map(issue => '• ' + issue).join('<br>');
    }
  } else if (lang === 'js') {
    try {
      // Try to parse the code
      new Function(code);
      output.innerHTML += '<span style="color:#8aed92">✅ JavaScript syntax is valid!</span>';
    } catch (e) {
      output.innerHTML += '<span style="color:#ff6b6b">❌ Syntax Error: ' + e.message + '</span>';
    }
  }
}

function clearAll(){
  document.getElementById('codeEditor').value = '';
  document.getElementById('consoleOutput').innerHTML = '';
  document.getElementById('previewFrame').src = 'about:blank';
}

function loadExample(){
  changeLanguage();
}
