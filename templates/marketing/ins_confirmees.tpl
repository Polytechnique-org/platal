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
        $Id: ins_confirmees.tpl,v 1.5 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}

<table class="bicol" summary="liste des nouveaux inscrits">
  <tr>
    <th>Inscription</th>
    <th>Promo</th>
    <th>Nom</th>
  </tr>
{foreach item=in from=$ins}
  <tr class="{cycle values="impair,pair"}">
    <td class="center">{$in.date_ins|date_format:"%d/%m/%Y - %H:%M"}</td>
    <td class="center">
      <a href="promo.php?promo={$in.promo}">{$in.promo}</a>
    </td>
    <td>
      <a href="javascript:x()"  onclick="popWin('{"fiche.php"|url}?user={$in.username}')">
        {$in.nom} {$in.prenom}</a>
    </td>
  </tr>
{/foreach}
</table>

<br />
<div class="right">
  [<a href="{$smarty.server.PHP_SELF}?sort=date_ins">par date</a>]
  [<a href="{$smarty.server.PHP_SELF}?sort=promo">par promo</a>]
</div>
<p>
{$nb_ins} Polytechniciens se sont inscrits depuis le début de la semaine !
</p>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
