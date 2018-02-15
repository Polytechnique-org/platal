{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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


<h1>Télépaiements</h1>

{if $erreur}
<p>Aucun paiement n'a été effectué.</p>
{else}
<p>Merci de nous avoir fait confiance pour ton paiement. La transaction
est terminée et s'est déroulée correctement. Tu vas recevoir un email accusant
réception de ton paiement.</p>

<p>{$texte|nl2br}</p>
{/if}
<p>[<a href="https://www.polytechnique.org/payment">retour aux Télépaiements</a>]</p>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
