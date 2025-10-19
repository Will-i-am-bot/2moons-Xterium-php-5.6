	if($CONF['treasure_event'] < TIMESTAMP) {
	$cautare = $GLOBALS['DATABASE']->query("SELECT * FROM ".PLANETS." p INNER JOIN ".USERS." u ON p.id_owner = u.id WHERE p.universe = ".$UNI." AND u.universe = ".$UNI." AND u.onlinetime < ".(TIMESTAMP - 60*60*24*7)." AND u.urlaubs_modus = 0 AND p.planet_type = 1 ORDER BY RAND() LIMIT 20;");
	while($xys = $GLOBALS['DATABASE']->fetch_array($cautare)){
	$metal = 500000000000;
	$crystal = 400000000000;
	$deuterium= 300000000000;
	$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `metal` =   ".$metal.", `crystal` = ".$crystal.", `deuterium` = ".$deuterium." WHERE id = ".$xys['id'].";");
	}
	$totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." WHERE universe = ".$UNI.";");
	while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
	$message = "<span class='admin'>Treasure Hunt has started.<br>
20 Inactive planets have big amounts of resources.<br>
Search for it, and steal it.<br>
Happy Hunting !!!<br><br>
La chasse au tresor a commencer.<br>
20 planets inactive sont rempli de resource.<br>
Chercher les, et vider les tous.<br>
Happy Hunting !!!<br><br>
SchatzJagd Hat Begonnen<br>
20 inaktiver Planeten, ist voll mit vielen Rohstoffen<br>
Du musst ihn finden, und hol dir die Rohstoffe<br>
Viel Spass beim Suchen !!! 
	</span>";
    SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Treasure Event", $message);
    }
	$new_event = $CONF['treasure_event'] + 60*60*24;
    $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET treasure_event = '".$new_event."' where `uni` = '".$UNI."';");
	}	
	
	if($CONF['treasure_event_1'] < TIMESTAMP) {
	$cautare = $GLOBALS['DATABASE']->query("SELECT * FROM ".PLANETS." p INNER JOIN ".USERS." u ON p.id_owner = u.id WHERE p.universe = ".$UNI." AND u.universe = ".$UNI." AND u.onlinetime < ".(TIMESTAMP - 60*60*24*7)." AND u.urlaubs_modus = 0 AND p.planet_type = 1 AND p.universe = ".$UNI." ORDER BY RAND() LIMIT 20;");
	while($xys = $GLOBALS['DATABASE']->fetch_array($cautare)){
	$metal = 500000000000;
	$crystal = 400000000000;
	$deuterium= 300000000000;
	$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `metal` =   ".$metal.", `crystal` = ".$crystal.", `deuterium` = ".$deuterium." WHERE id = ".$xys['id'].";");
	}
	$totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." WHERE universe = ".$UNI.";");
	while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
	$message = "<span class='admin'>Treasure Hunt has started.<br>
20 Inactive planets have big amounts of resources.<br>
Search for it, and steal it.<br>
Happy Hunting !!!<br><br>
La chasse au tresor a commencer.<br>
20 planets inactive sont rempli de resource.<br>
Chercher les, et vider les tous.<br>
Happy Hunting !!!<br><br>
SchatzJagd Hat Begonnen<br>
20 inaktiver Planeten, ist voll mit vielen Rohstoffen<br>
Du musst ihn finden, und hol dir die Rohstoffe<br>
Viel Spass beim Suchen !!! 
	</span>";
    SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Treasure Event", $message);
    }
	$new_event = $CONF['treasure_event_1'] + 60*60*24;
    $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET treasure_event_1 = '".$new_event."' where `uni` = '".$UNI."';");
	}	
	
	if($CONF['social_message'] < TIMESTAMP){
	$query = $GLOBALS['DATABASE']->query("SELECT DISTINCT id FROM uni1_users;");
	while($x = $GLOBALS['DATABASE']->fetch_array($query)){
	$msg = '<span class="admin">"Like" & "Share" for the chance to win 50k darkmatter: Every 30 combined likes & shares will equal to one winner: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Social Page</a><br><br>
	"Aime" & "Partage" pour avoir la chance de gagner 50K matiere noire: tout les 30ieme aime & partage combinee recevera 50k de matiere noir: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Social Page</a><br><br>
	"Like" & "Teilen" für die Chance, 50k darkmatter gewinnen: Alle 30 kombiniert Vorlieben und Aktien werden gleich einen Gewinner: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Sozial Seite</a></span>';
	SendSimpleMessage($x['id'], '', TIMESTAMP, 50, 'System', 'Questions', $msg);
	}
	$GLOBALS['DATABASE']->query("UPDATE uni1_config SET social_message = '".(TIMESTAMP + 2 * 24 * 60 * 60)."';");
	}
	//END GLOBAL MESSAGES
		
	//BEGIN ASTEROID EVENT
	if($CONF['asteroid_event'] < TIMESTAMP){
	$GLOBALS['DATABASE']->query("DELETE FROM ".PLANETS." where `id_owner` = '".Asteroid_Id."' ;");
	$galaxy = $this->randRange(1,5,5);
	foreach($galaxy as $Element){
	$system = $this->randRange(1,200,70);
	foreach($system as $System_Element){
	$planets = rand(1,15);
	$cautare = $GLOBALS['DATABASE']->query("SELECT *FROM ".PLANETS." where `galaxy` = '".$Element."' and `system` = '".$System_Element."' and `planet` = '".$planets."' AND `universe` = '".$UNI."';");
	if($GLOBALS['DATABASE']->numRows($cautare)==0){
	$metal_rand = Config::get('asteroid_metal');
	$crystal_rand = Config::get('asteroid_crystal');
	$deuterium_rand= Config::get('asteroid_deuterium');
	$GLOBALS['DATABASE']->query("INSERT INTO ".PLANETS."(`name`,`id_owner`,`universe`,`galaxy`,`system`,`planet`,`planet_type`,`image`,`diameter`,`metal`,`crystal`,`deuterium`,`last_update`) 
	VALUES('Asteroid','".Asteroid_Id."','".$UNI."','".$Element."','".$System_Element."','".$planets."','1','asteroid','9800','".$metal_rand."','".$crystal_rand."','".$deuterium_rand."','".TIMESTAMP."');");
	}
    }
	}
	$totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS.";");
	while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
	$message = '<span class="admin">Asteroid Event started<br>
	Every asteroid that you harvest will bring you resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">more details</a><br><br>
	Evenement asteroid a commencer<br>
	Chaque asteroid que tu harvest te raportera des resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">plus de details</a><br><br>
	Asteroid Ereignis begann <br>
	Jeder Asteroiden, die Sie ernten werden Sie Ressource bringen. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">weitere Informationen</a>
	</span>';
    SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }
	$newevkaka = $CONF['asteroid_event'] + 60*60*24;
    $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET asteroid_event = '".$newevkaka."' where `uni` = '".$UNI."';");
	}
	//END ASTEROID EVENT	
	//BEGIN ASTEROID EVENT
	if($CONF['asteroid_event_1'] < TIMESTAMP){
	$GLOBALS['DATABASE']->query("DELETE FROM ".PLANETS." where `id_owner` = '".Asteroid_Id."' ;");
	$galaxy = $this->randRange(1,5,5);
	foreach($galaxy as $Element){
	$system = $this->randRange(1,200,70);
	foreach($system as $System_Element){
	$planets = rand(1,15);
	$cautare = $GLOBALS['DATABASE']->query("SELECT *FROM ".PLANETS." where `galaxy` = '".$Element."' and `system` = '".$System_Element."' and `planet` = '".$planets."' AND `universe` = '".$UNI."';");
	if($GLOBALS['DATABASE']->numRows($cautare)==0){
	$metal_rand = Config::get('asteroid_metal');
	$crystal_rand = Config::get('asteroid_crystal');
	$deuterium_rand= Config::get('asteroid_deuterium');
	$GLOBALS['DATABASE']->query("INSERT INTO ".PLANETS."(`name`,`id_owner`,`universe`,`galaxy`,`system`,`planet`,`planet_type`,`image`,`diameter`,`metal`,`crystal`,`deuterium`,`last_update`) 
	VALUES('Asteroid','".Asteroid_Id."','".$UNI."','".$Element."','".$System_Element."','".$planets."','1','asteroid','9800','".$metal_rand."','".$crystal_rand."','".$deuterium_rand."','".TIMESTAMP."');");
	}
    }
	}
	$totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS.";");
	while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
	$message = '<span class="admin">Asteroid Event started<br>
	Every asteroid that you harvest will bring you resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">more details</a><br><br>
	Evenement asteroid a commencer<br>
	Chaque asteroid que tu harvest te raportera des resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">plus de details</a><br><br>
	Asteroid Ereignis begann <br>
	Jeder Asteroiden, die Sie ernten werden Sie Ressource bringen. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">weitere Informationen</a>
	</span>';
    SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }
	$newevkaka = $CONF['asteroid_event_1'] + 60*60*24;
    $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET asteroid_event_1 = '".$newevkaka."' where `uni` = '".$UNI."';");
	}
	//END ASTEROID EVENT	
	
	
	/* //BEGIN FORTRESS EVENT
	if($CONF['fortress_event'] < TIMESTAMP){
	$GLOBALS['DATABASE']->query("DELETE FROM ".PLANETS." where `planet_type` = '4' ;");
	$galaxy = $this->randRange(1,5,5);
	foreach($galaxy as $Element){
	$system = $this->randRange(1,200,15);
	foreach($system as $System_Element){
	$planets = rand(1,15);
	$timerforall = TIMESTAMP + 60*60*6;
	$cautare = $GLOBALS['DATABASE']->query("SELECT *FROM ".PLANETS." where `galaxy` = '".$Element."' and `system` = '".$System_Element."' and `planet` = '".$planets."' AND `universe` = '".$UNI."';");
	if($GLOBALS['DATABASE']->numRows($cautare)==0){
	$metal_rand = Config::get('asteroid_metal');
	$crystal_rand = Config::get('asteroid_crystal');
	$deuterium_rand= Config::get('asteroid_deuterium');
	$max_field= mt_rand(69,176);
	$GLOBALS['DATABASE']->query("INSERT INTO ".PLANETS."(`name`,`id_owner`,`universe`,`galaxy`,`system`,`planet`,`planet_type`,`image`,`diameter`,`field_current`,`field_max`,`metal`,`crystal`,`deuterium`,`last_update`,`capture_not`) 
	VALUES('Fortress Planet','".Fortress_Id."','".$UNI."','".$Element."','".$System_Element."','".$planets."','4','fortress','9800','0','".$max_field."','".$metal_rand."','".$crystal_rand."','".$deuterium_rand."','".TIMESTAMP."','".$timerforall."');");
	}
    }
	}
	$totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS.";");
	while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
	$message = "<span class='admin'>Fortress Event started<br>
	Capture your fortress and take advantage of the profits they offer you !<br><br>
	Evenement Fortress a commencer<br>
	Capturez votre forteresse et profiter des multiple avantage qu\'elles peuvent vous offrir ! 
	</span>";
    SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }
	$newevkaka = $timerforall + 10;
    $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fortress_event = '".$newevkaka."' where `uni` = '".$UNI."';");
	}
	//END FORTRESS EVENT	 */
		
		
	

	
		
		//if($CONF['end_game'] < TIMESTAMP){
		//$GLOBALS['DATABASE']->query("UPDATE uni1_config SET game_disable = '0', close_reason = 'The first season of the game cames to hes end. the entire game will be reseted today at 20h' WHERE uni = ".$UNI.";");
		//}
		
		
		if($CONF['question_message'] < TIMESTAMP){
		$query = $GLOBALS['DATABASE']->query("SELECT DISTINCT id FROM uni1_users WHERE universe = ".$UNI.";");
		while($x = $GLOBALS['DATABASE']->fetch_array($query)){
		$msg = '<span class="admin">If you have questions about the game: <a href="?page=ticket">Write them here</a><br>
		Si vous avez dues question sur le jeu: <a href="?page=ticket">Posez les ici</a><br>
		Falls du fragen über das Spiel hast: <a href="?page=ticket">Schreibe sie hier</a></span>';
		SendSimpleMessage($x['id'], '', TIMESTAMP, 50, 'System', 'Questions', $msg);
		}
		$GLOBALS['DATABASE']->query("UPDATE uni1_config SET question_message = '".(TIMESTAMP + 3 * 24 * 60 * 60)."' WHERE uni = ".$UNI.";");
		}
		
		
		if($CONF['referal_message'] < TIMESTAMP){
		$query = $GLOBALS['DATABASE']->query("SELECT DISTINCT id FROM uni1_users WHERE universe = ".$UNI.";");
		while($x = $GLOBALS['DATABASE']->fetch_array($query)){
		$msg = '<span class="admin"> Invite new players with your referral link and get for every new player 5,000,000 Dark Matter and 10.000 Antimatter: <a href="?page=Refystem">Referal System</a><br>
		Lade neue Spieler mit deinem Referral Link ein und bekomme 5.000.000 Dunkle Materie und 10.000 Anti Materie: <a href="?page=Refystem">Referal System</a></span>';
		SendSimpleMessage($x['id'], '', TIMESTAMP, 50, 'System', 'Referal', $msg);
		}
		$GLOBALS['DATABASE']->query("UPDATE uni1_config SET referal_message = '".(TIMESTAMP + 7 * 24 * 60 * 60)."' WHERE uni = ".$UNI.";");
		}
		
		if($CONF['fleet_event_active_1'] < TIMESTAMP) {
        $totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." where `universe` = '".$UNI."';");
        while($x = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
        $message = '<span class="admin">Friendly fleets landed on all your planets<br>
		Des vaisseaux allies ont atteris sur toutes vos planetes<br>
		Freundliche Flotten landen auf deinen Planeten</span>';
        SendSimpleMessage($x['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
        }
        $GLOBALS['DATABASE']->query("UPDATE ".PLANETS." p INNER JOIN ".USERS." u ON p.id_owner = u.id SET 
        `light_hunter` = `light_hunter` + 500000000,
        `bs_class_oneil` = `bs_class_oneil` + 20000,
		`frigate` = `frigate` + 50000
        WHERE p.universe = '".$UNI."' AND u.urlaubs_modus = 0 AND p.planet_type = 1 AND u.onlinetime > ".(TIMESTAMP - 60*60*24*7).";");
        $newevent3 = $CONF['fleet_event_active_1'] + 2*60*60*24;
        $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fleet_event_active_1 = '".$newevent3."' where `uni` = '".$UNI."';");
        }
		if($CONF['fleet_event_active_2'] < TIMESTAMP ) {
        $totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." where `universe` = '".$UNI."';");
        while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
        $message = '<span class="admin">Friendly fleets landed on your home planet<br>
		Des vaisseaux allies ont atteris sur votre planete mere<br>
		Freundliche Flotten landen auf deinen Planeten</span>
                            ';
        SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
        }
        $totalPremiums1 = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id_planet` FROM ".USERS." where `universe` = '".$UNI."';");
        while($omt = $GLOBALS['DATABASE']->fetch_array($totalPremiums1)){
        $GLOBALS['DATABASE']->query("UPDATE ".PLANETS." p INNER JOIN ".USERS." u ON p.id_owner = u.id SET 
       `light_hunter` = `light_hunter` + 500000000,
        `bs_class_oneil` = `bs_class_oneil` + 20000,
		`frigate` = `frigate` + 50000
        WHERE p.universe = '".$UNI."' AND p.id = ".$omt['id_planet']." AND u.urlaubs_modus = 0 AND p.planet_type = 1 AND u.onlinetime > ".(TIMESTAMP - 60*60*24*7).";");
		$newevent2 = $CONF['fleet_event_active_2'] + 2*60*60*24;
        $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fleet_event_active_2 = '".$newevent2."' where `uni` = '".$UNI."';");
        }
        }
		if($CONF['fleet_event_inactive_1'] < TIMESTAMP) {
        $totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." where `universe` = '".$UNI."';");
        while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
        $message = '<span class="admin">All inactive planets are full of resources and fleets<br>
All inactive moons are full of  resources<br>
Toutes les planetes inactive sont remplis de vaisseaux et de resources<br>
Toutes les lunes inactives sont remplis de resources<br>
Alle inaktiven Planeten sind voll mit Resourcen und Flotten<br>
Alle inaktiven Monde sind voll mit Resourcen</span>';
        SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
        }
		$newevent = $CONF['fleet_event_inactive_1'] + 60*60*24;
        $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fleet_event_inactive_1 = '".$newevent."' where `uni` = '".$UNI."';");
        }
		if($CONF['fleet_event_inactive_2'] < TIMESTAMP) {
        $totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." where `universe` = '".$UNI."';");
        while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
        $message = '<span class="admin">All inactive planets are full of resources and fleets<br>
All inactive moons are full of  resources<br>
Toutes les planetes inactive sont remplis de vaisseaux et de resources<br>
Toutes les lunes inactives sont remplis de resources<br>
Alle inaktiven Planeten sind voll mit Resourcen und Flotten<br>
Alle inaktiven Monde sind voll mit Resourcen</span>';
        SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
        }
		$newevent = $CONF['fleet_event_inactive_2'] + 60*60*24;
        $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fleet_event_inactive_2 = '".$newevent."' where `uni` = '".$UNI."';");
        }
		if($CONF['fleet_event_inactive_3'] < TIMESTAMP) {
        $totalPremiums = $GLOBALS['DATABASE']->query("SELECT DISTINCT `id` FROM ".USERS." where `universe` = '".$UNI."';");
        while($xy = $GLOBALS['DATABASE']->fetch_array($totalPremiums)){
        $message = '<span class="admin">All inactive planets are full of resources and fleets<br>
All inactive moons are full of  resources<br>
Toutes les planetes inactive sont remplis de vaisseaux et de resources<br>
Toutes les lunes inactives sont remplis de resources<br>
Alle inaktiven Planeten sind voll mit Resourcen und Flotten<br>
Alle inaktiven Monde sind voll mit Resourcen</span>';
        SendSimpleMessage($xy['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
        }
		$newevent = $CONF['fleet_event_inactive_3'] + 60*60*24;
        $GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET fleet_event_inactive_3 = '".$newevent."' where `uni` = '".$UNI."';");
        }