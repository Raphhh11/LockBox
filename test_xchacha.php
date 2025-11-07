<?php
require 'xchacha.php'; // pastikan file ini sama dengan yang dipakai di proyekmu

$password = 'testkey123';       // password untuk enkripsi
$original = 'Kripto Asik 2025'; // pesan yang mau diuji

echo "=== Uji XChaCha20 ===\n";
echo "Pesan asli: $original\n";

// Enkripsi
$cipher_b64 = xchacha_encrypt($original, $password);
echo "Cipher (Base64): $cipher_b64\n\n";

// Dekripsi
$hasil = xchacha_decrypt($cipher_b64, $password);

if ($hasil === false) {
    echo "❌ Dekripsi gagal!\n";
} else {
    echo "✅ Dekripsi berhasil: $hasil\n";
}
?>
