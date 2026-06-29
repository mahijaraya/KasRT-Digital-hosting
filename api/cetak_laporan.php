<?php
// ==============================================
// FILE: cetak_laporan.php
// FUNGSI: Halaman cetak laporan jimpitan dan pengeluaran
// FITUR: Cetak laporan bulanan atau harian berdasarkan tanggal
// ==============================================

session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'config/koneksi.php';

if(!isset($conn) || !$conn) {
    die("Koneksi database gagal. Silakan periksa file config/koneksi.php");
}

function validTanggal($tanggal) {
    if(empty($tanggal)) {
        return false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date && $date->format('Y-m-d') === $tanggal;
}

$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';
if(!validTanggal($tanggal)) {
    $tanggal = '';
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
if($bulan < 1 || $bulan > 12) {
    $bulan = (int)date('m');
}

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
if($tahun < 2020 || $tahun > ((int)date('Y') + 5)) {
    $tahun = (int)date('Y');
}

$is_harian = !empty($tanggal);

$daftar_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$nama_bulan = $daftar_bulan[$bulan] ?? 'Bulan ' . $bulan;
$judul_periode = $is_harian ? 'Tanggal ' . date('d/m/Y', strtotime($tanggal)) : $nama_bulan . ' ' . $tahun;

$data_jimpitan = [];
$total_jimpitan = 0;

if($is_harian) {
    $query_jimpitan = "SELECT 
                        j.tanggal, 
                        w.nama_warga, 
                        w.no_rumah, 
                        j.nominal, 
                        j.keterangan, 
                        'Jimpitan' AS tipe
                    FROM jimpitan j
                    LEFT JOIN warga w ON j.id_warga = w.id_warga
                    WHERE j.tanggal = ?
                    ORDER BY CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
    $stmt = mysqli_prepare($conn, $query_jimpitan);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);
} else {
    $query_jimpitan = "SELECT 
                        j.tanggal, 
                        w.nama_warga, 
                        w.no_rumah, 
                        j.nominal, 
                        j.keterangan, 
                        'Jimpitan' AS tipe
                    FROM jimpitan j
                    LEFT JOIN warga w ON j.id_warga = w.id_warga
                    WHERE MONTH(j.tanggal) = ? AND YEAR(j.tanggal) = ?
                    ORDER BY j.tanggal ASC, CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
    $stmt = mysqli_prepare($conn, $query_jimpitan);
    mysqli_stmt_bind_param($stmt, "ii", $bulan, $tahun);
}

mysqli_stmt_execute($stmt);
$result_jimpitan = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result_jimpitan)) {
    $data_jimpitan[] = $row;
    $total_jimpitan += (float)$row['nominal'];
}

mysqli_stmt_close($stmt);

$data_pengeluaran = [];
$total_pengeluaran = 0;

if($is_harian) {
    $query_pengeluaran = "SELECT 
                            tanggal, 
                            nama_pengeluaran, 
                            kategori, 
                            nominal, 
                            keterangan, 
                            'Pengeluaran' AS tipe
                        FROM pengeluaran
                        WHERE tanggal = ?
                        ORDER BY tanggal ASC";
    $stmt = mysqli_prepare($conn, $query_pengeluaran);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);
} else {
    $query_pengeluaran = "SELECT 
                            tanggal, 
                            nama_pengeluaran, 
                            kategori, 
                            nominal, 
                            keterangan, 
                            'Pengeluaran' AS tipe
                        FROM pengeluaran
                        WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                        ORDER BY tanggal ASC";
    $stmt = mysqli_prepare($conn, $query_pengeluaran);
    mysqli_stmt_bind_param($stmt, "ii", $bulan, $tahun);
}

mysqli_stmt_execute($stmt);
$result_pengeluaran = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result_pengeluaran)) {
    $data_pengeluaran[] = $row;
    $total_pengeluaran += (float)$row['nominal'];
}

mysqli_stmt_close($stmt);

$data_laporan = array_merge($data_jimpitan, $data_pengeluaran);
usort($data_laporan, function($a, $b) {
    return strtotime($a['tanggal']) - strtotime($b['tanggal']);
});

$saldo_akhir = $total_jimpitan - $total_pengeluaran;

function formatRupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Kas RT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            margin: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 4px 0;
        }
        .info {
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-box {
            border: 1px solid #ccc;
            padding: 12px;
            flex: 1;
            border-radius: 8px;
        }
        .summary-box strong {
            display: block;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px;
        }
        th {
            background: #f0f0f0;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .footer {
            margin-top: 45px;
            display: flex;
            justify-content: flex-end;
        }
        .signature {
            text-align: center;
            width: 250px;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 15px; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()">Cetak Laporan</button>
    <button onclick="window.close()">Tutup</button>
</div>

<div class="header">
    <h2>LAPORAN KAS RT DIGITAL</h2>
    <h3>Periode <?php echo $judul_periode; ?></h3>
</div>

<div class="info">
    <strong>Nama Aplikasi:</strong> Kas RT Digital<br>
    <strong>Jenis Laporan:</strong> Laporan Jimpitan dan Pengeluaran Kas RT<br>
    <strong>Mode Laporan:</strong> <?php echo $is_harian ? 'Harian' : 'Bulanan'; ?><br>
    <strong>Tanggal Cetak:</strong> <?php echo date('d/m/Y'); ?>
</div>

<div class="summary">
    <div class="summary-box">
        <strong>Total Jimpitan</strong>
        <?php echo formatRupiah($total_jimpitan); ?>
    </div>
    <div class="summary-box">
        <strong>Total Pengeluaran</strong>
        <?php echo formatRupiah($total_pengeluaran); ?>
    </div>
    <div class="summary-box">
        <strong>Saldo Akhir</strong>
        <?php echo formatRupiah($saldo_akhir); ?>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Tipe</th>
            <th class="text-end">Jimpitan</th>
            <th class="text-end">Pengeluaran</th>
        </tr>
    </thead>
    <tbody>
        <?php if(count($data_laporan) > 0): ?>
            <?php $no = 1; foreach($data_laporan as $row): ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td>
                    <?php
                    if($row['tipe'] == 'Jimpitan') {
                        echo 'Jimpitan - ' . htmlspecialchars($row['nama_warga'] ?? '-') . ' (Rumah ' . htmlspecialchars($row['no_rumah'] ?? '-') . ')';
                    } else {
                        echo htmlspecialchars($row['nama_pengeluaran'] ?? '-');
                    }

                    if(!empty($row['keterangan'])) {
                        echo '<br><small>' . htmlspecialchars($row['keterangan']) . '</small>';
                    }
                    ?>
                </td>
                <td><?php echo $row['tipe']; ?></td>
                <td class="text-end"><?php echo $row['tipe'] == 'Jimpitan' ? formatRupiah($row['nominal']) : '-'; ?></td>
                <td class="text-end"><?php echo $row['tipe'] == 'Pengeluaran' ? formatRupiah($row['nominal']) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pada periode ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-end">Total</th>
            <th class="text-end"><?php echo formatRupiah($total_jimpitan); ?></th>
            <th class="text-end"><?php echo formatRupiah($total_pengeluaran); ?></th>
        </tr>
        <tr>
            <th colspan="4" class="text-end">Saldo Akhir</th>
            <th colspan="2" class="text-end"><?php echo formatRupiah($saldo_akhir); ?></th>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <div class="signature">
        <p><strong><?php echo $_SESSION['nama'] ?? 'Bendahara RT'; ?></strong></p>
        <br><br><br>
        <p><strong><?php echo $_SESSION['nama'] ?? 'Bendahara RT'; ?></strong></p>
    </div>
</div>

<script>
window.onload = function() {
    // window.print(); // aktifkan jika ingin langsung membuka dialog print
}
</script>
</body>
</html>
