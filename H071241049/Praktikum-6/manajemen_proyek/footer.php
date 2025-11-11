<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    </main> <footer class="container" style="text-align: center; margin-top: 20px; padding: 20px; color: var(--text-light);">
        <p>&copy; <?php echo date("Y"); ?> Sistem Manajemen Proyek.</p>
    </footer>
    <?php endif; ?>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>