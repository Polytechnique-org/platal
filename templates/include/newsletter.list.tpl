{* $Id: newsletter.list.tpl,v 1.4 2004-08-30 09:14:50 x2000habouzit Exp $ *}

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
    <th>&nbsp;</th>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$nl.date|date_format:"%Y-%m-%d"}</td>
    <td>
      <a href="{"newsletter.php"|url}?nl_id={$nl.id}">{$nl.titre}</a>
    </td>
    {if $admin}
    <td>
      <form method="post" action="{$smarty.server.PHP_SELF}">
        <div>
          <input type="hidden" name="nl_id" value="{$nl.id}" />
          <input type="hidden" name="action" value="edit" />
          <input type="submit" value="edit" />
          <input type="submit" value="del" />
        </div>
      </form>
    </td>
    {else}
    <td>
      &nbsp;
    </td>
    {/if}
  </tr>
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
