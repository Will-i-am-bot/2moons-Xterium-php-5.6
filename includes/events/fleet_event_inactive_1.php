<?php
function runFleetEventInactive1()
{
    global $CONF, $UNI;

    if ($CONF['fleet_event_inactive_1'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];
    $usersResult = $database->query("SELECT DISTINCT `id` FROM " . USERS . " WHERE `universe` = " . $UNI . ";");

    $message = '<span class="admin">All inactive planets are full of resources and fleets<br>All inactive moons are full of  resources<br>Toutes les planetes inactive sont remplis de vaisseaux et de resources<br>Toutes les lunes inactives sont remplis de resources<br>Alle inaktiven Planeten sind voll mit Resourcen und Flotten<br>Alle inaktiven Monde sind voll mit Resourcen</span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }

    $database->free_result($usersResult);

    $nextRun = $CONF['fleet_event_inactive_1'] + 60 * 60 * 24;
    $database->query("UPDATE " . CONFIG . " SET fleet_event_inactive_1 = '" . $nextRun . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['fleet_event_inactive_1'] = $nextRun;
}
