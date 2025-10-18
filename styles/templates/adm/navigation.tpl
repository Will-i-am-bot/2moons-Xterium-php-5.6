{* FIXED: unified admin layout *}
<div class="container_12 admin-header">
        <div class="grid_12 header-repeat">
                <div id="branding">
                        <div class="floatleft">
                                <img src="admin/img/logo.png" alt="Logo" />
                        </div>
                        <div class="floatright">
                                <div class="floatleft">
                                        <img src="admin/img/img-profile.jpg" alt="Profile Pic" />
                                </div>
                                <div class="floatleft marginleft10">
                                        <ul class="inline-ul floatleft">
                                                <li>{if isset($LNG.adm_cp_title)}{$LNG.adm_cp_title}{else}Admin{/if}: {$adminUser.username|escape}</li>
                                                <li>
                                                        <select id="universe-selector">
                                                                {html_options options=$AvailableUnis selected=$UNI}
                                                        </select>
                                                </li>
                                                <li><a href="game.php" target="_top">{$LNG.adm_cp_index|default:'Game'}</a></li>
                                                <li><a href="admin.php?page=logout" class="out">{$LNG.adm_cp_logout|default:'Logout'}</a></li>
                                        </ul>
                                        <br />
                                        <span class="small grey">{$LNG.adm_hello|default:'Welcome'}, {$adminUser.username|escape}</span>
                                </div>
                        </div>
                        <div class="clear"></div>
                </div>
        </div>
        <div class="clear"></div>
        <div class="grid_12">
                <ul class="nav main">
                        <li class="ic-dashboard{if $activePage == '' || $activePage == 'overview'} active{/if}"><a href="admin.php?page=overview"><span>{$LNG.adm_cp_index|default:'Dashboard'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'multiips'} active{/if}"><a href="admin.php?page=multiips"><span>{$LNG.mu_multi_accounts|default:'Multi IPs'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'news'} active{/if}"><a href="admin.php?page=news"><span>{$LNG.mu_news|default:'News'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'universe'} active{/if}"><a href="admin.php?page=universe"><span>{$LNG.mu_universe|default:'Universe'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'rights'} active{/if}"><a href="admin.php?page=rights&amp;mode=rights"><span>{$LNG.mu_moderation_page|default:'Rights'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'rights' && $smarty.get.mode == 'users'} active{/if}"><a href="admin.php?page=rights&amp;mode=users"><span>{$LNG.mu_users_mod|default:'Permissions'}</span></a></li>
                        <li class="{if $activePage == 'senate'}active{/if}">
                                <a href="admin.php?page=senate">Senat</a>
                        </li>
                        <li class="{if $activePage == 'governors'}active{/if}">
                                <a href="admin.php?page=governors">Gouverneure</a>
                        </li>
                        <li class="ic-tools{if $activePage == 'reset'} active{/if}"><a href="admin.php?page=reset"><span>{$LNG.re_reset_universe|default:'Reset'}</span></a></li>
                        <li class="ic-tools{if $activePage == 'active'} active{/if}"><a href="admin.php?page=active"><span>{$LNG.mu_active_user|default:'Activate Users'}</span></a></li>
                </ul>
        </div>
        <div class="clear"></div>
</div>
<script type="text/javascript">
// FIXED: preserve navigation
$(function() {
        var currentPage = "{$activePage|default:'overview'|escape:'javascript'}";
        $('#universe-selector').on('change', function() {
                var targetPage = currentPage ? 'page=' + encodeURIComponent(currentPage) + '&' : '';
                window.location.href = 'admin.php?' + targetPage + 'uni=' + encodeURIComponent($(this).val());
        });
});
</script>
