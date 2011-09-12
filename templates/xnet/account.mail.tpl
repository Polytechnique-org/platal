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

{config_load file="mails.conf" section="xnet_registration"}
{if $mail_part eq 'head'}
{subject text=#subject#}
{from full=#from#}
{to addr="$to"}
{elseif $mail_part eq 'text'}
Bonjour,

{$sender_name} nous a demandé de vous créer un compte pour que vous puissiez disposer pleinement de toutes les fonctionnalités liées au groupe {$group}.

Après activation, vos paramètres de connexion seront :

identifiant  : {$email}
mot de passe : celui que vous choisirez

Vous pouvez, dès à présent et pendant une période d'un mois, activer votre compte en cliquant sur le lien suivant :

{$globals->baseurl}/register/ext/{$hash}

Si le lien ne fonctionne pas, copiez intégralement ce lien dans la barre d'adresse de votre navigateur.

Une fois votre compte activé, nous espérons que vous profiterez pleinement des services en ligne de Polytechnique.net. Pour cela, il vous suffira d'aller sur http://www.polytechnique.net/ et de vous connecter en tant qu' « Extérieur ».
{include file="include/signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
