{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="registration"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=#to#}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{elseif $mail_part eq 'text'}
{$firstname} {$lastname} ({$promo}) a terminé son inscription avec les données suivantes :
 - nom       : {$lastname}
 - prenom    : {$firstname}
 - promo     : {$promo}
 - naissance : {$birthdate} (date connue : {$birthdate_ref})
 - forlife   : {$forlife}
 - email     : {$email}
 - sexe      : {$sex}
 - ip        : {$logger->ip} ({$logger->host})
{if $logger->proxy_ip} - proxy     : {$logger->proxy_ip} ({$logger->proxy_host}){/if}


{if $market}Les marketings suivants avaient été effectués :
{$market}
{else}{$firstname} {$lastname} n'a jamais reçu d'email de marketing.
{/if}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
