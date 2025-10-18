{* FIXED: added Senate and Governors admin integration *}
{extends file="adm/layout_admin.tpl"}
{block name="content"}
<h2>Senat-Einstellungen</h2>
<p>Aktueller Status: {if $senateStatus}Aktiv{else}Deaktiviert{/if}</p>
<form method="post" action="admin.php?page=senate&mode={if $senateStatus}disable{else}enable{/if}">
        <button type="submit" class="btn">{if $senateStatus}Deaktivieren{else}Aktivieren{/if}</button>
</form>
{/block}
