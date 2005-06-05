{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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
<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="eid" value="{$evt.eid}" />
  <hr />
  <table>
    <tr>
      <td>Intitulé de l'événement :</td>
      <td><input type="text" name="intitule" value="{$evt.intitule}" size="45" maxlength="100" /></td>
    </tr>
    <tr>
      <td>Descriptif :</td>
      <td><textarea name="descriptif" cols="45" rows="6">{$evt.descriptif}</textarea></td>
    </tr>
    <tr>
      <td>Date de début :</td>
      <td>
        le
	{html_select_date prefix='deb_' end_year='+5' day_value_format='%02d' field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.debut}
	à
	{html_select_time use_24_hours=true display_seconds=false time=$evt.debut prefix='deb_' minute_interval=5}
	</select>
      </td>
    </tr>
    <tr>
      <td>Date de fin :</td>
      <td>
        le
	{html_select_date prefix='fin_' end_year='+5' day_value_format='%02d' field_order='DMY' field_separator=' / ' month_format='%m' time=$evt.fin}
	à
	{html_select_time use_24_hours=true display_seconds=false time=$evt.fin prefix='fin_' minute_interval=5}
      </td>
    </tr>
    <tr>
      <td colspan="2">Ouvert aux membres du groupe uniquement :
        <input type="radio" name="membres_only" value="1" {if $evt.membres_only}checked="checked"{/if} /> oui
        <input type="radio" name="membres_only" value="0" {if !$evt.membres_only}checked="checked"{/if} /> non
      </td>
    </tr>
    <tr>
      <td colspan="2">Annoncer l'événement publiquement sur le site :
        <input type="radio" name="advertise" value="1" {if $evt.advertise}checked{/if} /> oui
        <input type="radio" name="advertise" value="0" {if !$evt.advertise}checked{/if} /> non
      </td>
    </tr>
    <tr>
      <td colspan="2">Montrer la liste des participants à tous les membres :
        <input type="radio" name="show_participants" value="1" {if $evt.show_participants}checked{/if} /> oui
        <input type="radio" name="show_participants" value="0" {if !$evt.show_participants}checked{/if}/> non
      </td>
    </tr>
    <tr>
      <td>Référence de paiement :
      </td>
      <td>
      <select name="paiement">
      	<option value=''>Pas de paiement déclaré</option>
     	{html_options options=$paiements selected=$evt.paiement_id}
      </select>
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
  <center>
    <input type="submit" name="valid" value="Valider" />
    &nbsp;
    <input type="reset" value="Annuler" />
  </center>

</form>
