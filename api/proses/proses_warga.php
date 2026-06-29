<?php
// ==============================================
// FILE: proses/proses_warga.php
// FUNGSI: CRUD (Create, Read, Update, Delete) data warga
// AUTHOR: Anggota 3 - Back-End PHP dan Database MySQL
// ==============================================

// ==============================================
// START SESSION & CEK LOGIN
// ==============================================

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// ==============================================
// INCLUDE KONEKSI DATABASE
// ==============================================

require_once '../config/koneksi.php';

// ==============================================
// TAMPILKAN DATA WARGA (READ)
// ==============================================

if (isset($_GET['action']) && $_GET['action'] == 'tampil') {
    $query = "SELECT * FROM warga ORDER BY id_warga DESC";
    $result = mysqli_query($conn, $query);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// ==============================================
// TAMBAH DATA WARGA (CREATE)
// ==============================================

if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    // Ambil data dari form
    $nama_warga = bersihkanInput($conn, $_POST['nama_warga']);
    $no_rumah = bersihkanInput($conn, $_POST['no_rumah']);
    $no_hp = bersihkanInput($conn, $_POST['no_hp']);
    $status = bersihkanInput($conn, $_POST['status']);
    
    // Validasi data
    $errors = [];
    
    if (empty($nama_warga)) {
        $errors[] = "Nama warga tidak boleh kosong";
    }
    
    if (empty($no_rumah)) {
        $errors[] = "Nomor rumah tidak boleh kosong";
    }
    
    // Cek apakah nomor rumah sudah ada
    $cekQuery = "SELECT id_warga FROM warga WHERE no_rumah = ?";
    $cekStmt = mysqli_prepare($conn, $cekQuery);
    mysqli_stmt_bind_param($cekStmt, "s", $no_rumah);
    mysqli_stmt_execute($cekStmt);
    mysqli_stmt_store_result($cekStmt);
    
    if (mysqli_stmt_num_rows($cekStmt) > 0) {
        $errors[] = "Nomor rumah sudah terdaftar";
    }
    
    // Jika ada error, redirect dengan pesan
    if (!empty($errors)) {
        $errorMsg = implode(", ", $errors);
        redirect("../pages/warga.php?error=" . urlencode($errorMsg));
        exit();
    }
    
    // Insert data ke database
    $query = "INSERT INTO warga (nama_warga, no_rumah, no_hp, status) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nama_warga, $no_rumah, $no_hp, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        redirect("../pages/warga.php?msg=Warga berhasil ditambahkan&type=success");
    } else {
        redirect("../pages/warga.php?error=Gagal menambahkan warga: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// EDIT DATA WARGA (UPDATE)
// ==============================================

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    // Ambil data dari form
    $id_warga = bersihkanInput($conn, $_POST['id_warga']);
    $nama_warga = bersihkanInput($conn, $_POST['nama_warga']);
    $no_rumah = bersihkanInput($conn, $_POST['no_rumah']);
    $no_hp = bersihkanInput($conn, $_POST['no_hp']);
    $status = bersihkanInput($conn, $_POST['status']);
    
    // Validasi data
    $errors = [];
    
    if (empty($nama_warga)) {
        $errors[] = "Nama warga tidak boleh kosong";
    }
    
    if (empty($no_rumah)) {
        $errors[] = "Nomor rumah tidak boleh kosong";
    }
    
    // Cek apakah nomor rumah sudah digunakan oleh warga lain
    $cekQuery = "SELECT id_warga FROM warga WHERE no_rumah = ? AND id_warga != ?";
    $cekStmt = mysqli_prepare($conn, $cekQuery);
    mysqli_stmt_bind_param($cekStmt, "si", $no_rumah, $id_warga);
    mysqli_stmt_execute($cekStmt);
    mysqli_stmt_store_result($cekStmt);
    
    if (mysqli_stmt_num_rows($cekStmt) > 0) {
        $errors[] = "Nomor rumah sudah digunakan oleh warga lain";
    }
    
    // Jika ada error, redirect dengan pesan
    if (!empty($errors)) {
        $errorMsg = implode(", ", $errors);
        redirect("../pages/warga.php?error=" . urlencode($errorMsg));
        exit();
    }
    
    // Update data di database
    $query = "UPDATE warga SET nama_warga = ?, no_rumah = ?, no_hp = ?, status = ? WHERE id_warga = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssi", $nama_warga, $no_rumah, $no_hp, $status, $id_warga);
    
    if (mysqli_stmt_execute($stmt)) {
        redirect("../pages/warga.php?msg=Warga berhasil diupdate&type=success");
    } else {
        redirect("../pages/warga.php?error=Gagal mengupdate warga: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// HAPUS DATA WARGA (DELETE)
// ==============================================

if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id_warga = bersihkanInput($conn, $_GET['id_warga']);
    
    // Cek apakah warga memiliki data jimpitan
    $cekJimpitan = "SELECT id_jimpitan FROM jimpitan WHERE id_warga = ?";
    $stmtJimpitan = mysqli_prepare($conn, $cekJimpitan);
    mysqli_stmt_bind_param($stmtJimpitan, "i", $id_warga);
    mysqli_stmt_execute($stmtJimpitan);
    mysqli_stmt_store_result($stmtJimpitan);
    
    $hasJimpitan = mysqli_stmt_num_rows($stmtJimpitan) > 0;
    
    // Hapus data (foreign key akan otomatis menghapus data jimpitan karena ON DELETE CASCADE)
    $query = "DELETE FROM warga WHERE id_warga = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_warga);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Warga berhasil dihapus";
        if ($hasJimpitan) {
            $message .= " beserta data jimpitan terkait";
        }
        redirect("../pages/warga.php?msg=" . urlencode($message) . "&type=success");
    } else {
        redirect("../pages/warga.php?error=Gagal menghapus warga: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// GET DATA WARGA BY ID (untuk edit via AJAX)
// ==============================================

if (isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['id'])) {
    $id_warga = bersihkanInput($conn, $_GET['id']);
    
    $query = "SELECT * FROM warga WHERE id_warga = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_warga);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
    
    exit();
}

// ==============================================
// JIKA TINDAKAN TIDAK DIKENAL
// ==============================================

header("Location: ../pages/warga.php");
exit();
?>