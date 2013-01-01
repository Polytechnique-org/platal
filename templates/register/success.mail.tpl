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

{config_load file="mails.conf" section="inscription"}
{if $mail_part eq 'head'}
{from full=#from#}
{elseif $mail_part eq 'text'}

{$firstname}, félicitations pour ton inscription !

Tu as maintenant accès à l'annuaire en ligne, aux services de listes de
diffusion, aux infos promo, etc. N'oublie pas de mettre ta fiche-annuaire
à jour.
{include file="include/signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
