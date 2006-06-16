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

{if !$smarty.server.HTTP_USER_AGENT|regex_replace:"/^Mozilla\/(3|4\.[^0]).*$/":""}
<h1>ATTENTION !</h1>

<p class="erreur">
Netscape 4 et certains autres navigateurs très anciens ne sont pas supportés par ce site !!!
</p>
<p>
En effet, ils ne comprenent qu'une trop faible partie des standards du web.
Il faut donc s'attendre à ce que nombre des fonctionnalités de ce site soient de ce fait indisponnibles.
</p>
<p>
Nous conseillons très vivement d'utiliser des navigateurs récents, tels
<a href="http://www.mozilla.org/products/firefox/">Firefox</a>
</p>
<br />
{/if}

{foreach from=$xorg_errors item=err}
<div class="erreur">{$err|smarty:nodefaults}</div>
{/foreach}

{if !$xorg_failure && $xorg_tpl}{include file=$xorg_tpl}{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
