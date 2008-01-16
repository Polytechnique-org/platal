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


{if $cacheExists}
{include wiki=$article}
{if hasPerm('admin')}
<div class="center">
  <a href="{$article|replace:'.':'/'}?action=edit">{icon name="page_edit" title="Editer la page"} Mettre à jour la page</a>
</div>
{/if}
{elseif hasPerm('admin')}
La page wiki associée n'existe pas. <a href="{$article|replace:'.':'/'}?action=edit">Il faut l'éditer</a>.
{else}
<p class="erreur">La page n'existe pas</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
