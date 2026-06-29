<?php
// ==============================================
// FILE: pages/laporan.php
// FUNGSI: Laporan jimpitan dan pengeluaran kas RT
// FITUR: Filter bulanan dan filter harian/tanggal
// ==============================================

session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/koneksi.php';

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

$daftar_tahun = [];
for($i = (int)date('Y'); $i >= 2020; $i--) {
    $daftar_tahun[] = $i;
}

$data_jimpitan = [];
$total_jimpitan = 0;

if($is_harian) {
    $query_jimpitan = "SELECT 
                        j.id_jimpitan,
                        j.tanggal,
                        w.nama_warga,
                        w.no_rumah,
                        j.nominal,
                        j.keterangan,
                        'Jimpitan' AS tipe
                    FROM jimpitan j
                    LEFT JOIN warga w ON j.id_warga = w.id_warga
                    WHERE j.tanggal = ?
                    ORDER BY j.tanggal DESC, CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
    $stmt = mysqli_prepare($conn, $query_jimpitan);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);
} else {
    $query_jimpitan = "SELECT 
                        j.id_jimpitan,
                        j.tanggal,
                        w.nama_warga,
                        w.no_rumah,
                        j.nominal,
                        j.keterangan,
                        'Jimpitan' AS tipe
                    FROM jimpitan j
                    LEFT JOIN warga w ON j.id_warga = w.id_warga
                    WHERE MONTH(j.tanggal) = ? AND YEAR(j.tanggal) = ?
                    ORDER BY j.tanggal DESC, CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
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
                            id_pengeluaran,
                            tanggal,
                            nama_pengeluaran,
                            kategori,
                            nominal,
                            keterangan,
                            'Pengeluaran' AS tipe
                        FROM pengeluaran
                        WHERE tanggal = ?
                        ORDER BY tanggal DESC";
    $stmt = mysqli_prepare($conn, $query_pengeluaran);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);
} else {
    $query_pengeluaran = "SELECT 
                            id_pengeluaran,
                            tanggal,
                            nama_pengeluaran,
                            kategori,
                            nominal,
                            keterangan,
                            'Pengeluaran' AS tipe
                        FROM pengeluaran
                        WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                        ORDER BY tanggal DESC";
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
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

$saldo_akhir = $total_jimpitan - $total_pengeluaran;
$nama_bulan = $daftar_bulan[$bulan] ?? 'Bulan ' . $bulan;
$judul_periode = $is_harian ? 'Harian ' . date('d/m/Y', strtotime($tanggal)) : $nama_bulan . ' ' . $tahun;
$link_cetak = $is_harian
    ? "../cetak_laporan.php?tanggal=" . urlencode($tanggal)
    : "../cetak_laporan.php?bulan=" . urlencode($bulan) . "&tahun=" . urlencode($tahun);

function formatRupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas - Kas RT Digital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="bi bi-house-door-fill"></i>
                <span>KasRT Digital</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-arrow-left-short"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <div class="menu-title">MENU UTAMA</div>
            <a href="dashboard.php" class="menu-item">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="warga.php" class="menu-item">
                <i class="bi bi-people-fill"></i>
                <span>Data Warga</span>
            </a>
            <a href="jimpitan.php" class="menu-item">
                <i class="bi bi-cash-stack"></i>
                <span>Jimpitan</span>
            </a>
            <a href="pengeluaran.php" class="menu-item">
                <i class="bi bi-receipt"></i>
                <span>Pengeluaran</span>
            </a>
            <a href="laporan.php" class="menu-item active">
                <i class="bi bi-file-text-fill"></i>
                <span>Laporan Kas</span>
            </a>

            <div class="menu-title mt-4">PENGATURAN</div>
            <a href="../logout.php" class="menu-item" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo $_SESSION['nama'] ?? 'Bendahara'; ?></div>
                    <div class="user-role">Bendahara RT</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="page-title">Laporan Kas</h2>
            </div>
            <div class="navbar-right">
                <div class="date-display">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
            </div>
        </nav>

        <div class="content-wrapper" id="laporanContent">
            <div class="card-glass p-4 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-funnel-fill me-2 text-primary"></i>Filter Laporan
                </h5>

                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">Tanggal Harian</label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            class="form-control rounded-pill"
                            value="<?php echo htmlspecialchars($tanggal); ?>"
                        >
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">Bulan</label>
                        <select name="bulan" class="form-select rounded-pill">
                            <?php foreach($daftar_bulan as $key => $nama): ?>
                                <option value="<?php echo $key; ?>" <?php echo $bulan == $key ? 'selected' : ''; ?>>
                                    <?php echo $nama; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select name="tahun" class="form-select rounded-pill">
                            <?php foreach($daftar_tahun as $th): ?>
                                <option value="<?php echo $th; ?>" <?php echo $tahun == $th ? 'selected' : ''; ?>>
                                    <?php echo $th; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-4 d-grid">
                        <button type="submit" class="btn btn-primary-gradient rounded-pill">
                            <i class="bi bi-search me-2"></i>Tampilkan
                        </button>
                    </div>

                    <div class="col-xl-2 col-md-4 d-grid">
                        <a href="laporan.php" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card-dashboard stat-card-primary">
                        <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Total Jimpitan</span>
                            <h3 class="stat-value text-success"><?php echo formatRupiah($total_jimpitan); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="stat-card-dashboard stat-card-danger">
                        <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Total Pengeluaran</span>
                            <h3 class="stat-value text-danger"><?php echo formatRupiah($total_pengeluaran); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-12">
                    <div class="stat-card-dashboard stat-card-info">
                        <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Saldo Periode</span>
                            <h3 class="stat-value text-primary"><?php echo formatRupiah($saldo_akhir); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-glass">
                <div class="card-header-custom">
                    <h5>
                        <i class="bi bi-file-earmark-text-fill me-2"></i>
                        Laporan <?php echo $judul_periode; ?>
                    </h5>
                    <a href="<?php echo $link_cetak; ?>" target="_blank" class="btn btn-sm btn-primary-gradient rounded-pill">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </a>
                </div>

                <div class="card-body-custom">
                    <div class="table-responsive">
                        <table class="table table-custom" id="tabelLaporan">
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
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                            <td>
                                                <?php
                                                if($row['tipe'] == 'Jimpitan') {
                                                    echo 'Jimpitan - ' . htmlspecialchars($row['nama_warga'] ?? '-') . ' (Rumah ' . htmlspecialchars($row['no_rumah'] ?? '-') . ')';
                                                } else {
                                                    echo htmlspecialchars($row['nama_pengeluaran'] ?? '-');
                                                }
                                                ?>
                                                <?php if(!empty($row['keterangan'])): ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($row['keterangan']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($row['tipe'] == 'Jimpitan'): ?>
                                                    <span class="badge bg-success">Jimpitan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Pengeluaran</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                <?php echo $row['tipe'] == 'Jimpitan' ? formatRupiah($row['nominal']) : '-'; ?>
                                            </td>
                                            <td class="text-end text-danger fw-bold">
                                                <?php echo $row['tipe'] == 'Pengeluaran' ? formatRupiah($row['nominal']) : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Tidak ada data pada periode ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end text-success"><?php echo formatRupiah($total_jimpitan); ?></th>
                                    <th class="text-end text-danger"><?php echo formatRupiah($total_pengeluaran); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Saldo Akhir</th>
                                    <th colspan="2" class="text-end text-primary"><?php echo formatRupiah($saldo_akhir); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
function updateDate() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const currentDate = document.getElementById('currentDate');
    if(currentDate) currentDate.innerHTML = now.toLocaleDateString('id-ID', options);
}
updateDate();

const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');

if(sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
}

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
}
</script>
</body>
</html>
