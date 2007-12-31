{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{if $do_title}<h1>Recherche dans l'annuaire</h1>{/if}

<ul class="noprint">
  {if $with_soundex && ($smarty.request.firstname || $smarty.request.name)}
  <li>Étendre ta recherche par <strong>
    <a  href="search/adv{$plset_search}with_soundex=1">proximité sonore</a>
  </strong>
  </li>
  {/if}

  <li><strong><a href="search/adv{$plset_search|regex_replace:"/rechercher=[^&]*/":""}">Modifier</a>
  </strong> ta recherche
  </li>

  <li>Effectuer une nouvelle <strong>
    <a href="search/adv">recherche avancée</a>
  </strong>
  </li>
  
  <li>Effectuer une nouvelle <strong>
    <a href="search">recherche simple</a>
  </strong>
  </li>
</ul>
  
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
