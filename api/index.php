<?php
// ==============================================
// FILE: index.php
// FUNGSI: Halaman utama - redirect ke login
// AUTHOR: Anggota 1 - Front-End Login & Dashboard
// ==============================================

session_start();

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: pages/dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>