{* $Id: postfix.common.tpl,v 1.4 2004-08-26 14:44:43 x2000habouzit Exp $ *}

{dynamic}
<p class="erreur">{$erreur}</p>

<div class="rubrique">
{$title}
</div>

<a href="{""|url}">page d'admin</a> |
<a href="{"admin/postfix_blacklist.php"|url}">blacklist</a> |
<a href="{"admin/postfix_perm.php"|url}">permissions</a> | 
<a href="{"admin/postfix_retardes.php"|url}">mails retardés</a>

<p>
{$expl}
</p>

<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="text" name="nomligne" size="100" />
  <input type="submit" name="add" value="Add" />
</form>

{foreach item=line from=$list}
<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="text" name="nomligne" value="{$line}" size="100" />
  <input type="submit" name="del" value="Del" />
</form>
{/foreach}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
