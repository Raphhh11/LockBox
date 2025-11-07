<?php
require 'db.php';
require 'playfair.php';
require 'xchacha.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Unknown';

// Ambil data dari form
$pesan          = trim($_POST['pesan'] ?? '');
$kunci_playfair = trim($_POST['kunci_playfair'] ?? '');
$kunci_modern   = trim($_POST['kunci_modern'] ?? '');

// Validasi input
if ($pesan === '' || $kunci_playfair === '' || $kunci_modern === '') {
    die('Semua field wajib diisi.');
}

// 1. Enkripsi tahap pertama: Playfair Cipher (klasik)
$encrypted_playfair = playfair_encrypt($pesan, $kunci_playfair);

// 2. Enkripsi tahap kedua: XChaCha20 (modern, dengan libsodium)
$encrypted_final = xchacha_encrypt($encrypted_playfair, $kunci_modern);

// Validasi hasil enkripsi
if ($encrypted_final === false || $encrypted_final === null) {
    die('Gagal mengenkripsi pesan. Periksa konfigurasi libsodium.');
}

// 3. Simpan hasil enkripsi ke database (pastikan kolom ciphertext tipe TEXT atau MEDIUMTEXT)
$stmt = $pdo->prepare("INSERT INTO messages (user_id, ciphertext, created_at) VALUES (?, ?, NOW())");
$stmt->execute([$user_id, $encrypted_final]);

// 4. Redirect ke halaman daftar pesan
header('Location: pesan.php');
exit;
?>
