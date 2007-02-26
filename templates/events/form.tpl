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

<script type="text/javascript">//<![CDATA[
  {literal}
  function updatePreview()
  {
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

<form action="{$platal->path}" method="post">
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
      <td><textarea name="texte" id="texte" rows="10" cols="60" onfocus="update_texte_count(this.form)">{$texte}</textarea></td>
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
      </td>
    </tr>
  </table>

  <div class="center">
    <input type="submit" name="preview" value="Aperçu" onclick="updatePreview(); return false;" />
  </div>
  <p id="info" {if trim($texte) && trim($titre)}style="display: none"{/if}>
    Le bouton de confirmation n'apparaît que si l'aperçu est concluant.
  </p>
  <p class="erreur">
    N'oublie pas de remplir les informations suivantes&nbsp;:
  </p>

  <script type="text/javascript">//<![CDATA[
    {literal}
    function updateRange(min, max)
    {
      var range = document.getElementById('range');
      if (min == null) {
        min = document.getElementById('promo_min').value;
      }
      if (max == null) {
        max = document.getElementById('promo_max').value;
      }
      if (isNaN(min) || (min != 0 && (min < 1900 || min > 2020))) {
        range.innerHTML = '<span class="erreur">La promotion minimum n\'est pas valide</span>';
        return false;
      } else if (isNaN(max) || (max != 0 && (max < 1900  || max > 2020))) {
        range.innerHTML = '<span class="erreur">La promotion maximum n\'est pas valide</span>';
        return false;
      } else if (max != 0 && min != 0 && max < min) {
        range.innerHTML = '<span class="erreur">L\'intervalle de promotion est inversé</span>';
        return false;
      } else if (max == 0 && min == 0) {
        range.innerHTML = 'L\'annonce est destinée à toutes les promotions';
      } else if (max == 0) {
        range.innerHTML = 'L\'annonce sera affichée aux promotions plus jeunes que ' + min + ' (incluse)';
      } else if (min == 0) {
        range.innerHTML = "L\'annonce sera affichée aux promotions plus anciennes que " + max + ' (incluse)';
      } else {
        range.innerHTML = "L\'annonce sera affichées aux promotions de " + min + " à " + max + ' (incluses)';
      }
      return true;
    }
    {/literal}
  //]]></script>

  <table class="bicol">
    <tr>
      <th colspan="2">Informations complémentaires</th>
    </tr>
    <tr class="pair">
      <td colspan="2">
        Tu peux limiter la visibilité de ton annonce aux camarades de certaines promotions :
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Promotion la plus ancienne</td>
      <td>
        <input type="text" name="promo_min" id="promo_min" size="4" maxlength="4" value="{$promo_min}"
               onkeyup="return updateRange(null, null);" /> incluse
        &nbsp;<em>(ex : 1980, 0 signifie pas de minimum)</em>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Promotion la plus jeune</td>
      <td>
        <input type="text" name="promo_max" id="promo_max" size="4" maxlength="4" value="{$promo_max}"
               onkeyup="return updateRange(null, null);" /> incluse
        &nbsp;<em>(ex : 2000, 0 signifie pas de maximum)</em>
      </td>
    </tr>
    <tr class="impair">
      <td colspan="2" id="range" class="smaller">
        <script type="text/javascript">updateRange({$promo_min}, {$promo_max});</script>
      </td>
    </tr>
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
        <select name="peremption">
          {$select|smarty:nodefaults}
        </select>
      </td>
    </tr>
  </table>

  <div class="center" {if !trim($texte) || !trim($titre)}style="display: none"{/if} id="valid">
    <input type="hidden" name="evt_id" value="{$smarty.post.evt_id}" />
    <input type="submit" name="action" value="Proposer" />
  </div>

</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
