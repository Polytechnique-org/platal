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


{include file="profile/applis.js.tpl"}
<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" 
    summary="Profil : Informations générales">
    <tr>
      <th colspan="2">
        Informations générales
      </th>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" cellspacing="0" summary="Flags">
          <tr>
            <td class="vert">
              <input type="checkbox" disabled="disabled" checked="checked" />
            </td>
            <td class="texte">
              site public
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Nom</span>
        <span class="comm"></span>
      </td>
      <td class="cold">
        <input type='text' name='nom' value="{$nom}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Prénom</span>
        <span class="comm"></span>
      </td>
      <td class="cold">
        <input type='text' name='prenom' value="{$prenom}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Promotion</span>
      </td>
      <td class="cold">
        <span class="nom">X{$promo}{if ($promo != $promo_sortie - 3)} - X{math equation="a - b" a=$promo_sortie b=3}{/if}</span>
        <span class="lien"><a href="profile/orange">modifier</a>{if ($promo_sortie -3 == $promo)} pour les oranges{/if}</span>
      </td>
    </tr>     
    <tr>
      <td class="colg">
        <span class="titre">Nom d'usage</span><br />
        {if $smarty.session.sexe}
        <span class="comm">(Notamment nom d'épouse)</span>
        {else}
        <span class="comm">(si différent de {$nom} seulement)</span>
        {/if}
      </td>
      <td class="cold">
        <span class="nom">{$nom_usage|default:"Aucun"}</span>
        <span class="lien"><a href="profile/usage">modifier</a></span>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Nationalité</span>
      </td>
      <td class="cold">
        <select name="nationalite">
          {select_nat valeur=$nationalite}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Application</span><br />
        <span class="comm">(4ème année de l'X)</span>
      </td>
      <td class="cold">
        <select name="appli_id1" onchange="fillType(this.form.appli_type1, this.selectedIndex-1);">  
          {applis_options selected=$appli_id1}
        </select>
        <br />
        <select name="appli_type1">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--
          fillType(document.forms.prof_annu.appli_type1, document.forms.prof_annu.appli_id1.selectedIndex-1);
          selectType(document.forms.prof_annu.appli_type1, '{$appli_type1}');
          //-->
        </script>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Post-application</span>
      </td>
      <td class="dcold">
        <select name="appli_id2" onchange="fillType(this.form.appli_type2, this.selectedIndex-1);">   
          {applis_options selected=$appli_id2}
        </select>
        <br />
        <select name="appli_type2">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--
          fillType(document.forms.prof_annu.appli_type2, document.forms.prof_annu.appli_id2.selectedIndex-1);
          selectType(document.forms.prof_annu.appli_type2, '{$appli_type2}');
          //-->
        </script>
      </td>
    </tr>
  </table>
</div>

{if !$no_private_key}
<div class="blocunite">
  <table class="bicol" cellspacing="0" cellpadding="0" 
    summary="Profil : Informations générales">
    <tr>
      <th>
        Synchronisation avec l'AX
      </th>
    </tr>
    <tr>
      <td>
        <p>
          Le service annuaire de l'<a href='http://www.polytechniciens.com'>AX</a> met à jour l'annuaire papier à partir des informations que tu lui envoies. Tu peux choisir ici de récupérer directement ces données pour l'annuaire en ligne.
        </p>
        <p>
          La synchro prend en compte toutes les informations que tu as signalés à l'AX (en orange ou en vert). Elle peut alors effacer, modifier ou rajouter des informations selon ce qu'elle trouve sur ta <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$matricule_ax}">fiche AX</a>.
        </p>
        <p class="center">
          <a href="profile/edit/general?synchro_ax=confirm" onclick="return confirm('Es-tu sûr de vouloir lancer la synchronisation ?')"><input type="button" value="Synchroniser"/></a>
        </p>
      </td>
    </tr>
    <tr>
      <td class="col">
        <table class="flags" cellspacing="0" summary="Flags">
          <tr>
            <td class="orange">
              <input type="checkbox" name="synchro_ax" {if $synchro_ax}checked="checked" {/if}/>
            </td>
            <td class="texte">
              Autoriser la synchronisation depuis l'AX par des administrateurs ou des scripts automatiques.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
{/if}
    
<div class="blocunite">
  <table class="bicol" cellspacing="0" cellpadding="0"
    summary="Profil: Trombinoscope">
    <tr>
      <th colspan="3">
        Trombinoscope
      </th>
    </tr>
    <tr>
      <td class="col" colspan="3">
        Pour profiter de cette fonction intéressante, tu dois disposer 
        quelque part (sur ton ordinateur ou sur Internet) d'une photo
        d'identité (dans un fichier au format JPEG, PNG ou GIF).<br />
        <div class="center">
          <span class="lien">
            <a href="photo/change">Éditer ta photo</a>
          </span>
        </div>
      </td>
    </tr>
    <tr>
      <td class="col" colspan="3">
        <table class="flags" cellspacing="0" summary="Flags">
          <tr>
            <td class="vert">
              <input type="checkbox" name="photo_pub" {if $photo_pub eq 'public'}checked="checked" {/if}/>
            </td>
            <td class="texte">
              site public
            </td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0" summary="Trombinoscope">
          <tr>
            <td class="dcold">
              Voilà la photo qui apparaîtra sur la fiche de ton profil{if $nouvellephoto} (tu viens
              de changer ta photo, celle-ci correspond à ton ancien
              profil car la nouvelle n'a pas encore été validée par un administrateur du site !
              <a href="profile/{$smarty.session.forlife}?modif=new" class="popup2">Ta fiche avec la nouvelle photo</a>)
              {/if}.
            </td>
            <td class="dcolg">
              <img src="photo/{$smarty.session.forlife}" alt=" [ PHOTO ] " />
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
<div class="blocunite">
  <table class="bicol" cellspacing="0" cellpadding="0"
    summary="Profil: Divers">
    <tr>
      <th colspan="2">
        Divers
      </th>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="rouge">
              <input type="checkbox" disabled="disabled" checked="checked" />
            </td>
            <td class="texte">
              privé
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Surnom</span>
      </td>
      <td class="cold">
        <input type="text" size="35" maxlength="64" name="nickname" value="{$nickname}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="radio" name="mobile_pub" {if $mobile_pub eq 'public'}checked="checked"{/if} value='public' />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="radio" name="mobile_pub" {if $mobile_pub eq 'ax'}checked="checked"{/if} value='ax' />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="rouge">
              <input type="radio" name="mobile_pub" {if $mobile_pub eq 'private'}checked="checked"{/if} value='private' />
            </td>
            <td class="texte">
              privé
            </td>
            <td class="texte">
              <a href="Xorg/FAQ?display=light#flags" class="popup_800x240">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Téléphone mobile</span>
      </td>
      <td class="cold">
        <input type="text" size="18" maxlength="18" name="mobile"
        value="{$mobile}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="flags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="web_pub" {if $web_pub eq 'public'}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="texte">
              <a href="Xorg/FAQ?display=light#flags" class="popup_800x240">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Page web Perso</span>
      </td>
      <td class="dcold">
        <input type="text" size="35" maxlength="95" name="web"  
        value="{$web}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="freetext_pub" {if $freetext_pub eq 'public'}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="texte">
              <a href="Xorg/FAQ?display=light#flags" class="popup_800x240">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Complément libre</span>
        <span class="comm">Commentaire ? ICQ ? etc...</span>
      </td>
      <td class="dcold">
        <script type="text/javascript" src="javascript/ajax.js"></script>
        <div id="ft_preview" style="display: none"></div>
        <textarea name="freetext" id="freetext" rows="3" cols="29" >{$freetext}</textarea>
        <br/>
        <span class="smaller">
          <a href="wiki_help/notitle" class="popup3">
            {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée pour le commentaire
          </a>
        </span><br />
        <input type="submit" name="preview" value="Aperçu" onclick="previewWiki('freetext', 'ft_preview', true, 'ft_preview'); return false;" />
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
