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

{if $smarty.request.texte}
{assign var=texte value=$smarty.request.texte}
{/if}
{assign var=titre value=$smarty.request.titre|default:$titre}
{assign var=promo_min value=$smarty.request.promo_min|default:$promo_min}
{assign var=promo_max value=$smarty.request.promo_max|default:$promo_max}
{assign var=peremption value=$smarty.request.peremption|default:$peremption}
{assign var=important value=$smarty.request.important|default:$important}

<script type="text/javascript">//<![CDATA[
  {literal}
  function updatePreview()
  {
    if (document.getElementById('image').value != '' || document.getElementById('image_url').value != '') {
      return true;
    }
    var titre = document.getElementById('titre').value;
    var texte = document.getElementById('texte').value;

    if (titre == '' || texte == '') {
      document.getElementById('valid').style.display = 'none';
      document.getElementById('info').style.display = '';
    } else {
      document.getElementById('valid').style.display = '';
      document.getElementById('info').style.display = 'none';
    }
    var page  = 'events/preview?titre=' + encodeURIComponent(titre) + '&texte=' + encodeURIComponent(texte);
    if (is_IE) {
      {/literal}
      page = "{$globals->baseurl}/" + page;
      {literal}
    }
    Ajax.update_html('preview', page, null);
    return false;
  }
  {/literal}
//]]></script>

<div id="preview">
{include file="events/preview.tpl"}
</div>
<br />

<form action="{$platal->path}" method="post" enctype="multipart/form-data">
  <table class="bicol">
    <tr>
      <th colspan="2">Contenu de l'annonce</th>
    </tr>
    <tr>
      <td class="titre">Titre</td>
      <td>
        <input type="text" name="titre" id="titre" size="50" maxlength="200" value="{$titre}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Texte</td>
      <td>
        <textarea name="texte" id="texte" rows="10" cols="60" onfocus="update_texte_count(this.form)">{$texte}</textarea>
      </td>
    </tr>
    <tr>
      <td></td>
      <td class="smaller">
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée pour le texte de l'annonce
        </a>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="smaller">
        <script type="text/javascript">//<![CDATA[
          var form_propose_texte = false;
          {literal}
          function update_texte_count(f) {
            if (!form_propose_texte && f) form_propose_texte = f;
            form_propose_texte.texte_count.value=form_propose_texte.texte.value.length;
            setTimeout("update_texte_count(0)", 100);
          }
          {/literal}
        //]]></script>
        Essaie de faire un <strong>texte court</strong>, une annonce ne doit pas excéder 800 caractères soit une douzaine de ligne. Tu en es déjà à <input type='text' name='texte_count' size="4" /> caractères.
        <script type="text/javascript">update_texte_count(document.getElementById('texte').form);</script>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Illustration</td>
      <td>
        {if $eid && $img}
        <div style="float: left; text-align: center">
          <em>Image actuelle</em><br />
          <img src="events/photo/{$eid}" alt="Image actuelle" /><br />
          <input type="submit" name="action" value="Pas d'image" />
        </div>
        {/if}
        {if $upload && $upload->exists()}
        <div style="float: right; text-align: center">
          <em>Nouvelle image</em><br />
          <img src="events/photo" alt="Nouvelle Image" /><br />
          <input type="submit" name="action" value="Supprimer l'image" />
        </div>
        {/if}
        <div style="clear: both">
          Choisir un fichier : <input type="file" name="image" id="image" /><br />
          Indiquer une adresse : <input type="text" name="image_url" id="image_url" value="" />
        </div>
      </td>
    </tr>
  </table>

  <div class="center">
    <input type="submit" name="preview" value="Aperçu" onclick="return updatePreview();" />
  </div>
  <p id="info" {if trim($texte) && trim($titre)}style="display: none"{/if}>
    Le bouton de confirmation n'apparaît que si l'aperçu est concluant.
  </p>
  <p class="erreur">
    N'oublie pas de remplir les informations suivantes&nbsp;:
  </p>

  <table class="bicol">
    <tr>
      <th colspan="2">Informations complémentaires</th>
    </tr>
    <tr class="pair">
      <td colspan="2">
        Tu peux limiter la visibilité de ton annonce aux camarades de certaines promotions :
      </td>
    </tr>
    {include file="include/field.promo.tpl"}
    <tr class="pair">
      <td colspan="2">
        Choisis la date d'expiration de ton annonce :
      </td>
    </tr>
    <tr>
      <td class="titre">
        Dernier jour d'affichage
      </td>
      <td>
        {valid_date name="peremption" value=$peremption to=60}
      </td>
    </tr>
    {if $admin_evts}
    <tr>
      <td class="titre">
        Importance
      </td>
      <td>
        <input type="checkbox" name="important" {if $important}checked="checked"{/if}/> Marquer cette annonce comme très importante
      </td>
    </tr>
    {/if}
  </table>

  <div class="center" {if !trim($texte) || !trim($titre)}style="display: none"{/if} id="valid">
    <input type="hidden" name="evt_id" value="{$smarty.post.evt_id}" />
    <input type="submit" name="action" value="Proposer" />
  </div>

</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
