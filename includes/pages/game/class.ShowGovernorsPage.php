<?php

class ShowGovernorsPage extends AbstractPage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	public function UpdateExtra($Element)
	{
		global $PLANET, $USER, $resource, $pricelist;
		
		$costRessources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
			
		if (!BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costRessources)) {
			return;
		}
			
		$USER[$resource[$Element]]	= max($USER[$resource[$Element]], TIMESTAMP) + $pricelist[$Element]['time'];
			
		if(isset($costRessources[901])) { $PLANET[$resource[901]]	-= $costRessources[901]; }
		if(isset($costRessources[902])) { $PLANET[$resource[902]]	-= $costRessources[902]; }
		if(isset($costRessources[903])) { $PLANET[$resource[903]]	-= $costRessources[903]; }
		if(isset($costRessources[921])) { $USER[$resource[921]]		-= $costRessources[921]; }
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET
				   ".$resource[$Element]." = ".$USER[$resource[$Element]]."
				   WHERE
				   id = ".$USER['id'].";");
	}

	public function UpdateOfficier($Element)
	{
		global $USER, $PLANET, $reslist, $resource, $pricelist;
		
		$costRessources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
			
		if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element) 
			|| !BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costRessources) 
			|| $pricelist[$Element]['max'] <= $USER[$resource[$Element]]) {
			return;
		}
		
		$USER[$resource[$Element]]	+= 1;
		
		if(isset($costRessources[901])) { $PLANET[$resource[901]]	-= $costRessources[901]; }
		if(isset($costRessources[902])) { $PLANET[$resource[902]]	-= $costRessources[902]; }
		if(isset($costRessources[903])) { $PLANET[$resource[903]]	-= $costRessources[903]; }
		if(isset($costRessources[921])) { $USER[$resource[921]]		-= $costRessources[921]; }
		
		$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET
				   ".$resource[$Element]." = ".$USER[$resource[$Element]]."
				   WHERE
				   id = ".$USER['id'].";");
	}
	
	public function show()
	{
		global $USER, $CONF, $PLANET, $resource, $reslist, $LNG, $pricelist;
		if($USER['id'] != 1){
			$this->printMessage('under maintenace', true, array('game.php?page=overview', 2));
		}
		$updateID	  = HTTP::_GP('id', 0);
				
		if (!empty($updateID) && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0)
		{
			if(isModulAvalible(MODULE_OFFICIER) && in_array($updateID, $reslist['officier'])) {
				$this->UpdateOfficier($updateID);
			} elseif(isModulAvalible(MODULE_DMEXTRAS) && in_array($updateID, $reslist['dmfunc'])) {
				$this->UpdateExtra($updateID);
			}
		}
		$this->tplObj->loadscript('officier.js');		
		
		$darkmatterList	= array();
		$officierList	= array();
		
		if(isModulAvalible(MODULE_DMEXTRAS)) 
		{
			foreach($reslist['dmfunc'] as $Element)
			{
				if($USER[$resource[$Element]] > TIMESTAMP) {
					$this->tplObj->execscript("GetOfficerTime(".$Element.", ".($USER[$resource[$Element]] - TIMESTAMP).");");
				}
			
				$costRessources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
				$buyable			= BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costRessources);
				$costOverflow		= BuildFunctions::getRestPrice($USER, $PLANET, $Element, $costRessources);
				$elementBonus		= BuildFunctions::getAvalibleBonus($Element);

				$darkmatterList[$Element]	= array(
					'timeLeft'			=> max($USER[$resource[$Element]] - TIMESTAMP, 0),
					'costRessources'	=> $costRessources,
					'buyable'			=> $buyable,
					'time'				=> $pricelist[$Element]['time'],
					'costOverflow'		=> $costOverflow,
					'elementBonus'		=> $elementBonus,
				);
			}
		}
		
		if(isModulAvalible(MODULE_OFFICIER))
		{
			foreach($reslist['officier'] as $Element)
			{
				if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element))
					continue;
					
				$costRessources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
				$buyable			= BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costRessources);
				$costOverflow		= BuildFunctions::getRestPrice($USER, $PLANET, $Element, $costRessources);
				$elementBonus		= BuildFunctions::getAvalibleBonus($Element);
				
				$officierList[$Element]	= array(
					'level'				=> $USER[$resource[$Element]],
					'maxLevel'			=> $pricelist[$Element]['max'],
					'costRessources'	=> $costRessources,
					'buyable'			=> $buyable,
					'costOverflow'		=> $costOverflow,
					'elementBonus'		=> $elementBonus,
				);
			}
		}
		
		$this->tplObj->assign_vars(array(	
			'officierList'		=> $officierList,
			'darkmatterList'	=> $darkmatterList,
			'of_dm_trade'		=> sprintf($LNG['of_dm_trade'], $LNG['tech'][921]),
		));
		
		$this->display('page.governors.default.tpl');
	}
}