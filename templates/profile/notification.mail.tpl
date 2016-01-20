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

{config_load file="mails.conf" section="profile"}
{if $mail_part eq 'head'}
{from full=#from#}
{cc full=#cc#}
{subject text="Modification de ton profil"}
{elseif $mail_part eq 'wiki'}
{if $sex}Chère{else}Cher{/if} {$yourself},

Les champs suivants de ton profil ont été modifiés le {$date|date_format:"%x"} :
{foreach from=$modifications item=modification}
*{$modification.field} : « {$modification.oldText} » est devenu « {$modification.newText} » (effectuée par {$modification.full_name}).
{/foreach}

Tu peux voir ta fiche là :
*{$globals->baseurl}/profile/{$hrpid}
Tu peux aussi l'éditer toi-même là :
*{$globals->baseurl}/profile/edit/{$hrpid}

{include file="include/signature.mail.tpl"}

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
