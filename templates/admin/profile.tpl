{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<h1>Modifications récentes de profil</h1>

{if $updates->total() > 0}
<script type="text/javascript">//<![CDATA[
{literal}
  function toggleSelection()
  {
    var checked = $(':checkbox.updates:checked');
    var unchecked = $(':checkbox.updates:not(:checked)');
    checked.removeAttr('checked');
    unchecked.attr('checked', 'checked');
  }
{/literal}
//]]></script>

<form action="admin/profile" method="post">
  {xsrf_token_field}
  <table class="bicol" summary="liste des modifications de profil récentes">
    <tr>
      <th>Nom</th>
      <th>Éléments modifiés</th>
      <th>Liens</th>
      <th><a href="javascript:toggleSelection()">{icon name="arrow_refresh" title="Inverser la sélection"}</a></th>
    </tr>
    {iterate item=update from=$updates}
    <tr class="{cycle values="impair,pair"}">
      <td>{$update.directory_name}</td>
      <td class="center">{$update.field|wordwrap:80:'<br />'}</td>
      <td class="center">
        <a href="profile/{$update.hrpid}" class="popup2">{icon name=user_suit title="Voir le profil"}</a>
        <a href="profile/edit/{$update.hrpid}">{icon name=user_edit title="Éditer le profil"}</a>
      </td>
      <td class="center"><input type="checkbox" name="checked_{$update.pid}" class="updates" /></td>
    </tr>
    {/iterate}
  </table>
  <p class="center"><input type="submit" name="checked" value="Valider" /></p>
</form>
{else}
<p>Il n'y a rien à vérifier.</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
