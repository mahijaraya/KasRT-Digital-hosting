<?php
// ==============================================
// FILE: proses/proses_dashboard.php
// FUNGSI: Mengambil data chart untuk dashboard (AJAX)
// AUTHOR: Anggota 4 - Developer Laporan, JavaScript, dan Integrasi
// ==============================================

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/koneksi.php';

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_chart_data') {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    
    // Data jimpitan per bulan
    $data_jimpitan = array_fill(0, 12, 0);
    $query_jimpitan = "SELECT 
                            MONTH(tanggal) as bulan, 
                            COALESCE(SUM(nominal), 0) as total 
                        FROM jimpitan 
                        WHERE YEAR(tanggal) = $year
                        GROUP BY MONTH(tanggal)";
    $result_jimpitan = mysqli_query($conn, $query_jimpitan);
    if ($result_jimpitan) {
        while ($row = mysqli_fetch_assoc($result_jimpitan)) {
            $data_jimpitan[$row['bulan'] - 1] = (float)$row['total'];
        }
    }
    
    // Data pengeluaran per bulan
    $data_pengeluaran = array_fill(0, 12, 0);
    $query_pengeluaran = "SELECT 
                              MONTH(tanggal) as bulan, 
                              COALESCE(SUM(nominal), 0) as total 
                          FROM pengeluaran 
                          WHERE YEAR(tanggal) = $year
                          GROUP BY MONTH(tanggal)";
    $result_pengeluaran = mysqli_query($conn, $query_pengeluaran);
    if ($result_pengeluaran) {
        while ($row = mysqli_fetch_assoc($result_pengeluaran)) {
            $data_pengeluaran[$row['bulan'] - 1] = (float)$row['total'];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'jimpitan' => $data_jimpitan,
        'pengeluaran' => $data_pengeluaran,
        'year' => $year
    ]);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit();
?>