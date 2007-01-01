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


<p>
Trier par :
{if ($order eq 'nom') and not($smarty.request.inv)}
[<a href='carnet/contacts?order=nom&amp;trombi={$smarty.request.trombi}&amp;inv=1'><strong>nom <img src='images/dn.png' alt='decr.' /></strong></a>]
{else}
[<a href='carnet/contacts?order=nom&amp;trombi={$smarty.request.trombi}'>{if ($order eq 'nom')}<strong>nom <img src='images/up.png' alt='crois.' /></strong>{else}nom{/if}</a>]
{/if}
{if ($order eq 'promo') and ($smarty.request.inv)}
[<a href='carnet/contacts?order=promo&amp;trombi={$smarty.request.trombi}'><strong>promo <img src='images/up.png' alt='decr.' /></strong></a>]
{else}
[<a href='carnet/contacts?order=promo&amp;trombi={$smarty.request.trombi}&amp;inv=1'>{if ($order eq 'promo')}<strong>promo <img src='images/dn.png' alt='crois.' /></strong>{else}promo{/if}</a>]
{/if}
{if ($order eq 'last') and ($smarty.request.inv)}
[<a href='carnet/contacts?order=last&amp;trombi={$smarty.request.trombi}'><strong>dernière modification <img src='images/up.png' alt='decr.' /></strong></a>]
{else}
[<a href='carnet/contacts?order=last&amp;trombi={$smarty.request.trombi}&amp;inv=1'>{if ($order eq 'last')}<strong>dernière modification <img src='images/dn.png'i alt='crois.' /></strong>{else}dernière modification{/if}</a>]
{/if}
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
