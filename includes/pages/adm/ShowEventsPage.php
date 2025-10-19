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
 * @info $Id: ShowEventsPage.php 0000 2024-01-01 00:00:00Z xterium $
 * @link http://2moons.cc/
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) {
    throw new Exception("Permission error!");
}

function ShowEventsPage()
{
    global $LNG;

    require_once 'includes/events/EventManager.php';

    $events = EventManager::getRegisteredEvents();
    $successMessage = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enabledEvents = HTTP::_GP('events', array());

        if (!is_array($enabledEvents)) {
            $enabledEvents = array();
        }

        $database = $GLOBALS['DATABASE'];
        $universe = (int) $GLOBALS['UNI'];

        foreach ($events as $event) {
            $flagName = $event['flag'];
            $isActive = isset($enabledEvents[$flagName]) ? 1 : 0;

            $database->query(
                "UPDATE " . CONFIG . " SET `" . $flagName . "` = '" . $isActive . "' WHERE `uni` = '" . $universe . "';"
            );
        }

        Config::init();
        Config::setGlobals();

        $events = EventManager::getRegisteredEvents();
        $successMessage = 'Einstellungen erfolgreich gespeichert.';
    }

    $template = new template();
    $template->assign_vars(array(
        'events' => $events,
        'formAction' => 'admin.php?page=events',
        'successMessage' => $successMessage,
        'pageTitle' => 'Event Verwaltung',
    ));

    $template->show('EventsPage.tpl');
}
