<?php

class EventManager
{
    public static function checkAndRunActiveEvents()
    {
        foreach (self::getEventNames() as $eventName) {
            $flagName = self::getFlagName($eventName);
            self::ensureConfigFlag($flagName);

            if (!self::isEventActive($flagName)) {
                continue;
            }

            $functionName = self::buildFunctionName($eventName);

            if (function_exists($functionName)) {
                call_user_func($functionName);
            }
        }
    }

    private static function getEventNames()
    {
        $eventNames = array();
        foreach (glob(__DIR__ . '/*.php') as $file) {
            if (basename($file) === 'EventManager.php') {
                continue;
            }

            require_once $file;
            $eventNames[] = basename($file, '.php');
        }

        return $eventNames;
    }

    private static function buildFunctionName($eventName)
    {
        $parts = explode('_', $eventName);
        $functionName = 'run';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $functionName .= ucfirst($part);
        }

        return $functionName;
    }

    private static function getFlagName($eventName)
    {
        return $eventName . '_active';
    }

    private static function ensureConfigFlag($flagName)
    {
        static $ensuredFlags = array();

        if (isset($ensuredFlags[$flagName])) {
            return;
        }

        if (!preg_match('/^[a-z0-9_]+$/i', $flagName)) {
            throw new Exception('Invalid event flag name: ' . $flagName);
        }

        $database = $GLOBALS['DATABASE'];
        $escapedFlag = $database->escape($flagName, true);
        $result = $database->query("SHOW COLUMNS FROM " . CONFIG . " LIKE '" . $escapedFlag . "';");
        $exists = $database->numRows($result) > 0;
        $database->free_result($result);

        if (!$exists) {
            $database->query("ALTER TABLE " . CONFIG . " ADD `" . $flagName . "` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1';");
            $database->query("UPDATE " . CONFIG . " SET `" . $flagName . "` = 1;");

            if (isset($GLOBALS['CONFIG']) && is_array($GLOBALS['CONFIG'])) {
                foreach ($GLOBALS['CONFIG'] as $uni => $config) {
                    $GLOBALS['CONFIG'][$uni][$flagName] = 1;
                }
            }

            $GLOBALS['CONF'][$flagName] = 1;
        }

        $ensuredFlags[$flagName] = true;
    }

    private static function isEventActive($flagName)
    {
        if (isset($GLOBALS['CONF'][$flagName])) {
            return (bool) $GLOBALS['CONF'][$flagName];
        }

        $database = $GLOBALS['DATABASE'];
        $universe = (int) $GLOBALS['UNI'];

        $query = $database->query("SELECT `" . $flagName . "` FROM " . CONFIG . " WHERE `uni` = '" . $universe . "' LIMIT 1;");
        $row = $database->fetch_array($query);
        $database->free_result($query);

        if ($row !== null && array_key_exists($flagName, $row)) {
            $GLOBALS['CONF'][$flagName] = (int) $row[$flagName];
            return (bool) $row[$flagName];
        }

        return false;
    }
}
