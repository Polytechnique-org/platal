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

{if $ok}
<p>
  {if $sexe}Chère{else}Cher{/if} {$prenom},
</p>

<p>
le stockage de secours de tes emails a été activé avec succès. Tu trouveras davantage d'informations à ce
sujet sur la page de <a href="Xorg/IMAP">documentation</a>.
</p>

<p>
Tu peux également modifier à tout moment ton abonnement à ce service en te rendant sur la page de
<a href="emails/redirect">gestion de tes redirections</a>.
</p>
{else}
<p class="erreur">
Une erreur s'est produite lors de ton inscription au service de stockage de secours. N'hésite pas à nous signaler le
problème en écrivant à <a href="mailto:support@polytechnique.org">support@polytechnique.org</a>.
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
