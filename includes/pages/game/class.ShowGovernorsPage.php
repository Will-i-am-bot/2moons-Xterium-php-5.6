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
                       $this->printMessage($LNG['sys_module_inactive']);
                       return;
               }

                $gameDisable = 0;

                if(method_exists('Config', 'get'))
                {
                        $gameDisable = Config::get('game_disable', $USER['universe']);
                }
                elseif(method_exists('Config', 'getAll'))
                {
                        $config = Config::getAll(NULL, $USER['universe']);

                        if(isset($config['game_disable']))
                        {
                                $gameDisable = $config['game_disable'];
                        }
                }

               if(!empty($gameDisable) && $gameDisable == 1 && $USER['authlevel'] == AUTH_USR)
               {
                       $this->printMessage($LNG['ma_message_from_maintance']);
                       return;
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
