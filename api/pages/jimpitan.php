<?php
// ==============================================
// FILE: pages/jimpitan.php
// FUNGSI: Halaman input dan pengelolaan data jimpitan
// FITUR: Status (Isi/Kosong), Sorting, Filter, Pencarian
// AUTHOR: Anggota 4 - Developer Laporan, JavaScript, dan Integrasi
// ==============================================

session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/koneksi.php';

$data_jimpitan = [];
$data_warga = [];

function validTanggal($tanggal) {
    if(empty($tanggal)) {
        return false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date && $date->format('Y-m-d') === $tanggal;
}

$tanggal_filter = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';
if(!validTanggal($tanggal_filter)) {
    $tanggal_filter = '';
}

if(isset($conn) && $conn) {
    // Ambil data jimpitan dengan join warga, bisa difilter berdasarkan tanggal
    if(!empty($tanggal_filter)) {
        $query = "SELECT j.*, w.nama_warga, w.no_rumah
                  FROM jimpitan j
                  LEFT JOIN warga w ON j.id_warga = w.id_warga
                  WHERE j.tanggal = ?
                  ORDER BY CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $tanggal_filter);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $query = "SELECT j.*, w.nama_warga, w.no_rumah
                  FROM jimpitan j
                  LEFT JOIN warga w ON j.id_warga = w.id_warga
                  ORDER BY CAST(w.no_rumah AS UNSIGNED) ASC, w.no_rumah ASC";
        $result = mysqli_query($conn, $query);
    }

    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data_jimpitan[] = $row;
        }
    }

    if(isset($stmt) && $stmt) {
        mysqli_stmt_close($stmt);
    }

    $query_warga = "SELECT * FROM warga WHERE status = 'Aktif' ORDER BY CAST(no_rumah AS UNSIGNED) ASC, no_rumah ASC";
    $result_warga = mysqli_query($conn, $query_warga);
    if($result_warga) {
        while($row = mysqli_fetch_assoc($result_warga)) {
            $data_warga[] = $row;
        }
    }
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Hitung statistik
$total_jimpitan = 0;
$jumlah_isi = 0;
$jumlah_kosong = 0;

foreach($data_jimpitan as $item) {
    $total_jimpitan += (float)$item['nominal'];
    if($item['status'] == 'Isi') {
        $jumlah_isi++;
    } else {
        $jumlah_kosong++;
    }
}

$jumlah_transaksi = count($data_jimpitan);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jimpitan - Kas RT Digital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Style untuk membuat card statistik seragam */
        .stat-card-uniform {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 20px 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
            border: 1px solid var(--border-color);
            height: 100%;
            min-height: 110px;
        }
        .stat-card-uniform:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.15);
        }
        .stat-icon-uniform {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }
        .stat-info-uniform {
            flex: 1;
        }
        .stat-label-uniform {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        .stat-value-uniform {
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }
        .stat-sub-uniform {
            font-size: 11px;
            color: var(--text-gray);
            margin-top: 4px;
            display: block;
        }
        /* Warna khusus untuk setiap card */
        .bg-primary-light { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
        .bg-success-light { background: rgba(16, 185, 129, 0.12); color: #10b981; }
        .bg-warning-light { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .bg-danger-light { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    </style>
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
            <a href="jimpitan.php" class="menu-item active">
                <i class="bi bi-cash-stack"></i>
                <span>Jimpitan</span>
            </a>
            <a href="pengeluaran.php" class="menu-item">
                <i class="bi bi-receipt"></i>
                <span>Pengeluaran</span>
            </a>
            <a href="laporan.php" class="menu-item">
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
                <h2 class="page-title">Jimpitan Warga</h2>
            </div>
            <div class="navbar-right">
                <div class="date-display">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
                <button id="darkModeToggle" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-moon-fill"></i>
                </button>
            </div>
        </nav>

        <div class="content-wrapper">
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- STATISTIK CARDS - SERAGAM -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card-uniform">
                        <div class="stat-icon-uniform bg-primary-light">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-info-uniform">
                            <span class="stat-label-uniform">Total Jimpitan</span>
                            <h3 class="stat-value-uniform text-primary"><?php echo formatRupiah($total_jimpitan); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-uniform">
                        <div class="stat-icon-uniform bg-success-light">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <div class="stat-info-uniform">
                            <span class="stat-label-uniform">Total Transaksi</span>
                            <h3 class="stat-value-uniform text-success"><?php echo $jumlah_transaksi; ?></h3>
                            <span class="stat-sub-uniform">kali jimpitan</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-uniform">
                        <div class="stat-icon-uniform bg-warning-light">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="stat-info-uniform">
                            <span class="stat-label-uniform">Total Isi</span>
                            <h3 class="stat-value-uniform text-warning"><?php echo $jumlah_isi; ?></h3>
                            <span class="stat-sub-uniform">sudah membayar</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-uniform">
                        <div class="stat-icon-uniform bg-danger-light">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div class="stat-info-uniform">
                            <span class="stat-label-uniform">Total Kosong</span>
                            <h3 class="stat-value-uniform text-danger"><?php echo $jumlah_kosong; ?></h3>
                            <span class="stat-sub-uniform">belum membayar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER, SEARCH, SORTING, BUTTON TAMBAH -->
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-12 col-xl-3 col-md-6">
                    <label class="form-label fw-semibold">Pencarian</label>
                    <div class="input-group rounded-pill shadow-sm bg-white border">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-transparent rounded-pill" 
                               id="searchInput" placeholder="Cari nama atau no rumah...">
                    </div>
                </div>

                <div class="col-6 col-xl-2 col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select rounded-pill" id="filterStatus">
                        <option value="semua">📊 Semua Status</option>
                        <option value="Isi">💰 Isi</option>
                        <option value="Kosong">📭 Kosong</option>
                    </select>
                </div>

                <div class="col-6 col-xl-3 col-md-6">
                    <form method="GET" action="" class="d-flex gap-2 align-items-end">
                        <div class="flex-fill">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input 
                                type="date" 
                                name="tanggal" 
                                class="form-control rounded-pill" 
                                value="<?php echo htmlspecialchars($tanggal_filter); ?>"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill" title="Filter tanggal">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                        <?php if(!empty($tanggal_filter)): ?>
                            <a href="jimpitan.php" class="btn btn-outline-secondary rounded-pill" title="Reset tanggal">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="col-6 col-xl-2 col-md-6">
                    <label class="form-label fw-semibold">Urutkan</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary rounded-pill flex-fill" id="sortAscBtn">
                            <i class="bi bi-sort-alpha-up"></i>
                        </button>
                        <button class="btn btn-outline-primary rounded-pill flex-fill" id="sortDescBtn">
                            <i class="bi bi-sort-alpha-down"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 col-xl-2 col-md-6 d-flex justify-content-md-end">
                    <button type="button" class="btn btn-primary-gradient btn-modern w-100" data-bs-toggle="modal" data-bs-target="#modalTambahJimpitan">
                        <i class="bi bi-plus-circle me-2"></i>Tambah
                    </button>
                </div>
            </div>

            <!-- TABEL DATA JIMPITAN -->
            <div class="card-glass">
                <div class="card-header-custom">
                    <h5>
                        <i class="bi bi-table me-2"></i>Daftar Jimpitan Warga
                        <?php if(!empty($tanggal_filter)): ?>
                            <small class="text-muted ms-2">Tanggal <?php echo date('d/m/Y', strtotime($tanggal_filter)); ?></small>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-custom w-100" id="tabelJimpitan">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Warga</th>
                                    <th>No. Rumah</th>
                                    <th width="90">Status</th>
                                    <th class="text-end">Nominal</th>
                                    <th width="100" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if(count($data_jimpitan) > 0): ?>
                                    <?php $no = 1; foreach($data_jimpitan as $j): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($j['tanggal'])); ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($j['nama_warga'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($j['no_rumah'] ?? '-'); ?></td>
                                            <td>
                                                <?php if($j['status'] == 'Isi'): ?>
                                                    <span class="badge-status">💰 Isi</span>
                                                <?php else: ?>
                                                    <span class="badge-status kosong">📭 Kosong</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold <?php echo $j['status'] == 'Isi' ? 'text-success' : 'text-muted'; ?>">
                                                <?php echo $j['status'] == 'Isi' ? '+ ' . formatRupiah($j['nominal']) : '-'; ?>
                                            </td>
                                            <td class="text-center">
                                                <!-- Tombol Edit tetap ada untuk semua status -->
                                                <button type="button" class="btn btn-sm btn-warning rounded-pill me-1" 
                                                        onclick="editJimpitan(<?php echo $j['id_jimpitan']; ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger rounded-pill" 
                                                        onclick="hapusJimpitan(<?php echo $j['id_jimpitan']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Belum ada data jimpitan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH JIMPITAN -->
<div class="modal fade" id="modalTambahJimpitan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #10b981; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Jimpitan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_jimpitan.php" method="POST" id="formTambahJimpitan">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Warga *</label>
                        <select class="form-select rounded-pill" name="id_warga" id="tambah_id_warga" required>
                            <option value="">Pilih warga</option>
                            <?php foreach($data_warga as $w): ?>
                                <option value="<?php echo $w['id_warga']; ?>">
                                    <?php echo htmlspecialchars($w['no_rumah'] . ' - ' . $w['nama_warga']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status *</label>
                        <select class="form-select rounded-pill" name="status" id="tambah_status" required>
                            <option value="">Pilih Status</option>
                            <option value="Isi">💰 Isi (Membayar)</option>
                            <option value="Kosong">📭 Kosong (Belum)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="nominalField">
                        <label class="form-label fw-semibold">Nominal Jimpitan</label>
                        <input type="number" class="form-control rounded-pill" name="nominal" id="tambah_nominal" value="0" min="0">
                        <small class="text-muted">Isi nominal jika status "Isi" (wajib > 0)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal *</label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-save-fill me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT JIMPITAN -->
<div class="modal fade" id="modalEditJimpitan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #f59e0b; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Jimpitan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_jimpitan.php" method="POST" id="formEditJimpitan">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_jimpitan" id="edit_id_jimpitan">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Warga *</label>
                        <select class="form-select rounded-pill" name="id_warga" id="edit_id_warga" required>
                            <option value="">Pilih warga</option>
                            <?php foreach($data_warga as $w): ?>
                                <option value="<?php echo $w['id_warga']; ?>">
                                    <?php echo htmlspecialchars($w['no_rumah'] . ' - ' . $w['nama_warga']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status *</label>
                        <select class="form-select rounded-pill" name="status" id="edit_status" required>
                            <option value="Isi">💰 Isi (Membayar)</option>
                            <option value="Kosong">📭 Kosong (Belum)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editNominalField">
                        <label class="form-label fw-semibold">Nominal Jimpitan</label>
                        <input type="number" class="form-control rounded-pill" name="nominal" id="edit_nominal" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal *</label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" id="edit_tanggal" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                        <i class="bi bi-check-lg me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
// ==============================================
// JAVASCRIPT UNTUK HALAMAN JIMPITAN
// ==============================================

// Set current date
function updateDate() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    if (dateElement) dateElement.innerHTML = now.toLocaleDateString('id-ID', options);
}
updateDate();

// ==============================================
// TOGGLE NOMINAL BERDASARKAN STATUS (TAMBAH)
// Hanya disable field, tidak memaksa nilai 0
// ==============================================
const statusSelect = document.getElementById('tambah_status');
const nominalInput = document.getElementById('tambah_nominal');

function toggleNominalField() {
    if (statusSelect.value === 'Isi') {
        nominalInput.disabled = false;
        nominalInput.required = true;
        nominalInput.placeholder = "Masukkan nominal";
    } else {
        nominalInput.disabled = true;
        nominalInput.required = false;
        nominalInput.placeholder = "Tidak perlu diisi";
        nominalInput.value = '';
    }
}

statusSelect?.addEventListener('change', toggleNominalField);
toggleNominalField();

// ==============================================
// TOGGLE NOMINAL UNTUK EDIT
// ==============================================
function toggleEditNominal() {
    const editStatus = document.getElementById('edit_status');
    const editNominal = document.getElementById('edit_nominal');
    
    if (editStatus.value === 'Isi') {
        editNominal.disabled = false;
        editNominal.required = true;
    } else {
        editNominal.disabled = true;
        editNominal.required = false;
        editNominal.value = '';
    }
}

document.getElementById('edit_status')?.addEventListener('change', toggleEditNominal);

// ==============================================
// EDIT JIMPITAN
// ==============================================
function editJimpitan(id) {
    fetch(`../proses/proses_jimpitan.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            document.getElementById('edit_id_jimpitan').value = data.id_jimpitan;
            document.getElementById('edit_id_warga').value = data.id_warga;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_nominal').value = data.nominal;
            document.getElementById('edit_tanggal').value = data.tanggal;
            
            toggleEditNominal();

            const modal = new bootstrap.Modal(document.getElementById('modalEditJimpitan'));
            modal.show();
        })
        .catch(error => {
            alert('Gagal mengambil data jimpitan');
            console.error(error);
        });
}

// ==============================================
// HAPUS JIMPITAN
// ==============================================
function hapusJimpitan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data jimpitan ini?')) {
        window.location.href = `../proses/proses_jimpitan.php?action=hapus&id_jimpitan=${id}`;
    }
}

// ==============================================
// FILTER (SEARCH + STATUS)
// ==============================================
function filterTable() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const filterStatus = document.getElementById('filterStatus')?.value || 'semua';
    const rows = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        
        const namaWarga = row.cells[2]?.innerText.toLowerCase() || '';
        const noRumah = row.cells[3]?.innerText.toLowerCase() || '';
        const statusCell = row.cells[4]?.innerText || '';
        
        let status = '';
        if (statusCell.includes('Isi')) status = 'Isi';
        else if (statusCell.includes('Kosong')) status = 'Kosong';
        
        const matchSearch = namaWarga.includes(searchValue) || noRumah.includes(searchValue);
        const matchStatus = filterStatus === 'semua' || status === filterStatus;
        
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

document.getElementById('searchInput')?.addEventListener('keyup', filterTable);
document.getElementById('filterStatus')?.addEventListener('change', filterTable);

// ==============================================
// SORTING A-Z / Z-A (Berdasarkan Nomor Rumah)
// ==============================================
let currentSort = 'asc';

function sortTableByNoRumah(order) {
    const tbody = document.querySelector('#tableBody');
    if (!tbody) return;
    
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const dataRows = rows.filter(row => !row.querySelector('td[colspan]'));
    
    dataRows.sort((a, b) => {
        let aValue = a.cells[3]?.innerText || '';
        let bValue = b.cells[3]?.innerText || '';
        
        let aNum = parseInt(aValue);
        let bNum = parseInt(bValue);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            if (order === 'asc') return aNum - bNum;
            else return bNum - aNum;
        }
        
        if (order === 'asc') return aValue.localeCompare(bValue);
        else return bValue.localeCompare(aValue);
    });
    
    dataRows.forEach(row => tbody.appendChild(row));
    updateRowNumbers();
}

function updateRowNumbers() {
    const tbody = document.querySelector('#tableBody');
    if (!tbody) return;
    
    const rows = tbody.querySelectorAll('tr');
    let no = 1;
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const noCell = row.cells[0];
        if (noCell) noCell.innerText = no++;
    });
}

document.getElementById('sortAscBtn')?.addEventListener('click', function() {
    sortTableByNoRumah('asc');
    currentSort = 'asc';
    this.classList.add('active');
    document.getElementById('sortDescBtn')?.classList.remove('active');
});

document.getElementById('sortDescBtn')?.addEventListener('click', function() {
    sortTableByNoRumah('desc');
    currentSort = 'desc';
    this.classList.add('active');
    document.getElementById('sortAscBtn')?.classList.remove('active');
});

// ==============================================
// VALIDASI FORM TAMBAH - VERSI DEBUG
// ==============================================
document.getElementById('formTambahJimpitan')?.addEventListener('submit', function(e) {
    // Ambil nilai
    const warga = document.getElementById('tambah_id_warga').value;
    const status = document.getElementById('tambah_status').value;
    const nominalInput = document.getElementById('tambah_nominal');
    const nominal = nominalInput.value;
    const tanggal = this.querySelector('[name="tanggal"]').value;

    // DEBUG: Tampilkan nilai di console
    console.log('========== DEBUG VALIDASI ==========');
    console.log('Warga ID:', warga);
    console.log('Status:', status);
    console.log('Nominal value:', nominal);
    console.log('Nominal disabled:', nominalInput.disabled);
    console.log('Tanggal:', tanggal);
    console.log('=====================================');

    // Validasi warga
    if (!warga) {
        e.preventDefault();
        alert('Pilih warga terlebih dahulu!');
        return false;
    }

    // Validasi status
    if (!status) {
        e.preventDefault();
        alert('Pilih status terlebih dahulu!');
        return false;
    }

    // Validasi berdasarkan status
    if (status === 'Isi') {
        // Jika status Isi, nominal harus > 0
        if (!nominal || Number(nominal) <= 0) {
            e.preventDefault();
            alert('⚠️ Nominal harus lebih dari 0 untuk status Isi!');
            return false;
        }
    } else if (status === 'Kosong') {
        // Jika status Kosong, nominal TIDAK perlu divalidasi
        console.log('Status Kosong - skip validasi nominal');
        // Kosongkan nilai nominal agar tidak error
        nominalInput.value = '';
    }

    // Validasi tanggal
    if (!tanggal) {
        e.preventDefault();
        alert('Tanggal harus diisi!');
        return false;
    }

    console.log('Validasi BERHASIL, form akan dikirim');
});

// ==============================================
// VALIDASI FORM EDIT (DIPERBAIKI)
// ==============================================
document.getElementById('formEditJimpitan')?.addEventListener('submit', function(e) {
    const status = document.getElementById('edit_status').value;
    const nominal = document.getElementById('edit_nominal').value;
    const tanggal = document.getElementById('edit_tanggal').value;

    if (!tanggal) {
        e.preventDefault();
        alert('Tanggal harus diisi!');
        return false;
    }

    if (status === 'Isi' && (!nominal || nominal <= 0)) {
        e.preventDefault();
        alert('⚠️ Nominal harus lebih dari 0 untuk status Isi!');
        return false;
    }
});

// ==============================================
// SIDEBAR TOGGLE
// ==============================================
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
}
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => sidebar.classList.toggle('active'));
}

// ==============================================
// DARK MODE
// ==============================================
const darkModeToggle = document.getElementById('darkModeToggle');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('kasRT_darkMode', isDark);
        this.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
    });
    
    if (localStorage.getItem('kasRT_darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', function() {
    filterTable();
    updateRowNumbers();
});
</script>
</body>
</html>