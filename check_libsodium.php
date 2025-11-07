<?php
if (function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
    echo "Libsodium aktif dan siap digunakan!";
} else {
    echo "Libsodium belum aktif.";
}
?>
