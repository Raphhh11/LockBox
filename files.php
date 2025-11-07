<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// ======== ChaCha20 murni (tanpa Poly1305) ========
class ChaCha20 {
    private $state;
    public function __construct($key, $nonce, $counter = 1) {
        if (strlen($key) !== 32) $key = hash('sha256', $key, true);
        if (strlen($nonce) !== 12) $nonce = str_pad($nonce, 12, "\0");
        $constants = "expand 32-byte k";
        $this->state = array_values(unpack('V16',
            $constants .
            substr($key, 0, 16) .
            substr($key, 16, 16) .
            pack('V', $counter) .
            $nonce
        ));
    }
    private function rotate($v, $c) {
        return (($v << $c) & 0xffffffff) | ($v >> (32 - $c));
    }
    private function quarterRound(&$a, &$b, &$c, &$d) {
        $a = ($a + $b) & 0xffffffff; $d ^= $a; $d = $this->rotate($d, 16);
        $c = ($c + $d) & 0xffffffff; $b ^= $c; $b = $this->rotate($b, 12);
        $a = ($a + $b) & 0xffffffff; $d ^= $a; $d = $this->rotate($d, 8);
        $c = ($c + $d) & 0xffffffff; $b ^= $c; $b = $this->rotate($b, 7);
    }
    private function chachaBlock($counter) {
        $x = $this->state;
        $x[12] = ($x[12] + $counter) & 0xffffffff;
        for ($i = 0; $i < 10; $i++) {
            $this->quarterRound($x[0], $x[4], $x[8], $x[12]);
            $this->quarterRound($x[1], $x[5], $x[9], $x[13]);
            $this->quarterRound($x[2], $x[6], $x[10], $x[14]);
            $this->quarterRound($x[3], $x[7], $x[11], $x[15]);
            $this->quarterRound($x[0], $x[5], $x[10], $x[15]);
            $this->quarterRound($x[1], $x[6], $x[11], $x[12]);
            $this->quarterRound($x[2], $x[7], $x[8], $x[13]);
            $this->quarterRound($x[3], $x[4], $x[9], $x[14]);
        }
        for ($i = 0; $i < 16; $i++) $x[$i] = ($x[$i] + $this->state[$i]) & 0xffffffff;
        return pack('V16', ...$x);
    }
    public function encrypt($data) {
        $out = ''; $blocks = ceil(strlen($data) / 64);
        for ($i = 0; $i < $blocks; $i++) {
            $keyStream = $this->chachaBlock($i);
            $block = substr($data, $i * 64, 64);
            $out .= $block ^ substr($keyStream, 0, strlen($block));
        }
        return $out;
    }
}

function chacha20_encrypt_file($source, $password, $dest) {
    $nonce = random_bytes(12);
    $data = file_get_contents($source);
    $cipher = (new ChaCha20($password, $nonce))->encrypt($data);
    return file_put_contents($dest, base64_encode($nonce . $cipher)) !== false;
}

function chacha20_decrypt_file($source, $password, $dest) {
    $raw = base64_decode(file_get_contents($source), true);
    if ($raw === false || strlen($raw) <= 12) return false;
    $nonce = substr($raw, 0, 12);
    $cipher = substr($raw, 12);
    $plain = (new ChaCha20($password, $nonce))->encrypt($cipher);
    file_put_contents($dest, $plain);
    return $plain;
}

// ===== Handler =====
$encrypt_result = $decrypt_result = $error = null;
$preview_text = null;
$username = $_SESSION['username'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $password = trim($_POST['password'] ?? '');
    if ($action === 'encrypt') {
        $file = $_FILES['target_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $password === '') {
            $error = 'Lengkapi file dan password.';
        } else {
            $out = $upload_dir . 'encrypted_' . time() . '_' . basename($file['name']);
            if (chacha20_encrypt_file($file['tmp_name'], $password, $out))
                $encrypt_result = 'uploads/' . basename($out);
            else
                $error = 'Gagal mengenkripsi file.';
        }
    }
    if ($action === 'decrypt') {
        $file = $_FILES['encrypted_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $password === '') {
            $error = 'Lengkapi file terenkripsi dan password.';
        } else {
            $out = $upload_dir . 'decrypted_' . time() . '_' . basename($file['name']);
            $plain = chacha20_decrypt_file($file['tmp_name'], $password, $out);
            if ($plain === false) {
                $error = 'Password salah atau file rusak.';
            } else {
                $decrypt_result = 'uploads/' . basename($out);
                if (preg_match('/\.txt$/i', $file['name']))
                    $preview_text = nl2br(htmlspecialchars($plain));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Encryption | LockBox</title>
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

<!-- Konten Utama -->
<main class="pt-24 pb-20 px-6 md:px-20">
  <h1 class="text-3xl font-bold text-center mb-10 text-gray-800">File Encryption</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-10 justify-items-center">

    <!-- Card Enkripsi -->
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md transform transition duration-300 hover:-translate-y-1 hover:shadow-xl">
      <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-green-500 to-blue-400"></div>
      <h2 class="text-xl font-bold text-center text-green-600 mb-6">Enkripsi File</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="encrypt">
        <div>
          <label class="block text-sm text-gray-600 mb-1">File Target</label>
          <input type="file" name="target_file" required
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
        </div>
        <button type="submit"
                class="w-full bg-green-600 text-white py-2 rounded-full font-medium hover:bg-green-700 transition">
          Enkripsi
        </button>
      </form>

      <?php if ($encrypt_result): ?>
      <div class="mt-6 text-center">
        <p class="text-green-600 font-semibold">File berhasil dienkripsi</p>
        <a href="<?= htmlspecialchars($encrypt_result) ?>" download class="block mt-2 text-green-600 hover:underline">Download File</a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Card Dekripsi -->
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md transform transition duration-300 hover:-translate-y-1 hover:shadow-xl">
      <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-blue-500 to-pink-400"></div>
      <h2 class="text-xl font-bold text-center text-blue-600 mb-6">Dekripsi File</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="decrypt">
        <div>
          <label class="block text-sm text-gray-600 mb-1">File Terenkripsi</label>
          <input type="file" name="encrypted_file" required
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-full font-medium hover:bg-blue-700 transition">
          Dekripsi
        </button>
      </form>

      <?php if ($decrypt_result): ?>
      <div class="mt-6 text-center">
        <p class="text-green-600 font-semibold">File berhasil didekripsi</p>
        <a href="<?= htmlspecialchars($decrypt_result) ?>" download class="block mt-2 text-blue-600 hover:underline">Download File</a>
      </div>
      <?php if ($preview_text): ?>
      <div class="mt-6">
        <h3 class="font-semibold text-center text-green-600">Isi File:</h3>
        <div class="bg-gray-100 rounded-lg p-4 mt-3 font-mono text-sm break-words text-left"><?= $preview_text ?></div>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($error): ?>
  <div class="mt-10 bg-red-100 border border-red-300 text-red-700 rounded-lg p-4 max-w-2xl mx-auto text-center">
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>
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
