{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<script type='text/javascript'>
{literal}
function deadlineChange(box)
{
    var nd = document.getElementById('no_deadline');
    var dd = document.getElementById('do_deadline');

    if (box.checked) {
        nd.style.display = 'none';
        dd.style.display = 'inline';
    } else {
        nd.style.display = 'inline';
        dd.style.display = 'none';
    }
}
{/literal}
</script>

<h1>{$asso.nom} : {$evt.intitule|default:"Nouvel événement"}</h1>

<p class="descr">
  Un événement peut être une réunion, un séminaire, une conférence, un voyage promo,
  etc... Pour en organiser un et bénéficier des outils de suivi d'inscription et de
  paiement offerts, il te faut remplir les quelques champs du formulaire ci-dessous.
</p>
<p class="descr">
  Tu as la possibilité, pour un événement donné, de distinguer plusieurs "moments"
  distincts. Par exemple, dans le cas d'une réunion suivie d'un dîner, il peut être
  utile de comptabiliser les présents à la réunion d'une part, et de compter ceux
  qui s'inscrivent au repas d'autre part (en général certains participants à la réunion
  ne restent pas pour le dîner...), de sorte que tu sauras combien de chaises prévoir
  pour le premier "moment" (la réunion), et pour combien de personnes réserver le
  restaurant.
</p>

<hr />

<form method="post" action="{rel}/{$platal->ns}events/edit/{$platal->argv[1]}">
  <table class='bicol' cellspacing='0' cellpadding='0'>
    <colgroup>
      <col width='30%' />
    </colgroup>
    <tr>
      <td class='titre'>
        Intitulé de l'événement&nbsp;:
      </td>
      <td>
        <input type="text" name="intitule" value="{$evt.intitule}" size="45" maxlength="100" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Raccourci&nbsp;:<br />
        <small>(pour les mailings listes)</small>
      </td>
      <td>
        <input type="text" name="short_name" size="20" maxlength="20"
          value="{$evt.short_name|default:$smarty.request.short_name}" />
        <small><br />(n'utiliser que chiffres, lettres, tiret et point. garder court)</small>
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Descriptif&nbsp;:
      </td>
      <td>
        <textarea name="descriptif" cols="45" rows="10">{$evt.descriptif}</textarea>
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Date de début :
      </td>
      <td>
        le {html_select_date prefix='deb_' end_year='+5' day_value_format='%02d'
              field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.debut}
        à {html_select_time use_24_hours=true display_seconds=false 
              time=$evt.debut prefix='deb_' minute_interval=5}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Date de fin :
      </td>
      <td>
        le {html_select_date prefix='fin_' end_year='+5' day_value_format='%02d'
              field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.fin}
        à {html_select_time use_24_hours=true display_seconds=false
              time=$evt.fin prefix='fin_' minute_interval=5}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Fin des inscriptions&nbsp;:
      </td>
      <td>
        <input type='checkbox' name='deadline' value='1' onchange='deadlineChange(this)'
          {if $evt.deadline_inscription}checked='checked'{/if} />
        <span id='no_deadline' {if $evt.deadline_inscription}style='display: none'{/if}>
          Pas de deadline
        </span>
        <span  id='do_deadline' {if !$evt.deadline_inscription}style='display: none'{/if}>
          le {html_select_date prefix='inscr_' end_year='+5' day_value_format='%02d'
            field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.deadline_inscription}
          compris.
        </span>
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Options&nbsp;:
      </td>
      <td>
        Montrer la liste des inscrits aux membres :
        <input type="radio" name="show_participants" value="1" {if $evt.show_participants}checked="checked"{/if} /> oui
        <input type="radio" name="show_participants" value="0" {if !$evt.show_participants}checked="checked"{/if}/> non

        <br />
        Autoriser les invités :
        <input type="radio" name="noinvite" value="0" {if !$evt.noinvite}checked="checked"{/if} /> oui
        <input type="radio" name="noinvite" value="1" {if $evt.noinvite}checked="checked"{/if}/> non
      </td>
    </tr>
    <tr>
      <td>Référence de paiement&nbsp;:</td>
      <td>
        <select name="paiement_id" onchange="document.getElementById('new_pay').style.display=(value &lt; 0?'block':'none')">
          {if $evt.paiement_id eq -2}
          <option value='-2'>Paiement en attente de validation</option>
          {/if}
          <option value=''>Pas de paiement</option>
          <option value='-1'>- Nouveau paiement -</option>
          {html_options options=$paiements selected=$evt.paiement_id}
        </select>
      </td>
    </tr>
  </table>

  <table class='bicol' cellspacing='0' cellpadding='0' id="new_pay" style="display:none">
    <tr>
      <th>
        Nouveau paiement, message de confirmation&nbsp;:
      </th>
    </tr>
    <tr>
      <td>
        <textarea name="confirmation" rows="12" cols="65">&lt;salutation&gt; &lt;prenom&gt; &lt;nom&gt;,

    Ton inscription à [METS LE NOM DE L'EVENEMENT ICI] a bien été enregistrée et ton paiement de &lt;montant&gt; a bien été reçu. 
    [COMPLETE EN PRECISANT LA DATE ET LA PERSONNE A CONTACTER]

    A très bientot,

    [SIGNE ICI]</textarea>
      </td>
    </tr>
    <tr>
      <td>
        Page internet de l'événement&nbsp;: <input size="40" name="site" value="{$asso.site}" />
      </td>
    </tr>
    <tr>
      <td>
        Le nouveau paiement n'est pas rajouté automatiquement mais doit être
        validé par le trésorier de l'association Polytechnique.org, ce qui sera
        fait sous peu.
      </td>
    </tr>
  </table>

  {foreach from=$moments item=i}
  {assign var='moment' value=$items[$i]}
  <hr />
  <table>
    <tr><td colspan="2" align="center"><strong>"Moment" {$i}</strong></td></tr>
    <tr>
      <td>Intitulé :</td>
      <td><input type="text" name="titre{$i}" value="{$moment.titre}" size="45" maxlength="100" /></td>
    </tr>
    <tr>
      <td>Détails pratiques :</td>
      <td><textarea name="details{$i}" rows="6" cols="45">{$moment.details}</textarea></td>
    </tr>
    <tr>
      <td>Montant par participant :<br /><small>(0 si gratuit)</small></td>
      <td><input type="text" name="montant{$i}" value="{if $moment.montant}{$moment.montant|replace:".":","}{else}0,00{/if}" size="7" maxlength="7" /> &#8364;</td>
    </tr>
  </table>
  {/foreach}
 
  <div class="center">
    <input type="submit" name="valid" value="Valider" />
    &nbsp;
    <input type="reset" value="Annuler" />
  </div>

</form>
{* vim:set et sw=2 sts=2 sws=2: *}
