<?php
function runTreasureEvent1()
{
    global $CONF, $UNI;

    if ($CONF['treasure_event_1'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];

    $planetResult = $database->query(
        "SELECT p.id FROM " . PLANETS . " p INNER JOIN " . USERS . " u ON p.id_owner = u.id WHERE p.universe = " . $UNI .
        " AND u.universe = " . $UNI .
        " AND u.onlinetime < " . (TIMESTAMP - 60 * 60 * 24 * 7) .
        " AND u.urlaubs_modus = 0 AND p.planet_type = 1 ORDER BY RAND() LIMIT 20;"
    );

    while ($planetRow = $database->fetch_array($planetResult)) {
        $database->query(
            "UPDATE " . PLANETS . " SET `metal` = 500000000000, `crystal` = 400000000000, `deuterium` = 300000000000 WHERE id = " . $planetRow['id'] . ";"
        );
    }

    $database->free_result($planetResult);

    $usersResult = $database->query("SELECT DISTINCT `id` FROM " . USERS . " WHERE universe = " . $UNI . ";");

    $message = "<span class='admin'>Treasure Hunt has started.<br>20 Inactive planets have big amounts of resources.<br>Search for it, and steal it.<br>Happy Hunting !!!<br><br>La chasse au tresor a commencer.<br>20 planets inactive sont rempli de resource.<br>Chercher les, et vider les tous.<br>Happy Hunting !!!<br><br>SchatzJagd Hat Begonnen<br>20 inaktiver Planeten, ist voll mit vielen Rohstoffen<br>Du musst ihn finden, und hol dir die Rohstoffe<br>Viel Spass beim Suchen !!!</span>";

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], 1, TIMESTAMP, 50, "Event System", "Treasure Event", $message);
    }

    $database->free_result($usersResult);

    $newEventTime = $CONF['treasure_event_1'] + 60 * 60 * 24;
    $database->query("UPDATE " . CONFIG . " SET treasure_event_1 = '" . $newEventTime . "' WHERE `uni` = '" . $UNI . "';");

    $CONF['treasure_event_1'] = $newEventTime;
}
