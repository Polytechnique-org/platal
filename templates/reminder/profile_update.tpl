{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{if $profile_incitation}
  La dernière mise à jour de ta <a href="profile/{$smarty.session.hruid}" class="popup2">fiche</a>
  date du {$profile_last_update|date_format}. Il est possible qu'elle ne soit pas à jour.
  Si tu souhaites la modifier,
  <a href="{$reminder->baseurl()}/profile" style="text-decoration: none">
  clique ici&nbsp;!</a>
{elseif $photo_incitation}
  Tu n'as pas mis de photo de toi sur ta fiche, c'est dommage.
  <a href="{$reminder->baseurl()}/photo" style="text-decoration: none">
  Clique ici</a> si tu souhaites en ajouter une.
{elseif $geocoding_incitation > 0}
  Parmi tes adresses, il y en a {$geocoding_incitation} que nous n'avons pas pu localiser.
  <a href="{$reminder->baseurl()}/geoloc" style="text-decoration: none">
  Clique ici</a> pour rectifier.
{/if}

<div class="right">
  <a href="reminder/later" onclick="Ajax.update_html('reminder', '{$reminder->baseurl()}/dismiss'); return false" style="text-decoration: none">
    {icon name=cross} Mettre à jour plus tard
  </a>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
