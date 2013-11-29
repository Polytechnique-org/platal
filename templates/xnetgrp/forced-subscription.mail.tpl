{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="xnet_forced_subscription"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr="$to"}
{subject text="Inscription au groupe $group"}
{elseif $mail_part eq 'wiki'}
Bonjour,

Tu viens d'être inscrit au groupe {$group} sur Polytechnique.net par l'animateur de celui-ci, {$anim}. Si tu es opposé à cette inscription, tu peux l'annuler en cliquant [[http://www.polytechnique.net/{$diminutif}/unsubscribe|sur ce lien]].
{/if}
{include file="include/signature.mail.tpl"}

{* vim:set et sw=2 sts=2 sws=2: *}
