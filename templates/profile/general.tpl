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
      {if $tooltip_name}<span title="{$tooltip_name}" style="border-bottom: 1px dashed black;">{$display_name}</span>{else}{$display_name}{/if}
      <a href="profile/edit#names_advanced" onclick="$('#names_advanced').show('normal', {literal}function(){document.location = document.location + '#names_advanced';}{/literal});return false">
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
    <td class="titre">Date de naissance</td>
    <td><input type="text" {if $errors.naissance}class="error"{/if} name="naissance" value="{$naissance}" /></td>
  </tr>
  <tr>
    <td>
      <span class="titre">Nationalité</span>
    </td>
    <td>
      <select name="nationalite">
        {select_nat valeur=$nationalite}
      </select>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <span class="titre">Application</span><br />
      <span class="comm">(4e année de l'X)</span>
    </td>
    <td>
      <select name="appli1[id]" onchange="fillType(this.form['appli1[type]'], this.selectedIndex-1);">
        {applis_options selected=$appli1.id}
      </select>
      <br />
      <input type="hidden" name="appli1_tmp" value="{$appli1.type}" />
      <select name="appli1[type]">
        <option value=""></option>
      </select>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <span class="titre">Post-application</span>
    </td>
    <td>
      <select name="appli2[id]" onchange="fillType(this.form['appli2[type]'], this.selectedIndex-1);">
        {applis_options selected=$appli2.id}
      </select>
      <br />
      <input type="hidden" name="appli2_tmp" value="{$appli2.type}" />
      <select name="appli2[type]">
        <option value=""></option>
      </select>
    </td>
  </tr>
  <tr class="pair">
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
  <tr class="impair" {popup caption="Affichage courant de ton nom" text="Ceci
est le nom qui s'affichera partout sur le site quand ton nom est mentionné dans
une liste, en haut de ta fiche ou lorsque tu proposes une annonce. On utilise
généralement le prénom usuel suivi du nom usuel avec des majuscules seulement
pour les premières lettres : <strong>Alfred&nbsp;de&nbsp;Musset</strong>" width="400"}>
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_green" title="site public"}
      </span>&nbsp;
      <span class="titre">Affichage courant de ton nom</span>
    </td>
    <td>
      <input type="text" name="display_name" value="{$display_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair" {popup caption="Explication du nom" text="Tu peux rajouter une
  brêve explication de ton nom si par exemple il arrive qu'on confonde ton prénom
  et ton nom, ou bien que vous êtes deux de la même promo à porter le même nom.
  <strong>Prénom&nbsp;:&nbsp;Martin&nbsp;-&nbsp;Nom&nbsp;:&nbsp;Bernard</strong>" width="400"}>
    <td>
      <span class="titre">explication</span>
    </td>
    <td>
      <input type="text" name="tooltip_name" value="{$tooltip_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair" {popup caption="Rangement du nom" text="Dans une liste d'anciens
 ton nom sera rangé selon l'ordre alphabétique grâce à ce champs. On utilise
 généralement le nom (sans particule) suivi d'une virgule et du prénom : <strong>
 Dupont,&nbsp;Georges</strong>" width="400"}>
    <td>
      <span class="titre">ranger ce nom à</span>
    </td>
    <td>
      <input type="text" name="sort_name" value="{$sort_name}" size="40"/>
    </td>
  </tr>
  <tr class="impair" {popup caption="Comment doit-on t'appeler ?" text="Lorsque
  nous t'envoyons un e-mail, nous nous adressons à toi par ton prénom. Le champs
  suivant permet de changer cela. C'est surtout utile lorsque les e-mails sont
  envoyés à une tierce personne (veuf ou veuve par exemple)" width="400"}>
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </span>&nbsp;
      <span class="titre">Comment on doit t'appeller</span>
      <div class="smaller">dans les mails que nous t'envoyons</div>
    </td>
    <td>
      <input type="text" name="yourself" value="{$yourself}" size="40"/>
    </td>
  </tr>
  <tr class="impair" {popup caption="Noms de recherche" text="Tu peux ajouter ici
  des noms pour apparaître dans les recherches. Tu peux par exemple ajouter le
  nom que tu portais à l'école si tu as changé depuis ou bien un nom de scène, un
  surnom ou encore le nom de ton conjoint. Les recherches ne fonctionneront que
  sur la partie privée du site sauf si tu coches la case verte." width="400"}>
    <td colspan="2">
      <span class="titre">Recherche</span>
      <span class="smaller">, ta fiche apparaît quand on cherche un de ces noms</span>
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
  summary="Profil: Trombinoscope">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" name="photo_pub" {if $photo_pub eq 'public'}checked="checked" {/if}/>
        {icon name="flag_green" title="site public"}
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
  summary="Profil: Divers">
  <tr>
    <th colspan="2">
      Divers
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Téléphone mobile</span>
    </td>
    <td>
      <input type="text" size="18" maxlength="18" name="mobile"
             {if $errors.mobile}class="error"{/if} value="{$mobile}" />
      <span class="flags">
        {include file="include/flags.radio.tpl" name="mobile_pub" val=$mobile_pub}
      </span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <span class="titre">Messageries, networking et sites web</span>
    </td>
  </tr>
  <tr id="networking">
    <td>
      <span class="titre" style="margin-left:1em;">Type à ajouter</span>
    </td>
    <td>
      <select name="nw_type" onchange="updateNetworking()">
        <option value=""></option>
        {foreach from=$network_list item=network}
          <option value="{$network.type}">{$network.name}</option>
        {/foreach}
      </select>
      <span id="nw_add" style="display: none">
        <a href="javascript:addNetworking();">{icon name=add title="Ajouter cette adresse"}</a>
      </span>
    </td>
  </tr>
  {foreach from=$networking item=network key=id}
    {include file="profile/general.networking.tpl" nw=$network i=$id}
  {/foreach}
  <tr class="pair">
    <td>
      <div>
        <span class="flags">
          <input type="checkbox" name="freetext_pub" {if $freetext_pub eq 'public'}checked="checked"{/if} />
          {icon name="flag_green" title="site public"}
        </span>&nbsp;
        <span class="titre">Complément libre</span><br />
        <span class="comm">Commentaire ? ICQ ? etc...</span>
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
