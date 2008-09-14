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
{if $sexe}Chère{else}Cher{/if} {$prenom},

Tu reçois cet email car une demande de réécriture vient d'être effectuée sur {$sitename} pour que les mails
l'adresse {$mail->email} soit automatiquement réécrite en {$mail->rewrite}.

Si tu es à l'origine de cette demande, clique sur le lien suivant pour activer la réécriture :
* {$baseurl}/emails/rewrite/in/{$mail->email|replace:'@':'_'}/{$mail->hash}

Si tu n'est pas à l'origine de cette demande, il peut s'agir d'une tentative de détournement de ta correspondance par un
camarade mal intentionné. Dans ce cas, clique sur le lien suivant pour avertir l'équipe de {$sitename} :
* {$baseurl}/emails/rewrite/out/{$mail->email|replace:'@':'_'}/{$mail->hash}

Merci encore de la confiance que tu portes à nos services.

-- \\
Très Cordialement,\\
L'Équipe de Polytechnique.org
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
