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

<!-- Pour récupérer ce bandeau sur votre site :
	incluez le fichier http://www.polytechnique.org/bandeau dans vos php
	ou http://www.polytechnique.org/bandeau/login pour profiter du login
	automatique. Et rajoutez à la liste de css de vos pages
	http://www.polytechnique.org/bandeau.css. -->

{if !$login && $smarty.session.auth}
	{assign var="login" value="true"}
{/if}

<div id="bandeau-X">
	<img src="http://www.polytechnique.org/bandeau/icone.png" alt=""/>
	<a href="http://www.polytechnique.fr/">L'&Eacute;cole</a> &middot;
	<a href="http://www.polytechnique.edu/">Institutional site</a>
	&tilde;&tilde;
	<a href="http://www.fondationx.org/">FX</a> &middot;
	<a href="http://www.polytechniciens.com/">AX</a>
	&tilde;&tilde;
	<a href="http://www.polytechnique.org">Polytechnique.org</a> &middot;
	<a href="http://www.polytechnique.net{if $login}/login{/if}">Associations polytechniciennes</a> &middot;
	<a href="http://www.polytechnique.fr/eleves/">&Eacute;l&egrave;ves</a> &middot;
	<a href="http://www.manageurs.com/{if $login}anciens_accueil.php?asso=X.org{/if}">Manageurs</a>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
