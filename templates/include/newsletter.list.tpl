{* $Id: newsletter.list.tpl,v 1.3 2004-08-24 23:06:05 x2000habouzit Exp $ *}

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
<tr>
  <th>date</th>
  <th>titre</th>
  <th colspan="2">&nbsp;</th>
</tr>
{foreach item=nl from=$nl_list}
<tr class="{cycle values="impair,pair"}">
  <td>{$nl.date|date_format:"%Y-%m-%d"}</td>
  <td>
    <a href="{"newsletter.php?nl_id=`$nl.id`"|url}">{$nl.titre}</a>
  </td>
  {if $admin}
  <td>
    <form method="post" action="{$smarty.server.PHP_SELF}">
      <input type="hidden" name="nl_id" value="{$nl.id}" />
      <input type="hidden" name="action" value="edit" />
      <input type="submit" value="edit" />
    </form>
  </td>
  <td>
    <form method="post" action="{$smarty.server.PHP_SELF}">
      <input type="hidden" name="nl_id" value="{$nl.id}" />
      <input type="hidden" name="action" value="delete" />
      <input type="submit" value="del" />
    </form>
  </td>
  {else}
  <td colspan="2">
    &nbsp;
  </td>
  {/if}
</tr>
{/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
