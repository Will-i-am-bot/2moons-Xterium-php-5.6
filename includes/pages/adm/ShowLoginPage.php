<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kröpke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2012 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.3 (2013-05-19)
 * @info $Id: ShowLoginPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

function ShowLoginPage()
{
	global $USER, $LNG;

	if (!isset($USER['authlevel']) || $USER['authlevel'] < AUTH_MOD) {
		// FIXED: replaced permission error with proper admin redirect
		error_log('Admin permission denied for IP: ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));

		$requestedPage	= isset($_REQUEST['page']) ? $_REQUEST['page'] : '';

		if ($requestedPage !== 'login') {
			HTTP::redirectTo('admin.php?page=login');
		}

		HTTP::redirectTo('game.php');
	}

	if (!isset($_SESSION['admin_login'])) {
		$_SESSION['admin_login'] = null; // FIXED: ensure admin session marker exists
	}

	if(isset($_REQUEST['admin_pw']))
	{
		$plainPassword	= $_REQUEST['admin_pw']; // FIXED: capture raw password

		if (verifyPassword($plainPassword, $USER['password'])) { // FIXED: unified verification
			if(passwordNeedsRehash($USER['password'])) { // FIXED: upgrade legacy hash
				$newHash = cryptPassword($plainPassword); // FIXED: regenerate hash
				$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET `password` = '".$GLOBALS['DATABASE']->sql_escape($newHash)."' WHERE `id` = " . $USER['id'] . ";"); // FIXED: persist unified hash
				$USER['password'] = $newHash; // FIXED: refresh runtime copy
			}

			$_SESSION['admin_login']	= $USER['password']; // FIXED: store canonical hash
			$_SESSION['admin_auth']	= $USER['authlevel']; // FIXED: keep admin auth level in session
			HTTP::redirectTo('admin.php');
		}
	}

	$template	= new template();

	$template->assign_vars(array(
		'bodyclass'	=> 'standalone',
		'username'	=> $USER['username']
	));
	$template->show('LoginPage.tpl');
}
