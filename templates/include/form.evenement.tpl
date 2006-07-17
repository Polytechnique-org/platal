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

<form action="{$platal->path}" method="post">
  <table class="bicol">
    <tr>
      <th colspan="2">Contenu du message</th>
    </tr>
    <tr>
      <td><strong>Titre</strong></td>
      <td>
        <input type="text" name="titre" size="50" maxlength="200" value="{$titre}" />
      </td>
    </tr>
    <tr>
      <td><strong>Texte</strong></td>
      <td><textarea name="texte" rows="10" cols="60" onfocus="update_texte_count(this.form)">{$texte}</textarea></td>
    </tr>
    <tr>
      <td colspan="2">
        <script type="text/javascript">
          var form_propose_texte = false;
          {literal}
          function update_texte_count(f) {
            if (!form_propose_texte && f) form_propose_texte = f;
            form_propose_texte.texte_count.value=form_propose_texte.texte.value.length;
            setTimeout("update_texte_count(0)", 100);
          }
          {/literal}
        </script>
        Essaie de faire un texte court, une annonce ne doit pas excéder 800 caractères soit une douzaine de ligne. Tu en es déjà à <input type='text' name='texte_count' size="4"/> caractères.</td>
    </tr>
  </table>

  <table class="bicol">
    <tr>
      <th colspan="2">Informations complémentaires</th>
    </tr>
    <tr>
      <td>
        <strong>Promo min *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_min" size="4" maxlength="4" value="{$promo_min}" />
        &nbsp;<em>0 signifie pas de minimum</em>
      </td>
    </tr>
    <tr>
      <td>
        <strong>Promo max *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_max" size="4" maxlength="4" value="{$promo_max}" />
        &nbsp;<em>0 signifie pas de maximum</em>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        * sert à limiter l'affichage de l'annonce aux camarades appartenant à certaines promos seulement.
      </td>
    </tr>
    <tr>
      <td>
        <strong>Dernier jour d'affichage</strong>
      </td>
      <td>
        <select name="peremption">
          {$select|smarty:nodefaults}
        </select>
      </td>
    </tr>
  </table>

  <div class="center">
    <input type="hidden" name="evt_id" value="{$smarty.post.evt_id}" />
    <input type="submit" name="action" value="Proposer" />
  </div>

</form>

{* vim:set et sw=2 sts=2 sws=2: *}
