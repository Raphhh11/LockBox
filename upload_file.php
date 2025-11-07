<?php
// upload_file.php
require 'db.php';
require 'kripto_aes.php'; // Panggil helper AES-GCM kita

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['file_upload'] ?? null;
$password = $_POST['password'] ?? null;

// Validasi
if (!$file || $file['error'] !== UPLOAD_ERR_OK || empty($password)) {
    header('Location: files.php?error=Form tidak lengkap');
    exit;
}

// 1. Baca konten file asli
$plaintext = file_get_contents($file['tmp_name']);
$original_filename = $file['name'];

// 2. Buat HASH (Integritas) dari file ASLI
// Ini adalah implementasi SHA-256
$hash_sha256 = hash('sha256', $plaintext);

// 3. Enkripsi konten file (AES-256-GCM)
$ciphertext = aes_gcm_encrypt($plaintext, $password);
if ($ciphertext === false) {
    header('Location: files.php?error=Enkripsi gagal');
    exit;
}

// 4. Siapkan file untuk disimpan
$stored_filename = $user_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.enc';
$output_path = 'uploads/' . $stored_filename;

// 5. Simpan file terenkripsi ke disk
if (file_put_contents($output_path, $ciphertext) === false) {
    header('Location: files.php?error=Gagal menyimpan file ke disk');
    exit;
}

// 6. Simpan metadata ke database
try {
    $stmt = $pdo->prepare("INSERT INTO secure_files (user_id, original_filename, stored_filename, file_hash_sha256) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $original_filename, $stored_filename, $hash_sha256]);
    
    header('Location: files.php?success=File berhasil di-upload dan dienkripsi');
    exit;

} catch (\PDOException $e) {
    // Hapus file yang sudah terlanjur disimpan jika DB gagal
    unlink($output_path);
    header('Location: files.php?error=Database error: ' . urlencode($e->getMessage()));
    exit;
}
?>