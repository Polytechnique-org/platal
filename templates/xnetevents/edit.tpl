{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: {$evt.intitule|default:"Nouvel événement"}</h1>

<p class="descr">
  Un événement peut être une réunion, un séminaire, une conférence, un voyage promo&hellip;
  Pour en organiser un et bénéficier des outils de suivi d'inscription et de
  paiement offerts, il te faut remplir les quelques champs du formulaire ci-dessous.
</p>
<p class="descr">
  Tu as la possibilité, pour un événement donné, de distinguer plusieurs "moments"
  distincts. Par exemple, dans le cas d'une réunion suivie d'un dîner, il peut être
  utile de comptabiliser les présents à la réunion d'une part et de compter ceux
  qui s'inscrivent au repas d'autre part (en général certains participants à la réunion
  ne restent pas pour le dîner&hellip;), de sorte que tu sauras combien de chaises prévoir
  pour le premier "moment" (la réunion) et pour combien de personnes réserver le
  restaurant.
</p>

<hr />
<h2>Description de l'événement</h2>

{if $evt.eid}
<p class='erreur'>
  <strong>Attention&nbsp;:</strong> si tu souhaites modifier la structure d'un événement alors
  que des personnes y sont déjà inscrites, contacte préalablement
  <a href='mailto:contact@polytechnique.org'>l'équipe de Polytechnique.org</a>.
</p>
{/if}

<form method="post" action="{$platal->ns}events/edit/{$url_ref}">
  {xsrf_token_field}
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
        <small>(pour les listes de diffusion)</small>
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
          <option value='1' {if $evt.deadline_inscription}selected='selected'{/if}>Le&hellip;</option>
        </select>
        <span  id='do_deadline' {if !$evt.deadline_inscription}style='display: none'{/if}>
          {html_select_date prefix='inscr_' end_year='+5' day_value_format='%02d'
            field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.deadline_inscription}
          compris.
        </span>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Notifier lors d'une inscriptions&nbsp;:
      </td>
      <td>
        <select name="subscription_notification">
          <option value='creator' {if $evt.subscription_notification eq 'creator'}selected='selected'{/if}>le créateur de l'événement</option>
          <option value='animator' {if $evt.subscription_notification eq 'animator'}selected='selected'{/if}>les animateurs du groupe</option>
          <option value='both' {if $evt.subscription_notification eq 'both'}selected='selected'{/if}>le créateur de l'événement et les animateurs du groupe</option>
          <option value='nobody' {if $evt.subscription_notification eq 'nobody'}selected='selected'{/if}>personne</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Options&nbsp;:
      </td>
      <td>
        Montrer la liste des inscrits aux membres&nbsp;:
        <label><input type="radio" name="show_participants" value="1" {if $evt.show_participants}checked="checked"{/if} />
        oui</label>
        <label><input type="radio" name="show_participants" value="0" {if !$evt.show_participants}checked="checked"{/if}/>
        non</label>

        <br />
        Accepter les&nbsp;:
        <select name="access_control">
          <option value="group" {if $evt.access_control eq group}selected{/if}>membres du groupe</option>
          <option value="registered" {if $evt.access_control eq registered}selected{/if}>utilisateurs de polytechnique.net</option>
          <option value="all" {if $evt.access_control eq all}selected{/if}>tout le monde</option>
        </select>

        <br />
        Autoriser les invités&nbsp;:
        <label><input type="radio" name="noinvite" value="0" {if !$evt.noinvite}checked="checked"{/if} /> oui</label>
        <label><input type="radio" name="noinvite" value="1" {if $evt.noinvite}checked="checked"{/if}/> non</label>
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
          <option value='-1' {if $error}selected="selected"{/if}>- Nouveau paiement -</option>
          {html_options options=$paiements selected=$evt.paiement_id}
        </select>
      </th>
    </tr>
    {if $evt.paiement_id neq -2}
    <tr id="new_pay" style="display:none">
      <td colspan="2">
        Il faut que tu définisses le texte de l'email de confirmation de paiement. Pour ceci, tu peux adapter le modèle qui suit&nbsp;:
        <ul>
          <li><strong>Remplace les crochets</strong> ([&hellip;]) par le texte que tu désires y voir apparaître.</li>
          <li>&lt;salutation&gt;, &lt;prenom&gt;, &lt;nom&gt;, &lt;montant&gt; et &lt;comment&gt; seront <strong>automatiquement</strong> remplacés par les informations adaptées.</li>
          <li><a href="wiki_help" class="popup3">{icon name=information} Tu peux utiliser une syntaxe wiki pour formatter ton texte.</a></li>
        </ul>
        <div id="pay_preview" style="display: none">
          <strong>Aperçu du texte&nbsp;:</strong>
          <hr />
          <div id="preview"></div>
          <hr />
        </div>
        <textarea name="confirmation" id="payment_text" rows="12" cols="65">{if $payment_message}{$payment_message}{else}&lt;salutation&gt; &lt;prenom&gt; &lt;nom&gt;,

Ton inscription à [METS LE NOM DE L'ÉVÉNEMENT ICI] a bien été enregistrée et ton paiement de &lt;montant&gt; € a bien été reçu avec le commentaire suivant&nbsp;:
&lt;comment&gt;

[COMPLÈTE EN PRÉCISANT LA DATE ET LA PERSONNE À CONTACTER]

À très bientôt,

-- 
{$smarty.session.user->fullName("promo")}{/if}</textarea><br />
        {assign var='asso_url' value=$globals->baseurl|cat:'/'|cat:$platal->ns}
        Page internet de l'événement&nbsp;: <input size="40" name="site" value="{$payment_site|default:$asso->site|default:$asso_url}" /><br />
        Rendre public le télépaiement&nbsp;:
        <label><input type="radio" name="payment_public" value="no" {if !t($payment_public)}checked="checked"{/if} />Non</label>
        &nbsp;-&nbsp;
        <label>Oui<input type="radio" name="payment_public" value="yes" {if t($payment_public)}checked="checked"{/if} /></label><br />
        Attention&nbsp;: cela aura pour effet de rendre accessible ce télépaiement à tout le monde, même aux personnes non connectées.<br />
        Le nouveau paiement sera activé automatiquement après validation par le trésorier de Polytechnique.org,
        ce qui sera fait sous peu.
        <script type="text/javascript">//<![CDATA[
          document.getElementById('new_pay').style.display=
            (document.getElementById('payid').value < 0?'':'none');
        //]]></script><br />
        <input type="submit" name="preview" value="Aperçu" onclick="previewWiki('payment_text', 'preview', true, 'pay_preview'); return false;" />
      </td>
    </tr>
    {/if}
  </table>

  <hr />
  <h2>Déroulement de l'événement</h2>

  <table class="bicol">
    <colgroup>
      <col width='25%' />
    </colgroup>
    <tr>
      <td class='titre'>
        Début&nbsp;:
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
        Fin&nbsp;:
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
      <td class="titre">Intitulé&nbsp;:</td>
      <td><input type="text" name="titre{$i}" value="{$moment.titre}" size="45" maxlength="100" /></td>
    </tr>
    <tr>
      <td class="titre">Détails pratiques&nbsp;:</td>
      <td><textarea name="details{$i}" rows="6" cols="45">{$moment.details}</textarea></td>
    </tr>
    <tr>
      <td class="titre">Tarif&nbsp;:<br /><small>(par participant)</small></td>
      <td><input type="text" name="montant{$i}" value="{if $moment.montant}{$moment.montant|replace:".":","}{else}0,00{/if}" size="7" maxlength="7" /> € <small>(0 si gratuit)</small></td>
    </tr>
  {/foreach}
  </table>

  <div class="center">
    {if $evt.eid}<input type="hidden" name="uid" value="{$evt.uid}" />{/if}
    <input type="submit" name="valid" value="Valider" />
    &nbsp;
    <input type="reset" value="Annuler" />
  </div>

</form>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
