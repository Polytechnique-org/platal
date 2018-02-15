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

{config_load file="mails.conf" section="carnet"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text="Notifications de la semaine `$week`"}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{elseif $mail_part eq 'wiki'}
{if $sex}Chère{else}Cher{/if} {$yourself},

Voici les événements survenus dans la semaine écoulée, et depuis ta dernière visite sur le site.

Tu trouveras les mêmes informations sur [[https://www.polytechnique.org/carnet/panel|cette page]].

{foreach from=$notifs item=cat}
{$cat.title} :

{foreach from=$cat.users item=user}
{assign var=profile value=$user->profile()}
{if !$profile->isDead()}
* Le {$cat.operation->getDate($user)|date_format:"%d %B %Y"}, [[https://www.polytechnique.org/profile/private/{$profile->hrid()}|{$profile->fullname(true)}]]
{/if}
{/foreach}

{/foreach}
{include file="include/signature.mail.tpl"}
''Note :''  Tu reçois cet email car tu as activé la notification automatique par email des événements que tu surveilles.\\
Tu peux changer cette option sur la [[https://www.polytechnique.org/carnet/notifs|page de configuration des notifications]].

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
