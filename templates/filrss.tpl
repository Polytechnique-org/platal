{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Fil RSS</h1>

{if !$smarty.session.core_rss_hash}
Tu viens de cliquer sur le lien d'activation des fils RSS. Les fils RSS du site <br/>
ne sont pas activés dans tes préférences.
<ul>
  <li>
 Tu peux le faire tout de suite en cliquant sur Activer ci-dessous.
  </li>
  <li>
 Qu'est-ce qu'un <a href="http://www.weblogger.ch/blog/archives/2004/06/23/syndication-pas-pas/">fil RSS</a> ?
  </li>
  <li>
 Comment configurer un <a href="http://www.ac-reims.fr/ia52/rss/lire_rss.htm">agregateur RSS</a> ?
  </li>
</ul>

<form method="get" action="{rel}/{$goback}">
  <div>
    <input type="hidden" name="referer" value="{$goback}" />
    <input type="submit" value="Retour" />
    <input type="submit" name="act_rss" value="Activer" onclick="this.form.action='{rel}/prefs/rss'" />
  </div>
</form>

{else}
En voici les adresses :
<ul>
  <li>
  Anonces sur la page d'entrée :
  <a href='{rel}/rss.php/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}.xml'><img src='{rel}/images/rssicon.gif' alt='fil rss' title='fil RSS'/></a>
  </li>
  <li>
  Ton carnet polytechnicien :
  <a href='{rel}/carnet/rss.php/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}.xml'><img src='{rel}/images/rssicon.gif' alt='fil rss' title='fil RSS'/></a>
  </li>
</ul>
<p>
Tu peux le désactiver en allant dans Préférences et en cliquant sur "désactiver les fils RSS".
</p>
<p>
Attention: désactiver, puis réactiver le fil RSS en change l'adresse.
</p>
<p>[<a href="{rel}/{$goback}">retour à la page dont tu venais</a>]</p>
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
