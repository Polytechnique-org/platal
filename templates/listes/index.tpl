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
        $Id: index.tpl,v 1.3 2004-09-20 20:37:15 x2000habouzit Exp $
 ***************************************************************************}

<div class="rubrique">
  Listes de diffusion de Polytechnique.org
</div>

<p>
Les listes de diffusion publiques sont visibles par tous les X inscrits à Polytechnique.org.
</p>

<div class='ssrubrique'>
  L'inscription à une liste de diffusion
</div>

<p>
Certaines listes sont à inscription modérée, pour t'y inscrire, il te faut envoyer un mail aux
modérateurs en cliquant sur le lien "s'inscrire", si tu es déjà inscrit, le mot "inscrit" apparaît
près de la case à cocher.  Les autres listes sont dites libres : il suffit de cocher la case à
cocher et de cliquer sur le bouton "Enregistrer".
</p>

<p>
Dans tous les cas, pour se désinscrire, il suffit de décocher la case et de cliquer sur
"Enregistrer".  
</p>

<div class='ssrubrique'>
  La diffusion sur une liste de diffusion 
</div>
<p>
Certaines listes sont à diffusion modérée, l'envoi d'un mail à la liste est alors filtré par des
modérateurs : eux seuls peuvent accepter un message envoyé à la liste.  Pour les autres listes, la
diffusion est immédiate.
</p>
<p class='smaller'>
NB : les gestionnaires d'une liste sont aussi ses modérateurs.  
</p>

{dynamic}

<div class="rubrique">
  Listes de diffusion publiques
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv eq 0}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='liste.php?liste={$liste.list}'>{$liste.list}</a>
      {if $liste.you>1}[<a href='moderate.php?liste={$liste.list}'>mod</a>]{/if}
    </td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>

<div class="rubrique">
  Listes de diffusion privées
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv eq 1}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='liste.php?liste={$liste.list}'>{$liste.list}</a>
      {if $liste.you>1}[<a href='moderate.php?liste={$liste.list}'>mod</a>]{/if}
    </td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>

{perms level=admin}
<div class="rubrique">
  Listes d'administration
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv > 1}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='liste.php?liste={$liste.list}'>{$liste.list}</a>
      {if $liste.you>1}[<a href='moderate.php?liste={$liste.list}'>mod</a>]{/if}
    </td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>
{/perms}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
