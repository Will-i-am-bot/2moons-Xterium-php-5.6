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
 * @info $Id: ShowModulePage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) {
    throw new Exception("Permission error!");
}

function ShowModulePage()
{
    global $LNG;

    $moduleList = array(
        'Senate',
        'Governors',
        'Galaxy',
        'Fleet',
        'Defense',
        'Buildings',
        'Research',
        'Market',
        'Messages',
        'Alliance',
        'Expeditions',
        'Statistics',
        'Highscores',
        'Trade',
        'Tutorial',
        'DailyBonus',
        'Achievements',
        'Auction',
        'Premium',
        'Chat',
        'BattleSimulator',
        'Notes',
        'MultiAccounts',
        'BanSystem',
        'Giveaways',
        'Creator',
        'EditAccounts',
        'AdminTools',
        'ModulesManager',
    );

    $mode = HTTP::_GP('mode', '');
    $rawModuleStates = Config::get('moduls');

    if (!is_array($rawModuleStates)) {
        $rawModuleStates = trim($rawModuleStates);
        if ($rawModuleStates === '') {
            $rawModuleStates = array();
        } else {
            $rawModuleStates = explode(';', $rawModuleStates);
        }
    }

    $rawModuleStates = array_values(array_map('intval', $rawModuleStates));
    $moduleCount = count($moduleList);
    $normalizedStates = array();
    $needsSync = false;

    for ($index = 0; $index < $moduleCount; $index++) {
        if (isset($rawModuleStates[$index])) {
            $normalizedStates[$index] = (int)$rawModuleStates[$index];
        } else {
            $normalizedStates[$index] = 1;
            $needsSync = true; // FIXED: auto-sync missing modules
        }
    }

    $extraStates = array();
    if (count($rawModuleStates) > $moduleCount) {
        for ($index = $moduleCount; $index < count($rawModuleStates); $index++) {
            $extraStates[$index] = (int)$rawModuleStates[$index];
        }
    }

    if ($mode === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $enabledModules = isset($_POST['modules']) && is_array($_POST['modules']) ? $_POST['modules'] : array();
        $enabledModules = array_map('strval', $enabledModules);
        $updatedStates = array();

        foreach ($moduleList as $index => $moduleName) {
            $updatedStates[$index] = in_array($moduleName, $enabledModules) ? 1 : 0; // FIXED: added toggle control for all modules
        }

        if (!empty($extraStates)) {
            foreach ($extraStates as $index => $stateValue) {
                $updatedStates[$index] = $stateValue;
            }
        }

        Config::update(array('moduls' => implode(';', $updatedStates))); // FIXED: dynamic update for config->moduls
        HTTP::redirectTo('admin.php?page=module');
    }

    if ($needsSync) {
        $syncStates = $normalizedStates;
        if (!empty($extraStates)) {
            foreach ($extraStates as $index => $stateValue) {
                $syncStates[$index] = $stateValue;
            }
        }
        Config::update(array('moduls' => implode(';', $syncStates)));
    }

    $modulesForTemplate = array();
    foreach ($moduleList as $index => $moduleName) {
        $modulesForTemplate[] = array(
            'name' => $moduleName,
            'state' => isset($normalizedStates[$index]) ? $normalizedStates[$index] : 1,
        );
    }

    $template = new template();
    $template->assign_vars(array(
        'modules' => $modulesForTemplate,
        'moduleFormAction' => 'admin.php?page=module&mode=save',
        'mod_module' => $LNG['mod_module'],
        'mod_info' => $LNG['mod_info'],
        'mod_active' => $LNG['mod_active'],
        'mod_deactive' => $LNG['mod_deactive'],
        'mod_save_changes' => $LNG['cs_save_changes'],
    ));
    $template->show('ModulePage.tpl');
}
