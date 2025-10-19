<?php
// FIXED: unified hash helper definitions
if (!function_exists('xterium_password_hash')) {
    function xterium_password_hash($password)
    {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)); // FIXED: use native password_hash
        }

        if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH) {
            return crypt($password, xterium_password_generate_bcrypt_salt()); // FIXED: fallback bcrypt salt
        }

        return md5($password); // LEGACY fallback
    }

    function xterium_password_verify($password, $hash)
    {
        if (function_exists('password_verify') && xterium_password_is_bcrypt_hash($hash)) {
            return password_verify($password, $hash); // FIXED: use native password_verify
        }

        if (xterium_password_is_bcrypt_hash($hash)) {
            return crypt($password, $hash) === $hash; // LEGACY fallback
        }

        return md5($password) === $hash; // LEGACY fallback
    }

    function xterium_password_needs_rehash($hash)
    {
        if (!function_exists('password_hash')) {
            return false; // LEGACY fallback
        }

        if (!xterium_password_is_bcrypt_hash($hash)) {
            return true; // FIXED: upgrade md5/legacy hashes
        }

        if (function_exists('password_needs_rehash')) {
            return password_needs_rehash($hash, PASSWORD_BCRYPT); // FIXED: rehash detection
        }

        return strpos($hash, '$2a$') === 0; // LEGACY fallback
    }

    function xterium_password_is_bcrypt_hash($hash)
    {
        return strpos($hash, '$2y$') === 0 || strpos($hash, '$2b$') === 0 || strpos($hash, '$2a$') === 0; // FIXED: detect bcrypt variants
    }

    function xterium_password_generate_bcrypt_salt($cost = 12)
    {
        $cost = str_pad((int) $cost, 2, '0', STR_PAD_LEFT); // FIXED: cost normalization

        if (function_exists('random_bytes')) {
            $randomBytes = random_bytes(16); // FIXED: secure random bytes
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $randomBytes = openssl_random_pseudo_bytes(16); // LEGACY fallback
        } else {
            $randomBytes = md5(uniqid(mt_rand(), true), true); // LEGACY fallback
        }

        $salt = substr(strtr(base64_encode($randomBytes), '+', '.'), 0, 22); // FIXED: bcrypt salt encoding

        return '$2y$' . $cost . '$' . $salt . '$'; // FIXED: assemble bcrypt salt
    }
}
