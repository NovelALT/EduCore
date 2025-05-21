<?php
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $stmt = $conn->query("SELECT COUNT(*) FROM submissions");
    $total_submissions = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_submissions / $per_page);

    // Get submissions with user and exercise info
    $stmt = $conn->prepare("
        SELECT s.*, u.username, e.title as exercise_title
        FROM submissions s
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN exercises e ON s.exercise_id = e.exercise_id
        ORDER BY s.submitted_at DESC
        LIMIT :offset, :per_page
    ");
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<h1>ตรวจสอบการส่งงาน</h1>

<div class="submissions-table">
    <table>
        <thead>
            <tr>
                <th>ผู้ใช้</th>
                <th>แบบฝึกหัด</th>
                <th>วันที่ส่ง</th>
                <th>สถานะ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['username']); ?></td>
                <td><?php echo htmlspecialchars($sub['exercise_title']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($sub['submitted_at'])); ?></td>
                <td>
                    <span class="status-badge <?php echo $sub['status']; ?>">
                        <?php echo $sub['status']; ?>
                    </span>
                </td>
                <td>
                    <button onclick="viewSubmission(<?php echo $sub['submission_id']; ?>)">
                        <i class="fas fa-eye"></i> ดู
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=submissions&p=<?php echo $i; ?>" 
           class="<?php echo $i === $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>

<div id="submissionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>รายละเอียดการส่ง</h3>
            <span class="close" onclick="modal.hide('submissionModal')">&times;</span>
        </div>
        <div id="submissionDetails">
            <div class="submission-info"></div>
            <div class="code-comparison">
                <div class="code-section">
                    <h4>โค้ดที่ส่ง</h4>
                    <pre class="submitted-code"></pre>
                </div>
                <div class="code-section">
                    <h4>โค้ดต้นแบบ</h4>
                    <pre class="solution-code"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function viewSubmission(id) {
    try {
        const response = await fetch(`api/submissions/read.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const sub = data.submission;
            document.querySelector('.submission-info').innerHTML = `
                <p><strong>ผู้ส่ง:</strong> ${escapeHtml(sub.username)}</p>
                <p><strong>แบบฝึกหัด:</strong> ${escapeHtml(sub.exercise_title)}</p>
                <p><strong>วันที่ส่ง:</strong> ${formatDate(sub.submitted_at)}</p>
                <p><strong>สถานะ:</strong> <span class="status-badge ${sub.status}">${sub.status}</span></p>
            `;
            
            document.querySelector('.submitted-code').textContent = sub.submitted_code;
            document.querySelector('.solution-code').textContent = sub.solution_code;
            
            modal.show('submissionModal');
        } else {
            toast.show(data.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
        }
    } catch (error) {
        toast.show('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<style>
.code-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.code-section {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 4px;
}

.code-section pre {
    white-space: pre-wrap;
    overflow-x: auto;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.passed { background: #d4edda; color: #155724; }
.status-badge.failed { background: #f8d7da; color: #721c24; }
</style>
