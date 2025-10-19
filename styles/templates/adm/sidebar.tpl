{* FIXED: preserve navigation *}
<div class="grid_2">
        <div class="box sidemenu">
                <div class="block" id="section-menu">
                        <ul class="section menu">
                                <li class="{if $activePage == 'config' || $activePage == 'configuni' || $activePage == 'chat' || $activePage == 'module' || $activePage == 'statsconf'}current{/if}"><a class="menuitem">{$LNG.mu_settings|default:'Configuration'}</a>
                                        <ul class="submenu">
                                                <li><a href="admin.php?page=config" class="{if $activePage == 'config'}current{/if}">{$LNG.adm_cp_config|default:'Server Configuration'}</a></li>
                                                <li><a href="admin.php?page=configuni" class="{if $activePage == 'configuni'}current{/if}">{$LNG.mu_config_uni|default:'Uni Configuration'}</a></li>
                                                <li><a href="admin.php?page=chat" class="{if $activePage == 'chat'}current{/if}">{$LNG.mu_chat|default:'Chat Configuration'}</a></li>
                                                <li><a href="admin.php?page=module" class="{if $activePage == 'module'}current{/if}">{$LNG.mu_module|default:'Modules'}</a></li>
                                                <li><a href="admin.php?page=statsconf" class="{if $activePage == 'statsconf'}current{/if}">{$LNG.st_update_conf_header|default:'Stats Configuration'}</a></li>
                                                <li><a href="admin.php?page=events" class="{if $activePage == 'events'}current{/if}">Event Verwaltung</a></li>
                                        </ul>
                                </li>
                                <li class="{if $activePage == 'search' || $activePage == 'fleets' || $activePage == 'accountdata' || $activePage == 'voucher'}current{/if}"><a class="menuitem">{$LNG.mu_game_info|default:'Game Activity'}</a>
                                        <ul class="submenu">
                                                <li><a href="admin.php?page=search&amp;search=p_connect&amp;minimize=on" class="{if $activePage == 'search' && $smarty.get.search == 'p_connect'}current{/if}">{$LNG.mu_connected|default:'Users Activity'}</a></li>
                                                <li><a href="admin.php?page=search&amp;search=planet&amp;minimize=on" class="{if $activePage == 'search' && $smarty.get.search == 'planet'}current{/if}">{$LNG.mu_planet_list|default:'Active Planets'}</a></li>
                                                <li><a href="admin.php?page=fleets" class="{if $activePage == 'fleets'}current{/if}">{$LNG.mu_flying_fleets|default:'Flying Fleets'}</a></li>
                                                <li><a href="admin.php?page=accountdata" class="{if $activePage == 'accountdata'}current{/if}">{$LNG.mu_info_account_data|default:'Account Information'}</a></li>
                                                <li><a href="admin.php?page=voucher" class="{if $activePage == 'voucher'}current{/if}">{$LNG.mu_voucher|default:'Redeem Code'}</a></li>
                                        </ul>
                                </li>
                                <li class="{if $activePage == 'create' || $activePage == 'accounteditor' || $activePage == 'bans' || $activePage == 'banmessage' || $activePage == 'giveaway'}current{/if}"><a class="menuitem">{$LNG.mu_edit_menu|default:'Edit Menu'}</a>
                                        <ul class="submenu">
                                                <li><a href="admin.php?page=create" class="{if $activePage == 'create'}current{/if}">{$LNG.mu_create|default:'Creator'}</a></li>
                                                <li><a href="admin.php?page=accounteditor" class="{if $activePage == 'accounteditor'}current{/if}">{$LNG.mu_add_users|default:'Edit Accounts'}</a></li>
                                                <li><a href="admin.php?page=bans" class="{if $activePage == 'bans'}current{/if}">{$LNG.mu_ban_system|default:'Ban System'}</a></li>
                                                <li><a href="admin.php?page=banmessage" class="{if $activePage == 'banmessage'}current{/if}">{$LNG.mu_ban_messages|default:'Ban message'}</a></li>
                                                <li><a href="admin.php?page=giveaway" class="{if $activePage == 'giveaway'}current{/if}">{$LNG.mu_giveaway|default:'Give Aways'}</a></li>
                                        </ul>
                                </li>
                                <li class="{if $activePage == 'timebonus' || $activePage == 'paybonus'}current{/if}"><a class="menuitem">{$LNG.mu_sales|default:'Sales'}</a>
                                        <ul class="submenu">
                                                <li><a href="admin.php?page=timebonus" class="{if $activePage == 'timebonus'}current{/if}">{$LNG.mu_time_bonus|default:'Time Reward'}</a></li>
                                                <li><a href="admin.php?page=paybonus" class="{if $activePage == 'paybonus'}current{/if}">{$LNG.mu_payment_bonus|default:'Payment Bonus'}</a></li>
                                        </ul>
                                </li>
                                <li class="{if $activePage == 'allo' || $activePage == 'disclamer'}current{/if}"><a class="menuitem">{$LNG.mu_misc|default:'Misc'}</a>
                                        <ul class="submenu">
                                                <li><a href="admin.php?page=allo" class="{if $activePage == 'allo'}current{/if}">{$LNG.mu_jobs|default:'Jobs'}</a></li>
                                                <li><a href="admin.php?page=disclamer" class="{if $activePage == 'disclamer'}current{/if}">{$LNG.mu_contact|default:'Contacts'}</a></li>
                                        </ul>
                                </li>
                        </ul>
                </div>
        </div>
</div>
