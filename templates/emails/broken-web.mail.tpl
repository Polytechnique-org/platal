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

{config_load file="mails.conf" section="emails_broken"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text=#subject#}
{elseif $mail_part eq 'wiki'}
Bonjour !

Cet email a été généré automatiquement par le service de patte cassée de
Polytechnique.org car un autre utilisateur, {$request->fullName()},
nous a signalé qu'en t'envoyant un email, il avait reçu un message d'erreur
indiquant que ton adresse de redirection {$email}
ne fonctionnait plus !

Nous te suggérons de vérifier cette adresse, et le cas échéant de mettre
à jour tes adresses de redirection [[{$globals->baseurl}/emails|sur le site]].

Pour plus de renseignements sur le service de patte cassée, n'hésite pas à
consulter [[{$globals->baseurl}/emails/broken|la documentation sur le site]].
{include file="include/signature.mail.tpl"}
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
