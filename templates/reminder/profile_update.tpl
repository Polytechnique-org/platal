{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

<div class="warnings reminder">
  <span style="float: right">
    <a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/dismiss')">
      {icon name=cross title="Cacher cet avertissement."}
    </a>
  </span>
  <ul>
  {if $profile_date}
    <li>
      La dernière mise à jour de ta <a href="profile/{$smarty.session.hruid}" class="popup2">fiche</a>
      date du {$fiche_incitation|date_format}. Il est possible qu'elle ne soit pas à jour.
      Si tu souhaites la modifier,
      {if $incitations_count > 1}
      <a href="profile/edit">
      {else}
      <a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/profile')">
      {/if}
      clique ici !</a>
    </li>
  {/if}
  {if $photo_incitation}
    <li>
      Tu n'as pas mis de photo de toi sur ta fiche, c'est dommage. Clique
      {if $incitations_count > 1}
      <a href="photo/change">
      {else}
      <a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/photo')">
      {/if}
      ici</a> si tu souhaites en ajouter une.
    </li>
  {/if}
  {if $geoloc_incitation > 0}
    <li>
      Parmi tes adresses, il y en a {$geoloc_incitation} que nous n'avons pas pu localiser. Clique
      {if $incitations_count > 1}
      <a href="profile/edit/adresses">
      {else}
      <a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/geoloc')">
      {/if}
      ici</a> pour rectifier.
    </li>
  {/if}
  </ul>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
