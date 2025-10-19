<?php
// @description: Erinnern Spieler an das Einladen neuer Kommandanten über Referral-Links
function runReferalMessage()
{
    global $CONF, $UNI;

    if ($CONF['referal_message'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];
    $usersResult = $database->query("SELECT DISTINCT id FROM " . USERS . " WHERE universe = " . $UNI . ";");

    $message = '<span class="admin"> Invite new players with your referral link and get for every new player 5,000,000 Dark Matter and 10.000 Antimatter: <a href="?page=Refystem">Referal System</a><br>Lade neue Spieler mit deinem Referral Link ein und bekomme 5.000.000 Dunkle Materie und 10.000 Anti Materie: <a href="?page=Refystem">Referal System</a></span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], '', TIMESTAMP, 50, 'System', 'Referal', $message);
    }

    $database->free_result($usersResult);

    $nextRun = TIMESTAMP + 7 * 24 * 60 * 60;
    $database->query("UPDATE " . CONFIG . " SET referal_message = '" . $nextRun . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['referal_message'] = $nextRun;
}
