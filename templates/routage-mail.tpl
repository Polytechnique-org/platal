{dynamic}
{if $no_update_bd}
<p class="normal">
  Le site est en mode de consultation seulement, tu ne peux pas modifier tes adresses
  de redirections.
</p>
{/if}
{if $retour == $smarty.const.ERROR_INACTIVE_REDIRECTION}
  <p class="erreur">
  Tu ne peux pas avoir aucune adresse de redirection active, sinon ton adresse
  {$smarty.session.username}@polytechnique.org ne fonctionnerait plus.
  </p>
{/if}
{if $retour == $smarty.const.ERROR_INVALID_EMAIL}
  <p class="erreur">
  Erreur: l'email n'est pas valide.
  </p>
{/if}
{if $retour == $smarty.const.ERROR_LOOP_EMAIL}
  <p class="erreur">
  Erreur: {$smarty.session.username}@polytechnique.org doit renvoyer vers un email
  existant valide. En particulier, il ne peut pas être renvoyé vers lui-même,
  ni son équivalent en m4x.org, ni vers son équivalent polytechnique.edu.
  </p>
{/if}
{if $retour == $smarty.const.ERROR_DUPLICATE_EMAIL}
  <p class="erreur">
  L'adresse {$smarty.request.email} fait déjà partie de tes adresses de redirection,
  il est impossible de la mettre en double.
  </p>
{/if}
{if $mtic == 1}
  <p class="normal">
  Ton adresse de redirection {$smarty.request.email} fait partie d'un domaine refusant
  que les messages internes passent par l'extérieur, ces messages seront donc retransmis en pièces jointes.
  </p>
{/if}
<form action="{$smarty.server.PHP_SELF}" method="post" name="redirect">
  <div class="rubrique">
    Tes adresses de redirection
  </div>
  <p class="normal">
    Tu configures ici les adresses emails vers lesquelles tes adresses {if $grx neq ""}<strong>{$grx}</strong>, <strong>{$domaine}org</strong>, {/if}{if $alias neq ""}<strong>{$alias}@polytechnique.org</strong>, <strong>{$alias}@m4x.org</strong>,{/if}<strong>{$smarty.session.username}@polytechnique.org</strong> et <strong>{$smarty.session.username}@m4x.org</strong> sont redirigées.
  </p>
  <p class="normal">
    Le routage est en place pour les adresses dont la case "Actif" est cochée.
    Si tu modifies souvent ton routage, tu as tout intérêt à rentrer toutes les
    adresses qui sont susceptibles de recevoir ton routage, de sorte qu'en
    jouant avec les cases "Actif" tu pourras facilement mettre en place les unes
    ou bien les autres.
  </p>
  <p class="normal">
    Enfin, la réécriture consiste à substituer à ton adresse email habituelle
    (adresse wanadoo, yahoo, free, ou autre) ton adresse polytechnique.org ou
    m4x.org dans l'adresse d'expédition de tes messages, lorsque tu écris
    à un camarade sur son adresse polytechnique.org.
  </p>
  <div class="center">
    <table class="bicol" summary="Adresses de redirection">
      <tr>
        <th>Email</th>
        <th>Actif</th>
        <th>Réécriture</th>
        <th>&nbsp;</th>
      </tr>
      {section name=i loop=$emails}
      <tr class="{cycle values="pair,impair"}">
        <td><strong>{$emails[i]->email}</strong></td>
        <td><input type="checkbox" name="emails_actifs[]" value="{$emails[i]->num}" {if
        $emails[i]->active}checked{/if} /></td>
        <td>
          <select name="emails_rewrite[{$emails[i]->num}]">
            <option value="poly" {if $emails[i]->rewrite == 1 and $emails[i]->m4x == 0}selected{/if}>
              polytechnique.org
            </option>
            <option value="m4x" {if $emails[i]->m4x == 1}selected{/if}>
              m4x.org
            </option>
            <option value="no" {if $emails[i]->rewrite == 0}selected{/if}>
              aucune
            </option>
          </select>
        </td>
        <td><a href={$smarty.server.PHP_SELF}?emailop=retirer&amp;num={$emails[i]->num}>retirer</a></td>
      </tr>
      {/section}
    </table>
    <br />
    <input type="submit" value="Mettre à jour les emails actifs" name="emailop">
  </div>
  <p class="normal">
    Tu peux ajouter à cette liste une adresse email en la tapant ici et en cliquant sur Ajouter.
  </p>
  <input type="text" size="35" maxlength="60" name="email" value="" />
  &nbsp;&nbsp;
  <input type="submit" value="ajouter" name="emailop">
</form>
{/dynamic}
