<?php
// ==============================================
// FILE: proses/proses_login.php
// FUNGSI: Memproses login bendahara
// AUTHOR: Anggota 3 - Back-End PHP dan Database MySQL
// ==============================================

// ==============================================
// START SESSION
// ==============================================

session_start();

// ==============================================
// INCLUDE KONEKSI DATABASE
// ==============================================

require_once '../config/koneksi.php';

// ==============================================
// CEK APAKAH FORM SUDAH DISUBMIT
// ==============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

// ==============================================
// AMBIL DATA DARI FORM
// ==============================================

$username = isset($_POST['username']) ? bersihkanInput($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// ==============================================
// VALIDASI INPUT TIDAK KOSONG
// ==============================================

if (empty($username) || empty($password)) {
    header("Location: ../login.php?error=required");
    exit();
}

// ==============================================
// QUERY CEK USERNAME DI DATABASE
// ==============================================

$query = "SELECT id_user, nama, username, password, role FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ==============================================
// CEK APAKAH USERNAME DITEMUKAN
// ==============================================

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    
    // ==============================================
    // VERIFIKASI PASSWORD
    // ==============================================
    // Untuk password pertama kali, gunakan password_hash
    // Jika password masih plain text, verifikasi dengan cara:
    
    // Cara 1: Jika password sudah di-hash (recommended)
    if (password_verify($password, $user['password'])) {
        // Password cocok, buat session
        $_SESSION['logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect ke dashboard
        header("Location: ../pages/dashboard.php");
        exit();
    } 
    // Cara 2: Jika password masih plain text (sementara, untuk testing)
    elseif ($password === $user['password']) {
        // Update password ke format hash untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET password = ? WHERE id_user = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $user['id_user']);
        mysqli_stmt_execute($updateStmt);
        
        // Buat session
        $_SESSION['logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect ke dashboard
        header("Location: ../pages/dashboard.php");
        exit();
    }
}

// ==============================================
// JIKA LOGIN GAGAL
// ==============================================

header("Location: ../login.php?error=invalid");
exit();
?>