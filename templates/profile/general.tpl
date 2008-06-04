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
      <span class="titre">Nom</span>
      <span class="comm"></span>
    </td>
    <td>
      <input type='text' name='nom' {if $errors.nom}class="error"{/if} value="{$nom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Prénom</span>
      <span class="comm"></span>
    </td>
    <td>
      <input type='text' name='prenom' {if $errors.prenom}class="error"{/if} value="{$prenom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Promotion</span>
    </td>
    <td>
      <span class="nom">X{$promo}{if ($promo != $promo_sortie - 3)} - X{math equation="a - b" a=$promo_sortie b=3}{/if}</span>
      <span class="lien"><a href="profile/orange">modifier</a>{if ($promo_sortie -3 == $promo)} pour les oranges{/if}</span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Nom d'usage</span><br />
      {if $smarty.session.sexe}
      <span class="comm">(Notamment nom d'épouse)</span>
      {else}
      <span class="comm">(si différent de {$nom} seulement)</span>
      {/if}
    </td>
    <td>
      <span class="nom">{$nom_usage|default:"Aucun"}</span>
      <span class="lien"><a href="profile/usage">modifier</a></span>
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
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </span>&nbsp;
      <span class="titre">Surnom</span>
    </td>
    <td>
      <input type="text" size="35" maxlength="64"
             {if $errors.nick}class="error"{/if} name="nick" value="{$nick}" />
    </td>
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
    <td>
      <span class="flags">
        <input type="checkbox" name="web_pub" {if $web_pub eq 'public'}checked="checked"{/if} />
        {icon name="flag_green" title="site public"}
      </span>&nbsp;
      <span class="titre">Page web Perso</span>
    </td>
    <td>
      <input type="text" size="35" maxlength="95" name="web"
             {if $errors.web}class="error"{/if} value="{$web}" />
    </td>
  </tr>
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
