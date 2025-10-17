<?php
/**
 * Debug-Helfer zum Prüfen der aktuellen Passwort-Hashes.
 * [2024-04 MIGRATION] Erstellt für die PHP-5.6-Fehlersuche.
 */

require_once __DIR__.'/includes/classes/PlayerUtil.class.php';

if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (!is_string($known_string) || !is_string($user_string)) {
            return false;
        }

        $known_len = strlen($known_string);
        if ($known_len !== strlen($user_string)) {
            return false;
        }

        $res = $known_string ^ $user_string;
        $ret = 0;
        for ($i = $known_len - 1; $i >= 0; --$i) {
            $ret |= ord($res[$i]);
        }

        return $ret === 0;
    }
}

$password = isset($_REQUEST['password']) ? (string)$_REQUEST['password'] : '';
$referenceHash = isset($_REQUEST['hash']) ? (string)$_REQUEST['hash'] : '';

if ($password === '') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Bitte ?password=DEIN_PASSWORT (und optional &hash=HASH) übergeben.\n";
    exit;
}

$newHash = PlayerUtil::cryptPassword($password);
$legacyHash = md5($password);

$isMatch = false;
$hashUsed = '';

if ($referenceHash !== '') {
    if (hash_equals($referenceHash, $newHash)) {
        $isMatch = true;
        $hashUsed = 'bcrypt';
    } elseif (hash_equals($referenceHash, $legacyHash)) {
        $isMatch = true;
        $hashUsed = 'md5';
    }
}

header('Content-Type: text/plain; charset=UTF-8');
echo "Passwort: " . $password . "\n";
echo "Aktueller Hash: " . $newHash . "\n";
echo "Alte MD5-Signatur: " . $legacyHash . "\n";

if ($referenceHash !== '') {
    echo "Vergleich mit Referenz: " . ($isMatch ? 'gültig' : 'ungültig');
    if ($isMatch) {
        echo " (" . $hashUsed . ")";
    }
    echo "\n";
}
