<?php
require_once('../config/db_connect.php');
session_start();

$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjaxRequest && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    $action = $data['action'] ?? '';
    $exercise_id = $data['exercise_id'] ?? null;

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header('Location: ../auth/login.php');
        exit;
    }
    
    $code = $data['code'] ?? '';
    
    if ($action === 'submit_exercise') {
        $stmt = $pdo->prepare("SELECT solution_code FROM exercises WHERE exercise_id = ?");
        $stmt->execute([$exercise_id]);
        $exercise = $stmt->fetch();

        $userResult = json_decode(file_get_contents('http://localhost/learning/run_code.php', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => ['Content-Type: application/json', 'X-Requested-With: XMLHttpRequest'],
                'content' => json_encode(['code' => $code])
            ]
        ])), true);

        $solutionResult = json_decode(file_get_contents('http://localhost/learning/run_code.php', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => ['Content-Type: application/json', 'X-Requested-With: XMLHttpRequest'],
                'content' => json_encode(['code' => $exercise['solution_code']])
            ]
        ])), true);

        $userOutput = preg_replace('/\s+/', '', $userResult['output']); 
        $solutionOutput = preg_replace('/\s+/', '', $solutionResult['output']);

        $testPassed = ($userOutput === $solutionOutput);

        if ($testPassed) {
            $stmt = $pdo->prepare("
                INSERT INTO submissions (user_id, exercise_id, submitted_code, status)
                VALUES (?, ?, ?, 'passed')
                ON DUPLICATE KEY UPDATE 
                    submitted_code = VALUES(submitted_code),
                    status = VALUES(status),
                    submitted_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$user_id, $exercise_id, $code]);
        }

        echo json_encode([
            'success' => true,
            'passed' => $testPassed,
            'message' => $testPassed ? 'ส่งคำตอบถูกต้อง!' : 'คำตอบไม่ถูกต้อง กรุณาลองใหม่',
            'results' => [[
                'passed' => $testPassed,
                'actual' => $userResult['output']
            ]]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$user_id = $_SESSION['user_id'] ?? 1;
$exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$exercise_id) {
    header('Location: /learning/courses.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.*, l.title as lesson_title, c.title as course_title,
           c.course_id, l.lesson_id,
           (SELECT status FROM submissions 
            WHERE exercise_id = e.exercise_id 
            AND user_id = ? 
            ORDER BY submitted_at DESC LIMIT 1) as submission_status
    FROM exercises e
    JOIN lessons l ON e.lesson_id = l.lesson_id
    JOIN courses c ON l.course_id = c.course_id
    WHERE e.exercise_id = ?
");
$stmt->execute([$user_id, $exercise_id]);
$exercise = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT * FROM hints 
    WHERE exercise_id = ? 
    ORDER BY order_number
");
$stmt->execute([$exercise_id]);
$hints = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT current_code FROM code_progress
    WHERE user_id = ? AND exercise_id = ?
");
$stmt->execute([$user_id, $exercise_id]);
$progress = $stmt->fetch();

$initial_code = $progress ? $progress['current_code'] : $exercise['initial_code'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exercise['title']); ?> - Codly</title>
    <link rel="stylesheet" href="../assets/css/exercise.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/firacode@6.2.0/distr/fira_code.css">
    
    <!-- Add Monaco Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.js"></script>

    <style>
        .output-message {
            padding: 10px;
            border-radius: 4px;
            margin: 5px 0;
            font-family: 'Fira Code', monospace;
        }
        .error-message {
            color: #ff4444;
            background: rgba(255, 68, 68, 0.1);
            border-left: 3px solid #ff4444;
        }
        .success-output {
            background: #1e1e1e;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .loading-message {
            color: #666;
            font-style: italic;
        }
        
        .test-results {
            margin-top: 10px;
        }
        
        .test-case {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .test-header {
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f5f5f5;
        }
        
        .test-case.passed .test-header {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .test-case.failed .test-header {
            background: #fbe9e7;
            color: #c62828;
        }
        
        .test-details {
            padding: 12px;
            background: #fff;
        }
        
        .test-details pre {
            background: #f8f9fa;
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: 'Fira Code', monospace;
        }
        
        .submission-status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .submission-status.success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .submission-status.error {
            background: #fbe9e7;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="exercise-container">
        <div class="description-panel">
            <nav class="exercise-nav">
                <a href="lesson.php?course_id=<?php echo urlencode($exercise['course_id']); ?>&lesson_id=<?php echo urlencode($exercise['lesson_id']); ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i> กลับไปยังบทเรียน
                </a>
                <div class="exercise-breadcrumb">
                    <span><?php echo htmlspecialchars($exercise['course_title']); ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($exercise['lesson_title']); ?></span>
                </div>
            </nav>

            <div class="exercise-details">  
                <h1><?php echo htmlspecialchars($exercise['title']); ?></h1>
                <div class="exercise-description">
                    <?php echo $exercise['description']; ?>
                </div>
                
                <div class="hints-section">
                    <h3><i class="fas fa-lightbulb"></i> คำใบ้</h3>
                    <?php foreach ($hints as $hint): ?>
                        <div class="hint-card" data-cost="<?php echo $hint['cost']; ?>">
                            <button class="hint-trigger" onclick="showHint(this, <?php echo $hint['hint_id']; ?>)">
                                <i class="fas fa-key"></i>
                                ใช้ <?php echo $hint['cost']; ?> Hint Points
                            </button>
                            <div class="hint-content hidden">
                                <?php echo $hint['content']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="editor-panel">
            <div class="editor-toolbar">
                <div class="file-tab">main.py</div>
                <div class="editor-actions">
                    <button onclick="saveProgress(<?php echo $exercise_id; ?>)">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <button onclick="runCurrentCode()">
                        <i class="fas fa-play"></i> Run
                    </button>
                    <button onclick="submitExercise()" class="primary">
                        <i class="fas fa-check"></i> Submit
                    </button>
                </div>
            </div>
            
            <div class="code-area" id="editor-container">
                <div id="monaco-editor"></div>
            </div>

            <div class="output-panel">
                <div class="output-header">
                    <span>Output</span>
                    <div class="output-actions">
                        <button onclick="clearOutput()">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                </div>
                <div id="output-area" class="output-area">
                    <div class="output-message">Ready to run code...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            window.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: `<?php echo str_replace("\n", "\\n", addslashes($initial_code)); ?>`,
                language: 'python',
                theme: 'vs-dark',
                automaticLayout: true,
                minimap: { enabled: false },
                fontSize: 16,
                fontFamily: "'Fira Code', monospace",
                lineNumbers: 'on',
                roundedSelection: false,
                scrollBeyondLastLine: false,
                readOnly: false,
                tabSize: 4
            });
        });

        async function runCurrentCode() {
            const outputArea = document.getElementById('output-area');
            outputArea.innerHTML = '<div class="loading-message">กำลังรันโค้ด...</div>';
            
            try {
                const code = window.editor.getValue();
                const response = await fetch('run_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code: code })
                });

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON:', text);
                    throw new Error('Invalid response format');
                }

                if (data.success) {
                    let output = data.output || '';
                    if (data.error) {
                        output += '\n\nError:\n' + data.error;
                    }
                    output = output.replace(/&/g, '&amp;')
                                 .replace(/</g, '&lt;')
                                 .replace(/>/g, '&gt;');
                    outputArea.innerHTML = `<pre class="success-output">${output}</pre>`;
                } else {
                    throw new Error(data.message || 'เกิดข้อผิดพลาดในการรันโค้ด');
                }
            } catch (error) {
                console.error('Error:', error);
                outputArea.innerHTML = `<div class="error-message">
                    เกิดข้อผิดพลาดในการทำงาน: ${error.message}<br>
                    กรุณาลองใหม่อีกครั้ง
                </div>`;
            }
        }

        async function saveProgress() {
            try {
                const code = window.editor.getValue();
                const response = await fetch('exercise.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'save_progress',
                        exercise_id: <?php echo $exercise_id; ?>,
                        code: code
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const notification = document.createElement('div');
                notification.className = 'save-notification';
                notification.innerHTML = '<i class="fas fa-check"></i> บันทึกโค้ดแล้ว';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);

            } catch (error) {
                console.error('Failed to save:', error);
                const notification = document.createElement('div');
                notification.className = 'error-notification';
                notification.innerHTML = '<i class="fas fa-exclamation-circle"></i> ไม่สามารถบันทึกโค้ดได้';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            }
        }

        function clearOutput() {
            document.getElementById('output-area').innerHTML = '<div class="output-message">Ready to run code...</div>';
        }

        async function submitExercise() {
            const outputArea = document.getElementById('output-area');
            outputArea.innerHTML = '<div class="loading-message">กำลังตรวจสอบคำตอบ...</div>';
            
            try {
                const code = window.editor.getValue();
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'submit_exercise',
                        exercise_id: <?php echo $exercise_id; ?>,
                        code: code
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    let resultHtml = '<div class="test-results">';
                    
                    data.results.forEach((result, index) => {
                        resultHtml += `
                            <div class="test-case ${result.passed ? 'passed' : 'failed'}">
                                <div class="test-header">
                                    <span class="test-number">Test Case ${index + 1}</span>
                                    <span class="test-status">
                                        <i class="fas fa-${result.passed ? 'check' : 'times'}"></i>
                                        ${result.passed ? 'ผ่าน' : 'ไม่ผ่าน'}
                                    </span>
                                </div>
                                ${!result.passed ? `
                                    <div class="test-details">
                                        <div class="actual">
                                            <strong>ผลลัพธ์ที่ได้:</strong>
                                            <pre>${result.actual}</pre>
                                        </div>
                                        ${result.error ? `
                                            <div class="error">
                                                <strong>Error:</strong>
                                                <pre>${result.error}</pre>
                                            </div>
                                        ` : ''}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    });
                    
                    resultHtml += `
                        <div class="submission-status ${data.passed ? 'success' : 'error'}">
                            <i class="fas fa-${data.passed ? 'check-circle' : 'exclamation-circle'}"></i>
                            ${data.message}
                        </div>
                    </div>`;
                    
                    outputArea.innerHTML = resultHtml;
                    
                    if (data.passed) {
                        // Refresh the page after 2 seconds on success
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งคำตอบ');
                }
            } catch (error) {
                console.error('Error:', error);
                outputArea.innerHTML = `<div class="error-message">
                    เกิดข้อผิดพลาดในการส่งคำตอบ: ${error.message}<br>
                    กรุณาลองใหม่อีกครั้ง
                </div>`;
            }
        }

        setInterval(async () => {
            try {
                const code = window.editor.getValue();
                await fetch('lesson.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'save_progress',
                        exercise_id: <?php echo $exercise_id; ?>,
                        code: code
                    })
                });
            } catch (error) {
                console.error('Failed to auto-save:', error);
            }
        }, 30000);
    </script>
</body>
</html>
