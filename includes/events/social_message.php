<?php
// @description: Bewirbt Social-Media-Aktionen mit Bonusgewinnen für Spieler
function runSocialMessage()
{
    global $CONF, $UNI;

    if ($CONF['social_message'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];
    $usersResult = $database->query("SELECT DISTINCT id FROM " . USERS . " WHERE universe = " . $UNI . ";");

    $message = '<span class="admin">"Like" & "Share" for the chance to win 50k darkmatter: Every 30 combined likes & shares will equal to one winner: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Social Page</a><br><br>"Aime" & "Partage" pour avoir la chance de gagner 50K matiere noire: tout les 30ieme aime & partage combinee recevera 50k de matiere noir: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Social Page</a><br><br>"Like" & "Teilen" für die Chance, 50k darkmatter gewinnen: Alle 30 kombiniert Vorlieben und Aktien werden gleich einen Gewinner: <a href="https://www.facebook.com/pages/Dark-Space-Empire/1490309864518434">Dark-Space: Empire ! Sozial Seite</a></span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], '', TIMESTAMP, 50, 'System', 'Questions', $message);
    }

    $database->free_result($usersResult);

    $nextRun = TIMESTAMP + 2 * 24 * 60 * 60;
    $database->query("UPDATE " . CONFIG . " SET social_message = '" . $nextRun . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['social_message'] = $nextRun;
}
