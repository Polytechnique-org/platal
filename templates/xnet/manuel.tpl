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
  <div class="breadcrumb">
    {if $type}
    <a href="manuel">Manuel</a> »
    {if $type eq public}
    Public
    {elseif $type eq auth}
    Membres
    {elseif $type eq admin}
    Administrateurs
    {/if}
    {else}
    Manuel
    {/if}
  </div>

  {if $type eq public || $type eq auth || $type eq admin}
  {include file="xnet/manuel-`$type`.tpl"}
  {/if}

  <h1>Les manuels disponibles</h1>
  <ul> 
    <li>
      <a href="manuel/public">fonctionnalités publiques du site</a>
      <a href="docs/manuel.pdf">(version PDF)</a>
    </li>
    <li>
      <a href="manuel/auth">fonctionnalités accessibles par les membres des groupes X</a>
      <a href="docs/manuel.pdf">(version PDF)</a>
    </li>
    <li>
      <a href="manuel/admin">fonctionnalités à disposition des animateurs des groupes X</a>
      <a href="docs/manuel-admin.pdf">(version PDF)</a>
    </li>
  </ul>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
