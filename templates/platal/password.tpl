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


<h1>
  {if t($xnet)}Création du mot de passe{else}Changer de mot de passe{/if}
</h1>

<p>
  Le mot de passe du compte Polytechnique.org est maintenant enregistré sur le service d'authentification de Polytechnique.org, <a href="https://auth.polytechnique.org/">auth.polytechnique.org</a>. Pour le modifier, il suffit de se rendre sur <a href="https://auth.polytechnique.org/accounts/password/change/">la page qui y est consacrée</a>.
</p>

{if !t($xnet)}{if !t($xnet_reset)}
<p>
  Note bien qu'il s'agit là du mot de passe te permettant de t'authentifier sur le site {#globals.core.sitename#}&nbsp;;
  le mot de passe te permettant d'utiliser le serveur <a
  href="{"./Xorg/SMTPSécurisé"|urlencode}">SMTP</a>
  et <a href="{"Xorg/NNTPSécurisé"|urlencode}">NNTP</a>
  de {#globals.core.sitename#} (si tu as <a href="./password/smtp">activé l'accès SMTP et NNTP</a>)
  est indépendant de celui-ci et tu peux le modifier <a href="./password/smtp">ici</a>.
</p>
{/if}{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
