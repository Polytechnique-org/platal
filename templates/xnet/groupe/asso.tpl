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

<h1>{$asso.nom} : Accueil</h1>

<table id="content" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      {if $asso.site}
      <a href="{$asso.site}"><img src='getlogo.php' alt="LOGO" /></a>
      {else}
      <img src='getlogo.php' alt="LOGO" />
      {/if}

      {if $asso.site}
      <p class="descr">
      <strong>Site Web:</strong> <a href="{$asso.site}">{$asso.site}</a>
      </p>
      {/if}

      {if $asso.resp && $asso.mail}
      <p class="descr">
      <strong>Contact:</strong> {mailto address=$asso.mail text=$asso.resp encode=javascript}
      </p>
      {elseif $asso.resp}
      <p class="descr">
      <strong>Contact:</strong> {$asso.resp}
      </p>
      {/if}

      {if $asso.forum}
      <p class="descr">
      <strong>Forum:</strong>
      <a href="https://www.polytechnique.org/banana/thread.php?group={$asso.forum}">par le web</a> ou
      <a href="news://ssl.polytechnique.org/{$asso.forum}">par nntp</a>
      </p>
      {/if}

      <strong>TODO: INSCRIPTION</strong>

      {if $asso.ax}
      <p class="descr">
      <strong>groupe agrée par l'AX</strong>
      </p>
      {/if}

      <div>
        {$asso.descr|smarty:nodefaults}
      </div>
    </td>
    <td>
      {iterate from=$gps item=g}
      <div class="cat">
        <a href="../{$g.diminutif}/asso.php">{$g.nom}</a>
      </div>
      {/iterate}
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
