{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: moderate.tpl,v 1.1 2004-09-20 20:04:38 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de la modérer</p>

{else}

<div class='rubrique'>
  Inscriptions en attente de modération
</div>

<div class='rubrique'>
  Mails en attente de modération
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>émetteur</th>
    <th>sujet</th>
    <th>taille</th>
    <th>date</th>
    <th></th>
  </tr>
  {foreach from=$mails item=m}
  <tr class='{cycle values="pair,impair"}'>
    <td>{$m.sender}</td>
    <td>{$m.subj}</td>
    <td class='right'>{$m.size}o</td>
    <td class='right'>{$m.stamp|date_format:"%H:%M:%S<br />%d %b %Y"}</td>
    <td class='action'>
      <a href='{$smarty.server.PHP_SELF}?liste={$smarty.request.liste}&amp;mid={$m.id}'>voir</a>
    </td>
  </tr>
  {/foreach}
</table>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
