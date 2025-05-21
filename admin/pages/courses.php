<?php
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM courses");
    $total_courses = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_courses / $per_page);

    $stmt = $conn->prepare("SELECT * FROM courses ORDER BY created_at DESC LIMIT :offset, :per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="admin-content">
    <h1>จัดการคอร์ส</h1>
    
    <div class="admin-actions">
        <button class="btn btn-primary" onclick="showAddCourseModal()">
            <i class="fas fa-plus"></i> เพิ่มคอร์สใหม่
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>รูปภาพ</th>
                    <th>ชื่อคอร์ส</th>
                    <th>ระดับ</th>
                    <th>หมวดหมู่</th>
                    <th>วันที่สร้าง</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo $course['course_id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($course['image_url'] ?: '../assets/images/default-course.jpg'); ?>" 
                             alt="Course thumbnail" 
                             class="course-thumbnail">
                    </td>
                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                    <td>
                        <span class="badge badge-level <?php echo $course['level']; ?>">
                            <?php 
                            $levels = [
                                'beginner' => 'เริ่มต้น',
                                'intermediate' => 'ปานกลาง',
                                'advanced' => 'ขั้นสูง'
                            ];
                            echo $levels[$course['level']] ?? $course['level']; 
                            ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($course['category']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($course['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editCourse(<?php echo $course['course_id']; ?>)" title="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="manageLessons(<?php echo $course['course_id']; ?>)" title="จัดการบทเรียน">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteCourse(<?php echo $course['course_id']; ?>)" title="ลบ">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=courses&p=<?php echo $i; ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Course Modal -->
<div id="courseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">เพิ่มคอร์สใหม่</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="courseForm" onsubmit="handleSubmit(event)">
            <input type="hidden" id="course_id" name="course_id">
            
            <div class="form-group">
                <label for="title">ชื่อคอร์ส:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">คำอธิบาย:</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="level">ระดับ:</label>
                <select id="level" name="level" class="form-control" required>
                    <option value="beginner">เริ่มต้น</option>
                    <option value="intermediate">ปานกลาง</option>
                    <option value="advanced">ขั้นสูง</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">หมวดหมู่:</label>
                <input type="text" id="category" name="category" class="form-control">
            </div>

            <div class="form-group">
                <label for="image">รูปภาพ:</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <div id="imagePreview" class="mt-2"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
async function loadCourses(page = 1) {
    try {
        const response = await fetch(`api/courses/list.php?p=${page}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data.success) {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = data.courses.map(course => `
                <tr>
                    <td>${course.course_id}</td>
                    <td>
                        <img src="${course.image_url || '../assets/images/default-course.jpg'}" 
                             alt="Course thumbnail" 
                             class="course-thumbnail">
                    </td>
                    <td>${escapeHtml(course.title)}</td>
                    <td>
                        <span class="badge badge-level ${course.level}">
                            ${getLevelText(course.level)}
                        </span>
                    </td>
                    <td>${escapeHtml(course.category || '-')}</td>
                    <td>${formatDate(course.created_at)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editCourse(${course.course_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="manageLessons(${course.course_id})">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteCourse(${course.course_id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            const paginationContainer = document.querySelector('.pagination');
            if (paginationContainer && data.total_pages > 1) {
                paginationContainer.innerHTML = generatePagination(data.current_page, data.total_pages);
            }
        } else {
            throw new Error(data.message || 'ไม่สามารถโหลดข้อมูลได้');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message, 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function generatePagination(currentPage, totalPages) {
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <a href="javascript:void(0)" 
               onclick="loadCourses(${i})" 
               class="${i === currentPage ? 'active' : ''}">
                ${i}
            </a>`;
    }
    return html;
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = formData.get('course_id');
    
    try {
        const response = await fetch(`api/courses/${isEdit ? 'update' : 'create'}.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show(isEdit ? 'อัปเดตคอร์สสำเร็จ' : 'เพิ่มคอร์สสำเร็จ');
            closeModal();
            loadCourses();
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function editCourse(courseId) {
    try {
        const response = await fetch(`api/courses/read.php?id=${courseId}`);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            const course = data.course;
            document.getElementById('modalTitle').textContent = 'แก้ไขคอร์ส';
            
            const form = document.getElementById('courseForm');
            form.elements['course_id'].value = course.course_id;
            form.elements['title'].value = course.title;
            form.elements['description'].value = course.description || '';
            form.elements['level'].value = course.level;
            form.elements['category'].value = course.category || '';
            
            if (course.image_url) {
                document.getElementById('imagePreview').innerHTML = `
                    <img src="${course.image_url}" alt="Preview" style="max-width: 200px;">
                    <p class="text-muted">อัปโหลดรูปใหม่เพื่อเปลี่ยนรูปภาพ</p>
                `;
            }
            
            modal.show('courseModal');
        } else {
            toast.show(data.message || 'ไม่สามารถโหลดข้อมูลคอร์ส', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function deleteCourse(courseId) {
    const confirmed = await modal.confirm('คุณแน่ใจหรือไม่ที่จะลบคอร์สนี้?');
    if (confirmed) {
        try {
            const response = await fetch('api/courses/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ course_id: courseId })
            });
            
            const data = await response.json();
            if (data.success) {
                toast.show('ลบคอร์สสำเร็จ');
                loadCourses();
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    }
}

function closeModal() {
    document.getElementById('courseForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
    modal.hide('courseModal');
}

function manageLessons(courseId) {
    window.location.href = `?page=lessons&course_id=${courseId}`;
}

function showAddCourseModal() {
    const form = document.getElementById('courseForm');
    form.reset();
    document.getElementById('course_id').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('modalTitle').textContent = 'เพิ่มคอร์สใหม่';
    
    modal.show('courseModal');
}

function getLevelText(level) {
    const levels = {
        beginner: 'เริ่มต้น',
        intermediate: 'ปานกลาง',
        advanced: 'ขั้นสูง'
    };
    return levels[level] || level;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadCourses();
    
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="max-width: 200px;">
                `;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<style>
.course-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.badge-level {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.badge-level.beginner {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.badge-level.intermediate {
    background-color: #fff3e0;
    color: #e65100;
}

.badge-level.advanced {
    background-color: #fce4ec;
    color: #c2185b;
}
</style>
