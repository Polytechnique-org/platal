{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}


<h1>
  Déconnexion {if $smarty.cookies.ORGaccess}partielle {/if}effectuée
</h1>
<p>
<strong>Merci et à bientôt !</strong>
</p>

{if $smarty.cookies.ORGaccess}

<p>
Tu as demandé la connexion permanente donc cette deconnexion ne t'empêche pas d'utiliser la plupart
des fonctionnalités de consultation du site.
</p>
<p>
Tu peux donc aussi te <a href='?forget=1'>deconnecter complètement</a>.
</p>
<p>
De plus, ton adresse e-mail est toujours en mémoire dans ton navigateur afin de faciliter ta
prochaine connexion. Si tu utilises un ordinateur public ou que tu désires l'effacer, tu peux
<a href='?forgetUid=1&forget=1'>supprimer cette information et te déconnecter complètement</a>.
</p>

{elseif $smarty.cookies.ORGuid}

<p>
Ton adresse e-mail est toujours en mémoire dans ton navigateur afin de faciliter ta prochaine
connexion. Si tu utilises un ordinateur public ou que tu désires l'effacer, tu peux
<a href='?forgetUid=1'>supprimer cette information</a>.
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
