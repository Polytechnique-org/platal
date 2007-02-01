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

<h1>Carnet polytechnicien</h1>

<table class="bicol">
  <tr>
    <th colspan="2">
      Tes contacts
    </th>
  </tr>
  <tr class="impair">
    <td class='half'>
      <h3>
        <a href="carnet/contacts">Page de tes contacts</a>
      </h3>
      <div class="explication">
        Tu peux ici lister tes contacts, en ajouter et en retirer.
      </div>
    </td>
    <td class='half'>
      <h3>
        <a href="carnet/contacts/trombi">Le trombi de tes contacts</a>
      </h3>
      <div class="explication">
        La même chose que la page de tes contacts... <strong>en images !</strong>
      </div>
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th colspan="2">
      {if $smarty.session.core_rss_hash}
      <a href='carnet/rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml' style="display:block;float:right">
        {icon name=feed title='fil rss'}
      </a>
      {else}
      <a href='prefs/rss/?referer=carnet' style="display:block;float:right">
        {icon name=feed_add title='Activer mon fil rss'}
      </a>
      {/if}
      Notifications
    </th>
  </tr>
  <tr class="pair">
    <td class='half'>
      <h3>
        <a href="carnet/panel">Tous les évenements de la semaine</a>
      </h3>
      <div class="explication">
        Affichage de tous les évenements de camarades/promos
      </div>
    </td>
    <td class='half'>
      <h3>
        <a href="carnet/notifs">Configurer tes notifications</a>
      </h3>
      <div class="explication">
        Être notifié des inscriptions, décès, changement de fiche, ...
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
