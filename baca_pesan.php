<?php
require 'db.php';
require 'playfair.php';
require 'xchacha.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: signin.php');
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die('ID pesan tidak valid.');

$stmt = $pdo->prepare("SELECT ciphertext, created_at FROM messages WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$msg = $stmt->fetch();

if (!$msg) die('Pesan tidak ditemukan.');

$ciphertext_b64 = $msg['ciphertext'];
$created_at = $msg['created_at'];
$username = $_SESSION['username'];

$decrypted_plain = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kunci_modern  = $_POST['kunci_modern']  ?? '';
    $kunci_playfair = $_POST['kunci_playfair'] ?? '';

    if ($kunci_modern === '' || $kunci_playfair === '') {
        $error = 'Kedua kunci harus diisi.';
    } else {
        $decrypted_xchacha = xchacha_decrypt($ciphertext_b64, $kunci_modern);
        if ($decrypted_xchacha === false) {
            $error = 'Kunci XChaCha20 salah atau data rusak.';
        } else {
            $plaintext = playfair_decrypt($decrypted_xchacha, $kunci_playfair);
            $decrypted_plain = $plaintext;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dekripsi Pesan | LockBox</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navbar LockBox -->
<nav class="flex items-center justify-between px-10 py-4 bg-white shadow-sm fixed w-full top-0 z-50">
  <div class="flex items-center space-x-2">
    <div class="bg-blue-600 text-white p-2 rounded-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    </div>
    <span class="font-bold text-xl tracking-tight">LockBox</span>
  </div>

  <div class="hidden md:flex space-x-8 font-medium text-sm relative items-center">
    <a href="index.php" class="text">Home</a>
    <div class="relative">
      <button id="pagesBtn" class="flex items-center space-x-1 focus:outline-none select-none">
        <span>Pages</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.187l3.71-3.956a.75.75 0 011.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
      <div id="pagesMenu" class="hidden absolute bg-white border rounded-xl shadow-md mt-2 w-36">
        <a href="pesan.php" class="block px-4 py-2 hover:bg-gray-50">Message Encryption</a>
        <a href="files.php" class="block px-4 py-2 hover:bg-gray-50">File Encryption</a>
        <a href="stegano_fixed.php" class="block px-4 py-2 hover:bg-gray-50">Steganograph</a>
      </div>
    </div>
  </div>

  <div class="flex space-x-3 items-center">
    <?php if ($username): ?>
      <span class="text-sm text-gray-600">Hi, <b><?= htmlspecialchars($username) ?></b></span>
      <a href="logout.php" class="text-sm bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200">Logout</a>
    <?php else: ?>
      <a href="signin.php" class="text-sm text-blue-600 hover:underline">Sign In</a>
      <a href="signup.php" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Konten utama -->
<main class="pt-24 px-6 md:px-20">
  <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-2xl p-8 transform transition duration-300 hover:-translate-y-1 hover:shadow-xl relative">
    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-blue-500 to-pink-400"></div>

    <div class="text-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Dekripsi Pesan Rahasia</h2>
      <p class="text-sm text-gray-500">Pesan terenkripsi dari <strong><?= htmlspecialchars($username) ?></strong></p>
      <p class="text-xs text-gray-400">Tanggal: <?= htmlspecialchars($created_at) ?></p>
    </div>

    <form method="POST" class="space-y-5">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Kunci Modern (XChaCha20)</label>
        <input type="password" name="kunci_modern" required
               class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Kunci Playfair</label>
        <input type="password" name="kunci_playfair" required
               class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400 transition">
      </div>
      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-full font-medium hover:bg-blue-700 transition">
        Dekripsi Pesan
      </button>
    </form>

    <?php if ($error): ?>
      <div class="mt-5 bg-red-100 text-red-700 border border-red-300 rounded-lg p-3 text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($decrypted_plain !== null): ?>
      <div class="mt-8 text-center">
        <h3 class="font-semibold text-green-600 text-lg mb-2">Pesan Berhasil Didekripsi</h3>
        <div class="bg-gray-100 rounded-lg p-4 font-mono text-sm text-left text-gray-800">
          <?= nl2br(htmlspecialchars($decrypted_plain)) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="mt-8 text-center">
      <a href="pesan.php" class="text-blue-600 hover:underline">← Kembali ke daftar pesan</a>
    </div>
  </div>
</main>

<script>
  const pagesBtn = document.getElementById('pagesBtn');
  const pagesMenu = document.getElementById('pagesMenu');
  if (pagesBtn) {
    pagesBtn.addEventListener('click', () => {
      pagesMenu.classList.toggle('hidden');
    });
  }
</script>

</body>
</html>
