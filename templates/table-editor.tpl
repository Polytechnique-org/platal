{dynamic}

<div class="rubrique">
    {$title}
</div>

{if !$doedit}

{literal}
<script language="javascript" type="text/javascript">
  <!--
  function del( myid ) {
    if (confirm ("You are about to delete this entry. Do you want to proceed?")) {
      document.operations.action.value = "del";
      document.operations.{/literal}{$prefix}{literal}id.value = myid;
      document.operations.submit();
      return true;
    }
  }
  function edit( myid ) {
    document.operations.action.value = "edit";
    document.operations.{/literal}{$prefix}{literal}id.value = myid;
    document.operations.submit();
    return true;
  }
  // -->
</script>
{/literal}

<form method="post" action="{$smarty.server.PHP_SELF}" name="operations">
<input type="hidden" name="action" value="" />
<input type="hidden" name="{$prefix}id" value="" />
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
  <small>{$myarr.$mykey|date_format:"%Y-%m-%d %H:%M:%S"}</small>
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
<input type="hidden" name="action" value="update">
{if $id!=''}
<input type="hidden" name="{$prefix}id" value="{$id}">
{/if}
<table class="bicol">
<tr class="impair">
  <th colspan="2">
  {if $id!=''}modification de l'entrée {$id}
  {else}nouvelle entrée{/if}
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
    <textarea name="{$prefix}{$mykey}" rows="10" cols="70">{$myval.value|escape}</textarea>
{elseif $myval.type=="set"}
    {flags table=$table field=$mykey name="$prefix$mykey" selected=$myval.value}
{elseif $myval.type=="timestamp"}
    <input type="text" name="{$prefix}{$mykey}" value="{$myval.value|date_format:"%Y-%m-%d %H:%M:%S"}" />
{elseif $myval.type=="password"}
    <input type="password" name="{$prefix}{$mykey}" size="40" />
{else}
    <input type="{$myval.type}" name="{$prefix}{$mykey}" size="40" value="{$myval.value|escape}" />
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
