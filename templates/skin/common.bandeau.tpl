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

<!-- Pour récupérer ce bandeau sur votre site&nbsp;:
  incluez le fichier http://www.polytechnique.org/bandeau dans vos php
  ou http://www.polytechnique.org/bandeau/login pour profiter du login
  automatique. Et rajoutez à la liste de css de vos pages
  http://www.polytechnique.org/css/bandeau.css.
  Pour avoir l'icone, pour des raisons de sécurité il n'y a pas d'accès
  direct, il faut alors rediriger bandeau/icone.png vers celle du site&nbsp;:
  http://www.polytechnique.org/bandeau/icone.png
-->

<!-- Don't copy this list of emails!!!

{assign var="login" value="false"}
{if !t($login)}
  {if t($smarty.session.auth)}
  {assign var="login" value="true"}
  {/if}
{/if}

  {poison seed=$login}

  -->


<div id="bandeau-X">
  <img src="{if t($external)}bandeau/icone.png{else}images/x.png{/if}" width="13" height="14" alt=""/>
  <a href="http://www.polytechnique.edu/">L'&Eacute;cole</a> &middot;
  &tilde;&tilde;
  <a href="http://www.fondationx.org/">FX</a> &middot;
  <a href="https://ax.polytechnique.org/">AX</a>
  &tilde;&tilde;
  <a href="https://www.polytechnique.org">Polytechnique.org</a> &middot;
  <a href="http://www.polytechnique.net{if t($login)}/login{/if}">Associations polytechniciennes</a> &middot;
  <a href="http://www.polytechnique.fr/eleves/">&Eacute;l&egrave;ves</a> &middot;
  <a href="http://www.wats4u.com/{if t($login)}manageurs/login.mj?association=polytechnique{/if}">Wats4U</a>
</div>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf8: *}
