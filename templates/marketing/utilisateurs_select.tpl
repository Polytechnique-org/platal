{* $Id: utilisateurs_select.tpl,v 1.2 2004-08-26 14:44:45 x2000habouzit Exp $ *}

{dynamic}

<div class="rubrique">
  Selectionner un X non inscrit
</div>

<p>
Sélectionne l'X que tu veux inscrire ou &agrave; qui tu veux envoyer le mail de pré-inscription.
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="bicol" cellpadding="3" summary="Sélection de l'X non inscrit">
    <tr>
      <th>
        Sélection de l'X non inscrit
      </th>
    </tr>
    <tr>
      <td>
        <select name="xmat">
          {foreach from=$nonins item=x}
          <option value="{$x.matricule}">{$x.matricule} {$x.prenom} {$x.nom} (X{$x.promo})</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td class="center">
        {foreach from=$id_actions item=id_action}
        <input type="submit" name="submit" value="{$id_action}" />&nbsp;&nbsp;
        {/foreach}
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
