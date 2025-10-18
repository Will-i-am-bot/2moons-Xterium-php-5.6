<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{$pageTitle|default:$title}</title>
    <link rel="stylesheet" type="text/css" href="admin/css/reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="admin/css/text.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="admin/css/grid.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="admin/css/layout.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="admin/css/nav.css" media="screen" />
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="admin/css/ie6.css" media="screen" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" type="text/css" href="admin/css/ie.css" media="screen" /><![endif]-->
    <link rel="stylesheet" type="text/css" href="admin/css/jquery.jqplot.min.css" />
    <script src="admin/js/jquery-1.6.4.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="admin/js/jquery-ui/jquery.ui.core.min.js"></script>
    <script src="admin/js/jquery-ui/jquery.ui.widget.min.js" type="text/javascript"></script>
    <script src="admin/js/jquery-ui/jquery.ui.accordion.min.js" type="text/javascript"></script>
    <script src="admin/js/jquery-ui/jquery.effects.core.min.js" type="text/javascript"></script>
    <script src="admin/js/jquery-ui/jquery.effects.slide.min.js" type="text/javascript"></script>
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="admin/js/jqPlot/excanvas.min.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/jquery.jqplot.min.js"></script>
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/plugins/jqplot.barRenderer.min.js"></script>
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/plugins/jqplot.pieRenderer.min.js"></script>
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/plugins/jqplot.highlighter.min.js"></script>
    <script language="javascript" type="text/javascript" src="admin/js/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
    <script type="text/javascript" src="./scripts/base/jquery.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/base/jquery.ui.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/base/jquery.cookie.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/base/jquery.fancybox.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/base/jquery.validationEngine.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/l18n/validationEngine/jquery.validationEngine-{$lang}.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/base/tooltip.js?v={$REV}"></script>
    <script type="text/javascript" src="./scripts/game/base.js?v={$REV}"></script>
    {foreach item=scriptname from=$scripts}
    <script type="text/javascript" src="./scripts/game/{$scriptname}.js?v={$REV}"></script>
    {/foreach}
    <script type="text/javascript">
    var ServerTimezoneOffset = {$Offset};
    var serverTime  = new Date({$date.0}, {$date.1 - 1}, {$date.2}, {$date.3}, {$date.4}, {$date.5});
    var xsize       = screen.width; // FIXED: unified admin layout
    var ysize       = screen.height; // FIXED: unified admin layout
    var breite      = 720; // FIXED: unified admin layout
    var hoehe       = 300; // FIXED: unified admin layout
    var xpos        = (xsize-breite) / 2; // FIXED: unified admin layout
    var ypos        = (ysize-hoehe) / 2; // FIXED: unified admin layout
    var Ready               = "{$LNG.ready}";
    var Skin                = "{$dpath}";
    var Lang                = "{$lang}";
    var head_info   = "{$LNG.fcm_info}";
    var days                = {$LNG.week_day|json|default:'[]'};
    var months              = {$LNG.months|json|default:'[]'};
    var tdformat    = "{$LNG.js_tdformat}";
    function openEdit(id, type) {
            var editlist = window.open("?page=qeditor&edit="+type+"&id="+id, "edit", "scrollbars=yes,statusbar=no,toolbar=no,location=no,directories=no,resizable=no,menubar=no,width=850,height=600,screenX="+((screen.width-600)/2)+",screenY="+((screen.height-850)/2)+",top="+((screen.height-600)/2)+",left="+((screen.width-850)/2));
            editlist.focus();
    }
    </script>
    <script src="admin/js/setup.js" type="text/javascript"></script>
    <script type="text/javascript">
    $(document).ready(function () {
        setupDashboardChart('chart1');
        setupLeftMenu();
        setSidebarHeight();
        {$execscript}
    });
    </script>
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'overview'}" class="{$bodyclass}">
    <div id="tooltip" class="tip"></div>
    {if $showAdminLayout|default:true}
    <div id="admin-wrapper">
        <!-- FIXED: unified admin layout -->
        {include file="adm/navigation.tpl"}
        <div class="container_12 admin-container">
            {include file="adm/sidebar.tpl"}
    {/if}
