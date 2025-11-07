<?php
// pesan.php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT id, created_at FROM messages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pesan Rahasia | LockBox</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="favicon.ico">
  <link href="style.css" rel="stylesheet">
</head>

<body x-data="{ page: 'pesan', darkMode: true }"
      x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
      "
      class="bg-gray-50 min-h-screen">

  <!-- Navbar -->
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
  <main class="pt-24 px-4 md:px-0">
    <section class="i pg fh rm ki xn vq gj qp gr hj rp hr">
      <div class="animate_top bb af i va sg hh sm vk xm yi _n jp hi ao kp">
        
        <div class="rj">
          <h2 class="ek ck kk wm xb">Pesan Super Enkripsi</h2>
          <p>Selamat datang, <?php echo htmlspecialchars($username); ?>! Kirim pesan rahasia.</p>
        </div>

        <form class="sb" action="kirim_pesan.php" method="POST">
          <div class="wb">
            <label class="rc kk wm vb" for="pesan">Pesan Anda:</label>
            <textarea name="pesan" id="pesan" rows="4" placeholder="Tulis pesan rahasia di sini..."
              class="vd hh rg zk _g ch hm dm fm pl/50 ci mi sm xm pm dn/40"></textarea>
          </div>
          <div class="wb">
            <label class="rc kk wm vb" for="kunci_playfair">Kunci Playfair (Klasik):</label>
            <input type="password" name="kunci_playfair" id="kunci_playfair" placeholder="Kunci untuk Playfair"
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40" />
          </div>
          <div class="wb">
            <label class="rc kk wm vb" for="kunci_modern">Kunci Modern (XChaCha20):</label>
            <input type="password" name="kunci_modern" id="kunci_modern" placeholder="Kunci untuk XChaCha20"
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40" />
          </div>

          <button class="vd rj ek rc rg gh lk ml il _l gi hi">
            Kirim Pesan Terenkripsi
          </button>
        </form>

        <hr class="ch pm my-8">

        <div class="rj">
            <h3 class="ek ck kk wm xb">Pesan Tersimpan</h3>
        </div>
        <div>
            <?php if (empty($messages)): ?>
                <p class="rj">Belum ada pesan tersimpan.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($messages as $msg): ?>
                        <li style="padding: 10px; border-bottom: 1px solid #eee;">
                            <a href="baca_pesan.php?id=<?= $msg['id'] ?>" class="mk">
                                Pesan dari <?= $msg['created_at'] ?> (Klik untuk dekripsi)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

      </div>
    </section>
  </main>

  <script>
    // Dropdown menu
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
