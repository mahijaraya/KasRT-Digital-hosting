<?php
// ==============================================
// FILE: pages/dashboard.php
// FUNGSI: Halaman dashboard utama bendahara
// AUTHOR: Anggota 1 - Front-End Login & Dashboard
// MODIFIED: Anggota 4 - Chart Real Data, Dynamic Years, Modern Cards
// ==============================================

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Koneksi ke database
require_once '../config/koneksi.php';

// ==============================================
// AMBIL DATA DARI DATABASE
// ==============================================

// Query total jimpitan
$total_jimpitan = 0;
$total_pengeluaran = 0;
$saldo_akhir = 0;
$jumlah_warga = 0;
$transaksi_terbaru = [];

// Data untuk chart (jimpitan per bulan)
$chart_data_jimpitan = array_fill(1, 12, 0);
$chart_data_pengeluaran = array_fill(1, 12, 0);
$current_year = date('Y');

// Jika koneksi database tersedia, ambil data real
if (isset($conn) && $conn) {
    // Total jimpitan
    $query_jimpitan = "SELECT COALESCE(SUM(nominal), 0) as total FROM jimpitan";
    $result_jimpitan = mysqli_query($conn, $query_jimpitan);
    if ($result_jimpitan && mysqli_num_rows($result_jimpitan) > 0) {
        $row = mysqli_fetch_assoc($result_jimpitan);
        $total_jimpitan = $row['total'] ?? 0;
    }

    // Total pengeluaran
    $query_pengeluaran = "SELECT COALESCE(SUM(nominal), 0) as total FROM pengeluaran";
    $result_pengeluaran = mysqli_query($conn, $query_pengeluaran);
    if ($result_pengeluaran && mysqli_num_rows($result_pengeluaran) > 0) {
        $row = mysqli_fetch_assoc($result_pengeluaran);
        $total_pengeluaran = $row['total'] ?? 0;
    }

    // Saldo akhir
    $saldo_akhir = $total_jimpitan - $total_pengeluaran;

    // Jumlah warga
    $query_warga = "SELECT COUNT(*) as total FROM warga";
    $result_warga = mysqli_query($conn, $query_warga);
    if ($result_warga && mysqli_num_rows($result_warga) > 0) {
        $row = mysqli_fetch_assoc($result_warga);
        $jumlah_warga = $row['total'] ?? 0;
    }

    // 5 Transaksi jimpitan terbaru
    $query_transaksi = "SELECT j.*, w.nama_warga 
                        FROM jimpitan j 
                        LEFT JOIN warga w ON j.id_warga = w.id_warga 
                        ORDER BY j.tanggal DESC 
                        LIMIT 5";
    $result_transaksi = mysqli_query($conn, $query_transaksi);
    if ($result_transaksi) {
        while ($row = mysqli_fetch_assoc($result_transaksi)) {
            $transaksi_terbaru[] = $row;
        }
    }
    
    // ==============================================
    // DATA CHART JIMPITAN PER BULAN (TAHUN SEKARANG)
    // ==============================================
    $query_chart_jimpitan = "SELECT 
                                MONTH(tanggal) as bulan, 
                                COALESCE(SUM(nominal), 0) as total 
                              FROM jimpitan 
                              WHERE YEAR(tanggal) = $current_year
                              GROUP BY MONTH(tanggal)";
    $result_chart_jimpitan = mysqli_query($conn, $query_chart_jimpitan);
    if ($result_chart_jimpitan) {
        while ($row = mysqli_fetch_assoc($result_chart_jimpitan)) {
            $chart_data_jimpitan[$row['bulan']] = (float)$row['total'];
        }
    }
    
    // ==============================================
    // DATA CHART PENGELUARAN PER BULAN (TAHUN SEKARANG)
    // ==============================================
    $query_chart_pengeluaran = "SELECT 
                                  MONTH(tanggal) as bulan, 
                                  COALESCE(SUM(nominal), 0) as total 
                                FROM pengeluaran 
                                WHERE YEAR(tanggal) = $current_year
                                GROUP BY MONTH(tanggal)";
    $result_chart_pengeluaran = mysqli_query($conn, $query_chart_pengeluaran);
    if ($result_chart_pengeluaran) {
        while ($row = mysqli_fetch_assoc($result_chart_pengeluaran)) {
            $chart_data_pengeluaran[$row['bulan']] = (float)$row['total'];
        }
    }
}

// Konversi ke array untuk JavaScript
$chart_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$chart_values_jimpitan = array_values($chart_data_jimpitan);
$chart_values_pengeluaran = array_values($chart_data_pengeluaran);

// Generate tahun untuk dropdown (2020 sampai 5 tahun ke depan)
$start_year = 2020;
$end_year = date('Y') + 5;
$years = range($end_year, $start_year);

// Format Rupiah
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
    <title>Dashboard - Kas RT Digital</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* ============================================== */
        /* CARD STATISTIK MODERN & SERAGAM */
        /* ============================================== */
        .dashboard-stat-card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 20px 18px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            height: 100%;
            min-height: 120px;
            position: relative;
            overflow: hidden;
        }
        .dashboard-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.15);
        }
        .dashboard-stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .dashboard-stat-info {
            flex: 1;
        }
        .dashboard-stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            display: block;
        }
        .dashboard-stat-value {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
            color: var(--text-dark);
        }
        .dashboard-stat-sub {
            font-size: 11px;
            color: var(--text-gray);
            margin-top: 6px;
            display: block;
        }
        .dashboard-stat-trend {
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .trend-up {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }
        .trend-down {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }
        .trend-neutral {
            background: rgba(100, 116, 139, 0.12);
            color: #64748b;
        }
        /* Warna icon per card */
        .icon-jimpitan { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
        .icon-pengeluaran { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
        .icon-saldo { background: rgba(16, 185, 129, 0.12); color: #10b981; }
        .icon-warga { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        
        /* Dark mode adjustments */
        body.dark-mode .dashboard-stat-card {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        body.dark-mode .dashboard-stat-value {
            color: #f1f5f9;
        }
    </style>
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
                <a href="dashboard.php" class="menu-item active">
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
                    <h2 class="page-title">Dashboard</h2>
                </div>
                <div class="navbar-right">
                    <div class="date-display">
                        <i class="bi bi-calendar3"></i>
                        <span id="currentDate"></span>
                    </div>
                    <!-- ===== TOMBOL DARK MODE ===== -->
                    <button id="darkModeToggle" class="btn btn-sm btn-outline-secondary rounded-pill" style="margin-left: 10px;">
                        <i class="bi bi-moon-fill"></i>
                    </button>
                </div>
            </nav>

            <!-- ============================================== -->
            <!-- KONTEN DASHBOARD - CARD RINGKASAN MODERN -->
            <!-- ============================================== -->
            <div class="content-wrapper">
                <!-- Row Statistik Cards - SERAGAM & MODERN -->
                <div class="row g-4 mb-4">
                    <!-- Card Total Jimpitan -->
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-icon icon-jimpitan">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <span class="dashboard-stat-label">Total Jimpitan</span>
                                <h3 class="dashboard-stat-value"><?php echo formatRupiah($total_jimpitan); ?></h3>
                                <span class="dashboard-stat-trend trend-up">
                                    <i class="bi bi-graph-up"></i> +2.5%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Total Pengeluaran -->
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-icon icon-pengeluaran">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <span class="dashboard-stat-label">Total Pengeluaran</span>
                                <h3 class="dashboard-stat-value"><?php echo formatRupiah($total_pengeluaran); ?></h3>
                                <span class="dashboard-stat-trend trend-down">
                                    <i class="bi bi-graph-down"></i> -1.2%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Saldo Akhir -->
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-icon icon-saldo">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <span class="dashboard-stat-label">Saldo Akhir</span>
                                <h3 class="dashboard-stat-value"><?php echo formatRupiah($saldo_akhir); ?></h3>
                                <span class="dashboard-stat-trend trend-neutral">
                                    <i class="bi bi-dot"></i> Saat Ini
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Jumlah Warga -->
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-icon icon-warga">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <span class="dashboard-stat-label">Jumlah Warga</span>
                                <h3 class="dashboard-stat-value"><?php echo $jumlah_warga; ?></h3>
                                <span class="dashboard-stat-sub">Kepala Keluarga</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row Chart dan Transaksi Terbaru -->
                <div class="row g-4">
                    <!-- Chart Statistik -->
                    <div class="col-xl-6">
                        <div class="card-glass h-100">
                            <div class="card-header-custom">
                                <h5><i class="bi bi-pie-chart-fill me-2"></i> Statistik Kas</h5>
                                <select class="form-select-sm rounded-pill" id="chartType">
                                    <option value="bar">Bar Chart</option>
                                    <option value="doughnut">Doughnut</option>
                                </select>
                            </div>
                            <div class="card-body-custom">
                                <canvas id="kasChart" style="max-height: 300px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Transaksi Terbaru -->
                    <div class="col-xl-6">
                        <div class="card-glass h-100">
                            <div class="card-header-custom">
                                <h5><i class="bi bi-clock-history me-2"></i> Transaksi Terbaru</h5>
                                <a href="jimpitan.php" class="btn btn-sm btn-link">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="card-body-custom p-0">
                                <div class="transaction-list">
                                    <?php if (count($transaksi_terbaru) > 0): ?>
                                        <?php foreach ($transaksi_terbaru as $tr): ?>
                                            <div class="transaction-item">
                                                <div class="transaction-icon bg-success-light">
                                                    <i class="bi bi-cash-stack text-success"></i>
                                                </div>
                                                <div class="transaction-details">
                                                    <div class="transaction-title"><?php echo htmlspecialchars($tr['nama_warga'] ?? 'Warga'); ?></div>
                                                    <div class="transaction-subtitle">Jimpitan</div>
                                                </div>
                                                <div class="transaction-amount text-success">
                                                    + <?php echo formatRupiah($tr['nominal'] ?? 0); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p>Belum ada transaksi</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Bulanan -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card-glass">
                            <div class="card-header-custom">
                                <h5><i class="bi bi-calendar-week me-2"></i> Statistik Jimpitan per Bulan</h5>
                                <select class="form-select-sm rounded-pill" id="yearSelect">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>" <?php echo $year == $current_year ? 'selected' : ''; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="card-body-custom">
                                <canvas id="monthlyChart" style="max-height: 300px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
        // ==============================================
        // JAVASCRIPT UNTUK DASHBOARD
        // Fungsi: Chart, date display, sidebar toggle, dark mode
        // ==============================================

        // Data dari PHP untuk chart
        const chartDataJimpitan = <?php echo json_encode($chart_values_jimpitan); ?>;
        const chartDataPengeluaran = <?php echo json_encode($chart_values_pengeluaran); ?>;
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        let currentYear = <?php echo $current_year; ?>;

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
        // DASHBOARD CHART (Pie/Bar)
        // ==============================================
        let kasChart = null;

        function initChart() {
            const ctx = document.getElementById('kasChart').getContext('2d');
            const chartType = document.getElementById('chartType').value;

            if (kasChart) kasChart.destroy();

            kasChart = new Chart(ctx, {
                type: chartType === 'bar' ? 'bar' : 'doughnut',
                data: {
                    labels: ['Jimpitan', 'Pengeluaran', 'Saldo'],
                    datasets: [{
                        label: 'Nominal (Rp)',
                        data: [
                            <?php echo $total_jimpitan; ?>,
                            <?php echo $total_pengeluaran; ?>,
                            <?php echo $saldo_akhir; ?>
                        ],
                        backgroundColor: chartType === 'bar' ? ['#10b981', '#ef4444', '#3b82f6'] : ['#10b981', '#ef4444', '#3b82f6'],
                        borderColor: '#fff',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.raw;
                                    return label + ': Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        document.getElementById('chartType')?.addEventListener('change', function() {
            initChart();
        });

        // ==============================================
        // MONTHLY CHART (Bar Chart dengan Data Real)
        // ==============================================
        let monthlyChart = null;

        function initMonthlyChart() {
            const ctx = document.getElementById('monthlyChart').getContext('2d');

            if (monthlyChart) monthlyChart.destroy();

            monthlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Jimpitan',
                        data: chartDataJimpitan,
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.65
                    }, {
                        label: 'Pengeluaran',
                        data: chartDataPengeluaran,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.65
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 12, weight: 'bold' },
                                usePointStyle: true,
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.raw;
                                    return label + ': Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ==============================================
        // UPDATE CHART BERDASARKAN TAHUN (AJAX)
        // ==============================================
        function updateChartByYear(year) {
            if (typeof showLoading === 'function') showLoading();
            
            fetch(`../proses/proses_dashboard.php?action=get_chart_data&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    if (monthlyChart) {
                        monthlyChart.data.datasets[0].data = data.jimpitan;
                        monthlyChart.data.datasets[1].data = data.pengeluaran;
                        monthlyChart.update();
                    }
                    if (typeof hideLoading === 'function') hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof hideLoading === 'function') hideLoading();
                });
        }

        // Event listener untuk perubahan tahun
        document.getElementById('yearSelect')?.addEventListener('change', function() {
            const selectedYear = this.value;
            updateChartByYear(selectedYear);
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

        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // ==============================================
        // DARK MODE
        // ==============================================
        function initDarkMode() {
            const isDark = localStorage.getItem('kasRT_darkMode') === 'true';
            if (isDark) {
                document.body.classList.add('dark-mode');
                const darkBtn = document.getElementById('darkModeToggle');
                if (darkBtn) darkBtn.innerHTML = '<i class="bi bi-sun-fill"></i>';
            }
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('kasRT_darkMode', isDark);

            const darkBtn = document.getElementById('darkModeToggle');
            if (darkBtn) {
                darkBtn.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
            }
        }

        // ==============================================
        // INITIALIZE ALL
        // ==============================================
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            initMonthlyChart();
            initDarkMode();

            const darkBtn = document.getElementById('darkModeToggle');
            if (darkBtn) {
                darkBtn.addEventListener('click', toggleDarkMode);
            }
        });
    </script>
</body>

</html>