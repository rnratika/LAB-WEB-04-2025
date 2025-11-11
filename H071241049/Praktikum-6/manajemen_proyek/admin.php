<?php
require_once "config.php";
check_login();
check_role('superadmin');
$user_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['save_project'])) {
        $project_id = $_POST['project_id'];
        $nama_proyek = trim($_POST['nama_proyek']);
        $deskripsi = trim($_POST['deskripsi']);
        $tgl_mulai = $_POST['tanggal_mulai'];
        $tgl_selesai = $_POST['tanggal_selesai'];
        $manager_id = $_POST['manager_id'];

        if (empty($manager_id)) {
            $user_message = "<div class='alert'>Error: Anda wajib memilih Project Manager.</div>";
        } else {
            if (empty($project_id)) {
                $sql = "INSERT INTO project (nama_proyek, deskripsi, tanggal_mulai, tanggal_selesai, manager_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $nama_proyek, $deskripsi, $tgl_mulai, $tgl_selesai, $manager_id);
            } else {
                $sql = "UPDATE project SET nama_proyek = ?, deskripsi = ?, tanggal_mulai = ?, tanggal_selesai = ?, manager_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssii", $nama_proyek, $deskripsi, $tgl_mulai, $tgl_selesai, $manager_id, $project_id);
            }
            
            if ($stmt->execute()) {
                header("Location: admin.php?page=project"); 
                exit;
            } else {
                $user_message = "<div class='alert'>Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['save_user'])) {
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $role = $_POST['role'];
        $pm_id = ($role == 'team member' && !empty($_POST['project_manager_id'])) ? $_POST['project_manager_id'] : NULL;
        
        if ($role == 'team member' && empty($pm_id)) {
            $user_message = "<div class='alert'>Error: Saat menambah/mengedit Team Member, Anda wajib memilih Project Manager-nya.</div>";
        } else {
            if (empty($user_id)) {
                $password = trim($_POST['password']);
                $sql = "INSERT INTO users (username, password, role, project_manager_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $username, $password, $role, $pm_id);
                if ($stmt->execute()) {
                    $user_message = "<div class='alert alert-success'>User berhasil ditambahkan.</div>";
                } else {
                    $user_message = "<div class='alert'>Error: " . $stmt->error . "</div>";
                }
            } else { 
                if (!empty(trim($_POST['password']))) { 
                    $password = trim($_POST['password']);
                    $sql = "UPDATE users SET username = ?, password = ?, role = ?, project_manager_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssi", $username, $password, $role, $pm_id, $user_id);
                } else { 
                    $sql = "UPDATE users SET username = ?, role = ?, project_manager_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssi", $username, $role, $pm_id, $user_id);
                }
                if ($stmt->execute()) {
                    $user_message = "<div class='alert alert-success'>User berhasil diperbarui.</div>";
                } else {
                    $user_message = "<div class='alert'>Error: " . $stmt->error . "</div>";
                }
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['delete_project_id'])) {
        $project_id = $_POST['delete_project_id'];
        $sql = "DELETE FROM project WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?page=project");
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    if ($user_id == $_SESSION['user_id']) {
        $user_message = "<div class='alert'>Anda tidak dapat menghapus akun Anda sendiri.</div>";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $user_message = "<div class='alert alert-success'>User berhasil dihapus.</div>";
        } else {
            $user_message = "<div class='alert'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

$edit_user = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_user' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

$managers = $conn->query("SELECT id, username FROM users WHERE role = 'project manager'");

$page = $_GET['page'] ?? 'dashboard';
include 'header.php';
?>

<?php if ($page == 'dashboard'): ?>
    <div class="card">
        <h2>Dashboard Super Admin</h2>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Anda memiliki akses penuh ke sistem.</p>
    </div>
    
    <?php
    $total_users = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
    $total_project = $conn->query("SELECT COUNT(id) AS total FROM project")->fetch_assoc()['total'];
    $total_tasks = $conn->query("SELECT COUNT(id) AS total FROM tasks")->fetch_assoc()['total'];
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Pengguna</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Proyek</h3>
            <p><?php echo $total_project; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Tugas</h3>
            <p><?php echo $total_tasks; ?></p>
        </div>
    </div>

<?php elseif ($page == 'users'): ?>
    <div class="card">
        <h2>Manajemen Pengguna</h2>
        
        <?php echo $user_message; ?>

        <form action="admin.php?page=users" method="post" class="card" style="background: rgba(255,255,255,0.7);">
            <h3><?php echo $edit_user ? 'Edit' : 'Tambah'; ?> Pengguna</h3>
            <input type="hidden" name="user_id" value="<?php echo $edit_user['id'] ?? ''; ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="text" name="password" class="form-control" <?php echo $edit_user ? '' : 'required'; ?>>
                <?php if ($edit_user): ?><small>Kosongkan jika tidak ingin mengubah password.</small><?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" required onchange="togglePManager(this.value)">
                    <option value="project manager" <?php echo ($edit_user['role'] ?? '') == 'project manager' ? 'selected' : ''; ?>>Project Manager</option>
                    <option value="team member" <?php echo ($edit_user['role'] ?? '') == 'team member' ? 'selected' : ''; ?>>Team Member</option>
                </select>
            </div>
            
            <div class="form-group" id="pm-select" style="display: <?php echo ($edit_user['role'] ?? '') == 'team member' ? 'block' : 'none'; ?>;">
                <label for="project_manager_id">Project Manager</label>
                <select name="project_manager_id" id="project_manager_select" class="form-control">
                    <option value="">-- Pilih Project Manager --</option>
                    <?php 
                    $managers->data_seek(0); 
                    while($pm = $managers->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $pm['id']; ?>" <?php echo ($edit_user['project_manager_id'] ?? '') == $pm['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pm['username']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" name="save_user" class="btn">Simpan</button>
            <?php if ($edit_user): ?>
                <a href="admin.php?page=users" class="btn btn-secondary">Batal Edit</a>
            <?php endif; ?>
        </form>

        <h3>Daftar Pengguna</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Manager (jika Team)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT u.*, m.username AS manager_name FROM users u LEFT JOIN users m ON u.project_manager_id = m.id WHERE u.role != 'superadmin'";
                $result = $conn->query($sql);
                while ($user = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo htmlspecialchars($user['manager_name'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="admin.php?page=users&action=edit_user&id=<?php echo $user['id']; ?>" class="btn btn-sm">Edit</a>
                        <a href="admin.php?page=users&action=delete_user&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function togglePManager(role) {
        var pmSelectDiv = document.getElementById('pm-select');
        var pmSelectInput = document.getElementById('project_manager_select');
        
        if (role === 'team member') {
            pmSelectDiv.style.display = 'block';
            pmSelectInput.required = true; 
        } else {
            pmSelectDiv.style.display = 'none';
            pmSelectInput.required = false; 
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        var roleSelect = document.getElementById('role');
        if (roleSelect) { 
            var role = roleSelect.value;
            togglePManager(role);
        }
    });
    </script>

<?php elseif ($page == 'project'): ?>
    <div class="card">
        <h2>Semua Proyek</h2>
        <a href="admin.php?page=manage_project" class="btn">Buat Proyek Baru</a>
        
        <table style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Nama Proyek</th>
                    <th>Manager</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.*, u.username AS manager_name 
                        FROM project p 
                        JOIN users u ON p.manager_id = u.id 
                        ORDER BY p.tanggal_mulai DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0):
                    while ($project = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['nama_proyek']); ?></td>
                    <td><?php echo htmlspecialchars($project['manager_name']); ?></td>
                    <td><?php echo format_tanggal($project['tanggal_mulai']); ?></td>
                    <td><?php echo format_tanggal($project['tanggal_selesai']); ?></td>
                    <td>
                        <a href="admin.php?page=manage_project&id=<?php echo $project['id']; ?>" class="btn btn-sm">Edit</a>
                        
                        <form action="admin.php?page=project" method="post" style="display:inline;">
                            <input type="hidden" name="delete_project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus proyek ini? Ini akan menghapus semua tugas di dalamnya.')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Belum ada proyek.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($page == 'manage_project'): ?>
    <?php
    $edit_project = null;
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM project WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_project = $result->fetch_assoc();
        $stmt->close();
        if (!$edit_project) {
             echo "<div class='alert'>Proyek tidak ditemukan.</div>";
             include 'footer.php';
             exit;
        }
    }
    
    $managers->data_seek(0);
    ?>
    <div class="card">
        <h2><?php echo $edit_project ? 'Edit' : 'Buat'; ?> Proyek</h2>
        
        <?php echo $user_message; ?>

        <form action="admin.php" method="post" class="card" style="background: rgba(255,255,255,0.7);">
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
            <div class="form-group">
                <label>Project Manager</label>
                <select name="manager_id" class="form-control" required>
                    <option value="">Pilih Manager</option>
                    <?php while($mgr = $managers->fetch_assoc()): ?>
                    <option value="<?php echo $mgr['id']; ?>" <?php echo ($edit_project['manager_id'] ?? '') == $mgr['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mgr['username']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" name="save_project" class="btn">Simpan Proyek</button>
            <a href="admin.php?page=project" class="btn btn-secondary">Batal</a>
        </form>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>