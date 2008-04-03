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

{config_load file="mails.conf" section="googleapps"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=$email}
{cc full=#from#}
{subject text="[Polytechnique.org] Création de ton compte Google Apps"}
{elseif $mail_part eq 'wiki'}
{if $sexe}Chère{else}Cher{/if} {$prenom},

Ton compte Google Apps pour Polytechnique.org vient d'être crée.

Tu peux accèder aux services offerts par Google Apps aux adresses suivantes:
* [[http://google.polytechnique.org/|iGoogle, le portail des services Google Apps]] ;
{if $account->activate_mail_redirection}
* [[https://mail.google.com/a/{$googleapps_domain}/|GMail, pour accéder à tes emails Polytechnique.org]] ;
{/if}
* [[https://www.polytechnique.org/googleapps|Polytechnique.org, pour modifier les préférences de ton compte Google Apps]].

Ton nom d'utilisateur pour ces services Google est '''{$account->g_account_name}'''
{if $account->sync_password}
et ton mot de passe est celui de Polytechnique.org.
{else}
et ton mot de passe est celui que tu as choisi lors de ta demande de compte.
{/if}

Tu trouveras plus d'informations dans la [[https://www.polytechnique.org/Xorg/GoogleApps|documentation]] sur Polytechnique.org.

Cordialement,\\
-- \\
L'équipe de Polytechnique.org
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
