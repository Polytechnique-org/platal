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
 ***************************************************************************}


<h1>
  Lettre de Polytechnique.org
</h1>
<p>
Tu trouveras ici les archives de la lettre d'information de Polytechnique.org.  Pour t'abonner à
cette lettre, il te suffit de te <a href="{"listes/"|url}">rendre sur la page des listes</a>.
</p>
<p>
<strong>Pour demander l'ajout d'une annonce dans la prochaine lettre mensuelle</strong>,
utilise <a href='submit.php'>le formulaire dédié !</a>.
</p>

{dynamic}
<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$nl.date|date_format:"%d %b %Y"}</td>
    <td>
      <a href="{"newsletter/show.php"|url}?nid={$nl.id}">{$nl.titre}</a>
    </td>
  </tr>
  {/foreach}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
