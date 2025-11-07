<?php
// kripto_modern.php

// Pastikan ekstensi sodium ada
if (!function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_is_available')) {
    die('Ekstensi PHP libsodium tidak tersedia. Harap install/aktifkan.');
}

/**
 * Mengenkripsi teks menggunakan XChaCha20-Poly1305
 * @param string $plaintext Pesan yang akan dienkripsi
 * @param string $password Kunci (password)
 * @return string Ciphertext yang sudah di-encode Base64 (nonce + cipher)
 */
function xchacha20_encrypt($plaintext, $password) {
    // 1. Dapatkan kunci 32-byte dari password (menggunakan SHA-256)
    //    Ini adalah cara sederhana untuk demo. KDF (seperti Argon2) lebih disarankan di produksi.
    $key = hash('sha256', $password, true); // 'true' untuk output biner mentah

    // 2. Buat Nonce (Number used once) 24-byte yang unik
    $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES); // 24 bytes

    // 3. Enkripsi
    $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
        $plaintext,
        '', // AAD (Additional Associated Data) - kita kosongkan
        $nonce,
        $key
    );

    // 4. Gabungkan nonce + ciphertext, lalu encode Base64 agar aman disimpan di DB
    return base64_encode($nonce . $ciphertext);
}

/**
 * Mendekripsi teks XChaCha20-Poly1305
 * @param string $base64_ciphertext Ciphertext (Base64) dari fungsi encrypt
 * @param string $password Kunci (password) yang sama
 * @return string|false Plaintext jika berhasil, false jika gagal (kunci salah / data rusak)
 */
function xchacha20_decrypt($base64_ciphertext, $password) {
    // 1. Decode Base64
    $decoded = base64_decode($base64_ciphertext);
    if ($decoded === false) {
        return false; // Gagal decode
    }

    // 2. Dapatkan kunci 32-byte (harus sama persis)
    $key = hash('sha256', $password, true);

    // 3. Pisahkan nonce dan ciphertext
    $nonce_len = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES; // 24 bytes
    if (strlen($decoded) < $nonce_len) {
        return false; // Data terlalu pendek
    }
    $nonce = substr($decoded, 0, $nonce_len);
    $ciphertext = substr($decoded, $nonce_len);

    // 4. Dekripsi
    $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
        $ciphertext,
        '', // AAD (harus sama)
        $nonce,
        $key
    );

    return $plaintext; // Akan return false jika dekripsi gagal
}

?>