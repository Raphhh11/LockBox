<?php
// download_file.php
require 'db.php';
require 'kripto_aes.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = $_GET['id'] ?? null;

if (!$file_id) {
    die('ID file tidak valid.');
}

// Ambil info file dari DB
$stmt = $pdo->prepare("SELECT * FROM secure_files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file_info = $stmt->fetch();

if (!$file_info) {
    die('File tidak ditemukan atau Anda tidak punya akses.');
}

$error = null;

// Cek jika form password disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? null;
    
    if (empty($password)) {
        $error = "Password tidak boleh kosong.";
    } else {
        // 1. Baca file terenkripsi dari disk
        $file_path = 'uploads/' . $file_info['stored_filename'];
        if (!file_exists($file_path)) {
            die("FATAL: File fisik tidak ditemukan di server.");
        }
        $ciphertext = file_get_contents($file_path);
        
        // 2. Dekripsi file (AES-256-GCM)
        $plaintext = aes_gcm_decrypt($ciphertext, $password);
        
        if ($plaintext === false) {
            $error = "Password salah atau file telah rusak (Gagal Dekripsi AES-GCM).";
        } else {
            // 3. Verifikasi Integritas (SHA-256)
            $hash_cek = hash('sha256', $plaintext);
            
            if ($hash_cek !== $file_info['file_hash_sha256']) {
                $error = "DEKRIPSI BERHASIL, TAPI INTEGRITAS GAGAL! File mungkin rusak. Hash tidak cocok.";
            } else {
                // 4. BERHASIL! Kirim file ke user
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream'); // Tipe file generik
                header('Content-Disposition: attachment; filename="' . basename($file_info['original_filename']) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($plaintext));
                echo $plaintext;
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE-edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dekripsi File | LockBox</title>
  <link rel="icon" href="favicon.ico">
  <link href="style.css" rel="stylesheet">
</head>

<body x-data="{ page: 'download-file', 'darkMode': true }" :class="{'b eh': darkMode === true}">

  <header class="g s r vd ya cj hh sm _k dj bl ll">
     <div class="bb ze ki xn 2xl:ud-px-0 oo wf yf i">
        <div class="vd to/4 tc wf yf">
            <a href="index.php"><img class="om" src="images/logo-light.svg" alt="Logo Light" /></a>
        </div>
     </div>
  </header>

  <main>
    <section class="i pg fh rm ki xn vq gj qp gr hj rp hr">
      <div class="animate_top bb af i va sg hh sm vk xm yi _n jp hi ao kp">
        
        <div class="rj">
          <h2 class="ek ck kk wm xb">Dekripsi File</h2>
          <p>Anda akan men-download file: <strong><?php echo htmlspecialchars($file_info['original_filename']); ?></strong></p>
          <p>Masukkan password untuk mendekripsi file ini.</p>
        </div>
        
        <?php if ($error): ?>
             <div style="background: #fff0f0; border-radius: 8px; padding: 15px; margin-top: 15px; color: #a00; text-align:center;">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form class="sb" action="download_file.php?id=<?php echo $file_id; ?>" method="POST">
          <div class="wb">
            <label class="rc kk wm vb" for="password">Password (AES-256-GCM):</label>
            <input type="password" name="password" id="password" placeholder="Kunci rahasia file ini"
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40" required />
          </div>

          <button class="vd rj ek rc rg gh lk ml il _l gi hi">
            Dekripsi dan Download
          </button>
        </form>
        
        <a href="files.php" class="mk rj" style="display:block; margin-top: 20px;">Kembali</a>

      </div>
    </section>
  </main>

  <script defer src="bundle.js"></script>
</body>
</html>