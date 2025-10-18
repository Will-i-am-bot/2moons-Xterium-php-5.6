{include file="overall_header.tpl"}
{include file="head_nav.tpl"}

<div class="grid_10">
    <div class="box round first grid">
        <h2>{$mod_module}</h2>
        <div class="block">
            <p><strong>{$mod_info}</strong></p>
            <style type="text/css">
            .switch {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 24px;
            }
            .switch input {
                display: none;
            }
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #c0392b;
                -webkit-transition: .4s;
                transition: .4s;
                border-radius: 24px;
            }
            .slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
                border-radius: 50%;
            }
            input:checked + .slider {
                background-color: #27ae60;
            }
            input:checked + .slider:before {
                -webkit-transform: translateX(26px);
                -ms-transform: translateX(26px);
                transform: translateX(26px);
            }
            .module-state-text {
                margin-left: 10px;
                font-weight: bold;
            }
            .module-actions {
                margin-top: 15px;
            }
            </style>
            <form method="post" action="{$moduleFormAction}">
                <table class="data display datatable" id="moduleTable">
                    <thead>
                        <tr>
                            <th>Modulname</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $modules as $module}
                        <tr class="odd gradeX">
                            <td><center>{$module.name}</center></td>
                            <td class="center">
                                <label class="switch">
                                    <input type="checkbox" name="modules[]" value="{$module.name|escape:'html'}" {if $module.state == 1}checked{/if}>
                                    <span class="slider round"></span>
                                </label>
                                <span class="module-state-text">
                                    {if $module.state == 1}
                                        {$mod_active}
                                    {else}
                                        {$mod_deactive}
                                    {/if}
                                </span>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                <div class="module-actions">
                    <button type="submit" class="btn btn-primary">{$mod_save_changes}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="clear">
</div>
