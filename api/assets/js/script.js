// ==============================================
// FILE: assets/js/script.js
// FUNGSI: JavaScript Global untuk seluruh website
// AUTHOR: Anggota 1 & 4 - Dashboard & Integrasi
// ==============================================

// ===== GLOBAL VARIABLES =====
let darkModeEnabled = false;

// ===== DARK MODE FUNCTIONALITY =====
function initDarkMode() {
    const savedDarkMode = localStorage.getItem('kasRT_darkMode');
    if (savedDarkMode === 'true') {
        enableDarkMode();
    }
    
    // Cek apakah ada tombol dark mode di halaman
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', toggleDarkMode);
    }
}

function toggleDarkMode() {
    if (document.body.classList.contains('dark-mode')) {
        disableDarkMode();
    } else {
        enableDarkMode();
    }
}

function enableDarkMode() {
    document.body.classList.add('dark-mode');
    localStorage.setItem('kasRT_darkMode', 'true');
    
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
}

function disableDarkMode() {
    document.body.classList.remove('dark-mode');
    localStorage.setItem('kasRT_darkMode', 'false');
    
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.innerHTML = '<i class="bi bi-moon-fill"></i>';
    }
}

// ===== LOADING SPINNER =====
function showLoading() {
    let loader = document.getElementById('globalLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'globalLoader';
        loader.innerHTML = `
            <div class="loader-overlay">
                <div class="loader-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(loader);
        
        // Tambahkan style untuk loader
        const style = document.createElement('style');
        style.textContent = `
            .loader-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .loader-spinner {
                background: white;
                padding: 20px;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            body.dark-mode .loader-spinner {
                background: #1e293b;
            }
        `;
        document.head.appendChild(style);
    }
    loader.style.display = 'flex';
}

function hideLoading() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// ===== TOAST NOTIFICATION =====
function showToast(message, type = 'success') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9998;
        `;
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast_' + Date.now();
    const bgColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
    const icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill';
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast-notification';
    toast.style.cssText = `
        background: white;
        border-left: 4px solid ${bgColor};
        border-radius: 12px;
        padding: 12px 20px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInRight 0.3s ease;
        min-width: 250px;
    `;
    toast.innerHTML = `
        <i class="bi bi-${icon}" style="color: ${bgColor}; font-size: 20px;"></i>
        <span style="flex: 1; color: #333;">${message}</span>
        <button class="btn-close" style="font-size: 12px;" onclick="this.parentElement.remove()"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const toastEl = document.getElementById(toastId);
        if (toastEl) toastEl.remove();
    }, 3000);
}

// ===== ANIMATION STYLES =====
const animationStyle = document.createElement('style');
animationStyle.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .fade-in-up {
        animation: fadeInUp 0.4s ease forwards;
    }
`;
document.head.appendChild(animationStyle);

// ===== CONFIRM DELETE =====
function confirmDelete(message, callback) {
    // Buat modal konfirmasi
    let modalContainer = document.getElementById('confirmModal');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'confirmModal';
        modalContainer.className = 'modal fade';
        modalContainer.setAttribute('tabindex', '-1');
        modalContainer.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Konfirmasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <p id="confirmMessage" class="mb-0">Apakah Anda yakin?</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="confirmYesBtn" class="btn btn-danger rounded-pill">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalContainer);
    }
    
    document.getElementById('confirmMessage').innerText = message;
    
    const modal = new bootstrap.Modal(modalContainer);
    const confirmBtn = document.getElementById('confirmYesBtn');
    
    const handleConfirm = () => {
        modal.hide();
        confirmBtn.removeEventListener('click', handleConfirm);
        if (callback) callback();
    };
    
    confirmBtn.addEventListener('click', handleConfirm);
    modal.show();
}

// ===== FORMAT RUPIAH =====
function formatRupiah(angka) {
    if (angka === undefined || angka === null) angka = 0;
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ===== PARSE RUPIAH TO NUMBER =====
function parseRupiahToNumber(rupiahString) {
    return parseInt(rupiahString.replace(/[^0-9]/g, '')) || 0;
}

// ===== VALIDATION FUNCTIONS =====
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10,13}$/;
    return re.test(phone);
}

function isRequired(value) {
    return value !== null && value !== undefined && value.toString().trim() !== '';
}

// ===== SIDEBAR TOGGLE FOR MOBILE =====
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
}

// ===== AUTO REFRESH DATA (untuk dashboard real-time) =====
let autoRefreshInterval = null;

function startAutoRefresh(interval = 30000) {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
        if (!document.hidden) {
            refreshDashboardData();
        }
    }, interval);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function refreshDashboardData() {
    // Function ini akan dioverride oleh halaman dashboard
    if (typeof window.refreshDashboard === 'function') {
        window.refreshDashboard();
    }
}

// ===== EXPORT FUNCTIONS =====
function exportToExcel(tableId, filename = 'export_data') {
    const table = document.getElementById(tableId);
    if (!table) {
        showToast('Tabel tidak ditemukan!', 'error');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let text = cols[j].innerText.replace(/,/g, ';');
            row.push(text);
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = `${filename}_${new Date().toISOString().slice(0, 19)}.csv`;
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.click();
    URL.revokeObjectURL(downloadLink.href);
    
    showToast('Data berhasil diexport ke Excel!');
}

// ===== PRINT FUNCTION =====
function printElement(elementId, title = 'Laporan Kas RT') {
    const printContent = document.getElementById(elementId);
    if (!printContent) {
        showToast('Konten tidak ditemukan!', 'error');
        return;
    }
    
    const originalTitle = document.title;
    document.title = title;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Inter', sans-serif;
                    padding: 30px;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #2563eb;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h2>${title}</h2>
                <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}</p>
            </div>
            ${printContent.outerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
    
    document.title = originalTitle;
}

// ===== INITIALIZE ON PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    initDarkMode();
    initSidebar();
    
    // Add fade-in animation to cards
    document.querySelectorAll('.card-glass, .stat-card-dashboard').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.05}s`;
        card.classList.add('fade-in-up');
    });
});

// ===== MAKE FUNCTIONS GLOBAL =====
window.formatRupiah = formatRupiah;
window.showToast = showToast;
window.confirmDelete = confirmDelete;
window.exportToExcel = exportToExcel;
window.printElement = printElement;
window.showLoading = showLoading;
window.hideLoading = hideLoading;

// ==============================================
// TAMBAHAN UNTUK ANGGOTA 4
// FITUR: Pencarian Tabel, Validasi Form, Auto Hide Alerts
// ==============================================

// ===== 1. FUNGSI PENCARIAN TABEL (UNIVERSAL) =====
function initTableSearch(inputId, tableId, columnIndex = 1) {
    const searchInput = document.getElementById(inputId);
    if(!searchInput) return;
    
    searchInput.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const table = document.getElementById(tableId);
        if(!table) return;
        
        const tbody = table.getElementsByTagName('tbody')[0];
        if(!tbody) return;
        
        const rows = tbody.getElementsByTagName('tr');
        
        for(let i = 0; i < rows.length; i++) {
            const row = rows[i];
            // Skip jika baris adalah "tidak ada data"
            if(row.querySelector('td[colspan]')) continue;
            
            const cells = row.getElementsByTagName('td');
            if(cells.length > columnIndex) {
                const cellText = cells[columnIndex].textContent.toLowerCase();
                if(cellText.indexOf(searchValue) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });
}

// ===== 2. AUTO HIDE ALERTS (Pesan sukses/error otomatis hilang) =====
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                if(alert.parentNode) alert.remove();
            }, 500);
        }, 3000);
    });
}

// ===== 3. VALIDASI NOMINAL (Input angka positif) =====
function initNominalValidation() {
    const nominalInputs = document.querySelectorAll('input[type="number"][name="nominal"]');
    nominalInputs.forEach(input => {
        input.addEventListener('change', function() {
            if(this.value < 0) this.value = 0;
        });
        input.addEventListener('input', function() {
            if(this.value < 0) this.value = 0;
        });
    });
}

// ===== 4. VALIDASI NOMOR HP =====
function initPhoneValidation() {
    const phoneInputs = document.querySelectorAll('input[name="no_hp"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if(this.value.length > 13) this.value = this.value.slice(0, 13);
        });
    });
}

// ===== 5. VALIDASI FORM TAMBAH WARGA =====
function initWargaFormValidation() {
    const form = document.getElementById('formTambahWarga');
    if(!form) return;
    
    form.addEventListener('submit', function(e) {
        const namaWarga = document.querySelector('[name="nama_warga"]')?.value.trim();
        const noRumah = document.querySelector('[name="no_rumah"]')?.value.trim();
        
        if(!namaWarga) {
            e.preventDefault();
            alert('⚠️ Nama Warga tidak boleh kosong!');
            return false;
        }
        
        if(!noRumah) {
            e.preventDefault();
            alert('⚠️ Nomor Rumah tidak boleh kosong!');
            return false;
        }
    });
}

// ===== 6. VALIDASI FORM TAMBAH JIMPITAN =====
function initJimpitanFormValidation() {
    const form = document.getElementById('formTambahJimpitan');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const idWarga = document.querySelector('[name="id_warga"]')?.value;
        const status = document.querySelector('[name="status"]')?.value;
        const nominal = document.querySelector('[name="nominal"]')?.value;

        if (!idWarga) {
            e.preventDefault();
            alert('⚠️ Pilih Warga terlebih dahulu!');
            return false;
        }

        if (!status) {
            e.preventDefault();
            alert('⚠️ Pilih status terlebih dahulu!');
            return false;
        }

        if (status === 'Isi' && (!nominal || Number(nominal) <= 0)) {
            e.preventDefault();
            alert('⚠️ Nominal harus lebih dari 0 untuk status Isi!');
            return false;
        }
    });
}

// ===== 7. VALIDASI FORM TAMBAH PENGELUARAN =====
function initPengeluaranFormValidation() {
    const form = document.getElementById('formTambahPengeluaran');
    if(!form) return;
    
    form.addEventListener('submit', function(e) {
        const namaPengeluaran = document.querySelector('[name="nama_pengeluaran"]')?.value.trim();
        const kategori = document.querySelector('[name="kategori"]')?.value;
        const nominal = document.querySelector('[name="nominal"]')?.value;
        
        if(!namaPengeluaran) {
            e.preventDefault();
            alert('⚠️ Nama Pengeluaran tidak boleh kosong!');
            return false;
        }
        
        if(!kategori) {
            e.preventDefault();
            alert('⚠️ Pilih Kategori!');
            return false;
        }
        
        if(!nominal || nominal <= 0) {
            e.preventDefault();
            alert('⚠️ Nominal harus lebih dari 0!');
            return false;
        }
    });
}

// ===== 8. INISIALISASI SEMUA FITUR =====
document.addEventListener('DOMContentLoaded', function() {
    // Auto hide alerts
    initAutoHideAlerts();
    
    // Validasi nominal dan nomor HP
    initNominalValidation();
    initPhoneValidation();
    
    // Validasi form
    initWargaFormValidation();
    initJimpitanFormValidation();
    initPengeluaranFormValidation();
   
});