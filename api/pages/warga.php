<?php
// ==============================================
// FILE: pages/warga.php
// FUNGSI: Halaman untuk mengelola data warga RT
// AUTHOR: Anggota 2 - Front-End Form dan Halaman Data
// ==============================================

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Koneksi ke database (akan diisi Anggota 3)
require_once '../config/koneksi.php';

// ==============================================
// AMBIL DATA WARGA DARI DATABASE
// ==============================================
$data_warga = [];

if (isset($conn) && $conn) {
    $query = "SELECT * FROM warga ORDER BY LENGTH(no_rumah), no_rumah ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_warga[] = $row;
        }
    }
}

// Format Rupiah helper
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Warga - Kas RT Digital</title>

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
        <!-- Sidebar -->
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
                <a href="warga.php" class="menu-item active">
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
            <!-- Top Navbar -->
            <nav class="top-navbar">
                <div class="navbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2 class="page-title">Data Warga</h2>
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

            <!-- ============================================== -->
            <!-- KONTEN HALAMAN DATA WARGA -->
            <!-- ============================================== -->
            <div class="content-wrapper">

                <!-- Tombol Tambah Data -->
                <div class="mb-4">
                    <button type="button" class="btn btn-primary-gradient btn-modern" data-bs-toggle="modal" data-bs-target="#modalTambahWarga">
                        <i class="bi bi-person-plus-fill me-2"></i> Tambah Warga Baru
                    </button>
                </div>

                <div class="row mb-4 g-3 align-items-end">
                    <!-- Kolom Pencarian -->
                    <div class="col-12 col-md-5">
                        <div class="input-group rounded-pill shadow-sm bg-white border">
                            <span class="input-group-text bg-transparent border-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-0 bg-transparent rounded-pill"
                                id="searchInput" placeholder="Cari nama warga atau nomor rumah...">
                        </div>
                    </div>

                    <!-- Kolom Filter Status + Tombol Sorting (sejajar di desktop) -->
                    <div class="col-12 col-md-7">
                        <div class="row g-2">
                            <!-- Filter Status -->
                            <div class="col-12 col-sm-6">
                                <select class="form-select rounded-pill" id="filterStatus">
                                    <option value="semua">📊 Semua Status</option>
                                    <option value="Aktif">✅ Aktif</option>
                                    <option value="Tidak Aktif">❌ Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Tombol Sorting -->
                            <div class="col-12 col-sm-6">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary rounded-pill flex-fill" id="sortAscBtn">
                                        <i class="bi bi-sort-alpha-up"></i> A→Z
                                    </button>
                                    <button class="btn btn-outline-primary rounded-pill flex-fill" id="sortDescBtn">
                                        <i class="bi bi-sort-alpha-down"></i> Z→A
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Data Warga -->
                <div class="card-glass p-0 overflow-hidden">
                    <div class="px-4 py-3 border-bottom" style="background: #f8fafc;">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-table me-2" style="color:#2563eb;"></i>
                            Daftar Warga RT
                        </h5>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-custom w-100" id="tabelWarga">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Warga</th>
                                    <th width="120">Nomor Rumah</th>
                                    <th width="150">Nomor HP</th>
                                    <th width="100">Status</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if (count($data_warga) > 0): ?>
                                    <?php $no = 1;
                                    foreach ($data_warga as $warga): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($warga['nama_warga']); ?></td>
                                            <td><?php echo htmlspecialchars($warga['no_rumah']); ?></td>
                                            <td><?php echo htmlspecialchars($warga['no_hp']); ?></td>
                                            <td>
                                                <span class="badge-status <?php echo $warga['status'] == 'Aktif' ? '' : 'kosong'; ?>">
                                                    <?php echo $warga['status'] == 'Aktif' ? '✅ Aktif' : '❌ Tidak Aktif'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill action-btn me-1"
                                                    onclick="editWarga(<?php echo $warga['id_warga']; ?>)">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill action-btn"
                                                    onclick="hapusWarga(<?php echo $warga['id_warga']; ?>, '<?php echo htmlspecialchars($warga['nama_warga']); ?>')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block opacity-50"></i>
                                            Belum ada data warga. Silakan tambah warga baru.
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
    <!-- MODAL TAMBAH WARGA -->
    <!-- ============================================== -->
    <div class="modal fade" id="modalTambahWarga" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header" style="background: #2563eb; color: white; border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Warga Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../proses/proses_warga.php" method="POST" id="formTambahWarga">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-fill me-1 text-primary"></i> Nama Warga *
                            </label>
                            <input type="text" class="form-control rounded-pill" name="nama_warga" required
                                placeholder="Contoh: Bapak Slamet Riyadi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-house-fill me-1 text-primary"></i> Nomor Rumah *
                            </label>
                            <input type="text" class="form-control rounded-pill" name="no_rumah" required
                                placeholder="Contoh: 01, 02A, 15B">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-phone-fill me-1 text-primary"></i> Nomor HP
                            </label>
                            <input type="tel" class="form-control rounded-pill" name="no_hp"
                                placeholder="Contoh: 081234567890">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-check-circle-fill me-1 text-primary"></i> Status *
                            </label>
                            <select class="form-select rounded-pill" name="status" required>
                                <option value="Aktif">✅ Aktif</option>
                                <option value="Tidak Aktif">❌ Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-gradient rounded-pill px-4">
                            <i class="bi bi-save-fill me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL EDIT WARGA -->
    <!-- ============================================== -->
    <div class="modal fade" id="modalEditWarga" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header" style="background: #f59e0b; color: white; border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Edit Data Warga
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../proses/proses_warga.php" method="POST" id="formEditWarga">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_warga" id="edit_id_warga">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Warga *</label>
                            <input type="text" class="form-control rounded-pill" name="nama_warga" id="edit_nama_warga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Rumah *</label>
                            <input type="text" class="form-control rounded-pill" name="no_rumah" id="edit_no_rumah" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor HP</label>
                            <input type="tel" class="form-control rounded-pill" name="no_hp" id="edit_no_hp">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Status *</label>
                            <select class="form-select rounded-pill" name="status" id="edit_status" required>
                                <option value="Aktif">✅ Aktif</option>
                                <option value="Tidak Aktif">❌ Tidak Aktif</option>
                            </select>
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
        // JAVASCRIPT UNTUK HALAMAN DATA WARGA
        // Fungsi: Filter, search, edit, hapus, sorting
        // ==============================================

        // Set current date
        function updateDate() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateElement = document.getElementById('currentDate');
            if (dateElement) {
                dateElement.innerHTML = now.toLocaleDateString('id-ID', options);
            }
        }
        updateDate();

        // ==============================================
        // FUNGSI FILTER PENCARIAN & STATUS (FIX)
        // ==============================================
        function filterTable() {
            const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
            const filterStatus = document.getElementById('filterStatus')?.value || 'semua';
            const rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                // Skip baris jika tidak memiliki data (misalnya pesan "belum ada data")
                if (row.querySelector('td[colspan]')) return;

                // Ambil data dari setiap kolom
                const namaWarga = row.cells[1]?.innerText.toLowerCase() || '';
                const noRumah = row.cells[2]?.innerText.toLowerCase() || '';
                const statusCell = row.cells[4]?.innerText || '';

                // Ambil status dari badge (bersihkan dari icon/emoji)
                let status = '';
                if (statusCell.includes('✅ Aktif')) {
                    status = 'Aktif';
                } else if (statusCell.includes('❌ Tidak Aktif')) {
                    status = 'Tidak Aktif';
                } else {
                    status = statusCell.trim();
                }

                // Cek apakah sesuai pencarian
                const matchSearch = namaWarga.includes(searchValue) || noRumah.includes(searchValue);

                // Cek apakah sesuai filter status
                const matchStatus = filterStatus === 'semua' || status === filterStatus;

                // Tampilkan atau sembunyikan baris
                if (matchSearch && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // ==============================================
        // FUNGSI SORTING DATA WARGA (A-Z / Z-A)
        // Berdasarkan Nomor Rumah
        // ==============================================

        let currentSort = 'asc';

        function sortTableByNoRumah(order) {
            const tbody = document.querySelector('#tableBody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            const dataRows = rows.filter(row => !row.querySelector('td[colspan]'));

            dataRows.sort((a, b) => {
                let aValue = a.cells[2]?.innerText || '';
                let bValue = b.cells[2]?.innerText || '';

                if (order === 'asc') {
                    if (aValue.length !== bValue.length) {
                        return aValue.length - bValue.length;
                    }
                    return aValue.localeCompare(bValue, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                } else {
                    if (aValue.length !== bValue.length) {
                        return bValue.length - aValue.length;
                    }
                    return bValue.localeCompare(aValue, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }
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
                if (noCell) {
                    noCell.innerText = no++;
                }
            });
        }

        // ==============================================
        // EVENT LISTENERS
        // ==============================================
        
        // Search dan Filter
        document.getElementById('searchInput')?.addEventListener('keyup', filterTable);
        document.getElementById('filterStatus')?.addEventListener('change', filterTable);

        // Tombol Sorting
        document.getElementById('sortAscBtn')?.addEventListener('click', function() {
            sortTableByNoRumah('asc');
            currentSort = 'asc';
            document.getElementById('sortAscBtn')?.classList.add('active');
            document.getElementById('sortDescBtn')?.classList.remove('active');
        });

        document.getElementById('sortDescBtn')?.addEventListener('click', function() {
            sortTableByNoRumah('desc');
            currentSort = 'desc';
            document.getElementById('sortDescBtn')?.classList.add('active');
            document.getElementById('sortAscBtn')?.classList.remove('active');
        });

        // ==============================================
        // FUNGSI EDIT WARGA
        // ==============================================
        function editWarga(id) {
            const row = event?.target?.closest('tr');
            if (row) {
                document.getElementById('edit_id_warga').value = id;
                document.getElementById('edit_nama_warga').value = row.cells[1]?.innerText || '';
                document.getElementById('edit_no_rumah').value = row.cells[2]?.innerText || '';
                document.getElementById('edit_no_hp').value = row.cells[3]?.innerText || '';
                const statusText = row.cells[4]?.innerText || '';
                const status = statusText.includes('✅ Aktif') ? 'Aktif' : 'Tidak Aktif';
                document.getElementById('edit_status').value = status;

                const modal = new bootstrap.Modal(document.getElementById('modalEditWarga'));
                modal.show();
            }
        }

        // ==============================================
        // FUNGSI HAPUS WARGA
        // ==============================================
        function hapusWarga(id, nama) {
            confirmDelete(`Apakah Anda yakin ingin menghapus warga "${nama}"? Data yang terkait dengan warga ini juga akan terpengaruh.`, function() {
                window.location.href = `../proses/proses_warga.php?action=hapus&id_warga=${id}`;
            });
        }

        // ==============================================
        // VALIDASI FORM TAMBAH
        // ==============================================
        document.getElementById('formTambahWarga')?.addEventListener('submit', function(e) {
            const nama = this.querySelector('[name="nama_warga"]').value.trim();
            const noRumah = this.querySelector('[name="no_rumah"]').value.trim();

            if (nama === '') {
                e.preventDefault();
                showToast('Nama warga tidak boleh kosong!', 'error');
                return false;
            }

            if (noRumah === '') {
                e.preventDefault();
                showToast('Nomor rumah tidak boleh kosong!', 'error');
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
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // ==============================================
        // DARK MODE TOGGLE
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

        // ==============================================
        // INITIALIZE (update nomor urut & filter default)
        // ==============================================
        document.addEventListener('DOMContentLoaded', function() {
            updateRowNumbers();
            // Pastikan filter berfungsi saat halaman dimuat
            filterTable();
        });
    </script>
</body>

</html>