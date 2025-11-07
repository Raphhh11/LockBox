<?php
require 'db.php'; 


$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;
$ssn = $_POST['ssn'] ?? null;


if (empty($username) || empty($password) || empty($ssn)) {
    die('Semua field wajib diisi.');
}

$sha256_hash = hash('sha256', $password);
$bcrypt_hash = password_hash($sha256_hash, PASSWORD_BCRYPT);


$key = hash('sha256', 'LOCKBOX_AES_KEY', true); 
$iv = random_bytes(12);
$cipher = 'aes-256-gcm';
$ciphertext = openssl_encrypt($ssn, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);


$ssn_encrypted = base64_encode($iv . $tag . $ciphertext);

try {
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, ssn_encrypted) VALUES (?, ?, ?)");
    $stmt->execute([$username, $bcrypt_hash, $ssn_encrypted]);

    header('Location: signin.php');
    exit;

} catch (PDOException $e) {
    if ($e->getCode() == 23000 || $e->getCode() == 1062) {
        die('Username sudah terdaftar. Gunakan username lain.');
    } else {
        throw $e;
    }
}
?>
