class CodeEditor {
    constructor() {
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Auto-resize textarea
        document.querySelectorAll('.code-editor').forEach(editor => {
            editor.addEventListener('input', () => this.autoResizeTextarea(editor));
        });
    }

    autoResizeTextarea(element) {
        element.style.height = 'auto';
        element.style.height = (element.scrollHeight) + 'px';
    }

    async runCode(exerciseId) {
        const outputElement = document.getElementById(`output-${exerciseId}`);
        const codeElement = document.getElementById(`code-${exerciseId}`);
        
        try {
            outputElement.innerHTML = '<div class="py-loading">กำลังรันโค้ด...</div>';
            
            const response = await fetch('lesson.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'run_code',
                    code: codeElement.value
                })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                if (data.error) {
                    outputElement.innerHTML = `<div class="py-error">${data.output}</div>`;
                } else {
                    outputElement.innerHTML = `<pre class="py-terminal">${data.output || 'No output'}</pre>`;
                }
            } else {
                throw new Error(data.message || 'Failed to run code');
            }
        } catch (error) {
            console.error('Error:', error);
            outputElement.innerHTML = `<div class="py-error">
                <strong>เกิดข้อผิดพลาด:</strong><br>
                ${error.message}
            </div>`;
        }
    }

    async submitExercise(exerciseId) {
        const code = document.getElementById(`code-${exerciseId}`).value;
        const outputElement = document.getElementById(`output-${exerciseId}`);
        
        try {
            outputElement.innerHTML = '<div class="py-loading">Submitting...</div>';
            
            const response = await fetch('lesson.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'submit_exercise',
                    exercise_id: exerciseId,
                    code: code
                })
            });

            const data = await response.json();
            if (data.success) {
                let output = '<div class="results-container">';
                data.results.forEach(result => {
                    output += `
                        <div class="test-result ${result.passed ? 'passed' : 'failed'}">
                            <div class="result-header">
                                ${result.checkpoint}: ${result.passed ? '✅ ผ่าน' : '❌ ไม่ผ่าน'}
                            </div>
                            ${!result.passed ? `
                                <div class="result-details">
                                    <div>Expected: ${result.expected}</div>
                                    <div>Got: ${result.actual}</div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                output += '</div>';
                outputElement.innerHTML = output;
            }
        } catch (error) {
            outputElement.innerHTML = `<div class="py-error">Error: ${error.message}</div>`;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.codeEditor = new CodeEditor();
});

// Global functions for button onclick
function runPyScript(exerciseId) {
    window.pyScriptEditor.runPyScript(exerciseId);
}

function submitPyScript(exerciseId) {
    window.pyScriptEditor.submitPyScript(exerciseId);
}
