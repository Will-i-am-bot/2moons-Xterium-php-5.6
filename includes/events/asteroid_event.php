<?php
function runAsteroidEvent()
{
    global $CONF, $UNI;

    if ($CONF['asteroid_event'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];

    $database->query("DELETE FROM " . PLANETS . " WHERE `id_owner` = '" . Asteroid_Id . "';");

    $galaxies = array();
    while (count($galaxies) < 5) {
        $value = mt_rand(1, 5);
        if (!in_array($value, $galaxies)) {
            $galaxies[] = $value;
        }
    }

    foreach ($galaxies as $galaxy) {
        $systems = array();
        while (count($systems) < 70) {
            $value = mt_rand(1, 200);
            if (!in_array($value, $systems)) {
                $systems[] = $value;
            }
        }

        foreach ($systems as $system) {
            $planetPosition = mt_rand(1, 15);
            $existsQuery = $database->query(
                "SELECT id FROM " . PLANETS . " WHERE `galaxy` = '" . $galaxy . "' AND `system` = '" . $system . "' AND `planet` = '" . $planetPosition . "' AND `universe` = '" . $UNI . "' LIMIT 1;"
            );

            $exists = $database->numRows($existsQuery) > 0;
            $database->free_result($existsQuery);

            if ($exists) {
                continue;
            }

            $metal = Config::get('asteroid_metal');
            $crystal = Config::get('asteroid_crystal');
            $deuterium = Config::get('asteroid_deuterium');

            $database->query(
                "INSERT INTO " . PLANETS . "(`name`,`id_owner`,`universe`,`galaxy`,`system`,`planet`,`planet_type`,`image`,`diameter`,`metal`,`crystal`,`deuterium`,`last_update`) VALUES('Asteroid','" . Asteroid_Id . "','" . $UNI . "','" . $galaxy . "','" . $system . "','" . $planetPosition . "','1','asteroid','9800','" . $metal . "','" . $crystal . "','" . $deuterium . "','" . TIMESTAMP . "');"
            );
        }
    }

    $usersResult = $database->query("SELECT DISTINCT `id` FROM " . USERS . " WHERE universe = " . $UNI . ";");

    $message = '<span class="admin">Asteroid Event started<br>Every asteroid that you harvest will bring you resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">more details</a><br><br>Evenement asteroid a commencer<br>Chaque asteroid que tu harvest te raportera des resource. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">plus de details</a><br><br>Asteroid Ereignis begann <br>Jeder Asteroiden, die Sie ernten werden Sie Ressource bringen. <a href="http://forum.dark-space.org/index.php?/topic/3-asteroid-event">weitere Informationen</a></span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], 1, TIMESTAMP, 50, "Event System", "Event Info", $message);
    }

    $database->free_result($usersResult);

    $newTime = $CONF['asteroid_event'] + 60 * 60 * 24;
    $database->query("UPDATE " . CONFIG . " SET asteroid_event = '" . $newTime . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['asteroid_event'] = $newTime;
}
