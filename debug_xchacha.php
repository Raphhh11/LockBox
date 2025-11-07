<?php
require 'db.php';
require 'xchacha.php';

$id = 123; // isi id pesan yang bermasalah
$stmt = $pdo->prepare("SELECT ciphertext FROM messages WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
$b64 = $row['ciphertext'];

file_put_contents('dbg_b64.txt', $b64);
$raw = base64_decode($b64, true);
file_put_contents('dbg_raw_len.txt', strlen($raw));
$nonce_len = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
file_put_contents('dbg_nonce_len.txt', $nonce_len);
file_put_contents('dbg_firstbytes.txt', bin2hex(substr($raw,0,$nonce_len)));
echo "done\n";
