{dynamic}
{if $formulaire==0}
  <div class="rubrique">
    Résultats
  </div>
  <p class="smaller">
    {if $nb_resultats==0}Aucune{else}{$nb_resultats}{/if} réponse{if $nb_resultats>1}s{/if}.
  </p>
  <table class="bicol">
    {section name=resultat loop=$resultats}
    <tr class="{if $smarty.section.resultat.index is even}pair{else}impair{/if}">
      <td>
      <strong>{$resultats[resultat].nom} {$resultats[resultat].prenom}</strong>
      </td>
      <td>
      (X {$resultats[resultat].promo})
      </td>
    </tr>
    {/section}
  </table>
{else}
  <div class="rubrique">
    Recherche
  </div>
  <div class="center">
    <form action="{$smarty.server.PHP_SELF}" method="post">
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
