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

{config_load file="mails.conf" section="inscrire"}
{if $mail_part eq 'head'}
{subject text="$subject"}
{from full=#from#}
{to addr="$to"}
{elseif $mail_part eq 'text'}
Bonjour,

Ton inscription sur Polytechnique.org est presque terminée !

Après activation, tes paramètres de connexion seront :

identifiant  : {$emailXorg}
mot de passe : celui que tu as choisi

Rends-toi maintenant sur la page web suivante afin d'activer ta pré-inscription :

{$baseurl}/register/end/{$hash}

Si en cliquant dessus tu n'y arrives pas, copie intégralement ce lien dans la barre d'adresse de ton navigateur.

Nous espérons que tu profiteras pleinement des services en ligne de Polytechnique.org ; s'ils te convainquent, n'oublie pas d'en parler aux camarades autour de toi !
{include file="include/signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
