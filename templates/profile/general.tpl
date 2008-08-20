{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<table class="bicol" style="margin-bottom: 1em"
  summary="Profil&nbsp;: Informations générales">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" disabled="disabled" checked="checked" />
        {icon name="flag_green" title="site public"}
      </div>
      Informations générales
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Nom</span><br/>
    </td>
    <td>
      {$nom}
      <input type='hidden' name='nom' {if $errors.nom}class="error"{/if} value="{$nom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Prénom</span><br/>
    </td>
    <td>
      {$prenom}
      <input type='hidden' name='prenom' {if $errors.prenom}class="error"{/if} value="{$prenom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Affichage de ton nom</span>
    </td>
    <td>
      {if $tooltip_name}<span title="{$tooltip_name}" class="hint">{$display_name}</span>{else}{$display_name}{/if}
      <a href="profile/edit#names_advanced" onclick="$('#names_advanced').show();$(this).hide();document.location = document.location + '#names_advanced';return false">
        {icon name="page_edit" title="Plus de détail"}
      </a>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Promotion</span>
    </td>
    <td>
      <span class="nom">X{$promo}{if ($promo != $promo_sortie - 3)} - X{math equation="a - b" a=$promo_sortie b=3}{/if}</span>
      <span class="lien"><a href="profile/orange" {if ($promo_sortie -3 == $promo)} {popup text="pour les oranges"}{/if}>{icon name="page_edit" title="modifier"}</a></span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Date de naissance</span>
    </td>
    <td><input type="text" {if $errors.naissance}class="error"{/if} name="naissance" value="{$naissance}" /></td>
  </tr>
  <tr>
    <td>
      <span class="titre">Nationalité</span>
    </td>
    <td>
      <select name="nationalite">
        {select_nat valeur=$nationalite pad=1}
      </select>
      <a href="javascript:addNationality();">{icon name=add title="Ajouter une nationalité"}</a>
    </td>
  </tr>
  <tr id="nationalite2" {if !$nationalite2}style="display: none"{/if}>
    <td></td>
    <td>
      <select name="nationalite2">
        {select_nat valeur=$nationalite2 pad=1}
      </select>
      <a href="javascript:delNationality('2');">{icon name=cross title="Supprimer cette nationalité"}</a>
    </td>
  </tr>
  <tr id="nationalite3" {if !$nationalite3}style="display: none"{/if}>
    <td></td>
    <td>
      <select name="nationalite3">
        {select_nat valeur=$nationalite3 pad=1}
      </select>
      <a href="javascript:delNationality('3');">{icon name=cross title="Supprimer cette nationalité"}</a>
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em" summary="Profil&nbsp;: Formations">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" disabled="disabled" checked="checked" />
        {icon name="flag_green" title="site public"}
      </div>
      Formations
    </th>
  </tr>
  {foreach from=$edus key=eduid item=edu}
    {cycle values="impair, pair" assign=class}
    {include file="profile/edu.tpl" eduid=$eduid edu=$edu edu_fields=$edu_fields class=$class}
  {/foreach}
  {if $edus|@count eq 0}
    {cycle values="impair, pair" assign=class}
    {include file="profile/edu.tpl" eduid=0 edu=0 class=$class}
  {/if}
  {cycle values="impair, pair" assign=class}
  <tr id="edu_add" class="{$class}">
    <td colspan="2">
      <div class="center" style="clear: both; padding-top: 4px;">
        <a href="javascript:addEdu();">
          {icon name=add title="Ajouter une formation"} Ajouter une formation
        </a>
      </div>
    </td>
  </tr>
  <tr class="{$class}">
    <td class="center" colspan="2">
      <small>Si ta formation ne figure pas dans la liste,
      <a href="mailto:support@{#globals.mail.domain#}">contacte-nous</a>.</small>
    </td>
  </tr>
 </table>

<table class="bicol" style="margin-bottom: 1em;display:none"
  summary="Profil : Noms" id="names_advanced">
  <tr>
    <th colspan="2">
      Noms
    </th>
  </tr>
  <tr class="impair">
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_green" title="site public"}
      </span>&nbsp;
      <span class="titre">Affichage courant de ton nom</span>
      <a class="popup3" href="Xorg/Profil#name_displayed">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="display_name" value="{$display_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <span class="titre">explication</span>
      <a class="popup3" href="Xorg/Profil#name_tooltip">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="tooltip_name" value="{$tooltip_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <span class="titre">ranger ce nom à</span>
      <a class="popup3" href="Xorg/Profil#name_order">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="sort_name" value="{$sort_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </span>&nbsp;
      <span class="titre">Comment on doit t'appeller</span>
      <a class="popup3" href="Xorg/Profil#name_yourself">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="yourself" value="{$yourself}" size="40"/>
    </td>
  </tr>
  <tr class="impair">
    <td colspan="2">
      <span class="titre">Recherche</span><span class="smaller">, ta fiche apparaît quand on cherche un de ces noms</span>
      <a class="popup3" href="Xorg/Profil#name_search">{icon name="information" title="aide"}</a>
      {iterate from=$search_names item="sn"}
      <div id="search_name_{$sn.sn_id}" style="padding:2px" class="center">
        {include file="profile/general.searchname.tpl" i=$sn.sn_id sn=$sn}
      </div>
      {/iterate}
      <div id="add_search_name" class="center" style="clear: both">
        <a href="javascript:addSearchName()">
          {icon name=add title="Ajouter un nom de recherche"} Ajouter un nom
        </a>
      </div>
    </td>
</table>

{if !$no_private_key}
<table class="bicol"  style="margin-bottom: 1em"
  summary="Profil&nbsp;: Informations générales">
  <tr>
    <th>
      Synchronisation avec l'AX
    </th>
  </tr>
  <tr>
    <td class="flags">
      <input type="checkbox" name="synchro_ax" {if $synchro_ax}checked="checked" {/if}/>
      {icon name="flag_orange" title="transmis à l'AX"}
      <span class="texte">
        Autoriser la synchronisation vers l'AX par des administrateurs ou des scripts automatiques.
      </span>
    </td>
  </tr>
  <tr>
    <td>
      <p>
        Le service annuaire de l'<a href='http://www.polytechniciens.com'>AX</a> met à jour l'annuaire papier à partir des informations que tu lui fournis. Tu peux choisir ici d'envoyer directement les données de ta fiche Polytechnique.org vers ta <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$matricule_ax}">fiche AX</a>.
      </p>
      <p>
        L'opération de synchronisation prend en compte toutes les informations que tu as marquées comme transmises à l'AX (en orange ou en vert). Elle peut alors effacer, modifier ou rajouter des informations sur ta <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$matricule_ax}">fiche AX</a> selon ce qui s'y trouve déjà.
      </p>
      <p class="center">
        <a href="profile/edit/general?synchro_ax=confirm" onclick="return confirm('Es-tu sûr de vouloir lancer la synchronisation ?')"><input type="button" value="Synchroniser"/></a>
      </p>
    </td>
  </tr>
</table>
{/if}

<table class="bicol"  style="margin-bottom: 1em"
  summary="Profil&nbsp;: Trombinoscope">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <label><input type="checkbox" name="photo_pub" {if $photo_pub eq 'public'}checked="checked" {/if}/>
        {icon name="flag_green" title="site public"}</label>
      </div>
      Trombinoscope
    </th>
  </tr>
  <tr>
    <td {if !$nouvellephoto}colspan="2"{/if} class="center" style="width: 49%">
      <div class="titre">Ta photo actuelle</div>
      <img src="photo/{$smarty.session.forlife}" alt=" [ PHOTO ] " style="max-height: 250px; margin-top: 1em" />
    </td>
    {if $nouvellephoto}
    <td class="center" style="width: 49%">
      <div class="titre">Photo en attente de validation</div>
      <div>
        <a href="profile/{$smarty.session.forlife}?modif=new" class="popup2">
          Ta fiche avec cette photo
        </a>
      </div>
      <img src="photo/{$smarty.session.forlife}/req" alt=" [ PHOTO ] " style="max-height: 250px; margin-top: 1em" />
    </td>
    {/if}
  </tr>
  <tr class="pair">
    <td colspan="2">
      Pour profiter de cette fonction intéressante, tu dois disposer
      quelque part (sur ton ordinateur ou sur Internet) d'une photo
      d'identité (dans un fichier au format JPEG, PNG ou GIF).<br />
      <div class="center">
        <a href="photo/change">Éditer ta photo</a>
      </div>
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em"
  summary="Profil&nbsp;: Divers">
  <tr>
    <th colspan="2">
      Divers
    </th>
  </tr>
  <tr>
    <td colspan="2">
      <span class="titre">Téléphones personnels</span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {foreach from=$tels key=telid item=tel}
        <div id="tels_{$telid}" style="clear: both; padding-top: 4px; padding-bottom: 4px">
          {include file="profile/phone.tpl" prefname='tels' prefid='tels' telid=$telid tel=$tel}
        </div>
      {/foreach}
      {if $tels|@count eq 0}
        <div id="tels_0" style="clear: both; padding-top: 4px; padding-bottom: 4px">
          {include file="profile/phone.tpl" prefname='tels' preid='tels' telid=0 tel=0}
        </div>
      {/if}
      <div id="tels_add" class="center" style="clear: both; padding-top: 4px;">
        <a href="javascript:addTel('tels', 'tels');">
          {icon name=add title="Ajouter un téléphone"} Ajouter un téléphone
        </a>
      </div>
    </td>
  </tr>
  {if $email_error}
    {include file="include/emails.combobox.tpl" name="email_directory" val=$email_directory_error error=$email_error}
  {else}{include file="include/emails.combobox.tpl" name="email_directory" val=$email_directory error=$email_error}{/if}
  <tr>
    <td colspan="2">
      <span class="titre">Messageries, networking et sites web</span>
    </td>
  </tr>
  {foreach from=$networking item=network key=id}
    {include file="profile/general.networking.tpl" nw=$network i=$id}
  {/foreach}
  <tr id="networking">
    <script type="text/javascript">//<![CDATA[
      var nw_list = new Array();
      {foreach from=$network_list item=network}
        nw_list['{$network.name}'] = {$network.type};
      {/foreach}
    //]]></script>
    <td colspan="2">
      <div id="nw_add" class="center">
        <a href="javascript:addNetworking();">
          {icon name=add title="Ajouter une adresse"} Ajouter une adresse
        </a>
      </div>
    </td>
  </tr>
<!--  <tr id="networking">
    <td colspan="2">
      <div style="float: left; width: 200px;">
        <span class="titre" style="margin-left:1em;">Type à ajouter</span>
      </div>
      <div style="float: left;">
        <div id="nw_type_ac" style="background-color: white; border: solid 1px black; position: absolute; width: 208px; display: none">TEST</div>
        <input type="text" size="30" id="nw_type" name="nw_type" onkeyup="updateNetworking()">
        <span id="nw_add" style="display: none">
          <a href="javascript:addNetworking();">{icon name=add title="Ajouter cette adresse"}</a>
        </span>
      </div>
    </td>
  </tr>-->
  <tr class="pair">
    <td>
      <div>
        <span class="flags">
          <label><input type="checkbox" name="freetext_pub" {if $freetext_pub eq 'public'}checked="checked"{/if} />
          {icon name="flag_green" title="site public"}</label>
        </span>&nbsp;
        <span class="titre">Commentaire</span>
      </div>
      <div class="smaller" style="margin-top: 30px">
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée
        </a>
        <div class="center">
          <input type="submit" name="preview" value="Aperçu"
                  onclick="previewWiki('freetext', 'ft_preview', true, 'ft_preview'); return false;" />
        </div>
      </div>
    </td>
    <td>
      <div id="ft_preview" style="display: none"></div>
      <textarea name="freetext" {if $errors.freetext}class="error"{/if}
                id="freetext" rows="8" cols="50" >{$freetext}</textarea>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
