<?php

class ShowOverviewPage extends AbstractPage 
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
		private function GetTeamspeakData()
		{
		global $CONF, $USER, $LNG;
		if (Config::get('ts_modon') == 0)
		{
			return false;
		}
		
		$GLOBALS['CACHE']->add('teamspeak', 'TeamspeakBuildCache');
		$tsInfo	= $GLOBALS['CACHE']->get('teamspeak', false);
		
		if(empty($tsInfo))
		{
			return array(
				'error'	=> $LNG['ov_teamspeak_not_online']
			);
		}
		
		switch(Config::get('ts_version'))
		{
			case 2:
				$url = 'teamspeak://%s:%s?nickname=%s';
			break;
			case 3:
				$url = 'ts3server://%s?port=%d&amp;nickname=%s&amp;password=%s';
			break;
		}
		
		return array(
			'url'		=> sprintf($url, Config::get('ts_server'), Config::get('ts_tcpport'), $USER['username'], $tsInfo['password']),
			'current'	=> $tsInfo['current'],
			'max'		=> $tsInfo['maxuser'],
			'error'		=> false,
		);
	}

	private function GetFleets() {
		global $USER, $PLANET;
		require_once('includes/classes/class.FlyingFleetsTable.php');
		$fleetTableObj = new FlyingFleetsTable;
		$fleetTableObj->setUser($USER['id']);
		$fleetTableObj->setPlanet($PLANET['id']);
		return $fleetTableObj->renderTable();
	}
	
	function savePlanetAction()
	{
		global $USER, $PLANET;
		$password =	HTTP::_GP('password', '', true);
		if (!empty($password))
		{
			$IfFleets	= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".FLEETS." WHERE 
			(
				fleet_owner = ".$USER['id']."
				AND (
					fleet_start_id = ".$PLANET['id']." OR fleet_start_id = ".$PLANET['id_luna']."
				)
			) OR (
				fleet_target_owner = ".$USER['id']." 
				AND (
					fleet_end_id = ".$PLANET['id']." OR fleet_end_id = ".$PLANET['id_luna']."
				)
			);");
			
			if ($IfFleets > 0)
				exit(json_encode(array('message' => $LNG['ov_abandon_planet_not_possible'])));
			elseif ($USER['id_planet'] == $PLANET['id'])
				exit(json_encode(array('message' => $LNG['ov_principal_planet_cant_abanone'])));
			elseif (md5($password) != $USER['password'])
				exit(json_encode(array('message' => $LNG['ov_wrong_pass'])));
			else
			{
				if($PLANET['planet_type'] == 1) {
					$GLOBALS['DATABASE']->multi_query("UPDATE ".PLANETS." SET destruyed = ".(TIMESTAMP+ 86400)." WHERE id = ".$PLANET['id'].";DELETE FROM ".PLANETS." WHERE id = ".$PLANET['id_luna'].";");
				} else {
					$GLOBALS['DATABASE']->multi_query("UPDATE ".PLANETS." SET id_luna = 0 WHERE id_luna = ".$PLANET['id'].";DELETE FROM ".PLANETS." WHERE id = ".$PLANET['id'].";");
				}
				
				$PLANET['id']	= $USER['id_planet'];
				exit(json_encode(array('ok' => true, 'message' => $LNG['ov_planet_abandoned'])));
			}
		}
	}
		
	function show()
	{
		global $CONF, $LNG, $PLANET, $USER, $resource, $UNI;
		require_once 'includes/events/EventManager.php';
		EventManager::checkAndRunActiveEvents();

		$AdminsOnline   = array();
		$chatOnline     = array();
		$AllPlanets     = array();
		$Moon           = array();
		$RefLinks       = array();
		$Buildtime      = 0;


		foreach($USER['PLANETS'] as $ID => $CPLANET)
		{		
			if ($ID == $PLANET['id'] || $CPLANET['planet_type'] == 3)
				continue;

			if (!empty($CPLANET['b_building']) && $CPLANET['b_building'] > TIMESTAMP) {
				$Queue				= unserialize($CPLANET['b_building_id']);
				$BuildPlanet		= $LNG['tech'][$Queue[0][0]]." (".$Queue[0][1].")<br><span style=\"color:#7F7F7F;\">(".pretty_time($Queue[0][3] - TIMESTAMP).")</span>";
			} else {
				$BuildPlanet     = $LNG['ov_free'];
			}
			
			$AllPlanets[] = array(
				'id'	=> $CPLANET['id'],
				'name'	=> $CPLANET['name'],
				'image'	=> $CPLANET['image'],
				'build'	=> $BuildPlanet,
			);
		} 
	
		
		if ($PLANET['id_luna'] != 0) {
			$Moon		= $GLOBALS['DATABASE']->getFirstRow("SELECT id, name FROM ".PLANETS." WHERE id = '".$PLANET['id_luna']."';");
		}
			
		if ($PLANET['b_building'] - TIMESTAMP > 0) {
			$Queue			= unserialize($PLANET['b_building_id']);
			$buildInfo['buildings']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $PLANET['b_building'] - TIMESTAMP,
				'time'		=> $PLANET['b_building'],
				'starttime'	=> pretty_time($PLANET['b_building'] - TIMESTAMP),
			);
		}
		else {
			$buildInfo['buildings']	= false;
		}
		
		/* As FR#206 (http://tracker.2moons.cc/view.php?id=206), i added the shipyard and research status here, but i add not them the template. */
		
		if (!empty($PLANET['b_hangar_id'])) {
			$Queue	= unserialize($PLANET['b_hangar_id']);
			$time	= BuildFunctions::getBuildingTime($USER, $PLANET, $Queue[0][0]) * $Queue[0][1];
			$buildInfo['fleet']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $time - $PLANET['b_hangar'],
				'time'		=> $time,
				'starttime'	=> pretty_time($time - $PLANET['b_hangar']),
			);
		}
		else {
			$buildInfo['fleet']	= false;
		}
		
		if ($USER['b_tech'] - TIMESTAMP > 0) {
			$Queue			= unserialize($USER['b_tech_queue']);
			$buildInfo['tech']	= array(
				'id'		=> $Queue[0][0],
				'level'		=> $Queue[0][1],
				'timeleft'	=> $USER['b_tech'] - TIMESTAMP,
				'time'		=> $USER['b_tech'],
				'starttime'	=> pretty_time($USER['b_tech'] - TIMESTAMP),
			);
		}
		else {
			$buildInfo['tech']	= false;
		}
		
		
		
		$OnlineAdmins 	= $GLOBALS['DATABASE']->query("SELECT id,username FROM ".USERS." WHERE universe = ".$UNI." AND onlinetime >= ".(TIMESTAMP-10*60)." AND authlevel > '".AUTH_USR."';");
		while ($AdminRow = $GLOBALS['DATABASE']->fetch_array($OnlineAdmins)) {
			$AdminsOnline[$AdminRow['id']]	= $AdminRow['username'];
		}
		$GLOBALS['DATABASE']->free_result($OnlineAdmins);
		$balken = $GLOBALS['DATABASE']->countquery("SELECT COUNT(*) FROM ".USERS." WHERE universe = ".$UNI." AND onlinetime > '".(TIMESTAMP - 15 * 60 )."';");	$länge2 = 365/$CONF['users_amount'];	$länge  = $balken*$länge2;

		
		$chatUsers 	= $GLOBALS['DATABASE']->query("SELECT userName FROM ".CHAT_ON." WHERE dateTime > DATE_SUB(NOW(), interval 2 MINUTE) AND channel = 0");
		while ($chatRow = $GLOBALS['DATABASE']->fetch_array($chatUsers)) {
			$chatOnline[]	= $chatRow['userName'];
		}

		$GLOBALS['DATABASE']->free_result($chatUsers);
		
		//$this->tplObj->loadscript('overview.js');

		$Messages		= $USER['messages'];
		
		// Fehler: Wenn Spieler gelöscht werden, werden sie nicht mehr in der Tabelle angezeigt.
		$RefLinksRAW	= $GLOBALS['DATABASE']->query("SELECT u.id, u.username, s.total_points FROM ".USERS." as u LEFT JOIN ".STATPOINTS." as s ON s.id_owner = u.id AND s.stat_type = '1' WHERE ref_id = ".$USER['id'].";");
		
		if(Config::get('ref_active')) 
		{
			while ($RefRow = $GLOBALS['DATABASE']->fetch_array($RefLinksRAW)) {
				$RefLinks[$RefRow['id']]	= array(
					'username'	=> $RefRow['username'],
					'points'	=> min($RefRow['total_points'], Config::get('ref_minpoints'))
				);
			}
		}
		$statinfo	= $GLOBALS['DATABASE']->query("SELECT s.total_old_rank, s.total_rank FROM ".USERS." as u LEFT JOIN ".STATPOINTS." as s ON s.id_owner = u.id AND s.stat_type = '1' WHERE id = ".$USER['id'].";");
		while ($game = $GLOBALS['DATABASE']->fetch_array($statinfo)) {
		$ranking	= $game['total_old_rank'] - $game['total_rank'];
		
		if($ranking == 0){
		$position = "<span style='color:#87CEEB'>(*)</span>";
		}elseif($ranking < 0){
		$position = "<span style='color:red'>(".$ranking.")</span>";
		}elseif ($ranking > 0){
		$position = "<span style='color:green'>(+".$ranking.")</span>";
		}
		}
		if($USER['total_rank'] == 0) {
			$rankInfo	= "-";
		} else {
			$rankInfo	= sprintf($LNG['ov_userrank_info'], pretty_number($USER['total_points']), $LNG['ov_place'], $USER['total_rank'], $USER['total_rank'], $position, $LNG['ov_of'], Config::get('users_amount'));
		}
		
		$manual_start = 1;
		if($USER['training'] == 0 && $USER['training_step'] == 0){
		$manual_start = 0;
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '1' WHERE id = ".$USER['id'].";");
		}
		
		$manual_12 = 1;
		if($USER['training'] == 0 && $USER['training_step'] == 12){
		$manual_12 = 0;
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '13' WHERE id = ".$USER['id'].";");
		}
		
		$manual_20 = 1;
		if($USER['training'] == 0 && $USER['training_step'] == 20){
		$manual_20 = 0;
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET experience_peace = experience_peace + '650' WHERE id = ".$USER['id'].";");
		}
		
		$facebook_unliked = 1;
		if($USER['training'] == 1 && $USER['facebook_liked'] == 0){
		$facebook_unliked = 0;
		}
		
		$this->tplObj->loadscript("countdown.js");
		$this->tplObj->assign_vars(array(
			'competition_active' => ((!empty($CONF['end_game']) && $CONF['end_game'] > TIMESTAMP) ? ($CONF['end_game'] - TIMESTAMP) : 0),
			'rankInfo'					=> $rankInfo,
			'facebook_unliked'					=> $facebook_unliked,
			'planet_protections' => (($USER['immunity_until'] > TIMESTAMP) ? ($USER['immunity_until'] - TIMESTAMP) : 0),
			'planet_protectionbis' => $USER['immunity_until'],
			'manual_12'					=> $manual_12,
			'manual_20'					=> $manual_20,
			'is_news'					=> Config::get('OverviewNewsFrame'),
			'news'						=> makebr(Config::get('OverviewNewsText')),
			'planetname'				=> $PLANET['name'],
			'planetimage'				=> $PLANET['image'],
			'galaxy'					=> $PLANET['galaxy'],
			'system'					=> $PLANET['system'],
			'planet'					=> $PLANET['planet'],
			'planet_type'				=> $PLANET['planet_type'],
			'username'					=> $USER['username'],
			'training'					=> $manual_start,
			'userid'					=> $USER['id'],
			'buildInfo'					=> $buildInfo,
			'Moon'						=> $Moon,
			'fleets'					=> $this->GetFleets(),
			'AllPlanets'				=> $AllPlanets,
			'AdminsOnline'				=> $AdminsOnline,
			'teamspeakData'				=> $this->GetTeamspeakData(),
			'messages'					=> ($Messages > 0) ? (($Messages == 1) ? $LNG['ov_have_new_message'] : sprintf($LNG['ov_have_new_messages'], pretty_number($Messages))): false,
			'planet_diameter'			=> pretty_number($PLANET['diameter']),
			'planet_field_current' 		=> $PLANET['field_current'],
			'planet_field_max' 			=> CalculateMaxPlanetFields($PLANET),
			'planet_temp_min' 			=> $PLANET['temp_min'],
			'planet_temp_max' 			=> $PLANET['temp_max'],
			'ref_active'				=> Config::get('ref_active'),
			'ref_minpoints'				=> Config::get('ref_minpoints'),
			'RefLinks'					=> $RefLinks,
			'chatOnline'				=> $chatOnline,
			'servertime'				=> _date("M D d H:i:s", TIMESTAMP, $USER['timezone']),
			'path'						=> HTTP_PATH,
			'online_users'              => $balken,		'balken',
		));
		
		$this->display('page.overview.default.tpl');
	}
	
	function actions() 
	{
		global $LNG, $PLANET;

		$this->initTemplate();
		$this->setWindow('popup');
		
		$this->tplObj->loadscript('overview.actions.js');
		$this->tplObj->assign_vars(array(
			'ov_security_confirm'		=> sprintf($LNG['ov_security_confirm'], $PLANET['name'].' ['.$PLANET['galaxy'].':'.$PLANET['system'].':'.$PLANET['planet'].']'),
		));
		$this->display('page.overview.actions.tpl');
	}
	
	function rename() 
	{
		global $LNG, $PLANET;

		$newname        = HTTP::_GP('name', '', UTF8_SUPPORT);
		if (!empty($newname))
		{
			if (!CheckName($newname)) {
				$this->sendJSON(array('message' => $LNG['ov_newname_specialchar'], 'error' => true));
			} else {
				$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET name = '".$GLOBALS['DATABASE']->sql_escape($newname)."' WHERE id = ".$PLANET['id'].";");
				$this->sendJSON(array('message' => $LNG['ov_newname_done'], 'error' => false));
			}
		}
	}
	
	function GenerateName() 
	{
		global $LNG, $PLANET;
		
		$Names		= file('botnames.txt');
		$NamesCount	= count($Names);
		$Rand		= mt_rand(0, $NamesCount);
		$UserName 	= trim($Names[$Rand]);
		
	
		$this->sendJSON(array('message' => $UserName));
			
		
	}
	
	function starttraining() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '1' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	
	function starttraining2() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '2' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining3() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '4' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining4() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '5' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining5() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '9' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining6() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '12' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining7() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '14' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	
	function starttraining8() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '16' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining9() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '18' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining10() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '19' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining11() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '20' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining12() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '22' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining13() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '23' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining14() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '25' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	
	function facebook() 
	{
		global $USER, $LNG, $PLANET;
		
		if($USER['facebook_liked'] == 0){
		$GLOBALS['DATABASE']->query("INSERT INTO uni1_facebook_gift VALUES (".$USER['id'].", ".TIMESTAMP.");");
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET darkmatter = darkmatter + '500000' WHERE id = '".$USER['id']."'");
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET antimatter = antimatter + '5000' WHERE id = '".$USER['id']."'");
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET facebook_liked = '1' WHERE id = '".$USER['id']."'");
		
			}

	}
	
	function endfacebook() 
	{
		global $USER, $LNG, $PLANET;
		

		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET facebook_liked = '1' WHERE id = '".$USER['id']."'");
		
			}

	
	
		function facebookbis() 
	{
		global $USER, $LNG, $PLANET;
		
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET darkmatter = darkmatter - '500000' WHERE id = '".$USER['id']."'");
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET antimatter = antimatter - '5000' WHERE id = '".$USER['id']."'");
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET facebook_liked = '0' WHERE id = '".$USER['id']."'");
		
		
	}
	
	function starttraining15() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '27' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining16() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '28' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	function starttraining17() 
	{
		global $USER, $LNG, $PLANET;
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training = '1' WHERE id = '".$USER['id']."'");
	
		echo json_encode(array('success' => true)); 
			
		
	}
	
	function starttraining20() 
	{
		global $USER, $LNG, $PLANET;
		
		if($USER['training_step'] == 14 && $USER['training'] == 0 ){
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET training_step = '15' WHERE id = '".$USER['id']."'");
	} 
		echo json_encode(array('success' => true)); 
			
		
	}
	
	function Notification() 
	{
		global $USER, $LNG, $PLANET;
		$resp = 0;
		$loginID = 0;
		$type = 0;
		$text = 0;
		$getch = $GLOBALS['DATABASE']->query("SELECT COUNT(userID) as id, loginID, type FROM `uni1_buddy_notif` where userID = '".$USER['id']."' AND called = '0'");
		
			
		while($x = $GLOBALS['DATABASE']->fetch_array($getch)){
			if($x['id'] > 0){
                $resp = $x['id'];
                $loginID = getUsername($x['loginID']);
				$type = '';
				$text = '';
				
				if($x['type'] == 1){
				$type = 'success';
				$text = 'We are happy to announce you that '.$loginID.' logged in';
				}else{
				$type = 'error';
				$text = 'unfortunately, your friend '.$loginID.' logged out';
				}
                //$resp = 0;    
        
		
		$GLOBALS['DATABASE']->query("UPDATE `uni1_buddy_notif` SET called = '1' where called = '0'");
		$this->sendJSON(array('message' => $resp, 'loginID' => $loginID, 'type' => $type, 'text' => $text));
	}
	}
	}
	
	function Alarm() 
	{
		global $USER, $LNG, $PLANET;
		
		$resp = 0;
		
		$getch5 = $GLOBALS['DATABASE']->query("SELECT onlinetime FROM `uni1_users` where id = '".$USER['id']."'");
		while($xd = $GLOBALS['DATABASE']->fetch_array($getch5)){
                $ksos = $xd['onlinetime'];
                //$resp = 0;
               
        }
		
		
		//if($ksos > TIMESTAMP - 10 * 60){
		$getch = $GLOBALS['DATABASE']->query("SELECT COUNT(fleet_id) as id FROM `uni1_fleets_alarm` where `fleet_mission` = 1 AND fleet_target_owner = '".$USER['id']."' AND called = '0'");
		while($x = $GLOBALS['DATABASE']->fetch_array($getch)){
                $resp = $x['id'];
                //$resp = 0;
               
        }
		
		$wsh = $GLOBALS['DATABASE']->query("SELECT fleet_id FROM `uni1_fleets_alarm` where `fleet_mission` = 1 AND fleet_target_owner = '".$USER['id']."' AND called = '0'");
		if($GLOBALS['DATABASE']->numRows($wsh)> 0 ){
		while($tx = $GLOBALS['DATABASE']->fetch_array($wsh)){
                $wesh = $tx['fleet_id'];
                //$resp = 0;
               
        }
		
		$GLOBALS['DATABASE']->query("UPDATE `uni1_fleets_alarm` SET called = '1' where fleet_id = '".$wesh."'");
		
		}
		$GLOBALS['DATABASE']->query("DELETE FROM `uni1_fleets_alarm` where fleet_mission != '1'");
		$GLOBALS['DATABASE']->query("DELETE FROM `uni1_fleets_alarm` where called = '1'");
		//}
		$this->sendJSON(array('message' => $resp));
			
		
	}
	
	
	function newmail() 
	{
		global $USER, $LNG, $PLANET;
		
		$getch = $GLOBALS['DATABASE']->query("SELECT SUM(message_unread) as id FROM `uni1_messages` where `message_owner` = '".$USER['id']."' AND `message_unread` = '1' ");
		while($x = $GLOBALS['DATABASE']->fetch_array($getch)){
                $resp = $x['id'];
               
        }
		if($resp == null){
		$resp = 0;
		}
		$this->sendJSON(array('message' => $resp));
			
		
	}
	
	function delete() 
	{
		global $LNG, $PLANET, $USER;
		$password	= HTTP::_GP('password', '', true);
		
		if (!empty($password))
		{
			$IfFleets	= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".FLEETS." WHERE 
			(
				fleet_owner = '".$USER['id']."'
				AND (
						fleet_start_id = ".$PLANET['id']." OR fleet_start_id = ".$PLANET['id_luna']."
				)
			) OR (
				fleet_target_owner = '".$USER['id']."' 
				AND (
						fleet_end_id = '".$PLANET['id']."' OR fleet_end_id = ".$PLANET['id_luna']."
				)
			);");

			if ($IfFleets > 0) {
				$this->sendJSON(array('message' => $LNG['ov_abandon_planet_not_possible']));
			} elseif ($USER['id_planet'] == $PLANET['id']) {
				$this->sendJSON(array('message' => $LNG['ov_principal_planet_cant_abanone']));
			} elseif (md5($password) != $USER['password']) {
				$this->sendJSON(array('message' => $LNG['ov_wrong_pass']));
			} elseif ($USER['planet_cloak'] > TIMESTAMP) {
				$this->sendJSON(array('message' => 'Planet Cloak plugis is activated'));
			} else {
				if($PLANET['planet_type'] == 1) {
					$GLOBALS['DATABASE']->multi_query("UPDATE ".PLANETS." SET destruyed = ".(TIMESTAMP + 86400)." WHERE id = ".$PLANET['id'].";DELETE FROM ".PLANETS." WHERE id = ".$PLANET['id_luna'].";");
				} else {
					$GLOBALS['DATABASE']->multi_query("UPDATE ".PLANETS." SET id_luna = '0' WHERE id_luna = ".$PLANET['id'].";DELETE FROM ".PLANETS." WHERE id = ".$PLANET['id'].";");
				}
				
				$_SESSION['planet']     = $USER['id_planet'];
				$this->sendJSON(array('ok' => true, 'message' => $LNG['ov_planet_abandoned']));
			}
		}
	}
}
