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
        $Id: parselog.tpl,v 1.5 2004/11/09 13:59:19 x2000palatin Exp $
 ***************************************************************************}


<h1>
  Statistiques mail de polytechnique.org
</h1>

<p>
<a href="?order=D">[Resultats classes par ordre des noms de domaines destinataires]</a><br />
<a href="?order=N">[Resultats classes par nombre de mails achemines]</a><br />
<a href="?order=R">[Resultats classes par nombre de mails retardes]</a><br />
{perms level=admin}
  <a href="?raw=true">[Resultats bruts]</a><br />
{/perms}
</p>

{dynamic}
{if $smarty.request.order}
<table class="bicol" cellpadding="3" cellspacing="0" summary="Statistiques mails">
  <tr>
      <th>Domaine destinataire</th>
      <th>Delai moyen</th>
      <th>Delai maxi</th>
      <th>Nombre de mails</th>
      <th>Mails retardes</th>
  </tr>
{if $smarty.request.order eq "D"}  
  {include file="stats/lastParselogD.tpl"}
{elseif $smarty.request.order eq "R"}
  {include file="stats/lastParselogR.tpl"}
{elseif $smarty.request.order eq "N"}
  {include file="stats/lastParselogN.tpl"}
{/if}  
</table>
{/if}

{perms level=admin}
  {if $smarty.request.raw}
    <pre>
    {include file="stats/lastParselog.tpl" assign=content}
    {$content|escape}
    </pre>
  {/if}
{/perms}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
