<?php
// kripto_aes.php

// Pastikan ekstensi openssl ada
if (!function_exists('openssl_encrypt')) {
    die('Ekstensi PHP openssl tidak tersedia. Harap install/aktifkan.');
}

define('AES_GCM_CIPHER', 'aes-256-gcm');
define('AES_GCM_IV_LEN', 12); // 96 bits, standar untuk GCM
define('AES_GCM_TAG_LEN', 16); // 128 bits

/**
 * Mengenkripsi teks menggunakan AES-256-GCM
 * @param string $plaintext Pesan yang akan dienkripsi
 * @param string $password Kunci (password)
 * @return string Ciphertext yang sudah di-encode Base64 (iv + tag + cipher)
 */
function aes_gcm_encrypt($plaintext, $password) {
    // 1. Dapatkan kunci 32-byte (256-bit) dari password
    $key = hash('sha256', $password, true);

    // 2. Buat IV (Nonce) 12-byte yang unik
    $iv = openssl_random_pseudo_bytes(AES_GCM_IV_LEN);

    // 3. Enkripsi
    $tag = ""; // Tag akan diisi oleh openssl_encrypt
    $ciphertext = openssl_encrypt(
        $plaintext,
        AES_GCM_CIPHER,
        $key,
        OPENSSL_RAW_DATA, // Output biner mentah
        $iv,
        $tag, // Tag GCM (Authentication Tag)
        "",  // AAD (Additional Associated Data) - kita kosongkan
        AES_GCM_TAG_LEN
    );

    // 4. Gabungkan iv + tag + ciphertext, lalu encode Base64
    return base64_encode($iv . $tag . $ciphertext);
}

/**
 * Mendekripsi teks AES-256-GCM
 * @param string $base64_ciphertext Ciphertext (Base64) dari fungsi encrypt
 * @param string $password Kunci (password) yang sama
 * @return string|false Plaintext jika berhasil, false jika gagal (kunci salah / data rusak)
 */
function aes_gcm_decrypt($base64_ciphertext, $password) {
    // 1. Decode Base64
    $decoded = base64_decode($base64_ciphertext);
    if ($decoded === false) {
        return false; // Gagal decode
    }
    
    // 2. Dapatkan kunci 32-byte
    $key = hash('sha256', $password, true);
    
    // 3. Pisahkan komponen-komponennya
    $iv = substr($decoded, 0, AES_GCM_IV_LEN);
    $tag = substr($decoded, AES_GCM_IV_LEN, AES_GCM_TAG_LEN);
    $ciphertext = substr($decoded, AES_GCM_IV_LEN + AES_GCM_TAG_LEN);

    if (strlen($iv) !== AES_GCM_IV_LEN || strlen($tag) !== AES_GCM_TAG_LEN || $ciphertext === false) {
        return false; // Data tidak lengkap
    }

    // 4. Dekripsi
    $plaintext = openssl_decrypt(
        $ciphertext,
        AES_GCM_CIPHER,
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag // Berikan tag untuk verifikasi integritas
    );

    return $plaintext; // Akan return false jika dekripsi gagal (tag salah / kunci salah)
}
?>