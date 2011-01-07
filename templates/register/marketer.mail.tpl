{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="marketing"}
{if $mail_part eq 'head'}
{from full=#from#}
{elseif $mail_part eq 'text'}
{if $sender->isFemale()}Chère{else}Cher{/if} {$sender->firstName()},

Nous t'écrivons pour t'informer que {$firstname} {$lastname} ({$promo}), que tu avais incité{if $sex eq 'female'}e{/if} à s'inscrire à Polytechnique.org, vient à l'instant de terminer son inscription.

Merci de ta participation active à la reconnaissance de ce site !!!
{include file="include/signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
