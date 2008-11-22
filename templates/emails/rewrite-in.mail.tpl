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

{config_load file="mails.conf" section="rewrite_email"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=$to}
{subject text="Validation de la demande de réécriture pour l'adresse `$mail->email`"}
{elseif $mail_part eq 'wiki'}
Bonjour,

Un utilisateur du site {$sitename} a indiqué votre adresse de courrier
électronique {$mail->email} comme lui étant liée.

Si vous ne connaissez pas le site {$sitename}, si vous ne comprenez pas
ce email, ou si vous n'êtes pas à l'origine de cette demande, ne
faites rien, cette demande sera annulée automatiquement dans les
heures qui suivent.

Si vous recevez plusieurs messages de ce type en provenance du site
{$sitename}, vous pouvez nous avertir en nous écrivant à l'adresse
abuse@{$globals->mail->domain}.

Si vous êtes membre du site {$sitename} et à l'origine de cette demande,
vous avez demandé à ce que, lorsque les serveurs de {$sitename} traitent
un email émis depuis l'adresse {$mail->email}, ils remplacent cette adresse
d'expéditeur par {$mail->rewrite}.

Assurez-vous d'avoir bien lu et bien compris les enjeux de cette
fonctionnalité complexe tels qu'ils sont décrits sur la page :
* {$baseurl}/emails/redirect

Puis cliquez sur le lien suivant pour valider cette demande :
* {$baseurl}/rewrite/in/{$mail->email|replace:'@':'_'}/{$mail->hash}

-- \\
Très cordialement,\\
L'équipe de {$sitename}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
