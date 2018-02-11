{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<div class="contact-list" style="clear: both">
{foreach from=$set item=p}
  <div class="contact">
    <div class="photo">
      <img src="photo/{$p->hrid()}" alt="{$p->directory_name}" />
    </div>

    <div class="nom">
      {if $p->isFemale()}&bull;{/if}
      {$p->fullName()}
    </div>
    <div class="edu">
      {$p->promo()}
    </div>
    <div class="bits" style="width: 40%;">
      <span class='smaller'>
      <a href="profile/{$p->hrid()}" class="popup2">
        {icon name=user_suit title="Voir sa fiche"}</a> -
        <a href="referent/{$p->hrid()}" class="popup2">Voir sa fiche référent</a>
      </span>
    </div>
    <div class="long">
     <table cellspacing="0" cellpadding="0">
      <tr>
        <td class="lt">Expertise&nbsp;:</td>
        <td class="rt" colspan="2">{$p->mentor_expertise|nl2br}</td>
      </tr>
     </table>
    </div>
  </div>
{/foreach}
</div>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
