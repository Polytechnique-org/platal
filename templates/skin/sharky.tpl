{* $Id: sharky.tpl,v 1.1 2004-08-29 15:46:54 x2000habouzit Exp $ *}
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
{include file=skin/common.header.tpl}
  <link rel="stylesheet" type="text/css" href="{"css/default.css"|url}" media="screen" />
  <link rel="stylesheet" type="text/css" href="{"css/sharky.css"|url}" media="screen" />
{if $xorg_head}
{include file=$xorg_head}
{/if}
{include file=skin/common.bandeau.head.tpl}
  </head>
  <body>
{include file=skin/common.devel.tpl}

{if $smarty.session.suid}
    <table id="suid" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        {dynamic}
        {$smarty.session.suid} ({$smarty.session.username})
        {/dynamic}
        [<a href="{"exit.php"|url}">exit</a>]
      </td>
    </tr>
    </table>
{/if}

{include file=skin/common.bandeau.tpl}

    <table id="body" cellpadding="0" cellspacing="0">
    <tr>
      <td id="body-logo">
        <img src="{"images/sk_sharky_logo.png"|url}" alt="[ LOGO ]" />
      </td>
      <td id="body-top">
        <img src="{"images/sk_sharky_ban.png"|url}" alt="[ Polytechnique.org ]" />
        <table>
        <tr>
          <td class="date-heure">
          <script type="text/javascript">
          <!--
            document.write(getNow());
          //-->
          </script>
          </td>
          <td class="inscrits">{insert name="getNbIns"} polytechniciens sur le web</td>
        </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td id="body-menu">
{include_php file=menu.conf.php}
{foreach key=menu_title item=menu_list from=$menu}
{if $menu_title}
        <div class="menu_title">{$menu_title}</div>
{/if}
{foreach key=menu_item item=menu_url from=$menu_list}
        <div class="menu_item"><a href="{$menu_url|url}">{$menu_item}</a></div>
{/foreach}
{/foreach}
{perms level=admin}{insert name="mkStats"}{/perms}
      </td>
      <td id="content">
{include file=$xorg_tpl}
      </td>
    </tr>
    <tr>
      <td id="body-bottom" colspan="2">
{include file=skin/common.footer.tpl}
      </td>
    </tr>
    </table>

  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2: *}
