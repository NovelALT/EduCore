<?php
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

try {
    $base_query = "FROM lessons l 
                   JOIN courses c ON l.course_id = c.course_id";
    
    $where_clause = $course_id ? " WHERE l.course_id = :course_id" : "";
    
    $count_stmt = $conn->prepare("SELECT COUNT(*) " . $base_query . $where_clause);
    if ($course_id) {
        $count_stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
    }
    $count_stmt->execute();
    $total_lessons = $count_stmt->fetchColumn();
    $total_pages = ceil($total_lessons / $per_page);

    $query = "SELECT l.*, c.title as course_title " . 
             $base_query . $where_clause . 
             " ORDER BY c.title, l.order_number 
               LIMIT :offset, :per_page";
    
    $stmt = $conn->prepare($query);
    if ($course_id) {
        $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $courses_stmt = $conn->query("SELECT course_id, title FROM courses ORDER BY title");
    $courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="admin-content">
    <h1>จัดการบทเรียน</h1>

    <div class="admin-actions">
        <select id="courseFilter" class="form-control" style="max-width: 300px; margin-right: 1rem;" 
                onchange="window.location.href='?page=lessons&course_id=' + this.value">
            <option value="">ทุกคอร์ส</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['course_id']; ?>" 
                        <?php echo $course_id == $course['course_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-primary" onclick="showAddLessonModal()">
            <i class="fas fa-plus"></i> เพิ่มบทเรียน
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>คอร์ส</th>
                    <th>ชื่อบทเรียน</th>
                    <th>ลำดับ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $lesson): ?>
                <tr>
                    <td><?php echo $lesson['lesson_id']; ?></td>
                    <td><?php echo htmlspecialchars($lesson['course_title']); ?></td>
                    <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                    <td><?php echo $lesson['order_number']; ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editLesson(<?php echo $lesson['lesson_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="editLessonContent(<?php echo $lesson['lesson_id']; ?>)">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteLesson(<?php echo $lesson['lesson_id']; ?>)">
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
            <a href="?page=lessons<?php echo $course_id ? '&course_id='.$course_id : ''; ?>&p=<?php echo $i; ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal" id="lessonModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">เพิ่มบทเรียน</h3>
            <span class="close" onclick="modal.hide('lessonModal')">&times;</span>
        </div>
        <form id="lessonForm" onsubmit="return handleSubmit(event)">
            <input type="hidden" name="lesson_id" id="lesson_id">
            
            <div class="form-group">
                <label for="course_id">คอร์ส:</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>">
                            <?php echo htmlspecialchars($course['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">ชื่อบทเรียน:</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="content">เนื้อหา:</label>
                <textarea name="content" id="content" class="form-control" rows="10"></textarea>
            </div>

            <div class="form-group">
                <label for="order_number">ลำดับ:</label>
                <input type="number" name="order_number" id="order_number" class="form-control" min="1" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="modal.hide('lessonModal')">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddLessonModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มบทเรียน';
    document.getElementById('lessonForm').reset();
    document.getElementById('lesson_id').value = '';
    modal.show('lessonModal');
}

async function editLesson(lessonId) {
    try {
        const response = await fetch(`api/lessons/read.php?id=${lessonId}`);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            const lesson = data.lesson;
            document.getElementById('modalTitle').textContent = 'แก้ไขบทเรียน';
            
            const form = document.getElementById('lessonForm');
            form.elements['lesson_id'].value = lesson.lesson_id;
            form.elements['course_id'].value = lesson.course_id;
            form.elements['title'].value = lesson.title;
            form.elements['content'].value = lesson.content || '';
            form.elements['order_number'].value = lesson.order_number;
            
            modal.show('lessonModal');
        } else {
            toast.show('ไม่สามารถโหลดข้อมูลบทเรียน', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function editLessonContent(lessonId) {
    try {
        const response = await fetch(`api/lessons/read.php?id=${lessonId}`);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            document.getElementById('modalTitle').textContent = 'แก้ไขเนื้อหาบทเรียน';
            
            const lesson = data.lesson;
            document.getElementById('lesson_id').value = lesson.lesson_id;
            document.getElementById('content').value = lesson.content || '';
            
            const editorModal = document.createElement('div');
            editorModal.className = 'modal';
            editorModal.id = 'contentEditorModal';
            editorModal.innerHTML = `
                <div class="modal-content modal-large">
                    <div class="modal-header">
                        <h3 class="modal-title">แก้ไขเนื้อหาบทเรียน: ${lesson.title}</h3>
                        <span class="close" onclick="modal.hide('contentEditorModal')">&times;</span>
                    </div>
                    <form id="contentForm" onsubmit="return handleContentSubmit(event)">
                        <input type="hidden" name="lesson_id" value="${lesson.lesson_id}">
                        <div class="form-group">
                            <label for="lessonContent">เนื้อหา:</label>
                            <textarea id="lessonContent" name="content" class="form-control code-editor" 
                                    rows="20" style="font-family: monospace;">${lesson.content || ''}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            <button type="button" class="btn btn-secondary" 
                                    onclick="modal.hide('contentEditorModal')">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            `;
            
            const existingModal = document.getElementById('contentEditorModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            document.body.appendChild(editorModal);
            modal.show('contentEditorModal');
        } else {
            toast.show('ไม่สามารถโหลดข้อมูลบทเรียน', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function handleContentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('api/lessons/update-content.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const data = await response.json();
        if (data.success) {
            toast.show('อัปเดตเนื้อหาสำเร็จ');
            modal.hide('contentEditorModal');
            location.reload();
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

function loadLessons() {
    const courseId = new URLSearchParams(window.location.search).get('course_id');
    const page = new URLSearchParams(window.location.search).get('p') || 1;
    
    fetch(`api/lessons/read.php?course_id=${courseId || ''}&p=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('table tbody');
                tbody.innerHTML = data.lessons.map(lesson => `
                    <tr>
                        <td>${lesson.lesson_id}</td>
                        <td>${lesson.course_title}</td>
                        <td>${lesson.title}</td>
                        <td>${lesson.order_number}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" onclick="editLesson(${lesson.lesson_id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="editLessonContent(${lesson.lesson_id})">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                                <button class="btn-icon btn-danger" onclick="deleteLesson(${lesson.lesson_id})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => toast.show('ไม่สามารถโหลดข้อมูลได้', 'error'));
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = formData.get('lesson_id');
    
    try {
        const response = await fetch(`api/lessons/${isEdit ? 'update' : 'create'}.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show(isEdit ? 'อัปเดตบทเรียนสำเร็จ' : 'เพิ่มบทเรียนสำเร็จ');
            modal.hide('lessonModal');
            loadLessons();
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function deleteLesson(lessonId) {
    try {
        const confirmed = await modal.confirm('คุณแน่ใจหรือไม่ที่จะลบบทเรียนนี้?', {
            title: 'ยืนยันการลบ',
            confirmText: 'ลบ',
            cancelText: 'ยกเลิก'
        });

        if (confirmed) {
            const response = await fetch('api/lessons/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ lesson_id: lessonId })
            });
            
            const data = await response.json();
            if (data.success) {
                toast.show('ลบบทเรียนสำเร็จ');
                location.reload();
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadLessons();
    const form = document.getElementById('lessonForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
});
</script>

<style>
.code-editor {
    font-family: 'Consolas', monospace;
    font-size: 14px;
    line-height: 1.5;
    tab-size: 4;
    white-space: pre-wrap;
}

.modal-large {
    max-width: 90%;
    width: 1200px;
}

#content {
    min-height: 400px;
    font-family: monospace;
}
</style>
