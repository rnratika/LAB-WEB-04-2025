<?php
require_once "config.php";

$username = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["username"]))) {
        $error = "Silakan masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $error = "Silakan masukkan password Anda.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty($error)) {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $db_password, $role);
                    if ($stmt->fetch()) {
                        
                        if ($password === $db_password) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            header("location: index.php");
                            exit;
                        } else {
                            $error = "Username atau password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    $error = "Username atau password yang Anda masukkan salah.";
                }
            } else {
                $error = "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Proyek</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="card">
            <h1>Login</h1>
            <p style="text-align: center; margin-bottom: 20px;">Selamat datang kembali!</p>

            <?php 
            if (!empty($error)) {
                echo '<div class="alert">' . $error . '</div>';
            }
            ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo $username; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn" style="width: 100%;">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>