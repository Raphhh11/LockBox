<?php
if (!function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
    throw new Exception('Libsodium tidak aktif.');
}

function xchacha_encrypt($plaintext, $password) {
    // Derive fixed-length key from password
    $key = sodium_crypto_generichash($password, '', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
    $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
    $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, '', $nonce, $key);
    // store nonce + cipher, encoded base64 for DB
    return base64_encode($nonce . $cipher);
}

function xchacha_decrypt($b64, $password) {
    $raw = base64_decode($b64, true);
    if ($raw === false) return false;
    $nonce_len = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
    if (strlen($raw) <= $nonce_len) return false;
    $nonce = substr($raw, 0, $nonce_len);
    $cipher = substr($raw, $nonce_len);
    $key = sodium_crypto_generichash($password, '', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
    $plain = @sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, '', $nonce, $key);
    return $plain === false ? false : $plain;
}
?>
