<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (!function_exists('openssl_encrypt')) die('OpenSSL belum aktif di php.ini');
if (!function_exists('imagecreatefrompng')) die('GD belum aktif di php.ini');

define('IV_LEN', 12);
define('TAG_LEN', 16);

function aes_encrypt_base64($plaintext, $password) {
    $key = hash('sha256', $password, true);
    $iv = random_bytes(IV_LEN);
    $tag = '';
    $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', TAG_LEN);
    return base64_encode($iv . $tag . $cipher);
}

function aes_decrypt_base64($b64, $password) {
    $raw = base64_decode($b64, true);
    if (!$raw || strlen($raw) < IV_LEN + TAG_LEN) return false;
    $key = hash('sha256', $password, true);
    $iv  = substr($raw, 0, IV_LEN);
    $tag = substr($raw, IV_LEN, TAG_LEN);
    $cipher = substr($raw, IV_LEN + TAG_LEN);
    return openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
}

function str_to_bits($s) {
    $bits = '';
    for ($i = 0; $i < strlen($s); $i++)
        $bits .= str_pad(decbin(ord($s[$i])), 8, '0', STR_PAD_LEFT);
    return $bits;
}

function bits_to_str($bits) {
    $out = '';
    for ($i = 0; $i < strlen($bits); $i += 8) {
        $byte = substr($bits, $i, 8);
        if (strlen($byte) < 8) break;
        $out .= chr(bindec($byte));
    }
    return $out;
}

function lsb_encode_text($data, $src, $out) {
    $img = @imagecreatefrompng($src);
    if (!$img) return false;
    imagesavealpha($img, true);
    $w = imagesx($img);
    $h = imagesy($img);
    $payload = pack('N', strlen($data)) . $data;
    $bits = str_to_bits($payload);
    $capacity = $w * $h * 3;
    if (strlen($bits) > $capacity) {
        imagedestroy($img);
        return false;
    }
    $i = 0;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            if ($i >= strlen($bits)) break 2;
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $a = ($rgb & 0x7F000000) >> 24;
            if ($i < strlen($bits)) $r = ($r & 0xFE) | $bits[$i++];
            if ($i < strlen($bits)) $g = ($g & 0xFE) | $bits[$i++];
            if ($i < strlen($bits)) $b = ($b & 0xFE) | $bits[$i++];
            $color = imagecolorallocatealpha($img, $r, $g, $b, $a);
            imagesetpixel($img, $x, $y, $color);
        }
    }
    imagepng($img, $out);
    imagedestroy($img);
    return true;
}

function lsb_decode_text($src) {
    $img = @imagecreatefrompng($src);
    if (!$img) return false;
    $w = imagesx($img);
    $h = imagesy($img);
    $bits = '';
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $bits .= (($rgb >> 16) & 1);
            $bits .= (($rgb >> 8) & 1);
            $bits .= ($rgb & 1);
        }
    }
    $bytes = bits_to_str($bits);
    if (strlen($bytes) < 4) return false;
    $len = unpack('Nlen', substr($bytes, 0, 4))['len'];
    $payload = substr($bytes, 4, $len);
    imagedestroy($img);
    return ($len > 0 && strlen($payload) >= $len) ? $payload : false;
}

$encode_result = $decode_result = $error = null;
$username = $_SESSION['username'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'encode') {
        $file = $_FILES['cover_image'] ?? null;
        $msg  = $_POST['secret_message'] ?? '';
        $pw   = $_POST['password'] ?? '';
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !$msg || !$pw) {
            $error = 'Lengkapi semua field.';
        } else {
            $enc = aes_encrypt_base64($msg, $pw);
            $out = $upload_dir . 'stego_' . time() . '.png';
            if (lsb_encode_text($enc, $file['tmp_name'], $out))
                $encode_result = 'uploads/' . basename($out);
            else
                $error = 'Gagal menyisipkan data (gambar terlalu kecil/rusak).';
        }
    }
    if ($_POST['action'] === 'decode') {
        $file = $_FILES['stego_image'] ?? null;
        $pw   = $_POST['password'] ?? '';
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !$pw) {
            $error = 'Lengkapi semua field.';
        } else {
            $payload = lsb_decode_text($file['tmp_name']);
            if ($payload === false) {
                $error = 'Tidak ada pesan ditemukan atau file rusak.';
            } else {
                $plain = aes_decrypt_base64($payload, $pw);
                if ($plain === false)
                    $error = 'Password salah atau data rusak.';
                else
                    $decode_result = $plain;
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
<title>Steganografi | LockBox</title>
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

<!-- Konten -->
<main class="pt-24 pb-20 px-6 md:px-20">
  <h1 class="text-3xl font-bold text-center mb-10 text-gray-800">Steganograph</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-10 justify-items-center">

    <!-- ENCODE -->
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md transform transition duration-300 hover:-translate-y-1 hover:shadow-xl relative">
      <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-blue-500 to-green-400"></div>
      <h2 class="text-xl font-bold text-center text-blue-600 mb-6">Sembunyikan Pesan</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="encode">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Gambar PNG</label>
          <input type="file" name="cover_image" accept="image/png"
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Pesan Rahasia</label>
          <textarea name="secret_message" rows="3"
                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required></textarea>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input type="password" name="password"
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
        </div>
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-full font-medium hover:bg-blue-700 transition">
          Mulai Sembunyikan
        </button>
      </form>

      <?php if ($encode_result): ?>
      <div class="mt-6 text-center">
        <p class="text-green-600 font-semibold">Pesan berhasil disembunyikan!</p>
        <img src="<?= htmlspecialchars($encode_result) ?>" class="mt-3 rounded-lg shadow max-w-full">
        <a href="<?= htmlspecialchars($encode_result) ?>" download class="block mt-2 text-blue-600 hover:underline">Download Gambar</a>
      </div>
      <?php endif; ?>
    </div>

    <!-- DECODE -->
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md transform transition duration-300 hover:-translate-y-1 hover:shadow-xl relative">
      <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl bg-gradient-to-r from-green-500 to-pink-400"></div>
      <h2 class="text-xl font-bold text-center text-green-600 mb-6">Ekstrak Pesan</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="decode">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Gambar Stego (PNG)</label>
          <input type="file" name="stego_image" accept="image/png"
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input type="password" name="password"
                 class="w-full border border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition" required>
        </div>
        <button type="submit"
                class="w-full bg-green-600 text-white py-2 rounded-full font-medium hover:bg-green-700 transition">
          Mulai Ekstrak
        </button>
      </form>

      <?php if ($decode_result): ?>
      <div class="mt-6">
        <h3 class="font-semibold text-center text-green-600">Pesan Ditemukan:</h3>
        <div class="bg-gray-100 rounded-lg p-4 mt-3 font-mono text-sm break-words"><?= nl2br(htmlspecialchars($decode_result)) ?></div>
      </div>
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
