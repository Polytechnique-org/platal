{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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
    var dd = document.getElementById('do_deadline');

    if (box.value == 1) {
        dd.style.display = 'inline';
    } else {
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
<h2>Description de l'événement</h2>

<form method="post" action="{$platal->ns}events/edit/{$url_ref}">
  <table class='bicol' cellspacing='0' cellpadding='0'>
    <colgroup>
      <col width='25%' />
    </colgroup>
    <tr>
      <th colspan="2">
        Intitulé de l'événement
      </th>
    </tr>
    <tr>
      <td class='titre'>
        Nom complet&nbsp;:
      </td>
      <td>
        <input type="text" name="intitule" value="{$evt.intitule}" size="45" maxlength="100" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Nom raccourci&nbsp;:<br />
        <small>(pour les mailings listes)</small>
      </td>
      <td>
        <input type="text" name="short_name" size="20" maxlength="20"
          value="{$evt.short_name}" />
        <small>(n'utiliser que chiffres, lettres, tiret et point. garder court)</small>
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
      <th colspan="2">
        Inscriptions
      </th>
    </tr>
    <tr>
      <td class='titre'>
        Fin des inscriptions&nbsp;:
      </td>
      <td>
        <select name="deadline" onchange='deadlineChange(this)'>
          <option value='0' {if !$evt.deadline_inscription}selected='selected'{/if}>Jamais</option>
          <option value='1' {if $evt.deadline_inscription}selected='selected'{/if}>Le...</option>
        </select>
        <span  id='do_deadline' {if !$evt.deadline_inscription}style='display: none'{/if}>
          {html_select_date prefix='inscr_' end_year='+5' day_value_format='%02d'
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
        Autoriser les non-membres :
        <input type="radio" name="accept_nonmembre" value="1" {if $evt.accept_nonmembre}checked="checked"{/if} /> oui
        <input type="radio" name="accept_nonmembre" value="0" {if !$evt.accept_nonmembre}checked="checked"{/if}/> non

        <br />
        Autoriser les invités :
        <input type="radio" name="noinvite" value="0" {if !$evt.noinvite}checked="checked"{/if} /> oui
        <input type="radio" name="noinvite" value="1" {if $evt.noinvite}checked="checked"{/if}/> non
      </td>
    </tr>
    <tr>
      <th colspan="2">
        Paiement&nbsp;:&nbsp;
        <select name="paiement_id" id="payid"  onchange="document.getElementById('new_pay').style.display=(value &lt; 0?'':'none')">
          {if $evt.paiement_id eq -2}
          <option value='-2'>Paiement en attente de validation</option>
          {/if}
          <option value=''>Pas de paiement</option>
          <option value='-1' {if $paiement_message}selected="selected"{/if}>- Nouveau paiement -</option>
          {html_options options=$paiements selected=$evt.paiement_id}
        </select>
      </th>
    </tr>
    <tr id="new_pay" style="display:none">
      <td colspan="2">
        Il faut que tu définisses le texte du mail de confirmation de paiement. Pour ceci, tu peux adapter le modèle qui suit :
        <ul>
          <li><strong>Remplace les crochets</strong> ([...]) par le texte que tu désires y voir apparaître</li>
          <li>&lt;salutation&gt;, &lt;prenom&gt;, &lt;nom&gt; et &lt;montant&gt; seront <strong>automatiquement</strong> remplacés par les informations adaptées</li>
          <li><a href="wiki_help" class="popup3">{icon name=information} tu peux utiliser une syntaxe wiki pour formatter ton texte</a></li>
        </ul>
        {javascript name=ajax}
        <div id="pay_preview" style="display: none">
          <strong>Aperçu du texte&nbsp;:</strong>
          <hr />
          <div id="preview"></div>
          <hr />
        </div>
        <textarea name="confirmation" id="payment_text" rows="12" cols="65">{if $paiement_message}{$paiement_message}{else}&lt;salutation&gt; &lt;prenom&gt; &lt;nom&gt;,

Ton inscription à [METS LE NOM DE L'EVENEMENT ICI] a bien été enregistrée et ton paiement de &lt;montant&gt; a bien été reçu.

[COMPLETE EN PRECISANT LA DATE ET LA PERSONNE A CONTACTER]

A très bientôt,

-- 
{$smarty.session.prenom} {$smarty.session.nom}{/if}</textarea><br />
        Page internet de l'événement&nbsp;: <input size="40" name="site" value="{$paiement_site|default:$asso.site|default:$platal->ns}" /><br />
        Le nouveau paiement sera activé automatiquement après validation par le trésorier de Polytechnique.org,
        ce qui sera fait sous peu.
        <script type="text/javascript">//<![CDATA[
          document.getElementById('new_pay').style.display=
            (document.getElementById('payid').value < 0?'':'none');
        //]]></script><br />
        <input type="submit" name="preview" value="Aperçu" onclick="previewWiki('payment_text', 'preview', true, 'pay_preview'); return false;" />
      </td>
    </tr>
  </table>

  <hr />
  <h2>Déroulement de l'événement</h2>

  <table class="bicol">
    <colgroup>
      <col width='25%' />
    </colgroup>
    <tr>
      <td class='titre'>
        Début :
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
        Fin :
      </td>
      <td>
        le {html_select_date prefix='fin_' end_year='+5' day_value_format='%02d'
              field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.fin}
        à {html_select_time use_24_hours=true display_seconds=false
              time=$evt.fin prefix='fin_' minute_interval=5}
      </td>
    </tr>

  {foreach from=$moments item=i}
  {assign var='moment' value=$items[$i]}
    <tr>
      <th colspan="2">Moment {$i}</th>
    </tr>
    <tr>
      <td class="titre">Intitulé :</td>
      <td><input type="text" name="titre{$i}" value="{$moment.titre}" size="45" maxlength="100" /></td>
    </tr>
    <tr>
      <td class="titre">Détails pratiques :</td>
      <td><textarea name="details{$i}" rows="6" cols="45">{$moment.details}</textarea></td>
    </tr>
    <tr>
      <td class="titre">Tarif :<br /><small>(par participant)</small></td>
      <td><input type="text" name="montant{$i}" value="{if $moment.montant}{$moment.montant|replace:".":","}{else}0,00{/if}" size="7" maxlength="7" /> &#8364; <small>(0 si gratuit)</small></td>
    </tr>
  {/foreach}
  </table>
 
  <div class="center">
    {if $evt.eid}<input type="hidden" name="organisateur_uid" value="{$evt.organisateur_uid}" />{/if}
    <input type="submit" name="valid" value="Valider" />
    &nbsp;
    <input type="reset" value="Annuler" />
  </div>

</form>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
