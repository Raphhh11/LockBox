<?php


require 'db.php'; 

// Ambil data dari form
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

// Validasi input
if (empty($username) || empty($password)) {
    die('Username dan password tidak boleh kosong.');
}

try {
  
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Username tidak ditemukan.');
    }

    
    $sha256_hash = hash('sha256', $password);

    if (password_verify($sha256_hash, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: index.php');
        exit;
    } else {
        die('Password salah.');
    }

} catch (PDOException $e) {
    throw $e;
}
?>
