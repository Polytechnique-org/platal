{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="test_email"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=$email}
{subject text="Test de ton adresse `$email`"}
{elseif $mail_part eq 'wiki'}
{if $sexe}Chère{else}Cher{/if} {$display_name},

Tu reçois cet email car tu as demandé la confirmation du bon fonctionnement de ton adresse polytechnicienne {$email}.
{if count($redirects) gt 1}Si toutes tes redirections fonctionnent correctement tu devrais recevoir une copie de cet email
dans les boîtes suivantes :
{foreach from=$redirects item=mail}
* {$mail->display_email}
{/foreach}
{/if}

Tu trouveras sur le site divers outils pour gérer ton adresse email :
* [[https://www.polytechnique.org/emails/redirect|La gestion de tes redirections]]
* [[https://www.polytechnique.org/emails/antispam|La gestion de ton antispam]]
* [[https://www.polytechnique.org/emails/send|Un formulaire pour envoyer des emails d'où que tu sois]]

N'hésite pas à venir découvrir ou redécouvrir les services du site grâce au [[https://www.polytechnique.org/review|tour d'horizon]].

Merci encore de la confiance que tu portes à nos services.

-- \\
Très Cordialement,\\
L'Équipe de Polytechnique.org
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
