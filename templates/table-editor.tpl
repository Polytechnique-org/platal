{* $Id: table-editor.tpl,v 1.7 2004-08-26 12:31:15 x2000habouzit Exp $ *}

{dynamic}

<div class="rubrique">
    {$title}
</div>

{if !$doedit}

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

<form method="post" action="{$smarty.server.PHP_SELF}" id="operations">
  <div>
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="{$prefix}id" value="" />
  </div>
</form>

<table class="bicol">
<tr>
  <th>id</th>
  {foreach from=$vars item=myval}
  {if $myval.sum}<th>{$myval.desc}</th>{/if}
  {/foreach}
  <th>action</th>
</tr>
<tr class="impair">
  <td colspan="{$ncols}"><strong>nouvelle entrée</strong></td>
  <td class="action">
    <a href="javascript:edit('');">create</a>
  </td>
</tr>
{foreach from=$rows item=myrow}{assign var="myarr" value=$myrow[2]}
<tr class="{cycle values="pair,impair"}">
  <td>{$myrow[1]}</td>
{foreach from=$vars key=mykey item=myval}
{if $myval.sum}
  <td>
  {if $myval.type=="timestamp"}
  <span class="smaller">{$myarr.$mykey|date_format:"%Y-%m-%d %H:%M:%S"}</span>
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
  <td class="action">
    <a href="javascript:edit('{$myrow[1]}');">edit</a>
    <a href="javascript:del('{$myrow[1]}');">delete</a>
{foreach from=$myrow[3] item=myaction}
    {a lnk=$myaction}
{/foreach}
  </td>
</tr>
{/foreach}

</table>

{else}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <table class="bicol">
    <tr class="impair">
      <th colspan="2">
        <input type="hidden" name="action" value="update" />
        {if $id!=''}
        <input type="hidden" name="{$prefix}id" value="{$id}"/>
        modification de l'entrée 
        {else}
        nouvelle entrée
        {/if}
      </th>
    </tr>
    {foreach from=$vars key=mykey item=myval}
    {if $mykey != $idfield}
    <tr class="{cycle values="pair,impair"}">
      <td>
        <strong>{$myval.desc}</strong>
        {if $myval.type=="password"}<br /><em>(blank=no change)</em>{/if}
      </td>
      <td>
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
        <input type="text" name="{$prefix}{$mykey}" value="{$myval.value|date_format:"%Y-%m-%d %H:%M:%S"}" />
        {elseif $myval.type=="password"}
        <input type="password" name="{$prefix}{$mykey}" size="40" />
        {else}
        <input type="{$myval.type}" name="{$prefix}{$mykey}" size="40" value="{$myval.value}" />
        {/if}
      </td>
    </tr>
    {/if}
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

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
