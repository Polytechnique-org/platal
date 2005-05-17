{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

{if !$doedit}
{if !$readonly}

{literal}
<script type="text/javascript">
  <!--
  function del( myid ) {
    if (confirm ("You are about to delete this entry. Do you want to proceed?")) {
      document.forms.operations.action.value = "del";
      document.forms.operations.{/literal}{$prefix}{literal}id.value = myid;
      document.forms.operations.submit();
      return true;
    }
  }
  function edit( myid ) {
    document.forms.operations.action.value = "edit";
    document.forms.operations.{/literal}{$prefix}{literal}id.value = myid;
    document.forms.operations.submit();
    return true;
  }
  // -->
</script>
{/literal}
{/if}

<form method="post" action="{$smarty.server.PHP_SELF}" id="operations">
  <div>
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="{$prefix}id" value="" />
  </div>
</form>

<table class="bicol">
<tr>
  {if $idsum}<th>id</th>{/if}
  {foreach from=$vars item=myval}
  {if $myval.sum}<th>{$myval.desc}</th>{/if}
  {/foreach}
  {if !$hideactions}
  <th>action</th>
  {/if}
</tr>
{if !$readonly}
<tr class="impair">
  <td colspan="{$ncols}"><strong>nouvelle entrée</strong></td>
  <td class="action">
    <a href="javascript:edit('');">create</a>
  </td>
</tr>
{/if}
{foreach from=$rows item=myrow}{assign var="myarr" value=$myrow[1]}
<tr class="{cycle values="pair,impair"}">
  {if $idsum}<td>{$myrow[0]}</td>{/if}
{foreach from=$vars key=mykey item=myval}
{if $myval.sum}
  <td>
  {if $myval.type=="timestamp"}
  <span class="smaller">{$myarr.$mykey|date_format:"%x %X"}</span>
  {elseif $myval.type=="set" and $myval.trans}
  {$myval.trans[$myval.value]}
  {elseif $myval.type=="ext"}
  {extval table=$table field=$mykey value=$myarr.$mykey vtable=$myval.vtable vjoinid=$myval.vjoinid vfield=$myval.vfield}
  {else}
  {$myarr.$mykey}
  {/if}
  </td>
{/if}
{/foreach}
  {if !$hideactions}
  <td class="action">
    {if !$readonly}
    <a href="javascript:edit('{$myrow[0]}');">edit</a>
    <a href="javascript:del('{$myrow[0]}');">delete</a>
    {/if}
    {foreach from=$myrow[2] item=myaction}
    {a lnk=$myaction}
    {/foreach}
  </td>
  {/if}
</tr>
{/foreach}
</table>

{if ($p_prev > -1) || ($p_next > -1)}
<p class="pagenavigation">
{if $p_prev > -1}<a href="?start={$p_prev}">{$msg_previous_page}</a>&nbsp;{/if}
{if $p_next > -1}<a href="?start={$p_next}">{$msg_next_page}</a>{/if}
</p>
{/if}

{else}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <table class="bicol">
    <tr class="impair">
      <th colspan="2">
        <input type="hidden" name="action" value="update" />
        {if $id!=''}
        modification de l'entrée 
        <input type="hidden" name="{$prefix}id" value="{$id}" />
        {else}
        nouvelle entrée
        {/if}
      </th>
    </tr>
    {foreach from=$vars key=mykey item=myval}
    <tr class="{cycle values="pair,impair"}">
      <td>
        <strong>{$myval.desc}</strong>
        {if $myval.type=="password"}<br /><em>(blank=no change)</em>{/if}
      </td>
      <td>
        {if $myval.edit}
        {if $myval.type=="textarea"}
        <textarea name="{$prefix}{$mykey}" rows="10" cols="70">{$myval.value}</textarea>
        {elseif $myval.type=="set"}
        {if $myval.trans}
        {flags table=$table field=$mykey name="$prefix$mykey" selected=$myval.trans[$myval.value] trans=$myval.trans}
        {else}
        {flags table=$table field=$mykey name="$prefix$mykey" selected=$myval.value}
        {/if}
        {elseif $myval.type=="ext"}
        {extval table=$table field=$mykey name="$prefix$mykey" vtable=$myval.vtable vjoinid=$myval.vjoinid vfield=$myval.vfield selected=$myval.value}
        {elseif $myval.type=="timestamp"}
        <input type="text" name="{$prefix}{$mykey}" value="{$myval.value|date_format:"%x %X"}" />
        {elseif $myval.type=="password"}
        <input type="password" name="{$prefix}{$mykey}" size="40" />
        {else}
        <input type="{$myval.type}" name="{$prefix}{$mykey}" size="40" value="{$myval.value}" />
        {/if}
        {else}
        {$myval.value|escape}
        {/if}
      </td>
    </tr>
    {/foreach}
  </table>

  <p class="center">
  <input type="submit" value="enregistrer" />
  </p>

</form>

<p>
<a href="{$smarty.server.PHP_SELF}">back</a>
</p>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
