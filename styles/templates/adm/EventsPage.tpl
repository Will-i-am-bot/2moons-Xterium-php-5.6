{include file="overall_header.tpl"}
{include file="head_nav.tpl"}

<div class="grid_10">
    <div class="box round first grid">
        <h2>Event Verwaltung</h2>
        <div class="block">
            {if $successMessage != ''}
            <div class="message success">
                <p>{$successMessage|escape:'html'}</p>
            </div>
            {/if}

            {if $events|@count == 0}
            <p>Keine Events gefunden.</p>
            {else}
            <form method="post" action="{$formAction}">
                <table class="data display datatable" id="eventsTable">
                    <thead>
                        <tr>
                            <th>Event-Name</th>
                            <th>Beschreibung</th>
                            <th>Aktiv</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $events as $event}
                        <tr class="odd gradeX">
                            <td><strong>{$event.displayName|escape:'html'}</strong></td>
                            <td>{$event.description|default:'Keine Beschreibung vorhanden.'|escape:'html'}</td>
                            <td class="center">
                                <input type="checkbox" name="events[{$event.flag|escape:'html'}]" value="1" {if $event.active == 1}checked="checked"{/if} />
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                <div class="module-actions">
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
            {/if}
        </div>
    </div>
</div>
<div class="clear"></div>
{include file="overall_footer.tpl"}
