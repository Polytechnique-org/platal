{* $Id: admin_trombino.tpl,v 1.1 2004-07-19 09:33:21 x2000habouzit Exp $ *}

<div class="rubrique">
  Gestion du trombino
</div>

{dynamic}
<p>
Photo actuelle de {$username}
</p>

<img src="../getphoto.php?x={$smarty.request.uid}" />
<br />

<p>
<a href="{$smarty.server.PHP_SELF}?uid={$smarty.request.uid}&amp;action=supprimer">Supprimer cette photo</a>
</p>

<p>
<a href="{$smarty.server.PHP_SELF}?uid={$smarty.request.uid}&amp;action=ecole">Voir sa photo de trombi récupérée à l'école (si disponible)</a>
</p>

<form action="{$smarty.server.PHP_SELF}" method="post" enctype="multipart/form-data">
  <input type="hidden" name="uid" value="{$smarty.request.uid}" />
  <input type="hidden" name="action" value="valider" />
  <input name="userfile" type="file" size="20" maxlength="150" />
  <input type="submit" value="Envoyer" />
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
