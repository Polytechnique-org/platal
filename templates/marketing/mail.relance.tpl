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

{config_load file="mails.conf" section="marketing_relance"}
{if !$html_version}
{subject text="$subj"}
{from full=#from#}
{to addr="$lemail"}
Bonjour,

Il y a quelques temps, le {$fdate}, tu as commencé ton inscription à Polytechnique.org ! Tu n'as toutefois pas tout à fait terminé cette inscription, aussi nous nous permettons de te renvoyer cet email pour te rappeler tes paramètres de connexion, au cas où tu souhaiterais terminer cette inscription, et accéder à l'ensemble des services que nous offrons aux {$nbdix} Polytechniciens déjà inscrits (email à vie, annuaire en ligne, etc...).

UN SIMPLE CLIC sur le lien ci-dessous et ton compte sera activé !

Après activation, tes paramètres seront :

login        : {$lusername}
mot de passe : {$nveau_pass}

(ceci annule les paramètres envoyés par le mail initial)

Rends-toi sur la page web suivante afin d'activer ta pré-inscription, et de changer ton mot de passe en quelque chose de plus facile à mémoriser :

{$baseurl}/register/end/{$lins_id}

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur.

En cas de difficulté, nous sommes bien entendu à ton entière disposition !

Bien cordialement,
Polytechnique.org
"Le portail des élèves & anciens élèves de l'Ecole polytechnique"

{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
