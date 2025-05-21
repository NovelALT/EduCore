<div class="admin-content">
    <h1>จัดการคำใบ้</h1>
    
    <div class="admin-actions">
        <button class="btn btn-primary" onclick="showAddHintModal()">
            <i class="fas fa-plus"></i> เพิ่มคำใบ้
        </button>
    </div>

    <div id="loadingIndicator" style="display: none;" class="text-center">
        <i class="fas fa-spinner fa-spin"></i> กำลังโหลด...
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exercise</th>
                    <th>เนื้อหา</th>
                    <th>ประเภท</th>
                    <th>ค่าใช้จ่าย</th>
                    <th>ลำดับ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody id="hintsTableBody"></tbody>
        </table>
    </div>
    <div id="pagination" class="pagination"></div>
</div>

<!-- Add/Edit Hint Modal -->
<div id="hintModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">เพิ่มคำใบ้</h3>
            <span class="close" onclick="modal.hide('hintModal')">&times;</span>
        </div>
        <form id="hintForm" onsubmit="return handleSubmit(event)">
            <input type="hidden" id="hint_id" name="hint_id">
            
            <div class="form-group">
                <label for="exercise_id">แบบฝึกหัด:</label>
                <select id="exercise_id" name="exercise_id" class="form-control" required></select>
            </div>

            <div class="form-group">
                <label for="content">เนื้อหา:</label>
                <textarea id="content" name="content" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label for="type">ประเภท:</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="code_snippet">Code Snippet</option>
                    <option value="explanation">คำอธิบาย</option>
                    <option value="example">ตัวอย่าง</option>
                    <option value="solution_step">ขั้นตอนการแก้ปัญหา</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cost">ค่าใช้จ่าย (Points):</label>
                <input type="number" id="cost" name="cost" class="form-control" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label for="order_number">ลำดับ:</label>
                <input type="number" id="order_number" name="order_number" class="form-control" value="1" min="1" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="modal.hide('hintModal')">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

async function loadHints(page = 1) {
    currentPage = page;
    const loadingIndicator = document.getElementById('loadingIndicator');
    try {
        loadingIndicator.style.display = 'block';
        const response = await fetch(`api/hints/list.php?p=${page}`);
        const data = await response.json();

        if (data.success) {
            renderHints(data);
            renderPagination(data.current_page, data.total_pages);
        } else {
            toast.show('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    } finally {
        loadingIndicator.style.display = 'none';
    }
}

function renderHints(data) {
    const tbody = document.getElementById('hintsTableBody');
    tbody.innerHTML = data.hints.map(hint => `
        <tr>
            <td>${hint.hint_id}</td>
            <td>${escapeHtml(hint.exercise_title || '')}</td>
            <td>${escapeHtml(hint.content)}</td>
            <td>${getHintTypeText(hint.type)}</td>
            <td>${hint.cost}</td>
            <td>${hint.order_number}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" onclick="editHint(${hint.hint_id})" title="แก้ไข">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="deleteHint(${hint.hint_id})" title="ลบ">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function getHintTypeText(type) {
    const types = {
        'code_snippet': 'Code Snippet',
        'explanation': 'คำอธิบาย',
        'example': 'ตัวอย่าง',
        'solution_step': 'ขั้นตอนการแก้ปัญหา'
    };
    return types[type] || type;
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = formData.get('hint_id');

    try {
        const response = await fetch(`api/hints/${isEdit ? 'update' : 'create'}.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show(isEdit ? 'อัปเดตข้อมูลสำเร็จ' : 'เพิ่มคำใบ้สำเร็จ');
            modal.hide('hintModal');
            loadHints(currentPage);
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function loadExercises() {
    const select = document.getElementById('exercise_id');
    try {
        select.disabled = true;
        const response = await fetch('api/exercises/list.php');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data.success) {
            select.innerHTML = `
                <option value="">เลือกแบบฝึกหัด</option>
                ${data.exercises.map(exercise => 
                    `<option value="${exercise.exercise_id}">${escapeHtml(exercise.title)}</option>`
                ).join('')}
            `;
        } else {
            throw new Error(data.message || 'ไม่สามารถโหลดข้อมูลแบบฝึกหัด');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการโหลดแบบฝึกหัด: ' + error.message, 'error');
        select.innerHTML = '<option value="">ไม่สามารถโหลดข้อมูลได้</option>';
    } finally {
        select.disabled = false;
    }
}

function showAddHintModal() {
    const form = document.getElementById('hintForm');
    form.reset();
    document.getElementById('hint_id').value = '';
    document.getElementById('modalTitle').textContent = 'เพิ่มคำใบ้';
    loadExercises();
    modal.show('hintModal');
}

async function editHint(hintId) {
    try {
        const response = await fetch(`api/hints/read.php?id=${hintId}`);
        const data = await response.json();
        
        if (data.success) {
            const hint = data.hint;
            document.getElementById('modalTitle').textContent = 'แก้ไขคำใบ้';
            
            await loadExercises();
            const form = document.getElementById('hintForm');
            form.elements['hint_id'].value = hint.hint_id;
            form.elements['exercise_id'].value = hint.exercise_id;
            form.elements['content'].value = hint.content;
            form.elements['type'].value = hint.type;
            form.elements['cost'].value = hint.cost;
            form.elements['order_number'].value = hint.order_number;
            
            modal.show('hintModal');
        } else {
            toast.show(data.message || 'ไม่สามารถโหลดข้อมูลคำใบ้', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function deleteHint(hintId) {
    const confirmed = await modal.confirm('คุณแน่ใจหรือไม่ที่จะลบคำใบ้นี้?');
    if (confirmed) {
        try {
            const response = await fetch('api/hints/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ hint_id: hintId })
            });
            
            const data = await response.json();
            if (data.success) {
                toast.show('ลบคำใบ้สำเร็จ');
                loadHints(currentPage);
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadHints();
});
</script>
