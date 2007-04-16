{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

<h1>Proposition d'information événementielle</h1>

{if $ok}
<p>
Ta proposition a bien été enregistrée, un administrateur va se charger de la valider aussi rapidement que possible.
</p>
<p>
Merci pour ta contribution à la vie du site!
</p>
<p>
<a href="events">Retour à la page d'accueil</a>
</p>
{else}

{include file="events/form.tpl"}

{include wiki=Xorg.Annonce}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
