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
        $Id: index.tpl,v 1.7 2004-09-25 14:56:53 x2000habouzit Exp $
 ***************************************************************************}

<div class="rubrique">
  Listes de diffusion de Polytechnique.org
</div>

<div class='ssrubrique'>
  L'inscription à une liste de diffusion
</div>

<p>
Certaines listes sont à inscription modérée, l'inscription n'y est pas
immédiate.  Il faut en effet l'action d'un modérateur de la liste pour valider
(ou éventuellement refuser) ta candidature.
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

<p>
Les listes de diffusion publiques sont visibles par tous les X inscrits à Polytechnique.org.
</p>

{include file='listes/listes.inc.tpl' min=0}

<div class="rubrique">
  Listes de diffusion privées (et de promo)
</div>

<p>
Si tu te désinscrit de ces listes, tu ne seras plus capable de t'y réinscrire par toi même !
</p>

{include file='listes/listes.inc.tpl' min=1}

<br />

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='tinybicol' cellspacing='0' cellpadding='2'>
    <tr>
      <th colspan='2'>Inscription à une liste de diffusion promo</th>
    </tr>
    <tr>
      <td class='titre'>Promotion:</td>
      <td>
        <input type='text' size='4' maxlength='4' name='promo_add' />
        &nbsp;
        <input type='submit' value="m'inscrire" />
      </td>
    </tr>
  </table>
</form>

{perms level=admin}
<div class="rubrique">
  Listes d'administration
</div>

{include file='listes/listes.inc.tpl' min=2 max=4}

{/perms}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
