<?php
require_once "config.php";
check_login();
check_role('team member');

$team_member_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $task_id, $team_member_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "<div class='alert alert-success'>Status tugas berhasil diperbarui.</div>";
        } else {
            $message = "<div class='alert'>Anda tidak memiliki izin untuk mengubah tugas ini.</div>";
        }
    } else {
        $message = "<div class='alert'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$page = $_GET['page'] ?? 'dashboard';

include 'header.php';
?>

<?php if ($page == 'dashboard' || $page == 'tasks'): ?>
    <div class="card">
        <h2>Dashboard Team Member</h2>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Berikut adalah daftar tugas yang ditugaskan kepada Anda.</p>
    </div>
    
    <?php
    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM tasks WHERE assigned_to = ?");
    $stmt->bind_param("i", $team_member_id); $stmt->execute();
    $total_tasks = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM tasks WHERE assigned_to = ? AND status != 'selesai'");
    $stmt->bind_param("i", $team_member_id); $stmt->execute();
    $tasks_pending = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM tasks WHERE assigned_to = ? AND status = 'selesai'");
    $stmt->bind_param("i", $team_member_id); $stmt->execute();
    $tasks_selesai = $stmt->get_result()->fetch_assoc()['total'];
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tugas Saya</h3>
            <p><?php echo $total_tasks; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tugas Belum Selesai</h3>
            <p><?php echo $tasks_pending; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tugas Selesai</h3>
            <p><?php echo $tasks_selesai; ?></p>
        </div>
    </div>

    <div class="card">
        <h3>Daftar Tugas Saya</h3>
        <?php echo $message; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Nama Tugas</th>
                    <th>Proyek</th>
                    <th>Deskripsi</th>
                    <th>Status Saat Ini</th>
                    <th>Ubah Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT t.*, p.nama_proyek 
                        FROM tasks t
                        JOIN project p ON t.project_id = p.id
                        WHERE t.assigned_to = ?
                        ORDER BY p.id, t.id";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $team_member_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $status_options = ['belum', 'proses', 'selesai'];
                
                if ($result->num_rows > 0):
                    while ($task = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['nama_tugas']); ?></td>
                    <td><?php echo htmlspecialchars($task['nama_proyek']); ?></td>
                    <td><?php echo htmlspecialchars(substr($task['deskripsi'], 0, 50) . '...'); ?></td>
                    <td><b><?php echo ucfirst(htmlspecialchars($task['status'])); ?></b></td>
                    <td>
                        <form action="member.php?page=tasks" method="post">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <div style="display: flex; gap: 5px;">
                                <select name="status" class="form-control" style="width: 150px;">
                                    <?php foreach ($status_options as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $task['status'] == $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm">Update</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Anda belum memiliki tugas.</td>
                </tr>
                <?php endif; $stmt->close(); ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>