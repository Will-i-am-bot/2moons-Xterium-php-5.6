<?php
function ShowLoginPage()
{
	global $USER, $LNG;
	
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
