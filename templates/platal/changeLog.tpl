{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
<h1>ChangeLog</h1>
{if !$core}
<p>Voici la liste des modifications faites sur <a href="http://opensource.polytechnique.org/platal/">plat/al</a>, le support libre de ce site.</p>

<p>Cette version utilise <a href="changelog/core">plat/al-core {$globals->coreVersion}</a>.</p>
{else}
<p>Voici la liste des modifications faites sur la bibliothèque plat/al-core.</p>
{/if}

{$ChangeLog|smarty:nodefaults}
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
