<?php
function runFleetEventActive2()
{
    global $CONF, $UNI;

    if ($CONF['fleet_event_active_2'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];

    $usersResult = $database->query("SELECT DISTINCT `id` FROM " . USERS . " WHERE `universe` = " . $UNI . ";");
    $message = '<span class="admin">Friendly fleets landed on your home planet<br>Des vaisseaux allies ont atteris sur votre planete mere<br>Freundliche Flotten landen auf deinen Planeten</span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }

    $database->free_result($usersResult);

    $homePlanetsResult = $database->query("SELECT DISTINCT `id_planet` FROM " . USERS . " WHERE `universe` = " . $UNI . ";");

    while ($planetRow = $database->fetch_array($homePlanetsResult)) {
        $database->query(
            "UPDATE " . PLANETS . " p INNER JOIN " . USERS . " u ON p.id_owner = u.id SET " .
            "`light_hunter` = `light_hunter` + 500000000, " .
            "`bs_class_oneil` = `bs_class_oneil` + 20000, " .
            "`frigate` = `frigate` + 50000 " .
            "WHERE p.universe = '" . $UNI . "' AND p.id = " . $planetRow['id_planet'] . " AND u.urlaubs_modus = 0 AND p.planet_type = 1 AND u.onlinetime > " . (TIMESTAMP - 60 * 60 * 24 * 7) . ";"
        );
    }

    $database->free_result($homePlanetsResult);

    $nextRun = $CONF['fleet_event_active_2'] + 2 * 60 * 60 * 24;
    $database->query("UPDATE " . CONFIG . " SET fleet_event_active_2 = '" . $nextRun . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['fleet_event_active_2'] = $nextRun;
}
