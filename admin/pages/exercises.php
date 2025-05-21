<?php
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM exercises");
    $total_exercises = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_exercises / $per_page);

    $stmt = $conn->prepare("
        SELECT e.*, l.title as lesson_title 
        FROM exercises e
        LEFT JOIN lessons l ON e.lesson_id = l.lesson_id
        ORDER BY l.course_id, l.order_number, e.exercise_id
        LIMIT :offset, :per_page
    ");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("
        SELECT l.lesson_id, l.title, c.title as course_title
        FROM lessons l
        JOIN courses c ON l.course_id = c.course_id
        ORDER BY c.title, l.order_number
    ");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="admin-content">
    <h1>จัดการแบบฝึกหัด</h1>

    <div class="admin-actions">
        <button class="btn btn-primary" onclick="showAddExerciseModal()">
            <i class="fas fa-plus"></i> เพิ่มแบบฝึกหัด
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>บทเรียน</th>
                    <th>ชื่อแบบฝึกหัด</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?php echo $exercise['exercise_id']; ?></td>
                    <td><?php echo htmlspecialchars($exercise['lesson_title']); ?></td>
                    <td><?php echo htmlspecialchars($exercise['title']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editExercise(<?php echo $exercise['exercise_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="editExerciseContent(<?php echo $exercise['exercise_id']; ?>)">
                                <i class="fas fa-code"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteExercise(<?php echo $exercise['exercise_id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=exercises&p=<?php echo $i; ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div id="exerciseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">เพิ่มแบบฝึกหัด</h2>
        <form id="exerciseForm">
            <input type="hidden" id="exercise_id" name="exercise_id">
            
            <div class="form-group">
                <label for="lesson_id">บทเรียน:</label>
                <select id="lesson_id" name="lesson_id" class="form-control" required>
                    <?php foreach ($lessons as $lesson): ?>
                        <option value="<?php echo $lesson['lesson_id']; ?>">
                            <?php echo htmlspecialchars($lesson['course_title'] . ' - ' . $lesson['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">ชื่อแบบฝึกหัด:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">คำอธิบาย:</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<div id="contentEditorModal" class="modal">
    <div class="modal-content modal-large">
        <span class="close">&times;</span>
        <h2>แก้ไขเนื้อหาแบบฝึกหัด</h2>
        <form id="contentEditorForm">
            <input type="hidden" id="edit_exercise_id" name="exercise_id">
            
            <div class="form-group">
                <label for="initial_code">โค้ดเริ่มต้น:</label>
                <textarea id="initial_code" name="initial_code" class="form-control code-editor" rows="8"></textarea>
            </div>

            <div class="form-group">
                <label for="solution_code">เฉลย:</label>
                <textarea id="solution_code" name="solution_code" class="form-control code-editor" rows="8"></textarea>
            </div>

            <div class="form-group">
                <label for="test_cases">Test Cases:</label>
                <textarea id="test_cases" name="test_cases" class="form-control code-editor" rows="8"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="closeContentEditor()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-large {
    width: 90%;
    max-width: 1200px;
}

.code-editor {
    font-family: monospace;
    white-space: pre;
    tab-size: 4;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    padding: 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    transform: translateY(-2px);
}

.btn-icon.btn-danger {
    color: #dc3545;
}

.btn-icon.btn-danger:hover {
    background: #dc3545;
    color: white;
}
</style>

<script>
function showAddExerciseModal() {
    const form = document.getElementById('exerciseForm');
    form.reset();
    document.getElementById('exercise_id').value = '';
    document.getElementById('modalTitle').textContent = 'เพิ่มแบบฝึกหัด';
    
    modal.show('exerciseModal');
}

function loadExercises() {
    const page = new URLSearchParams(window.location.search).get('p') || 1;
    
    fetch(`api/exercises/read.php?p=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderExercises(data.exercises);
            }
        })
        .catch(error => toast.show('ไม่สามารถโหลดข้อมูลได้', 'error'));
}

function renderExercises(exercises) {
    const tbody = document.querySelector('table tbody');
    tbody.innerHTML = exercises.map(exercise => `
        <tr>
            <td>${exercise.exercise_id}</td>
            <td>${exercise.lesson_title}</td>
            <td>${exercise.title}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" onclick="editExercise(${exercise.exercise_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="editExerciseContent(${exercise.exercise_id})">
                        <i class="fas fa-code"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="deleteExercise(${exercise.exercise_id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = formData.get('exercise_id');
    
    try {
        const response = await fetch(`api/exercises/${isEdit ? 'update' : 'create'}.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show(isEdit ? 'อัปเดตแบบฝึกหัดสำเร็จ' : 'เพิ่มแบบฝึกหัดสำเร็จ');
            modal.hide('exerciseModal');
            loadExercises();
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function handleContentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('api/exercises/update-content.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show('อัปเดตเนื้อหาสำเร็จ');
            modal.hide('contentEditorModal');
            loadExercises();
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function deleteExercise(exerciseId) {
    const confirmed = await modal.confirm('คุณแน่ใจหรือไม่ที่จะลบแบบฝึกหัดนี้?');
    if (confirmed) {
        try {
            const response = await fetch('api/exercises/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ exercise_id: exerciseId })
            });
            
            const data = await response.json();
            if (data.success) {
                toast.show('ลบแบบฝึกหัดสำเร็จ');
                loadExercises();
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    }
}

async function editExercise(exerciseId) {
    try {
        const response = await fetch(`api/exercises/read.php?id=${exerciseId}`);
        const data = await response.json();
        
        if (data.success) {
            const exercise = data.exercise;
            document.getElementById('modalTitle').textContent = 'แก้ไขแบบฝึกหัด';
            
            const form = document.getElementById('exerciseForm');
            form.elements['exercise_id'].value = exercise.exercise_id;
            form.elements['lesson_id'].value = exercise.lesson_id;
            form.elements['title'].value = exercise.title;
            form.elements['description'].value = exercise.description || '';
            form.elements['initial_code'].value = exercise.initial_code || '';
            form.elements['solution_code'].value = exercise.solution_code || '';
            form.elements['test_cases'].value = exercise.test_cases || '';
            
            modal.show('exerciseModal');
        } else {
            toast.show('ไม่สามารถโหลดข้อมูลแบบฝึกหัด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function editExerciseContent(exerciseId) {
    try {
        const response = await fetch(`api/exercises/read.php?id=${exerciseId}`);
        const data = await response.json();
        
        if (data.success) {
            const exercise = data.exercise;
            
            document.getElementById('edit_exercise_id').value = exercise.exercise_id;
            document.getElementById('initial_code').value = exercise.initial_code || '';
            document.getElementById('solution_code').value = exercise.solution_code || '';
            document.getElementById('test_cases').value = exercise.test_cases || '';
            
            modal.show('contentEditorModal');
        } else {
            toast.show('ไม่สามารถโหลดข้อมูลแบบฝึกหัด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadExercises();
    
    const form = document.getElementById('exerciseForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
    
    const contentForm = document.getElementById('contentEditorForm');
    if (contentForm) {
        contentForm.addEventListener('submit', handleContentSubmit);
    }
});
</script>
