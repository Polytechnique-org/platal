{* $Id: choix_date.tpl,v 1.2 2004-02-11 13:15:35 x2000habouzit Exp $ *}

{assign var=def_month value=$smarty.now|date_format:"%m"}
{assign var=def_year value=$smarty.now|date_format:"%Y"}
{assign var=month value=$smarty.request.mois|default:$def_month}

<div class="center">
  <form method="POST" action="{$smarty.server.PHP_SELF}">
    <input type="hidden" name="action" value="lister" />
    Afficher la période suivante :
    <select name="mois" size="1">
{foreach key=key item=item from=$month_arr}
      <option value="{$key}" {if $month eq $key}selected="selected"{/if}>{$item}</option>
{/foreach}
    </select>
    <input type="text" name="annee" size="10" value="{$smarty.request.annee|default:$def_year}" />
    <input type="submit" value="lister" />
  </form>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
