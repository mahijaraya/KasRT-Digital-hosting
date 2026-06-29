KAS RT DIGITAL - VERSI REVISI FOKUS JIMPITAN

Perubahan utama dari versi sebelumnya:
1. Input pemasukan/iuran disederhanakan menjadi input jimpitan saja.
2. File halaman utama untuk input kas masuk: pages/jimpitan.php
3. File proses CRUD jimpitan: proses/proses_jimpitan.php
4. Tabel database yang dipakai untuk kas masuk: jimpitan
5. Menu "Pemasukan / Jimpitan" diganti menjadi "Jimpitan".
6. Dashboard menampilkan Total Jimpitan, Total Pengeluaran, Saldo Akhir, dan Jumlah Warga.
7. Laporan menampilkan rekap Jimpitan dan Pengeluaran.
8. File lama pemasukan.php dan proses_pemasukan.php tetap ada sebagai redirect agar link lama tidak error.

Cara menjalankan:
1. Ekstrak folder kas-rt-digital ke htdocs XAMPP.
2. Buka phpMyAdmin.
3. Buat/import database dari file database/kas_rt_digital.sql.
4. Sesuaikan config/koneksi.php:
   - host: localhost
   - user: root
   - password: kosong atau sesuai MySQL
   - database: kas_rt_digital
   - port: 8111. Jika XAMPP memakai default, ganti menjadi 3306.
5. Jalankan di browser:
   http://localhost/kas-rt-digital/

Akun login:
username: bendahara
password: 12345
