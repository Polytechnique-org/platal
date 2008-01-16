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

<h1>Fil RSS</h1>

{if !$smarty.session.core_rss_hash}
<p>
  Tu viens de cliquer sur le lien d'activation des fils RSS. Les fils RSS du site
  ne sont pas activés dans tes préférences.
</p>
<ul>
  <li>
    Tu peux le faire tout de suite en cliquant sur Activer ci-dessous.
  </li>
  <li>
    Qu'est-ce qu'un <a href="Xorg/RSS">fil RSS</a> ?
  </li>
  <li>
    Comment configurer un <a href="Xorg/RSS">agregateur RSS</a> ?
  </li>
</ul>

<form method="get" action="{$goback}">
  <div>
    <input type="hidden" name="referer" value="{$goback}" />
    <input type="submit" value="Retour" />
    <input type="submit" name="act_rss" value="Activer" onclick="this.form.action='prefs/rss'" />
  </div>
</form>

{else}
En voici les adresses&nbsp;:
<ul>
  <li>
  Anonces sur la page d'entrée&nbsp;:
  <a href='rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml'>{icon name=feed title='fil rss'}</a>
  </li>
  <li>
  Ton carnet polytechnicien&nbsp;:
  <a href='carnet/rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml'>{icon name=feed title='fil rss'}</a>
  </li>
</ul>
<p>
Tu peux le désactiver en allant dans Préférences et en cliquant sur "désactiver les fils RSS".
</p>
<p>
Attention: désactiver, puis réactiver le fil RSS en change l'adresse.
</p>
<p>[<a href="{$goback}">retour à la page dont tu venais</a>]</p>
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
