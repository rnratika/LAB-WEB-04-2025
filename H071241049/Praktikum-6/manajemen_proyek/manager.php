<?php
require_once "config.php";
check_login();
check_role('project manager');

$pm_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_project'])) {
        $project_id = $_POST['project_id'];
        $nama_proyek = trim($_POST['nama_proyek']);
        $deskripsi = trim($_POST['deskripsi']);
        $tgl_mulai = $_POST['tanggal_mulai'];
        $tgl_selesai = $_POST['tanggal_selesai'];
        
        if (empty($project_id)) { 
            $sql = "INSERT INTO project (nama_proyek, deskripsi, tanggal_mulai, tanggal_selesai, manager_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nama_proyek, $deskripsi, $tgl_mulai, $tgl_selesai, $pm_id);
        } else {
            $sql = "UPDATE project SET nama_proyek = ?, deskripsi = ?, tanggal_mulai = ?, tanggal_selesai = ? WHERE id = ? AND manager_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $nama_proyek, $deskripsi, $tgl_mulai, $tgl_selesai, $project_id, $pm_id);
        }
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Proyek berhasil disimpan.</div>";
        } else {
            $message = "<div class='alert'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }

    if (isset($_POST['delete_project_id'])) {
        $project_id = $_POST['delete_project_id'];
        $sql = "DELETE FROM project WHERE id = ? AND manager_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $project_id, $pm_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manager.php?page=project");
        exit;
    }

    if (isset($_POST['save_task'])) {
        $task_id = $_POST['task_id'];
        $project_id = $_POST['project_id'];
        $nama_tugas = trim($_POST['nama_tugas']);
        $deskripsi_tugas = trim($_POST['deskripsi_tugas']);
        $assigned_to = $_POST['assigned_to'];
        $status = $_POST['status'];
        
        $check_sql = "SELECT id FROM project WHERE id = ? AND manager_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $project_id, $pm_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows == 1) {
            if (empty($task_id)) { 
                $sql = "INSERT INTO tasks (project_id, nama_tugas, deskripsi, status, assigned_to) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssi", $project_id, $nama_tugas, $deskripsi_tugas, $status, $assigned_to);
            } else {
                $sql = "UPDATE tasks SET nama_tugas = ?, deskripsi = ?, status = ?, assigned_to = ? WHERE id = ? AND project_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiii", $nama_tugas, $deskripsi_tugas, $status, $assigned_to, $task_id, $project_id);
            }
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Tugas berhasil disimpan.</div>";
            } else {
                $message = "<div class='alert'>Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert'>Error: Anda tidak punya akses ke proyek ini.</div>";
        }
        $check_stmt->close();
    }

    if (isset($_POST['delete_task_id'])) {
        $task_id = $_POST['delete_task_id'];
        $project_id = $_POST['project_id_for_delete'];
        
        $sql = "DELETE t FROM tasks t 
                JOIN project p ON t.project_id = p.id 
                WHERE t.id = ? AND p.id = ? AND p.manager_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $task_id, $project_id, $pm_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manager.php?page=tasks&project_id=" . $project_id);
        exit;
    }
}

$page = $_GET['page'] ?? 'dashboard';
include 'header.php';
?>

<?php if ($page == 'dashboard'): ?>
    <div class="card">
        <h2>Dashboard Project Manager</h2>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Di sini Anda dapat mengelola proyek dan tugas Anda.</p>
    </div>
    
    <?php
    $stmt = $conn->prepare("SELECT COUNT(id) AS total FROM project WHERE manager_id = ?");
    $stmt->bind_param("i", $pm_id); $stmt->execute();
    $total_project = $stmt->get_result()->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(t.id) AS total FROM tasks t JOIN project p ON t.project_id = p.id WHERE p.manager_id = ?");
    $stmt->bind_param("i", $pm_id); $stmt->execute();
    $total_tasks = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT COUNT(t.id) AS total FROM tasks t JOIN project p ON t.project_id = p.id WHERE p.manager_id = ? AND t.status = 'selesai'");
    $stmt->bind_param("i", $pm_id); $stmt->execute();
    $tasks_selesai = $stmt->get_result()->fetch_assoc()['total'];
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Proyek Saya</h3>
            <p><?php echo $total_project; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Tugas</h3>
            <p><?php echo $total_tasks; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tugas Selesai</h3>
            <p><?php echo $tasks_selesai; ?></p>
        </div>
    </div>

<?php elseif ($page == 'projects'): ?>
    <div class="card">
        <h2>Proyek Saya</h2>
        <?php echo $message; ?>
        <a href="manager.php?page=manage_project" class="btn">Buat Proyek Baru</a>
        
        <table>
            <thead>
                <tr>
                    <th>Nama Proyek</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM project WHERE manager_id = ? ORDER BY tanggal_mulai DESC");
                $stmt->bind_param("i", $pm_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0):
                    while ($project = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['nama_proyek']); ?></td>
                    <td><?php echo format_tanggal($project['tanggal_mulai']); ?></td>
                    <td><?php echo format_tanggal($project['tanggal_selesai']); ?></td>
                    <td>
                        <a href="manager.php?page=tasks&project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-secondary">Lihat Tugas</a>
                        <a href="manager.php?page=manage_project&id=<?php echo $project['id']; ?>" class="btn btn-sm">Edit</a>
                        <form action="manager.php?page=project" method="post" style="display:inline;">
                            <input type="hidden" name="delete_project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus proyek ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">Anda belum memiliki proyek.</td>
                </tr>
                <?php endif; $stmt->close(); ?>
            </tbody>
        </table>
    </div>

<?php elseif ($page == 'manage_project'): ?>
    <?php
    $edit_project = null;
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM project WHERE id = ? AND manager_id = ?");
        $stmt->bind_param("ii", $project_id, $pm_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_project = $result->fetch_assoc();
        if (!$edit_project) {
            echo "<div class='alert'>Proyek tidak ditemukan atau Anda tidak punya akses.</div>";
            include 'footer.php';
            exit;
        }
        $stmt->close();
    }
    ?>
    <div class="card">
        <h2><?php echo $edit_project ? 'Edit' : 'Buat'; ?> Proyek</h2>
        <form action="manager.php?page=project" method="post">
            <input type="hidden" name="project_id" value="<?php echo $edit_project['id'] ?? ''; ?>">
            <div class="form-group">
                <label>Nama Proyek</label>
                <input type="text" name="nama_proyek" class="form-control" value="<?php echo htmlspecialchars($edit_project['nama_proyek'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"><?php echo htmlspecialchars($edit_project['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo $edit_project['tanggal_mulai'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="<?php echo $edit_project['tanggal_selesai'] ?? ''; ?>" required>
            </div>
            <button type="submit" name="save_project" class="btn">Simpan Proyek</button>
            <a href="manager.php?page=project" class="btn btn-secondary">Batal</a>
        </form>
    </div>

<?php elseif ($page == 'tasks' && isset($_GET['project_id'])): ?>
    <?php
    $project_id = $_GET['project_id'];
    $stmt = $conn->prepare("SELECT nama_proyek FROM project WHERE id = ? AND manager_id = ?");
    $stmt->bind_param("ii", $project_id, $pm_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    if (!$project) {
        echo "<div class='alert'>Proyek tidak ditemukan atau Anda tidak punya akses.</div>";
        include 'footer.php';
        exit;
    }
    $nama_proyek = $project['nama_proyek'];
    $stmt->close();
    ?>
    <div class="card">
        <h2>Manajemen Tugas untuk Proyek: <?php echo htmlspecialchars($nama_proyek); ?></h2>
        <?php echo $message; ?>
        <a href="manager.php?page=manage_task&project_id=<?php echo $project_id; ?>" class="btn">Tambah Tugas Baru</a>
        <a href="manager.php?page=project" class="btn btn-secondary">Kembali ke Proyek</a>
        
        <table>
            <thead>
                <tr>
                    <th>Nama Tugas</th>
                    <th>Ditugaskan Kepada</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT t.*, u.username AS assigned_username 
                                        FROM tasks t 
                                        LEFT JOIN users u ON t.assigned_to = u.id 
                                        WHERE t.project_id = ?");
                $stmt->bind_param("i", $project_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0):
                    while ($task = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['nama_tugas']); ?></td>
                    <td><?php echo htmlspecialchars($task['assigned_username'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($task['status']); ?></td>
                    <td>
                        <a href="manager.php?page=manage_task&project_id=<?php echo $project['id']; ?>&id=<?php echo $task['id']; ?>" class="btn btn-sm">Edit</a>
                        <form action="manager.php?page=tasks&project_id=<?php echo $project_id; ?>" method="post" style="display:inline;">
                            <input type="hidden" name="delete_task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="project_id_for_delete" value="<?php echo $project_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus tugas ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">Belum ada tugas untuk proyek ini.</td>
                </tr>
                <?php endif; $stmt->close(); ?>
            </tbody>
        </table>
    </div>

<?php elseif ($page == 'manage_task' && isset($_GET['project_id'])): ?>
    <?php
    $project_id = $_GET['project_id'];
    $stmt = $conn->prepare("SELECT id FROM project WHERE id = ? AND manager_id = ?");
    $stmt->bind_param("ii", $project_id, $pm_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo "<div class='alert'>Proyek tidak ditemukan atau Anda tidak punya akses.</div>";
        include 'footer.php';
        exit;
    }
    $stmt->close();
    
    $team_stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'team member'");
    $team_stmt->execute();
    $team_members = $team_stmt->get_result();

    $edit_task = null;
    if (isset($_GET['id'])) {
        $task_id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND project_id = ?");
        $stmt->bind_param("ii", $task_id, $project_id);
        $stmt->execute();
        $edit_task = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    
    $status_options = ['belum', 'proses', 'selesai'];
    ?>
    <div class="card">
        <h2><?php echo $edit_task ? 'Edit' : 'Tambah'; ?> Tugas</h2>
        <form action="manager.php?page=tasks&project_id=<?php echo $project_id; ?>" method="post">
            <input type="hidden" name="task_id" value="<?php echo $edit_task['id'] ?? ''; ?>">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            
            <div class="form-group">
                <label>Nama Tugas</label>
                <input type="text" name="nama_tugas" class="form-control" value="<?php echo htmlspecialchars($edit_task['nama_tugas'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi_tugas" class="form-control"><?php echo htmlspecialchars($edit_task['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Tugaskan Kepada</label>
                <select name="assigned_to" class="form-control" required>
                    <option value="">-- Pilih Team Member --</option>
                    <?php while($member = $team_members->fetch_assoc()): ?>
                    <option value="<?php echo $member['id']; ?>" <?php echo ($edit_task['assigned_to'] ?? '') == $member['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($member['username']); ?>
                    </option>
                    <?php endwhile; $team_stmt->close(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <?php foreach ($status_options as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo ($edit_task['status'] ?? 'belum') == $status ? 'selected' : ''; ?>>
                        <?php echo ucfirst($status); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="save_task" class="btn">Simpan Tugas</button>
            <a href="manager.php?page=tasks&project_id=<?php echo $project_id; ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>

<?php endif; ?>

<?php include 'footer.php'; ?>