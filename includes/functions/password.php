<?php
// FIXED: unified hash helper definitions
if (!defined('XTERIUM_LEGACY_PASSWORD_PREFIX')) {
    define('XTERIUM_LEGACY_PASSWORD_PREFIX', 'legacy$');
}

if (!defined('XTERIUM_LEGACY_PASSWORD_SALT')) {
    define('XTERIUM_LEGACY_PASSWORD_SALT', 'xterium_legacy_salt');
}

if (!function_exists('xterium_password_hash')) {
    function xterium_password_hash($password)
    {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        return XTERIUM_LEGACY_PASSWORD_PREFIX . xterium_password_legacy_hash($password); // LEGACY fallback
    }

    function xterium_password_verify($password, $hash)
    {
        if (function_exists('password_verify') && xterium_password_is_bcrypt_hash($hash)) {
            return password_verify($password, $hash); // use native password_verify when possible
        }

        if (xterium_password_is_bcrypt_hash($hash)) {
            return crypt($password, $hash) === $hash; // support bcrypt verification without password_verify
        }

        if (xterium_password_is_legacy_hash($hash)) {
            $legacyHash = substr($hash, strlen(XTERIUM_LEGACY_PASSWORD_PREFIX));

            return $legacyHash === xterium_password_legacy_hash($password); // legacy salted hash check
        }

        return $hash === xterium_password_legacy_hash($password); // backwards compatibility without prefix
    }

    function xterium_password_needs_rehash($hash)
    {
        if (!function_exists('password_hash')) {
            return false; // legacy environments cannot rehash automatically
        }

        if (!xterium_password_is_bcrypt_hash($hash)) {
            return true; // upgrade legacy hashes once password_hash() is available
        }

        if (function_exists('password_needs_rehash')) {
            return password_needs_rehash($hash, PASSWORD_DEFAULT);
        }

        return false;
    }

    function xterium_password_is_bcrypt_hash($hash)
    {
        return strpos($hash, '$2y$') === 0 || strpos($hash, '$2b$') === 0 || strpos($hash, '$2a$') === 0;
    }

    function xterium_password_is_legacy_hash($hash)
    {
        return strpos($hash, XTERIUM_LEGACY_PASSWORD_PREFIX) === 0;
    }

    function xterium_password_legacy_hash($password)
    {
        return sha1(XTERIUM_LEGACY_PASSWORD_SALT . md5($password));
    }
}
