<?php

/**
 *  2Moons
 *  Copyright (C) 2011  Slaver
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
 * @author Slaver <slaver7@gmail.com>
 * @copyright 2009 Lucky <lucky@xgproyect.net> (XGProyecto)
 * @copyright 2011 Slaver <slaver7@gmail.com> (Fork/2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.6.1 (2011-11-19)
 * @info $Id: class.template.php 2641 2013-03-24 13:43:52Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

require('includes/libs/Smarty/Smarty.class.php');
		
class template extends Smarty
{
	protected $window	= 'full';
	protected $jsscript	= array();
	protected $script	= array();
	
	function __construct()
	{	
		parent::__construct();
		$this->smartySettings();
	}
	
	function smartySettings()
	{	
		$this->force_compile 			= false;
		$this->caching 					= true; #Set true for production!
		$this->merge_compiled_includes	= true;
		$this->compile_check			= true; #Set false for production!
		$this->php_handling				= Smarty::PHP_REMOVE;
		
		$compileDir = ROOT_PATH.'cache/';
		if(!is_dir($compileDir) || !is_writable($compileDir))
		{
			$compileDir = $this->getTempPath();
		}
		$this->setCompileDir($compileDir);
		
		$cacheDir = ROOT_PATH.'cache/templates';
		if(!is_dir($cacheDir))
		{
			@mkdir($cacheDir, 0777, true);
		}
		$this->setCacheDir($cacheDir);
		
		$templateDirectories = array();
		$templateDirectories[] = ROOT_PATH.'styles/templates/';
		
		$gameTemplateDir = ROOT_PATH.'styles/templates/game/';
		if(is_dir($gameTemplateDir))
		{
			$templateDirectories[] = $gameTemplateDir;
		}
		
		$this->setTemplateDir($templateDirectories);
	}
	
	public function loadscript($script)
	{
		$this->jsscript[]			= substr($script, 0, -3);
	}
	
	public function execscript($script)
	{
		$this->script[]				= $script;
	}
	
	public function getTempPath()
	{
		$this->force_compile 		= true;
		include 'includes/libs/wcf/BasicFileUtil.class.php';
		return BasicFileUtil::getTempFolder();
	}
		
	public function assign_vars($var, $nocache = true) 
	{		
		parent::assign($var, NULL, $nocache);
	}
	
	private function adm_main()
	{
		global $LNG, $USER, $DATABASE, $UNI; // FIXED: unified admin layout

		$dateTimeServer		= new DateTime("now");
		if(isset($USER['timezone'])) {
			try {
				$dateTimeUser	= new DateTime("now", new DateTimeZone($USER['timezone']));
			} catch (Exception $e) {
				$dateTimeUser	= $dateTimeServer; // FIXED: undefined offset protection
			}
		} else {
			$dateTimeUser	= $dateTimeServer;
		}

		$this->assign_vars(array(
				'scripts'				=> $this->script,
				'title'				=> Config::get('game_name').' - '.$LNG['adm_cp_title'],
				'fcm_info'			=> $LNG['fcm_info'],
				'lang'				=> $LNG->getLanguage(),
				'REV'				=> substr(Config::get('VERSION'), -4),
				'date'				=> explode("|", date('Y|n|j|G|i|s|Z', TIMESTAMP)),
				'Offset'			=> $dateTimeUser->getOffset() - $dateTimeServer->getOffset(),
				'VERSION'			=> Config::get('VERSION'),
				'dpath'				=> 'styles/theme/gow/',
				'bodyclass'		=> 'full'
		));

		$currentPage = HTTP::_GP('page', 'overview'); // FIXED: preserve navigation
		if(empty($currentPage))
		{
			$currentPage = 'overview'; // FIXED: preserve navigation
		}

		if($this->getTemplateVars('showAdminLayout') === NULL)
		{
			$this->assign_vars(array(
					'showAdminLayout'		=> true, // FIXED: unified admin layout
			));
		}

		if($this->getTemplateVars('pageTitle') === NULL)
		{
			$this->assign_vars(array(
					'pageTitle'			=> Config::get('game_name').' - '.$LNG['adm_cp_title'], // FIXED: consistent include path
			));
		}

		if(!isset($_SESSION['adminuni']) || empty($_SESSION['adminuni']))
		{
			$_SESSION['adminuni'] = $UNI; // FIXED: preserve navigation
		}

		$AvailableUnis  = array(); // FIXED: unified admin layout
		$AvailableUnis[Config::get('uni')] = Config::get('uni_name').' (ID: '.Config::get('uni').')';

		$UniverseQuery  = $DATABASE->query("SELECT `uni`, `uni_name` FROM ".CONFIG." WHERE `uni` != '".$UNI."' ORDER BY `uni` ASC;");

		while($UniverseRow = $DATABASE->fetch_array($UniverseQuery))
		{
			$AvailableUnis[$UniverseRow['uni']] = $UniverseRow['uni_name'].' (ID: '.$UniverseRow['uni'].')'; // FIXED: preserve navigation
		}

		ksort($AvailableUnis);

		$this->assign_vars(array(
				'activePage'			=> $currentPage, // FIXED: unified admin layout
				'AvailableUnis'		=> $AvailableUnis,
				'UNI'				=> $_SESSION['adminuni'],
				'adminUser'			=> $USER,
		));
	}

	public function show($file)
	{		
		global $USER, $PLANET, $LNG, $THEME;

		if($THEME->isCustomTPL($file))
			$this->setTemplateDir($THEME->getTemplatePath());
			
		$tplDir	= $this->getTemplateDir();
			
		if(MODE === 'INSTALL') {
			$this->setTemplateDir($tplDir[0].'install/');
		} elseif(MODE === 'ADMIN') {
			$this->setTemplateDir($tplDir[0].'adm/');
			$this->adm_main();
		}

		$this->assign_vars(array(
			'scripts'		=> $this->jsscript,
			'execscript'	=> implode("\n", $this->script),
		));

		$this->assign_vars(array(
			'LNG'			=> $LNG,
		), false);
		
		$this->compile_id	= $LNG->getLanguage();
		
		parent::display($file);
	}
	
	public function display($file)
	{
		global $LNG;
		$this->compile_id	= $LNG->getLanguage();
		parent::display($file);
	}
	
	public function gotoside($dest, $time = 3)
	{
		$this->assign_vars(array(
			'gotoinsec'	=> $time,
			'goto'		=> $dest,
		));
	}
	
	public function message($mes, $dest = false, $time = 3, $Fatal = false)
	{
		global $LNG, $THEME;
	
		$this->assign_vars(array(
			'mes'		=> $mes,
			'fcm_info'	=> $LNG['fcm_info'],
			'Fatal'		=> $Fatal,
			'dpath'		=> $THEME->getTheme(),
		));
		
		$this->gotoside($dest, $time);
	
		$templateFile = 'error_message_body.tpl';
		$templateDirectory = $this->locateTemplateDirectory($templateFile);
	
		if($templateDirectory === false)
		{
			$fallbackSource = $this->locateFallbackTemplate($templateFile);
			if($fallbackSource !== false)
			{
				$templateDirectory = $this->prepareTemplateFromFallback($templateFile, $fallbackSource);
			}
			else
			{
				$templateDirectory = $this->generateDefaultErrorTemplate($templateFile);
			}
		}
	
		if($templateDirectory === false)
		{
			$this->displayInlineErrorMessage($mes, $dest, $time, $Fatal);
			return;
		}
	
		$currentTemplateDir = $this->getTemplateDir();
		$this->setTemplateDir($templateDirectory);
		$this->show($templateFile);
		$this->setTemplateDir($currentTemplateDir);
	}
	
	public static function printMessage($Message, $fullSide = true, $redirect = NULL) {
		$template	= new self;
		if(!isset($redirect)) {
			$redirect	= array(false, 0);
		}
		
		$template->message($Message, $redirect[0], $redirect[1], !$fullSide);
		exit;
	}
	
    /**
    * Workaround  for new Smarty Method to add custom props...
    */

    public function __get($name)
    {
        $allowed = array(
        'template_dir' => 'getTemplateDir',
        'config_dir' => 'getConfigDir',
        'plugins_dir' => 'getPluginsDir',
        'compile_dir' => 'getCompileDir',
        'cache_dir' => 'getCacheDir',
        );

        if (isset($allowed[$name])) {
            return $this->{$allowed[$name]}();
        } else {
            return $this->{$name};
        }
    }
	
    public function __set($name, $value)
    {
        $allowed = array(
        'template_dir' => 'setTemplateDir',
        'config_dir' => 'setConfigDir',
        'plugins_dir' => 'setPluginsDir',
        'compile_dir' => 'setCompileDir',
        'cache_dir' => 'setCacheDir',
        );

        if (isset($allowed[$name])) {
            $this->{$allowed[$name]}($value);
        } else {
            $this->{$name} = $value;
        }
    }

	protected function locateTemplateDirectory($file)
	{
		$directories = $this->getTemplateDir();
		if(!is_array($directories))
		{
			$directories = array($directories);
		}

		$defaultDirectories = array(
			ROOT_PATH.'styles/templates/game/',
			ROOT_PATH.'styles/templates/',
		);

		foreach($defaultDirectories as $directory)
		{
			if(!in_array($directory, $directories))
			{
				$directories[] = $directory;
			}
		}

		$checked = array();
		foreach($directories as $directory)
		{
			if(empty($directory))
			{
				continue;
			}

			$directory = rtrim($directory, '/\\').'/';

			if(isset($checked[$directory]))
			{
				continue;
			}

			$checked[$directory] = true;

			if(is_file($directory.$file))
			{
				return $directory;
			}
		}

		return false;
	}

	protected function locateFallbackTemplate($file)
	{
		$paths = array(
			ROOT_PATH.'styles/templates/game/'.$file,
			ROOT_PATH.'styles/templates/'.$file,
			ROOT_PATH.'styles/templates/adm/'.$file,
			ROOT_PATH.'styles/templates/install/'.$file,
		);

		foreach($paths as $path)
		{
			if(is_file($path))
			{
				return $path;
			}
		}

		return false;
	}

	protected function prepareTemplateFromFallback($file, $source)
	{
		$targetDir = ROOT_PATH.'styles/templates/game/';

		if(!is_dir($targetDir))
		{
			@mkdir($targetDir, 0777, true);
		}

		if(is_dir($targetDir) && is_writable($targetDir))
		{
			if(@copy($source, $targetDir.$file) || is_file($targetDir.$file))
			{
				return $targetDir;
			}
		}

		return dirname($source).'/';
	}

	protected function generateDefaultErrorTemplate($file)
	{
		$targetDir = ROOT_PATH.'styles/templates/game/';

		if(!is_dir($targetDir))
		{
			@mkdir($targetDir, 0777, true);
		}

		if(is_dir($targetDir) && is_writable($targetDir))
		{
			$content = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>{if isset($LNG.sys_error_headline)}{$LNG.sys_error_headline}{else}{$fcm_info}{/if}</title>
	<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
</head>
<body class="message error">
	<div id="errorMessage">
		<div class="messageBox">
			<h1>{if isset($LNG.sys_error_headline)}{$LNG.sys_error_headline}{else}{$fcm_info}{/if}</h1>
			<p>{$mes}</p>
			{if $goto}
			<p class="redirect">{$LNG.sys_redirect_message|default:'Weiterleitung'}: <a href="{$goto}">{$goto}</a></p>
			{/if}
		</div>
	</div>
	{if !$Fatal}
	<script type="text/javascript">
	{literal}
		(function(){
			var redirectLink = '{/literal}{$goto|default:''}{literal}';
			var redirectDelay = {/literal}{$gotoinsec|default:0}{literal};
			if(redirectLink !== '' && redirectDelay > 0){
				window.setTimeout(function(){ window.location.href = redirectLink; }, redirectDelay * 1000);
			}
		})();
	{/literal}
	</script>
	{/if}
</body>
</html>
EOT;

			if(@file_put_contents($targetDir.$file, $content) !== false)
			{
				return $targetDir;
			}
		}

		return false;
	}

	protected function displayInlineErrorMessage($mes, $dest, $time, $Fatal)
	{
		header('Content-Type: text/html; charset=UTF-8');
		$gameName = Config::get('game_name');
		echo '<!DOCTYPE html><html><head><meta charset="UTF-8" />';
		echo '<title>'.htmlspecialchars($gameName, ENT_QUOTES, 'UTF-8').'</title>';
		echo '</head><body class="message error">';
		echo '<div id="errorMessage"><div class="messageBox">';
		echo '<h1>'.htmlspecialchars($gameName, ENT_QUOTES, 'UTF-8').'</h1>';
		echo '<p>'.nl2br(htmlspecialchars($mes, ENT_QUOTES, 'UTF-8')).'</p>';
		if(!empty($dest))
		{
			echo '<p class="redirect"><a href="'.htmlspecialchars($dest, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($dest, ENT_QUOTES, 'UTF-8').'</a></p>';
		}
		if(!$Fatal && !empty($dest) && !empty($time))
		{
			$seconds = (int) $time;
			echo '<script type="text/javascript">';
			echo 'setTimeout(function(){ window.location.href = \'' . addslashes($dest) . '\'; }, ' . ($seconds * 1000) . ');';
			echo '</script>';
		}
		echo '</div></div></body></html>';
		exit;
	}

}
