<?php
// ==============================================
// FILE: config/koneksi.php
// FUNGSI: Koneksi ke database MySQL
// ==============================================

// ==============================================
// KONFIGURASI DATABASE
// ==============================================

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'kas_rt_digital';
$port = 3306; // Jika memakai XAMPP default, ubah menjadi 3306 atau hapus parameter port di mysqli_connect.

// ==============================================
// MEMBUAT KONEKSI
// ==============================================

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// ==============================================
// FUNGSI-FUNGSI BANTUAN
// ==============================================

function getTotalJimpitan($conn, $bulan = null, $tahun = null) {
    $sql = "SELECT COALESCE(SUM(nominal), 0) as total FROM jimpitan";

    if ($bulan && $tahun) {
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;
        $sql .= " WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun";
    } elseif ($tahun) {
        $tahun = (int)$tahun;
        $sql .= " WHERE YEAR(tanggal) = $tahun";
    }

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return floatval($row['total']);
}

// Alias agar kode lama yang masih memanggil getTotalPemasukan tetap aman.
// Pada versi revisi, pemasukan yang digunakan hanya jimpitan.
function getTotalPemasukan($conn, $bulan = null, $tahun = null) {
    return getTotalJimpitan($conn, $bulan, $tahun);
}

function getTotalPengeluaran($conn, $bulan = null, $tahun = null) {
    $sql = "SELECT COALESCE(SUM(nominal), 0) as total FROM pengeluaran";

    if ($bulan && $tahun) {
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;
        $sql .= " WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun";
    } elseif ($tahun) {
        $tahun = (int)$tahun;
        $sql .= " WHERE YEAR(tanggal) = $tahun";
    }

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return floatval($row['total']);
}

function getSaldoAkhir($conn) {
    $totalJimpitan = getTotalJimpitan($conn);
    $totalPengeluaran = getTotalPengeluaran($conn);
    return $totalJimpitan - $totalPengeluaran;
}

function getJumlahWarga($conn) {
    $sql = "SELECT COUNT(*) as total FROM warga WHERE status = 'Aktif'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return intval($row['total']);
}

function bersihkanInput($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        header("Location: $url?msg=" . urlencode($message) . "&type=$type");
    } else {
        header("Location: $url");
    }
    exit();
}
?>
