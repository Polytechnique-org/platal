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

{if $smarty.session.mail_fmt eq 'texte'}
<h3><a href="?mail_fmt=html">Recevoir les mails en format HTML</a></h3>
{else}
<h3><a href="?mail_fmt=texte">Recevoir les mails en format texte</a></h3>
{/if}
<div class='explication'>
  Les mails envoyés par le site (lettre mensuelle, carnet, ...) le sont de préférence en format {$smarty.session.mail_fmt}.
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
