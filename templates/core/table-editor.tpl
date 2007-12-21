{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}


<h1>{$title}</h1>

{if $list}
<script type="text/javascript">
	{literal}
	function redirect(a) {
		document.location = a;
	}
	{/literal}
</script>
<table class="bicol">
<tr>
  {foreach from=$t->vars item=myval key=myvar}{if $myval.display}
    <th style="cursor:pointer" onclick="redirect('{$t->pl}/sort{if $t->sortfield eq $myvar && !$t->sortdesc}desc{/if}/{$myvar}')">{$myval.desc}{if $t->sortfield eq $myvar}{if $t->sortdesc}{icon name="bullet_arrow_down"}{else}{icon name="bullet_arrow_up"}{/if}{/if}</th>
  {/if}{/foreach}
  {if !$hideactions}
  <th>action</th>
  {/if}
</tr>
{if !$readonly}
<tr class="impair">
  <td colspan="{$t->nbfields}">
    <strong>
      Nouvelles entrées&nbsp;: <a href="{$t->pl}/new">Manuellement</a> &bull; <a href="{$t->pl}/massadd">Depuis un CSV</a>
    </strong>
  </td>
  <td class="right">
    <a href="{$t->pl}/new">{icon name=add title='nouvelle entrée'}</a>
  </td>
</tr>
{/if}
{iterate from=$list item=myrow}
<tr class="{cycle values="pair,impair"}">
{foreach from=$t->vars item=myval}{if $myval.display}
  <td>
    {assign var="myfield" value=$myval.Field}
    {if $myfield eq $t->idfield}
        {assign var="idval" value=$myrow.$myfield}
    {/if}
    {if $myval.Type eq 'timestamp'}
      <span class="smaller">{$myrow.$myfield|date_format:"%x %X"}</span>
    {elseif $myval.Type eq 'checkbox'}
      <input type="checkbox" disabled="disabled"{if $myrow.$myfield} checked="checked"{/if}/>
    {else}
      {$myrow.$myfield}
    {/if}
  </td>
{/if}{/foreach}
  {if !$hideactions}
  <td class="action">
    {if !$readonly}
    <a href="{$t->pl}/edit/{$idval}">{icon name=page_edit title='éditer'}</a>
    <a href="{$t->pl}/delete/{$idval}">{icon name=delete title='supprimer'}</a>
    {/if}
  </td>
  {/if}
</tr>
{/iterate}
</table>

{if ($p_prev > -1) || ($p_next > -1)}
<p class="pagenavigation">
{if $p_prev > -1}<a href="{$platal->path}?start={$p_prev}">{$msg_previous_page}</a>&nbsp;{/if}
{if $p_next > -1}<a href="{$platal->path}?start={$p_next}">{$msg_next_page}</a>{/if}
</p>
{/if}

{elseif $massadd}
{include file="include/csv-importer.tpl"}

<p>
<a href="{$t->pl}">back</a>
</p>

{else}

<form method="post" action="{$t->pl}/update/{$id}">
  <table class="bicol">
    <tr class="impair">
      <th colspan="2">
        {if $id}
            modification de l'entrée 
        {else}
            nouvelle entrée
        {/if}
      </th>
    </tr>
    {foreach from=$t->vars item=myval}{assign var="myfield" value=$myval.Field}{if ($myfield neq $t->idfield) or ($t->idfield_editable)}
    <tr class="{cycle values="pair,impair"}">
      <td>
        <strong>{$myval.desc}</strong>
      </td>
      <td>
        {if $myval.Type eq 'set'}
          <select name="{$myfield}[]" multiple="multiple">
            {foreach from=$myval.List item=option}
              <option value="{$option}" {if $entry.$myfield.$option}selected="selected"{/if}>{$option}</option>
            {/foreach}
          </select>
        {elseif $myval.Type eq 'enum'}
          <select name="{$myfield}">
            {foreach from=$myval.List item=option}
              <option value="{$option}" {if $entry.$myfield eq $option}selected="selected"{/if}>{$option}</option>
            {/foreach}
          </select>
        {elseif ($myval.Type eq 'textarea') or ($myval.Type eq 'varchar200')}
          <textarea name="{$myfield}" rows="{if $myval.Type eq 'varchar200'}3{else}10{/if}" cols="70">{$entry.$myfield}</textarea>
        {elseif ($myval.Type eq 'checkbox')}
          <input type="checkbox" name="{$myfield}" value="{$myval.Value}"{if $entry.$myfield} checked="checked"{/if}/>
        {else}
          <input type="text" name="{$myfield}" value="{$entry.$myfield}" {if $myval.Size}size="{$myval.Size}" maxlength="{$myval.Maxlength}"{/if}/>
          {if $myval.Type eq 'timestamp'}<em>jj/mm/aaaa hh:mm:ss</em>{/if}
          {if $myval.Type eq 'date'}<em>jj/mm/aaaa</em>{/if}
          {if $myval.Type eq 'time'}<em>hh:mm:ss</em>{/if}
        {/if}
      </td>
    </tr>
    {/if}{/foreach}
  </table>

  <p class="center">
  <input type="submit" value="enregistrer" />
  </p>

</form>

<p>
<a href="{$t->pl}">back</a>
</p>

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
