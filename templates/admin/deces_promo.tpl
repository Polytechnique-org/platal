{* $Id: deces_promo.tpl,v 1.2 2004-02-04 22:47:45 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post">
<table class="tinybicol">
  <tr>
    <td><input type="submit" value="<<" name="sub10" /></td>
    <td><input type="submit" value="<"  name="sub01" /></td>
    <td>
      Promotion :
{dynamic}
      <input type="text" name="promo" value="{$promo}" size="4" maxlength="4" />
{/dynamic}
      <input type="submit" value="GO" />
    </td>
    <td><input type="submit" value=">"  name="add01" /></td>
    <td><input type="submit" value=">>" name="add10" /></td>
  </tr>
</table>
</form>

<form action="{$smarty.server.PHP_SELF}" method="post">
<input type="hidden" name="promo" value="{$promo}" />
<table class="bicol" summary="liste des dates de décès">
  <tr>
    <th>Nom</th>
    <th>Date de décès</th>
  </tr>
{dynamic}
{foreach item=x from=$decedes}
  <tr class="{cycle values="impair,pair"}">
    <td>{$x.nom} {$x.prenom}</td>
    <td class="center">
      <input type="text" name="{$x.matricule}" value="{$x.deces}" size="10" maxlength="10">
    </td>
  </tr>
{/foreach}
{/dynamic}
  <tr>
    <td class="center">
      <input type="submit" name="valider" value="Valider" />
    </td>
  </tr>
</table>
</form>
	
{* vim:set et sw=2 sts=2 sws=2: *}
