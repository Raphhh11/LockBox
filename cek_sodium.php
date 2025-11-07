<?php
echo "Versi PHP: " . phpversion() . "<br>";

if (extension_loaded('sodium')) {
    echo "Extension sodium: AKTIF<br>";
} else {
    echo "Extension sodium: TIDAK AKTIF<br>";
}

if (function_exists('sodium_crypto_stream_chacha20_xor')) {
    echo "Fungsi sodium_crypto_stream_chacha20_xor: TERSEDIA<br>";
} else {
    echo "Fungsi sodium_crypto_stream_chacha20_xor: TIDAK ADA<br>";
}
?>
