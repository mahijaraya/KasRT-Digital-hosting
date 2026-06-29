<?php
// ==============================================
// FILE: logout.php
// FUNGSI: Menghapus session dan logout
// AUTHOR: Anggota 1 - Front-End Login & Dashboard
// ==============================================

session_start();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login dengan pesan sukses
header("Location: login.php?logout=success");
exit();
?>