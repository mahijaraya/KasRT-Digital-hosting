<?php
// ==============================================
// FILE: pages/pengeluaran.php
// FUNGSI: Halaman untuk mengelola data pengeluaran kas
// AUTHOR: Anggota 2 - Front-End Form dan Halaman Data
// ==============================================

session_start();

// Cek apakah user sudah login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Koneksi ke database (akan diisi Anggota 3)
require_once '../config/koneksi.php';

// ==============================================
// AMBIL DATA PENGELUARAN DARI DATABASE
// ==============================================
$data_pengeluaran = [];

if(isset($conn) && $conn) {
    $query = "SELECT * FROM pengeluaran ORDER BY tanggal DESC, id_pengeluaran DESC";
    $result = mysqli_query($conn, $query);
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data_pengeluaran[] = $row;
        }
    }
}

// Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - Kas RT Digital</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- ============================================== -->
<!-- SIDEBAR / NAVIGASI UTAMA -->
<!-- ============================================== -->
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
            <a href="pengeluaran.php" class="menu-item active">
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
    
    <!-- Main Content -->
    <main class="main-content">
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="page-title">Pengeluaran Kas</h2>
            </div>
            <div class="navbar-right">
                <div class="date-display">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
            </div>
        </nav>
        
        <!-- ============================================== -->
        <!-- KONTEN HALAMAN PENGELUARAN -->
        <!-- ============================================== -->
        <div class="content-wrapper">
            
            <!-- Tombol Tambah Data -->
            <div class="mb-4">
                <button type="button" class="btn btn-primary-gradient btn-modern" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran">
                    <i class="bi bi-cash-minus me-2"></i> Tambah Pengeluaran
                </button>
            </div>
            
            <!-- Form Pencarian dan Filter -->
            <div class="row mb-4 g-3">
                <div class="col-md-5">
                    <div class="input-group rounded-pill shadow-sm bg-white border">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-transparent rounded-pill" 
                               id="searchInput" placeholder="Cari nama pengeluaran atau kategori...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select rounded-pill" id="filterKategori">
                        <option value="semua">📊 Semua Kategori</option>
                        <option value="Kegiatan RT">🎉 Kegiatan RT</option>
                        <option value="Kebersihan">🧹 Kebersihan</option>
                        <option value="Keamanan">🛡️ Keamanan</option>
                        <option value="Pembangunan">🏗️ Pembangunan</option>
                        <option value="Lainnya">📝 Lainnya</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="month" class="form-control rounded-pill" id="filterBulan" placeholder="Filter bulan">
                </div>
            </div>
            
            <!-- Ringkasan Pengeluaran -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card-dashboard stat-card-danger">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Total Pengeluaran</span>
                            <h4 class="stat-value text-danger" id="totalPengeluaran">Rp 0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card-dashboard stat-card-warning">
                        <div class="stat-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Jumlah Transaksi</span>
                            <h4 class="stat-value" id="jumlahTransaksi">0</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabel Data Pengeluaran -->
            <div class="card-glass p-0 overflow-hidden">
                <div class="px-4 py-3 border-bottom" style="background: #f8fafc;">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table me-2" style="color:#2563eb;"></i>
                        Daftar Pengeluaran Kas
                    </h5>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-custom w-100" id="tabelPengeluaran">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Tanggal</th>
                                <th>Nama Pengeluaran</th>
                                <th>Kategori</th>
                                <th class="text-end">Nominal</th>
                                <th>Keterangan</th>
                                <th width="130" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if(count($data_pengeluaran) > 0): ?>
                                <?php $no = 1; foreach($data_pengeluaran as $pengeluaran): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pengeluaran['tanggal'])); ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($pengeluaran['nama_pengeluaran']); ?></td>
                                    <td>
                                        <span class="badge-status kosong">
                                            <?php
                                            $icon = match($pengeluaran['kategori']) {
                                                'Kegiatan RT' => '🎉',
                                                'Kebersihan' => '🧹',
                                                'Keamanan' => '🛡️',
                                                'Pembangunan' => '🏗️',
                                                default => '📝'
                                            };
                                            echo $icon . ' ' . htmlspecialchars($pengeluaran['kategori']);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        - <?php echo formatRupiah($pengeluaran['nominal']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($pengeluaran['keterangan'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill action-btn me-1" 
                                                onclick="editPengeluaran(<?php echo $pengeluaran['id_pengeluaran']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill action-btn" 
                                                onclick="hapusPengeluaran(<?php echo $pengeluaran['id_pengeluaran']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block opacity-50"></i>
                                        Belum ada data pengeluaran. Silakan tambah pengeluaran baru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH PENGELUARAN -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahPengeluaran" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #ef4444; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-minus me-2"></i>Tambah Pengeluaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_pengeluaran.php" method="POST" id="formTambahPengeluaran">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-receipt me-1 text-danger"></i> Nama Pengeluaran *
                        </label>
                        <input type="text" class="form-control rounded-pill" name="nama_pengeluaran" required 
                               placeholder="Contoh: Belanja untuk kegiatan 17 Agustus">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag-fill me-1 text-danger"></i> Kategori *
                        </label>
                        <select class="form-select rounded-pill" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Kegiatan RT">🎉 Kegiatan RT</option>
                            <option value="Kebersihan">🧹 Kebersihan</option>
                            <option value="Keamanan">🛡️ Keamanan</option>
                            <option value="Pembangunan">🏗️ Pembangunan</option>
                            <option value="Lainnya">📝 Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-cash me-1 text-danger"></i> Nominal *
                        </label>
                        <input type="number" class="form-control rounded-pill" name="nominal" required 
                               placeholder="0" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar3 me-1 text-danger"></i> Tanggal *
                        </label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-text me-1 text-danger"></i> Keterangan
                        </label>
                        <textarea class="form-control rounded-4" name="keterangan" rows="2" 
                                  placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-save-fill me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT PENGELUARAN -->
<!-- ============================================== -->
<div class="modal fade" id="modalEditPengeluaran" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #f59e0b; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_pengeluaran.php" method="POST" id="formEditPengeluaran">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_pengeluaran" id="edit_id_pengeluaran">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Pengeluaran *</label>
                        <input type="text" class="form-control rounded-pill" name="nama_pengeluaran" id="edit_nama_pengeluaran" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori *</label>
                        <select class="form-select rounded-pill" name="kategori" id="edit_kategori" required>
                            <option value="Kegiatan RT">🎉 Kegiatan RT</option>
                            <option value="Kebersihan">🧹 Kebersihan</option>
                            <option value="Keamanan">🛡️ Keamanan</option>
                            <option value="Pembangunan">🏗️ Pembangunan</option>
                            <option value="Lainnya">📝 Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nominal *</label>
                        <input type="number" class="form-control rounded-pill" name="nominal" id="edit_nominal" required min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal *</label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" id="edit_tanggal" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea class="form-control rounded-4" name="keterangan" id="edit_keterangan" rows="2"></textarea>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
// ==============================================
// JAVASCRIPT UNTUK HALAMAN PENGELUARAN
// ==============================================

// Set current date
function updateDate() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    if(dateElement) {
        dateElement.innerHTML = now.toLocaleDateString('id-ID', options);
    }
}
updateDate();

// ===== UPDATE RINGKASAN =====
function updateSummary() {
    const rows = document.querySelectorAll('#tableBody tr');
    let total = 0;
    let transaksiCount = 0;
    
    rows.forEach(row => {
        if(row.querySelector('td[colspan]')) return;
        
        const nominalText = row.cells[4]?.innerText || '0';
        const nominal = parseInt(nominalText.replace(/[^0-9]/g, '')) || 0;
        total += nominal;
        transaksiCount++;
    });
    
    document.getElementById('totalPengeluaran').innerText = formatRupiah(total);
    document.getElementById('jumlahTransaksi').innerText = transaksiCount;
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ===== FUNGSI SEARCH DAN FILTER =====
function filterTable() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const filterKategori = document.getElementById('filterKategori')?.value || 'semua';
    const filterBulan = document.getElementById('filterBulan')?.value || '';
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        if(row.querySelector('td[colspan]')) return;
        
        const namaPengeluaran = row.cells[2]?.innerText.toLowerCase() || '';
        const kategori = row.cells[3]?.innerText || '';
        const tanggalText = row.cells[1]?.innerText || '';
        
        const [day, month, year] = tanggalText.split('/');
        const rowDate = year && month ? `${year}-${month.padStart(2, '0')}` : '';
        
        const matchSearch = namaPengeluaran.includes(searchValue) || kategori.toLowerCase().includes(searchValue);
        const matchKategori = filterKategori === 'semua' || kategori.includes(filterKategori);
        const matchBulan = !filterBulan || rowDate === filterBulan;
        
        row.style.display = matchSearch && matchKategori && matchBulan ? '' : 'none';
    });
    
    updateSummary();
}

// Event listeners
document.getElementById('searchInput')?.addEventListener('keyup', filterTable);
document.getElementById('filterKategori')?.addEventListener('change', filterTable);
document.getElementById('filterBulan')?.addEventListener('change', filterTable);

// ===== FUNGSI EDIT PENGELUARAN =====
function editPengeluaran(id) {
    const row = event?.target?.closest('tr');
    if(row) {
        document.getElementById('edit_id_pengeluaran').value = id;
        document.getElementById('edit_nama_pengeluaran').value = row.cells[2]?.innerText || '';
        
        const kategoriText = row.cells[3]?.innerText || '';
        const kategori = kategoriText.replace(/[🎉🧹🛡️🏗️📝]/g, '').trim();
        document.getElementById('edit_kategori').value = kategori;
        
        const nominalText = row.cells[4]?.innerText || '0';
        const nominal = parseInt(nominalText.replace(/[^0-9]/g, '')) || 0;
        document.getElementById('edit_nominal').value = nominal;
        
        const tanggal = row.cells[1]?.innerText || '';
        const [day, month, year] = tanggal.split('/');
        if(year && month && day) {
            document.getElementById('edit_tanggal').value = `${year}-${month}-${day}`;
        }
        
        document.getElementById('edit_keterangan').value = row.cells[5]?.innerText || '';
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditPengeluaran'));
        modal.show();
    }
}

// ===== FUNGSI HAPUS PENGELUARAN =====
function hapusPengeluaran(id) {
    confirmDelete('Apakah Anda yakin ingin menghapus data pengeluaran ini?', function() {
        window.location.href = `../proses/proses_pengeluaran.php?action=hapus&id_pengeluaran=${id}`;
    });
}

// ===== VALIDASI FORM TAMBAH =====
document.getElementById('formTambahPengeluaran')?.addEventListener('submit', function(e) {
    const nama = this.querySelector('[name="nama_pengeluaran"]').value.trim();
    const nominal = this.querySelector('[name="nominal"]').value;
    
    if(nama === '') {
        e.preventDefault();
        showToast('Nama pengeluaran tidak boleh kosong!', 'error');
        return false;
    }
    
    if(nominal <= 0) {
        e.preventDefault();
        showToast('Nominal harus lebih dari 0!', 'error');
        return false;
    }
});

// ===== INITIAL SUMMARY =====
setTimeout(() => {
    updateSummary();
}, 100);

// ===== SIDEBAR TOGGLE =====
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

// Dark mode toggle
const darkModeToggle = document.getElementById('darkModeToggle');
if(darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('kasRT_darkMode', isDark);
        this.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
    });
    
    if(localStorage.getItem('kasRT_darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
}
</script>
</body>
</html>