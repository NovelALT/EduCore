<?php
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_users / $per_page);

    $stmt = $conn->prepare("SELECT user_id, username, email, firstname, lastname, role, created_at 
                           FROM users 
                           ORDER BY created_at DESC 
                           LIMIT :offset, :per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="admin-content">
    <h1>จัดการผู้ใช้</h1>
    
    <div class="admin-actions">
        <button class="btn btn-primary" onclick="showAddUserModal()">
            <i class="fas fa-plus"></i> เพิ่มผู้ใช้
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
                    <th>ชื่อผู้ใช้</th>
                    <th>อีเมล</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>บทบาท</th>
                    <th>วันที่สร้าง</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
            </tbody>
        </table>
    </div>

    <div id="pagination" class="pagination">
    </div>
</div>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">เพิ่มผู้ใช้ใหม่</h3>
            <span class="close" onclick="modalUtils.close('addUserModal')">&times;</span>
        </div>
        <form id="addUserForm" onsubmit="return handleSubmit(event)">
            <input type="hidden" id="user_id" name="user_id">
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="firstname">ชื่อ:</label>
                <input type="text" id="firstname" name="firstname" class="form-control">
            </div>
            <div class="form-group">
                <label for="lastname">นามสกุล:</label>
                <input type="text" id="lastname" name="lastname" class="form-control">
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" class="form-control">
                <small class="text-muted" id="passwordHelp">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</small>
            </div>
            <div class="form-group">
                <label for="role">บทบาท:</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="student">นักเรียน</option>
                    <option value="teacher">ครู</option>
                    <option value="admin">ผู้ดูแลระบบ</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" onclick="modalUtils.close('addUserModal')">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;

async function loadUsers(page = 1) {
    currentPage = page;
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    try {
        loadingIndicator.style.display = 'block';
        const response = await fetch(`api/users/list.php?p=${page}`);
        const data = await response.json();

        if (data.success) {
            renderUsers(data);
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

function renderUsers(data) {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = data.users.map(user => `
        <tr>
            <td>${user.user_id}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.firstname)} ${escapeHtml(user.lastname)}</td>
            <td><span class="badge badge-role ${user.role}">${user.role}</span></td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" onclick="editUser(${user.user_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="deleteUser(${user.user_id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(currentPage, totalPages) {
    const pagination = document.getElementById('pagination');
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<a href="javascript:void(0)" 
                   onclick="loadUsers(${i})" 
                   class="${i === currentPage ? 'active' : ''}">${i}</a>`;
    }
    pagination.innerHTML = html;
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = formData.get('user_id');

    try {
        const response = await fetch(`api/users/${isEdit ? 'update' : 'create'}.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            toast.show(isEdit ? 'อัปเดตข้อมูลสำเร็จ' : 'เพิ่มผู้ใช้สำเร็จ');
            modalUtils.close('addUserModal');
            loadUsers(currentPage);
        } else {
            toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

async function deleteUser(userId) {
    const confirmed = await modal.confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?');
    if (confirmed) {
        try {
            const formData = new FormData();
            formData.append('id', userId);
            
            const response = await fetch('api/users/delete.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                toast.show('ลบผู้ใช้สำเร็จ');
                loadUsers(currentPage);
            } else {
                toast.show(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    }
}

async function editUser(userId) {
    try {
        const response = await fetch(`api/users/read.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            const user = data.user;
            document.getElementById('modalTitle').textContent = 'แก้ไขข้อมูลผู้ใช้';
            
            const form = document.getElementById('addUserForm');
            form.elements['user_id'].value = user.user_id;
            form.elements['username'].value = user.username;
            form.elements['email'].value = user.email;
            form.elements['firstname'].value = user.firstname || '';
            form.elements['lastname'].value = user.lastname || '';
            form.elements['role'].value = user.role;
            
            // Don't require password for edit
            form.elements['password'].required = false;
            document.getElementById('passwordHelp').style.display = 'block';
            
            modal.show('addUserModal');
        } else {
            toast.show(data.message || 'ไม่สามารถโหลดข้อมูลผู้ใช้', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

function showAddUserModal() {
    const form = document.getElementById('addUserForm');
    form.reset();
    form.elements['user_id'].value = '';
    form.elements['password'].required = true;
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้ใหม่';
    modal.show('addUserModal');
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
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
    loadUsers();
});
</script>

<style>
#loadingIndicator {
    text-align: center;
    padding: 20px;
    color: #666;
}

.text-center {
    text-align: center;
}
</style>
