<?php
// FIXED: expose unified password helpers for installer
require_once(ROOT_PATH.'includes/functions/password.php'); // FIXED: unify installer hashing

if (!function_exists('install_create_admin_password')) {
    function install_create_admin_password($password)
    {
        return xterium_password_hash($password); // FIXED: reuse shared hashing
    }
}
