{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<div id="content">
  <h1>Manuel d'utilisation de Polytechnique.net</h1>

  {if !$smarty.get.type}
  <p>
  Nous te proposons divers manuels :
  </p>
  <ul>
    <li>
      <a href="manuel?type=public">fonctionnalités publiques du site</a>
      <a href="docs/manuel.pdf">(version PDF)</a>
    </li>
    <li>
      <a href="manuel?type=auth">fonctionnalités accessibles par les membres des groupes X</a>
      <a href="docs/manuel.pdf">(version PDF)</a>
    </li>
    <li>
      <a href="manuel?type=admin">fonctionnalités à disposition des animateurs des groupes X</a>
      <a href="docs/manuel-admin.pdf">(version PDF)</a>
    </li>
  </ul>
  {elseif $smarty.get.type eq public || $smarty.get.type eq auth || $smarty.get.type eq admin}
  {include file="xnet/manuel-`$smarty.get.type`.tpl"}
  {/if}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
