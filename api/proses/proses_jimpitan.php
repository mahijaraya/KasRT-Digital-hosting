<?php
// ==============================================
// FILE: proses/proses_jimpitan.php
// FUNGSI: CRUD data jimpitan dengan status (Isi/Kosong)
// AUTHOR: Anggota 4 - Developer Laporan, JavaScript, dan Integrasi
// ==============================================

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/koneksi.php';

// ==============================================
// CEK KONEKSI DATABASE
// ==============================================
if(!isset($conn) || !$conn) {
    die("Koneksi database gagal. Silakan periksa file config/koneksi.php");
}

// ==============================================
// TAMBAH DATA JIMPITAN
// ==============================================
if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    $id_warga = isset($_POST['id_warga']) ? (int)$_POST['id_warga'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'Kosong';
    $nominal = isset($_POST['nominal']) ? (float)$_POST['nominal'] : 0;
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';

    // ==============================================
    // VALIDASI DATA
    // ==============================================
    $errors = [];

    if ($id_warga <= 0) {
        $errors[] = "Warga harus dipilih";
    }

    if (empty($status)) {
        $errors[] = "Status harus dipilih";
    }

    // PERBAIKAN: Hanya validasi nominal jika status "Isi"
    if ($status == 'Isi') {
        if ($nominal <= 0) {
            $errors[] = "Nominal jimpitan harus lebih dari 0 untuk status Isi";
        }
    } else {
        // Jika status Kosong, nominal dipaksa 0
        $nominal = 0;
    }

    if (empty($tanggal)) {
        $errors[] = "Tanggal harus diisi";
    }

    if (!empty($errors)) {
        header("Location: ../pages/jimpitan.php?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    // ==============================================
    // INSERT KE TABEL JIMPITAN
    // ==============================================
    $query = "INSERT INTO jimpitan (id_warga, status, nominal, tanggal, keterangan) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        die("Error prepare statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "isiss", $id_warga, $status, $nominal, $tanggal, $keterangan);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/jimpitan.php?msg=Data jimpitan berhasil ditambahkan&type=success");
    } else {
        header("Location: ../pages/jimpitan.php?error=Gagal menambahkan jimpitan: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    exit();
}

// ==============================================
// EDIT DATA JIMPITAN
// ==============================================
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id_jimpitan = isset($_POST['id_jimpitan']) ? (int)$_POST['id_jimpitan'] : 0;
    $id_warga = isset($_POST['id_warga']) ? (int)$_POST['id_warga'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'Kosong';
    $nominal = isset($_POST['nominal']) ? (float)$_POST['nominal'] : 0;
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';

    $errors = [];

    if ($id_jimpitan <= 0) {
        $errors[] = "ID jimpitan tidak valid";
    }

    if ($id_warga <= 0) {
        $errors[] = "Warga harus dipilih";
    }

    if (empty($status)) {
        $errors[] = "Status harus dipilih";
    }

    // PERBAIKAN: Hanya validasi nominal jika status "Isi"
    if ($status == 'Isi') {
        if ($nominal <= 0) {
            $errors[] = "Nominal jimpitan harus lebih dari 0 untuk status Isi";
        }
    } else {
        $nominal = 0;
    }

    if (empty($tanggal)) {
        $errors[] = "Tanggal harus diisi";
    }

    if (!empty($errors)) {
        header("Location: ../pages/jimpitan.php?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    $query = "UPDATE jimpitan SET id_warga = ?, status = ?, nominal = ?, tanggal = ?, keterangan = ? WHERE id_jimpitan = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        die("Error prepare statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "isissi", $id_warga, $status, $nominal, $tanggal, $keterangan, $id_jimpitan);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/jimpitan.php?msg=Data jimpitan berhasil diupdate&type=success");
    } else {
        header("Location: ../pages/jimpitan.php?error=Gagal mengupdate jimpitan: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    exit();
}

// ==============================================
// HAPUS DATA JIMPITAN
// ==============================================
if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id_jimpitan = isset($_GET['id_jimpitan']) ? (int)$_GET['id_jimpitan'] : 0;

    if ($id_jimpitan <= 0) {
        header("Location: ../pages/jimpitan.php?error=ID jimpitan tidak valid");
        exit();
    }

    $query = "DELETE FROM jimpitan WHERE id_jimpitan = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        die("Error prepare statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id_jimpitan);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/jimpitan.php?msg=Data jimpitan berhasil dihapus&type=success");
    } else {
        header("Location: ../pages/jimpitan.php?error=Gagal menghapus jimpitan: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    exit();
}

// ==============================================
// GET DATA JIMPITAN UNTUK MODAL EDIT
// ==============================================
if (isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['id'])) {
    $id_jimpitan = (int)$_GET['id'];

    $query = "SELECT * FROM jimpitan WHERE id_jimpitan = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare statement gagal']);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id_jimpitan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    header('Content-Type: application/json');
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
    
    mysqli_stmt_close($stmt);
    exit();
}

header("Location: ../pages/jimpitan.php");
exit();
?>