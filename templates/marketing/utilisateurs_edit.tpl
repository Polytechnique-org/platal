{* $Id: utilisateurs_edit.tpl,v 1.4 2004-08-30 12:18:41 x2000habouzit Exp $ *}

<div class="rubrique">
  Editer la base de tous les X
</div>
{dynamic}
{if $success eq "1"}
<p>
La modification de la table identification a été effectuée.
</p>
<p>
<a href="{$smarty.server.PHP_SELF}">Retour</a>
</p>
{else}
<p>
<strong>Attention</strong> la table d'identification contenant la liste des polytechniciens sera
modifiée !! (aucune vérification n'est faite)
</p>
<div class="center">
  <form action="{$smarty.server.PHP_SELF}" method="get">
    <table class="bicol" summary="Edition de fiche">
      <tr>
        <th colspan="2">
          Editer
        </th>
      </tr>
      <tr>
        <td class="titre">Prénom :</td>
        <td>
          <input type="text" size="40" maxlength="60" value="{$row.prenom}" name="prenomN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Nom :</td>
        <td>
          <input type="text" size="40" maxlength="60" value="{$row.nom}" name="nomN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Femme :</td>
        <td>
          <input type="checkbox" name="flag_femmeN" value="1"{if in_array("femme",explode(",",$row.flags))}checked{/if} />
        </td>
      </tr>
      <tr>
        <td class="titre">Promo :</td>
        <td>
          <input type="text" size="4" maxlength="4" value="{$row.promo}" name="promoN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Décés :</td>
        <td>
          <input type="text" size="10" value="{$row.deces}" name="decesN" />
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$row.matricule_ax}" onclick="return popup(this)">Voir sa fiche sur le site de l'AX</a>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="hidden" name="xmat" value="{$smarty.request.xmat}" />
          <input type="submit" value="Modifier la base" name="submit" />
        </td>
      </tr>
    </table>
  </form>
</div>
{/if}
{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}
