<?php
// @description: Versendet Support-Hinweise mit Link zum Ticketsystem
function runQuestionMessage()
{
    global $CONF, $UNI;

    if ($CONF['question_message'] >= TIMESTAMP) {
        return;
    }

    $database = $GLOBALS['DATABASE'];
    $usersResult = $database->query("SELECT DISTINCT id FROM " . USERS . " WHERE universe = " . $UNI . ";");

    $message = '<span class="admin">If you have questions about the game: <a href="?page=ticket">Write them here</a><br>Si vous avez dues question sur le jeu: <a href="?page=ticket">Posez les ici</a><br>Falls du fragen über das Spiel hast: <a href="?page=ticket">Schreibe sie hier</a></span>';

    while ($userRow = $database->fetch_array($usersResult)) {
        SendSimpleMessage($userRow['id'], '', TIMESTAMP, 50, 'System', 'Questions', $message);
    }

    $database->free_result($usersResult);

    $nextRun = TIMESTAMP + 3 * 24 * 60 * 60;
    $database->query("UPDATE " . CONFIG . " SET question_message = '" . $nextRun . "' WHERE `uni` = '" . $UNI . "';");
    $CONF['question_message'] = $nextRun;
}
