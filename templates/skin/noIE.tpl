{* $Id: noIE.tpl,v 1.1 2004-08-29 21:14:41 x2000habouzit Exp $ *}
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    {include file=skin/common.header.tpl}
    <link rel="stylesheet" type="text/css" href="{"css/noie.css"|url}" media="screen" />
    {if $xorg_head}
    {include file=$xorg_head}
    {/if}
    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>

  {if $simple}

    <div id="content">
      {include file=$xorg_tpl}
    </div>

  {else}

    <div id="top">
      {if $smarty.session.suid}
      <div id="suid">
        {dynamic}
        {$smarty.session.suid} ({$smarty.session.username})
        {/dynamic}
        [<a href="{"exit.php"|url}">exit</a>]
      </div>
      {/if}

      {include file=skin/common.bandeau.tpl}
     
      <div class="center">
        <img src="{"images/sk_sharky_ban.png"|url}" alt="[ BAN ]" />
        <span>
          {insert name="getNbIns"} polytechniciens sur le web
        </span>
      </div>

      <ul id="menu">
        {include_php file=menu.conf.php}
        {foreach key=menu_title item=menu_list from=$menu}
        {if $menu_title}
        
        <li>
        {$menu_title}
        <div class="liens">
          {foreach key=menu_item item=menu_url from=$menu_list}
          <a class="menu_item" href="{$menu_url|url}">{$menu_item}</a>
          {/foreach}
        </div>
        </li>
        
        {else}
        
        {foreach key=menu_item item=menu_url from=$menu_list}
        <li>
        <a class="menu_item" href="{$menu_url|url}">{$menu_item}</a>
        </li>
        {/foreach}
       
        {/if}
        {/foreach}
        
        {perms level=admin}
        <li><a href="{"admin/valider.php"|url}">{insert name="mkStats"}</a></li>
        {/perms}
        <li style="clear:both"></li>
      </ul>

    </div>
    
    <div id="content">
      {include file=$xorg_tpl}
      {include file=skin/common.devel.tpl}
    </div>

    <div id="bottom">
      {include file=skin/common.footer.tpl}
    </div>
  {/if}
  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2: *}
