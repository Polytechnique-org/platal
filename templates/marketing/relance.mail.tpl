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

{config_load file="mails.conf" section="marketing_relance"}
{if $mail_part eq 'head'}
{subject text="$subj"}
{from full=#from#}
{to addr="$lemail"}
{elseif $mail_part eq 'text'}
Cher camarade,

Il y a quelques temps, le {$fdate}, tu as commencé ton inscription à Polytechnique.org. Tu n'as toutefois pas tout à fait terminé cette inscription, aussi nous nous permettons de te renvoyer cet email pour te rappeler tes paramètres de connexion, au cas où tu souhaiterais la terminer, et accéder à l'ensemble des services que nous offrons aux {$nbdix} polytechniciens déjà inscrits : emails à vie, annuaire en ligne, etc.

UN SIMPLE CLIC sur le lien ci-dessous et ton compte sera activé !

Après activation, tes paramètres seront :

login        : {$lusername}
mot de passe : {$nveau_pass}

(ceci annule les paramètres envoyés par l'email initial)

Rends-toi sur la page web suivante afin d'achever ton inscription, et de changer ton mot de passe :

{$baseurl}/register/end/{$lins_id}

Si le lien ci-dessus ne fonctionne pas en cliquant dessus, copie le intégralement dans la barre d'adresse de ton navigateur.

En cas de difficulté, nous sommes bien entendu à ton entière disposition !
{include file="include/signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
