<?php

class ShowGovernorsPage extends AbstractPage
{
        public static $requireModule = MODULE_GOVERNORS;

        function __construct()
        {
                parent::__construct();
        }

        function show()
        {
                global $USER, $LNG;

                if(!allowedToModule(MODULE_GOVERNORS))
                {
                        message($LNG['sys_module_inactive'], '', 'error');
                }

                if(method_exists('Config', 'getAll'))
                {
                        $config = Config::getAll();

                        if(!empty($config->game_disable) && $config->game_disable == 1 && $USER['authlevel'] == AUTH_USR)
                        {
                                message($LNG['ma_message_from_maintance'], '', 'error');
                        }
                }

                $sql = "SELECT * FROM %%GOVERNORS%% WHERE id_owner = :userId;";
                $governors = Database::get()->select($sql, array(
                        ':userId'       => $USER['id']
                ));

                $this->tplObj->assign_vars(array(
                        'governors'     => $governors,
                        'hasGovernors'  => !empty($governors),
                ));

                $this->display('page.governors.default.tpl');
        }
}
