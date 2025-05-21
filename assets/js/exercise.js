class ExerciseEditor {
    constructor() {
        this.initializeEditor();
    }

    initializeEditor() {
        // Auto-resize และ tab handling
        document.querySelectorAll('.code-editor').forEach(editor => {
            editor.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = editor.selectionStart;
                    const end = editor.selectionEnd;
                    editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                    editor.selectionStart = editor.selectionEnd = start + 4;
                }
            });
        });
    }

    async saveProgress(exerciseId) {
        const code = document.getElementById(`code-${exerciseId}`).value;
        
        try {
            const response = await fetch('lesson.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'save_progress',
                    exercise_id: exerciseId,
                    code: code
                })
            });
            
            const data = await response.json();
            if (data.success) {
                this.showNotification('บันทึกโค้ดเรียบร้อย', 'success');
            }
        } catch (error) {
            this.showNotification('ไม่สามารถบันทึกโค้ดได้', 'error');
        }
    }

    showNotification(message, type) {
        // Implementation of notification system
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.exerciseEditor = new ExerciseEditor();
});

// Global functions
function saveProgress(exerciseId) {
    window.exerciseEditor.saveProgress(exerciseId);
}

function runCode(exerciseId) {
    const code = document.getElementById('code-' + exerciseId).value;
    const outputElement = document.getElementById('output-' + exerciseId);
    
    outputElement.innerHTML = '<div class="py-loading">กำลังรันโค้ด...</div>';

    fetch('lesson.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'run_code',
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            outputElement.innerHTML = `<pre class="py-terminal ${data.error ? 'error' : ''}">${data.output}</pre>`;
        } else {
            throw new Error(data.message || 'Failed to run code');
        }
    })
    .catch(error => {
        outputElement.innerHTML = `<div class="py-error">Error: ${error.message}</div>`;
    });
}

function clearOutput(exerciseId) {
    document.getElementById(`output-${exerciseId}`).innerHTML = 'Ready to run code...';
}

function showHint(button, hintId) {
    const hintCard = button.closest('.hint-card');
    const hintContent = hintCard.querySelector('.hint-content');
    const cost = parseInt(hintCard.dataset.cost);
    
    // Implement hint point system here
    hintContent.classList.remove('hidden');
    button.disabled = true;
}
