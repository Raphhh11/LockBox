<?php
function playfair_encrypt($plaintext, $key) {
    $plaintext = strtoupper(preg_replace('/[^A-Z]/', '', str_replace('J', 'I', $plaintext)));
    $key = strtoupper(preg_replace('/[^A-Z]/', '', str_replace('J', 'I', $key)));
    $matrix = [];
    $seen = [];
    foreach (str_split($key . 'ABCDEFGHIKLMNOPQRSTUVWXYZ') as $c)
        if (!isset($seen[$c])) $matrix[] = $seen[$c] = $c;
    $pairs = [];
    for ($i = 0; $i < strlen($plaintext); $i += 2) {
        $a = $plaintext[$i];
        $b = $plaintext[$i + 1] ?? 'X';
        if ($a === $b) $b = 'X';
        $pairs[] = [$a, $b];
    }
    $cipher = '';
    foreach ($pairs as [$a, $b]) {
        $ia = array_search($a, $matrix);
        $ib = array_search($b, $matrix);
        $ra = intdiv($ia, 5);
        $ca = $ia % 5;
        $rb = intdiv($ib, 5);
        $cb = $ib % 5;
        if ($ra === $rb) {
            $cipher .= $matrix[$ra * 5 + (($ca + 1) % 5)];
            $cipher .= $matrix[$rb * 5 + (($cb + 1) % 5)];
        } elseif ($ca === $cb) {
            $cipher .= $matrix[((($ra + 1) % 5) * 5) + $ca];
            $cipher .= $matrix[((($rb + 1) % 5) * 5) + $cb];
        } else {
            $cipher .= $matrix[$ra * 5 + $cb];
            $cipher .= $matrix[$rb * 5 + $ca];
        }
    }
    return $cipher;
}

function playfair_decrypt($ciphertext, $key) {
    $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
    $ciphertext = strtoupper(preg_replace('/[^A-Z]/', '', $ciphertext));
    if ($key === '' || $ciphertext === '') return '';

    $matrix = [];
    $alphabet = 'ABCDEFGHIKLMNOPQRSTUVWXYZ';
    $used = '';

    foreach (str_split($key . $alphabet) as $ch) {
        if (!str_contains($used, $ch)) {
            $matrix[] = $ch;
            $used .= $ch;
        }
    }

    $pairs = str_split($ciphertext, 2);
    $plain = '';
    foreach ($pairs as $pair) {
        if (strlen($pair) < 2) continue;
        $a = strpos($used, $pair[0]);
        $b = strpos($used, $pair[1]);
        $ra = intdiv($a, 5);
        $ca = $a % 5;
        $rb = intdiv($b, 5);
        $cb = $b % 5;

        if ($ra === $rb) {
            $plain .= $used[$ra * 5 + (($ca + 4) % 5)];
            $plain .= $used[$rb * 5 + (($cb + 4) % 5)];
        } elseif ($ca === $cb) {
            $plain .= $used[((($ra + 4) % 5) * 5) + $ca];
            $plain .= $used[((($rb + 4) % 5) * 5) + $cb];
        } else {
            $plain .= $used[$ra * 5 + $cb];
            $plain .= $used[$rb * 5 + $ca];
        }
    }

    return $plain;
}

?>
