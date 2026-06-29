<?php
// File: generate_hash.php - Simpan di root folder

$passwords = [
    'sekretaris123' => 'Sekretaris RT',
    'rt123' => 'Ketua RT', 
    'petugas123' => 'Petugas Kas'
];

echo "<h2>Hash Password untuk User Baru</h2>";
echo "<pre>";

foreach($passwords as $password => $nama) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "// $nama (username: " . str_replace('123', '', $password) . ")\n";
    echo "// Password: $password\n";
    echo "INSERT INTO users (nama, username, password, role) VALUES \n";
    echo "('$nama', '" . str_replace('123', '', $password) . "', '$hash', 'bendahara');\n\n";
}

echo "</pre>";
echo "<p><strong>Copy query di atas dan jalankan di phpMyAdmin</strong></p>";
?>