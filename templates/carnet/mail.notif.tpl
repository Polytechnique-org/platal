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

{config_load file="mails.conf" section="carnet"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text="Notifications de la semaine `$week`"}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{elseif $mail_part eq 'wiki'}
{if $u.sexe}Chère{else}Cher{/if} {$u.prenom},

Voici les événements survenus dans la semaine écoulée, et depuis ta dernière visite sur le site.

Tu trouveras les mêmes informations sur [[https://www.polytechnique.org/carnet/panel|cette page]].

{foreach from=$u.data key=cid item=d}

!{if $d|@count eq 1}{$cats[$cid].mail_sg}{else}{$cats[$cid].mail}{/if} :

{foreach from=$d key=promo item=x}
* (X{$x.promo}), le {$x.date|date_format:"%d %b %Y"}, [[https://www.polytechnique.org/profile/private/{$x.bestalias}|{$x.prenom} {$x.nom}]]
{/foreach}

{/foreach}
-- 
L'Équipe de Polytechnique.org

'''''Note :'''''  Tu reçois ce mail ce mail car tu as activé la notification automatique par mail des événements que tu surveilles.\\
Tu peux changer cette options sur la [[https://www.polytechnique.org/carnet/notifs|page de configuration des notifications]].

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
