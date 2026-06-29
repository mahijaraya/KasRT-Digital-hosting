<?php
// ==============================================
// FILE: proses/proses_pengeluaran.php
// FUNGSI: CRUD (Create, Read, Update, Delete) data pengeluaran
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
// TAMBAH DATA PENGELUARAN (CREATE)
// ==============================================

if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    // Ambil data dari form
    $nama_pengeluaran = bersihkanInput($conn, $_POST['nama_pengeluaran']);
    $kategori = bersihkanInput($conn, $_POST['kategori']);
    $nominal = bersihkanInput($conn, $_POST['nominal']);
    $tanggal = bersihkanInput($conn, $_POST['tanggal']);
    $keterangan = bersihkanInput($conn, $_POST['keterangan']);
    
    // Validasi data
    $errors = [];
    
    if (empty($nama_pengeluaran)) {
        $errors[] = "Nama pengeluaran tidak boleh kosong";
    }
    
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    if (empty($nominal) || $nominal <= 0) {
        $errors[] = "Nominal harus lebih dari 0";
    }
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal harus diisi";
    }
    
    // Jika ada error, redirect dengan pesan
    if (!empty($errors)) {
        $errorMsg = implode(", ", $errors);
        redirect("../pages/pengeluaran.php?error=" . urlencode($errorMsg));
        exit();
    }
    
    // Insert data ke database
    $query = "INSERT INTO pengeluaran (nama_pengeluaran, kategori, nominal, tanggal, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssiss", $nama_pengeluaran, $kategori, $nominal, $tanggal, $keterangan);
    
    if (mysqli_stmt_execute($stmt)) {
        redirect("../pages/pengeluaran.php?msg=Pengeluaran berhasil ditambahkan&type=success");
    } else {
        redirect("../pages/pengeluaran.php?error=Gagal menambahkan pengeluaran: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// EDIT DATA PENGELUARAN (UPDATE)
// ==============================================

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    // Ambil data dari form
    $id_pengeluaran = bersihkanInput($conn, $_POST['id_pengeluaran']);
    $nama_pengeluaran = bersihkanInput($conn, $_POST['nama_pengeluaran']);
    $kategori = bersihkanInput($conn, $_POST['kategori']);
    $nominal = bersihkanInput($conn, $_POST['nominal']);
    $tanggal = bersihkanInput($conn, $_POST['tanggal']);
    $keterangan = bersihkanInput($conn, $_POST['keterangan']);
    
    // Validasi data
    $errors = [];
    
    if (empty($nama_pengeluaran)) {
        $errors[] = "Nama pengeluaran tidak boleh kosong";
    }
    
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    if (empty($nominal) || $nominal <= 0) {
        $errors[] = "Nominal harus lebih dari 0";
    }
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal harus diisi";
    }
    
    // Jika ada error, redirect dengan pesan
    if (!empty($errors)) {
        $errorMsg = implode(", ", $errors);
        redirect("../pages/pengeluaran.php?error=" . urlencode($errorMsg));
        exit();
    }
    
    // Update data di database
    $query = "UPDATE pengeluaran SET nama_pengeluaran = ?, kategori = ?, nominal = ?, tanggal = ?, keterangan = ? WHERE id_pengeluaran = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssissi", $nama_pengeluaran, $kategori, $nominal, $tanggal, $keterangan, $id_pengeluaran);
    
    if (mysqli_stmt_execute($stmt)) {
        redirect("../pages/pengeluaran.php?msg=Pengeluaran berhasil diupdate&type=success");
    } else {
        redirect("../pages/pengeluaran.php?error=Gagal mengupdate pengeluaran: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// HAPUS DATA PENGELUARAN (DELETE)
// ==============================================

if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id_pengeluaran = bersihkanInput($conn, $_GET['id_pengeluaran']);
    
    $query = "DELETE FROM pengeluaran WHERE id_pengeluaran = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_pengeluaran);
    
    if (mysqli_stmt_execute($stmt)) {
        redirect("../pages/pengeluaran.php?msg=Pengeluaran berhasil dihapus&type=success");
    } else {
        redirect("../pages/pengeluaran.php?error=Gagal menghapus pengeluaran: " . mysqli_error($conn));
    }
    
    exit();
}

// ==============================================
// GET DATA PENGELUARAN BY ID (untuk edit via AJAX)
// ==============================================

if (isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['id'])) {
    $id_pengeluaran = bersihkanInput($conn, $_GET['id']);
    
    $query = "SELECT * FROM pengeluaran WHERE id_pengeluaran = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_pengeluaran);
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

header("Location: ../pages/pengeluaran.php");
exit();
?>