<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>{if isset($LNG.sys_error_headline)}{$LNG.sys_error_headline}{else}{$fcm_info}{/if}</title>
<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
</head>
<body class="message error">
<div id="errorMessage">
<div class="messageBox">
<h1>{if isset($LNG.sys_error_headline)}{$LNG.sys_error_headline}{else}{$fcm_info}{/if}</h1>
<p>{$mes}</p>
{if $goto}
<p class="redirect">{$LNG.sys_redirect_message|default:'Weiterleitung'}: <a href="{$goto}">{$goto}</a></p>
{/if}
</div>
</div>
{if !$Fatal}
<script type="text/javascript">
{literal}
(function(){
var redirectLink = '{/literal}{$goto|default:''}{literal}';
var redirectDelay = {/literal}{$gotoinsec|default:0}{literal};
if(redirectLink !== '' && redirectDelay > 0){
window.setTimeout(function(){ window.location.href = redirectLink; }, redirectDelay * 1000);
}
})();
{/literal}
</script>
{/if}
</body>
</html>
