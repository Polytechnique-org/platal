{dynamic}
{if $formulaire==0}
  <div class="rubrique">
    Résultats
  </div>
  <p class="smaller">
    {if $nb_resultats_total==0}Aucune{else}{$nb_resultats_total}{/if} réponse{if $nb_resultats_total>1}s{/if}.
  </p>
  <table class="bicol">
    {section name=resultat loop=$resultats}
    <tr class="{cycle values="pair,impair"}">
      <td>
        <strong>{$resultats[resultat].nom} {$resultats[resultat].prenom}</strong>
        {if $resultats[resultat].epouse neq ""}
          <div>({$resultats[resultat].epouse} {$resultats[resultat].prenom})</div>
        {/if}
        {if $resultats[resultat].decede == 1}
          <div>(décédé)</div>
        {/if}
      </td>
      <td>
        (X {$resultats[resultat].promo})
        {if $resultats[resultat].inscrit==1}
          <a href="javascript:x()"  onclick="popWin('x.php?x={$resultats[resultat].username}')">
          <img src="images/loupe.gif" border=0 ALT="Afficher les détails"></a>
          <a href="vcard.php/{$resultats[resultat].username}.vcf?x={$resultats[resultat].username}">
          <img src="images/vcard.png" border=0 ALT="Afficher la carte de visite"></a>
          <a href="mescontacts.php?action={if $resultats[resultat].contact!=""}retirer{else}ajouter{/if}&amp;user={$resultats[resultat].username}&amp;mode=normal">
          <img src="images/{if $resultats[resultat].contact!=""}retirer{else}ajouter{/if}.gif" border=0 ALT="{if $resultats[resultat].contact!=""}Retirer de{else}Ajouter parmi{/if} mes contacts"></a>
        {/if}
      </td>
    </tr>
    {/section}
  </table>
  {if $perpage<$nb_resultats_total}
    {if $offset!=0}
        <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&rechercher=1&{$url_args}&offset=0">Précédent</a>
    {/if}
    {if $offset<$nb_resultats_total-$perpage}
        <a
        href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&rechercher=1&{$url_args}&offset={$offset+$perpage}">Suivant</a>
    {/if}
  {/if}
{else}
  <div class="rubrique">
    Recherche
  </div>
  <div class="center">
    <form action="{$smarty.server.PHP_SELF}" method="post">
    <input type="hidden" name="public_directory" value="{$public_directory}">
    <table class="tinybicol" cellpadding="3" summary="Recherche">
      <tr>
        <td>Nom</td>
        <td><input type="text" name="name" size="50" maxlength="50" /></td>
      </tr>
      <tr>
        <td>Prénom</td>
        <td><input type="text" name="firstname" size="50" maxlength="50" /></td>
      </tr>
      <tr>
        <td>Promotion</td>
        <td>
        <select name="egal">
        <option value="=" selected>&nbsp;=&nbsp;</option>
        <option value=">" >&nbsp;&gt;&nbsp;</option>
        <option value="<" >&nbsp;&lt;&nbsp;</option>
        </select>
        <input type="text" name="promo" size="4" maxlength="4" />
        </td>
      </tr>
      <tr>
        <td colspan="2" class="center"><input type="submit" name="rechercher" value="Ok" /></td>
      </tr>
    </table>
    </form>
  </div>
{/if}
{/dynamic}
