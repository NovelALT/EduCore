class CodeEditor {
    constructor() {
        this.htmlEditor = document.querySelector('.code-input.html');
        this.cssEditor = document.querySelector('.code-input.css');
        this.previewFrame = document.getElementById('preview-frame');
        this.runBtn = document.querySelector('.run-btn');
        this.resetBtn = document.querySelector('.reset-btn');
        this.copyBtns = document.querySelectorAll('.copy-btn');
        
        this.originalHtml = this.htmlEditor.value;
        this.originalCss = this.cssEditor.value;
        
        this.init();
    }

    init() {
        // Setup event listeners
        this.runBtn.addEventListener('click', () => this.updatePreview());
        this.resetBtn.addEventListener('click', () => this.resetCode());
        
        // Setup copy buttons
        this.copyBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.copyCode(e.currentTarget.dataset.editor));
        });

        // Auto-update preview on input
        ['input', 'change'].forEach(event => {
            this.htmlEditor.addEventListener(event, () => this.debounce(() => this.updatePreview(), 1000));
            this.cssEditor.addEventListener(event, () => this.debounce(() => this.updatePreview(), 1000));
        });

        // Initial preview
        this.updatePreview();
        this.setupEditors();
    }

    updatePreview() {
        const html = this.htmlEditor.value;
        const css = this.cssEditor.value;
        
        const previewContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>${css}</style>
            </head>
            <body>${this.extractBody(html)}</body>
            </html>
        `;

        const previewDocument = this.previewFrame.contentDocument || this.previewFrame.contentWindow.document;
        previewDocument.open();
        previewDocument.write(previewContent);
        previewDocument.close();

        // Add success animation to run button
        this.runBtn.classList.add('success');
        setTimeout(() => this.runBtn.classList.remove('success'), 1000);
    }

    extractBody(html) {
        const bodyMatch = html.match(/<body[^>]*>([\s\S]*)<\/body>/i);
        return bodyMatch ? bodyMatch[1] : html;
    }

    resetCode() {
        this.htmlEditor.value = this.originalHtml;
        this.cssEditor.value = this.originalCss;
        this.updatePreview();
        
        // Add reset animation
        this.resetBtn.classList.add('active');
        setTimeout(() => this.resetBtn.classList.remove('active'), 500);
    }

    copyCode(editor) {
        const text = editor === 'html' ? this.htmlEditor.value : this.cssEditor.value;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector(`[data-editor="${editor}"]`);
            btn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i>', 2000);
        });
    }

    setupEditors() {
        // Add line numbers and syntax highlighting
        [this.htmlEditor, this.cssEditor].forEach(editor => {
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

    debounce(func, wait) {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(func, wait);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new CodeEditor();
});

let editor;
let currentExercise;

function initEditor() {
    if (!editor) {
        editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            mode: 'php',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: true,
            lineWrapping: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "F11": (cm) => {
                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                },
                "Esc": (cm) => {
                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                },
                "Ctrl-S": (cm) => {
                    saveProgress();
                    return false;
                }
            }
        });

        // Auto-save ทุก 30 วินาที
        setInterval(saveProgress, 30000);
    }
}

function runCode() {
    const code = editor.getValue();
    const output = document.getElementById('output');
    output.innerHTML = '<div class="loading">กำลังรันโค้ด...</div>';

    fetch('api/exercise-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'run',
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            output.innerHTML = `<pre class="success">${data.output}</pre>`;
        } else {
            output.innerHTML = `<pre class="error">${data.error}</pre>`;
        }
    })
    .catch(error => {
        output.innerHTML = `<pre class="error">เกิดข้อผิดพลาด: ${error}</pre>`;
    });
}

function submitCode() {
    const code = editor.getValue();
    if (!code.trim()) {
        alert('กรุณาเขียนโค้ดก่อนส่ง');
        return;
    }

    if (!confirm('ยืนยันการส่งคำตอบ?')) return;

    const submitBtn = document.querySelector('.submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';

    fetch('api/exercise-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'submit',
            exercise_id: currentExercise.exercise_id,
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> ส่งคำตอบ';

        if (data.success) {
            if (data.status === 'passed') {
                showSuccessModal();
            } else {
                showErrorOutput(data.output || data.error);
            }
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> ส่งคำตอบ';
        alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
    });
}

function saveProgress() {
    if (!currentExercise || !editor) return;

    const code = editor.getValue();
    fetch('api/exercise-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_progress',
            exercise_id: currentExercise.exercise_id,
            code: code
        })
    });
}

// ฟังก์ชันเสริม
function showSuccessModal() {
    // แสดง modal หรือ notification เมื่อทำแบบฝึกหัดผ่าน
    const modal = document.createElement('div');
    modal.className = 'success-modal';
    modal.innerHTML = `
        <div class="success-content">
            <i class="fas fa-check-circle"></i>
            <h3>ยินดีด้วย!</h3>
            <p>คุณทำแบบฝึกหัดนี้ผ่านแล้ว</p>
            <button onclick="location.reload()">ตกลง</button>
        </div>
    `;
    document.body.appendChild(modal);
}

function showErrorOutput(error) {
    const output = document.getElementById('output');
    output.innerHTML = `<pre class="error">${error}</pre>`;
    output.scrollIntoView({ behavior: 'smooth' });
}

// เพิ่ม event listeners
document.addEventListener('DOMContentLoaded', () => {
    initEditor();
});
