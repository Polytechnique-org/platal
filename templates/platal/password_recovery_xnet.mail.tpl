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

{config_load file="mails.conf" section="password_recovery_xnet"}
{if $mail_part eq 'head'}
{subject text="Votre certificat d'authentification"}
{from full=#from#}
{elseif $mail_part eq 'text'}

Visitez la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD/{$hash}

Si en cliquant dessus vous n'y arrivez pas, copiez intégralement l'adresse dans la barre de votre navigateur. Si vous n'avez pas utilisé ce lien dans six heures, vous pouvez tout simplement recommencer cette procédure.

{include file="include/signature.mail.tpl"}

Email envoyé à {$email}
{/if}
