<?php
// ==============================================
// FILE: login.php
// FUNGSI: Halaman login untuk Bendahara RT
// AUTHOR: Anggota 1 - Front-End Login & Dashboard
// ==============================================

session_start();

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: pages/dashboard.php");
    exit();
}

// Cek apakah ada pesan error dari proses login
$error = '';
if(isset($_GET['error'])) {
    if($_GET['error'] == 'invalid') {
        $error = 'Username atau password salah!';
    } elseif($_GET['error'] == 'required') {
        $error = 'Harap isi username dan password!';
    } elseif($_GET['error'] == 'session') {
        $error = 'Sesi login berakhir, silakan login kembali.';
    }
}

// Cek apakah ada pesan sukses logout
$success = '';
if(isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success = 'Anda berhasil logout.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kas RT Digital</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* CSS Khusus Halaman Login (tambahan dari style.css) */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(115deg, #1e3a8a 0%, #2563eb 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .login-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .login-body {
            padding: 40px 35px;
        }
        
        .form-control-custom {
            border-radius: 60px;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .form-control-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(95deg, #2563eb, #1e40af);
            border: none;
            border-radius: 60px;
            padding: 12px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.2s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }
        
        .rt-info {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header Login -->
            <div class="login-header">
                <i class="bi bi-house-door-fill" style="font-size: 48px;"></i>
                <h1>Kas RT Digital</h1>
                <p>Sistem Pengelolaan Kas RT</p>
            </div>
            
            <!-- Body Login -->
            <div class="login-body">
                <!-- Pesan Error/Sukses -->
                <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-pill" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-pill" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Form Login -->
                <form action="proses/proses_login.php" method="POST" id="loginForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person-circle me-1"></i> Username
                        </label>
                        <input type="text" 
                               name="username" 
                               id="username"
                               class="form-control form-control-custom" 
                               placeholder="Masukkan username"
                               autocomplete="off"
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1"></i> Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control form-control-custom" 
                                   placeholder="Masukkan password"
                                   required>
                            <button type="button" 
                                    class="btn btn-outline-secondary rounded-pill" 
                                    id="togglePassword"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login w-100 text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Dashboard
                    </button>
                </form>
                
                <div class="rt-info">
                    <i class="bi bi-building"></i> Sistem Informasi Kas RT<br>
                    Data tersimpan aman dan terenkripsi
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ==============================================
        // JAVASCRIPT UNTUK HALAMAN LOGIN
        // Fungsi: Toggle password visibility
        // ==============================================
        
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
        
        // Validasi form sebelum submit (JavaScript - tambahan keamanan)
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if(username === '') {
                e.preventDefault();
                alert('Username tidak boleh kosong!');
                return false;
            }
            
            if(password === '') {
                e.preventDefault();
                alert('Password tidak boleh kosong!');
                return false;
            }
        });
    </script>
</body>
</html>