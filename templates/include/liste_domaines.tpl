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
 ***************************************************************************
        $Id: liste_domaines.tpl,v 1.4 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}
{$result}

{if $nb_dom}
<div class="rubrique">
Administrer le routage email sur ton(tes) domaine(s)
</div>

<p>
  Voici le(s) domaine(s) dont tu es administrateur.
  Pour administrer un domaine, il te suffit à l'heure actuelle de cliquer sur son nom.
  Cependant, prends bien note que cette administration se fera bientôt depuis le site www.polytechnique.net.
</p>

<div class="right">
{foreach item=dom from=$domaines}
  <a href="{"domaine.php?domaine=$dom"|escape:"url"|url}">{$dom}</a>
  <br />
{/foreach}
</div>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
