<?php
// Cek apakah config.php sudah di-include
if (!isset($conn)) {
    require_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Proyek</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    <div class="container">
        <header>
            <nav>
                <div class="logo">ProyeKu</div>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    
                    <?php if ($_SESSION["role"] == 'superadmin'): ?>
                        <li><a href="admin.php?page=users">Manajemen User</a></li>
                        <li><a href="admin.php?page=project">Semua Proyek</a></li>
                    <?php elseif ($_SESSION["role"] == 'project manager'): ?>
                        <li><a href="manager.php?page=projects">Proyek Saya</a></li>
                    <?php elseif ($_SESSION["role"] == 'team member'): ?>
                        <li><a href="member.php?page=tasks">Tugas Saya</a></li>
                    <?php endif; ?>
                </ul>
                <div class="user-info">
                    <span>Halo, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </nav>
        </header>
    </div>
    <main class="container">
    <?php endif; ?>