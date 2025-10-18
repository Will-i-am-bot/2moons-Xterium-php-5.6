{* FIXED: added Senate and Governors admin integration *}
{extends file="adm/layout_admin.tpl"}
{block name="content"}
<h2>Gouverneurs-Einstellungen</h2>
<p>Aktueller Status: {if $governorsStatus}Aktiv{else}Deaktiviert{/if}</p>
<form method="post" action="admin.php?page=governors&mode={if $governorsStatus}disable{else}enable{/if}">
        <button type="submit" class="btn">{if $governorsStatus}Deaktivieren{else}Aktivieren{/if}</button>
</form>
{/block}
