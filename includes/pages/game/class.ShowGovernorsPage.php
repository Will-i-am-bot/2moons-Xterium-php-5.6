<?php

class ShowGovernorsPage extends AbstractGamePage
{
    public static $requireModule = MODULE_GOVERNORS;

    function __construct()
    {
        parent::__construct();
    }

    function show()
    {
        global $USER, $PLANET, $LNG, $CONF;

        // ✅ FIX 1 – Modul-Check: Nur prüfen, ob es aktiv ist
        if (!allowedToModule(MODULE_GOVERNORS)) {
            message($LNG['sys_module_inactive'], '', 'error');
        }

        // ✅ FIX 2 – Maintenance-Check entfernen oder auf Admin beschränken
        // (alter Code blendete die Seite für Spieler aus, wenn das Universum in Maintenance stand)
        if (method_exists('Config', 'getAll')) {
            $config = Config::getAll();
            if (!empty($config->game_disable) && $config->game_disable == 1 && $USER['authlevel'] == 0) {
                // Falls das Spiel in Maintenance ist und Spieler kein Admin sind
                message($LNG['ma_message_from_maintance'], '', 'error');
            }
        }

        // ✅ FIX 3 – Governor-Daten laden
        $sql = "SELECT * FROM %%GOVERNORS%% WHERE id_owner = :userId;";
        $governors = Database::get()->select($sql, [':userId' => $USER['id']]);

        // Falls keine Governor existieren
        if (empty($governors)) {
            $governors = [];
        }

        // ✅ FIX 4 – Daten an Template übergeben
        $this->assign([
            'governors' => $governors,
            'hasGovernors' => !empty($governors),
        ]);

        // ✅ FIX 5 – Template anzeigen
        $this->display('page.governors.default.tpl');
    }
}
