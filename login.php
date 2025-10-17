<?php
// FIXED: standalone login entrypoint using unified hashing

define('MODE', 'LOGIN');
define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)).'/');
set_include_path(ROOT_PATH);

require(ROOT_PATH.'includes/common.php');
require_once(ROOT_PATH.'includes/functions/password.php');

if (empty($_POST)) {
    HTTP::redirectTo('index.php'); // FIXED: align with existing flow
}

$username = HTTP::_GP('email', '', UTF8_SUPPORT);
$password = HTTP::_GP('password', '', true);

$loginData = $GLOBALS['DATABASE']->getFirstRow("SELECT id, password, username FROM ".USERS." WHERE universe = ".$GLOBALS['UNI']." AND email = '".$GLOBALS['DATABASE']->escape($username)."';"); // FIXED: fetch stored hash

if (!isset($loginData) || !verifyPassword($password, $loginData['password'])) { // FIXED: unified verification
    Session::redirectCode(1);
}

if (passwordNeedsRehash($loginData['password'])) { // FIXED: upgrade legacy hash
    $newHash = cryptPassword($password); // FIXED: regenerate hash
    $GLOBALS['DATABASE']->query("UPDATE ".USERS." SET password = '".$GLOBALS['DATABASE']->escape($newHash)."' WHERE id = ".$loginData['id'].";");
    $loginData['password'] = $newHash;
}

$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET peacefull_last_update = '".TIMESTAMP."' WHERE id = ".$loginData['id'].";");
Session::create($loginData['id']);
HTTP::redirectTo('game.php?page=overview');
