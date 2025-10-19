<?php

class EventManager
{
    public static function checkAndRunActiveEvents()
    {
        $events = self::getRegisteredEvents();

        foreach ($events as $event) {
            if ((int) $event['active'] !== 1) {
                continue;
            }

            $functionName = $event['function'];

            if (function_exists($functionName)) {
                call_user_func($functionName);
            }
        }
    }

    public static function getRegisteredEvents()
    {
        $events = array();

        foreach (self::getEventFiles() as $file) {
            $eventName = basename($file, '.php');
            $functionName = self::buildFunctionName($eventName);
            $flagName = self::getFlagName($eventName);

            require_once $file;

            self::ensureConfigFlag($flagName);

            $events[] = array(
                'name' => $eventName,
                'displayName' => self::buildDisplayName($eventName),
                'function' => $functionName,
                'flag' => $flagName,
                'description' => self::extractDescription($file),
                'active' => self::isEventActive($flagName) ? 1 : 0,
            );
        }

        usort($events, array(__CLASS__, 'compareEvents'));

        return $events;
    }

    private static function getEventFiles()
    {
        $files = glob(__DIR__ . '/*.php');
        $eventFiles = array();

        if ($files === false) {
            return $eventFiles;
        }

        foreach ($files as $file) {
            if (basename($file) === 'EventManager.php') {
                continue;
            }

            $eventFiles[] = $file;
        }

        usort($eventFiles, 'strnatcasecmp');

        return $eventFiles;
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

    private static function buildDisplayName($eventName)
    {
        $normalized = str_replace(array('_', '-'), ' ', $eventName);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return ucwords(trim($normalized));
    }

    private static function getFlagName($eventName)
    {
        return $eventName . '_active';
    }

    private static function extractDescription($file)
    {
        $contents = @file_get_contents($file);

        if ($contents === false) {
            return '';
        }

        if (preg_match('/@description\s*:\s*(.+)/i', $contents, $matches)) {
            return trim($matches[1]);
        }

        return '';
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
            $database->query("ALTER TABLE " . CONFIG . " ADD `" . $flagName . "` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");
            $database->query("UPDATE " . CONFIG . " SET `" . $flagName . "` = 0;");

            if (class_exists('Config')) {
                Config::init();
                Config::setGlobals();
            }

            self::ensureGlobalsContainFlag($flagName, 0);
        } else {
            self::ensureGlobalsContainFlag($flagName, 0);
        }

        $ensuredFlags[$flagName] = true;
    }

    private static function ensureGlobalsContainFlag($flagName, $defaultValue)
    {
        if (!isset($GLOBALS['CONF'][$flagName])) {
            $GLOBALS['CONF'][$flagName] = $defaultValue;
        }

        if (isset($GLOBALS['CONFIG']) && is_array($GLOBALS['CONFIG'])) {
            foreach ($GLOBALS['CONFIG'] as $universe => $config) {
                if (!array_key_exists($flagName, $config)) {
                    $GLOBALS['CONFIG'][$universe][$flagName] = $defaultValue;
                }
            }
        }
    }

    private static function isEventActive($flagName)
    {
        if (isset($GLOBALS['CONF'][$flagName])) {
            return (int) $GLOBALS['CONF'][$flagName] === 1;
        }

        $database = $GLOBALS['DATABASE'];
        $universe = (int) $GLOBALS['UNI'];

        $query = $database->query("SELECT `" . $flagName . "` FROM " . CONFIG . " WHERE `uni` = '" . $universe . "' LIMIT 1;");
        $row = $database->fetch_array($query);
        $database->free_result($query);

        if ($row !== null && array_key_exists($flagName, $row)) {
            $value = (int) $row[$flagName];
            $GLOBALS['CONF'][$flagName] = $value;

            return $value === 1;
        }

        return false;
    }

    private static function compareEvents($firstEvent, $secondEvent)
    {
        return strcasecmp($firstEvent['displayName'], $secondEvent['displayName']);
    }
}
