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
        $Id: index.tpl,v 1.15 2004-10-24 14:41:13 x2000habouzit Exp $
 ***************************************************************************}

<h1>
  Listes de diffusion de Polytechnique.org
</h1>

<div class='ssrubrique'>
  L'inscription à une liste de diffusion
</div>

<p>
Pour t'inscrire à une liste il suffit de cliquer sur l'icone
<img src="{"images/ajouter.gif"|url}" alt="[ inscription ]" /> située en fin de ligne.
</p>

<p>
Certaines listes sont à inscription modérée, l'inscription n'y est pas
immédiate.  Il faut en effet l'action d'un modérateur de la liste pour valider
(ou éventuellement refuser) ta candidature.  Ces listes apparaissent avec l'icone 
<img src="{"images/flag.png"|url}" alt="[ en cours ]" />.
</p>

<p>
Pour se désinscrire, il suffit de la même manière de cliquer sur l'icone
<img src="{"images/retirer.gif"|url}" alt="[ désinscription ]" />.
</p>

<div class='ssrubrique'>
  La diffusion sur une liste de diffusion 
</div>
<p>
La diffusion a trois niveaux de modération.  La diffusion peut être :
</p>
<ul>
  <li>libre : tout le monde peut y envoyer des mails, la diffusion y est
  immédiate;</li>
  <li>restreinte : les membres de la liste peuvent envoyer librement des mails,
  les extérieurs sont modérés;</li>
  <li>modérée: l'envoi d'un mail à la liste est alors filtré par des
  modérateurs, eux seuls peuvent accepter un message envoyé à la liste.</li>
</ul>

<p class='smaller'>
NB : les gestionnaires d'une liste sont aussi ses modérateurs.<br />
les listes avec une asterisque sont les listes dont tu es le gestionnaire.
</p>

<div class='ssrubrique'>
  Demander la création d'une liste de diffusion
</div>

<p>
Nos listes ont pour but de réunir des X autour de thèmes ou centres d'intérêt communs.  C'est un
moyen pratique et efficace de rassembler plusieurs personnes autour d'un projet commun ou d'une
thématique particulière.
</p>
<p>
Tu peux demander <a href='create.php'>la création</a> d'une liste de diffusion sur le thème de ton choix.  
</p>

{dynamic}

<h1>
  Lettre mensuelle de Polytechnique.org
</h1>

{if $nl eq html}
<p>
Tu es actuellement inscrit à la lettre mensuelle de Polytechnique.org dans sont format HTML !
</p>
{elseif $nl eq text}
<p>
Tu es actuellement inscrit à la lettre mensuelle de Polytechnique.org dans sont format texte !
</p>
{else}
<p>
Tu n'es actuellement pas inscrit à la lettre mensuelle de Polytechnique.org.
</p>
{/if}

{if $nl neq "text"}
<p>
Pour recevoir la version texte suis le lien :
</p>
<div class='center'>
  [<a href='?nl_sub=text'>m'inscrire pour le format texte</a>]
</div>
{/if}

{if $nl neq "html"}
<p>
Pour recevoir la version HTML suis le lien :
</p>
<div class='center'>
  [<a href='?nl_sub=html'>m'inscrire pour le format HTML</a>]
</div>
{/if}

{if $nl}
<p>
Pour te désinscrire suis le lien :
</p>
<div class='center'>
  [<a href='?nl_unsub=1'>me désinscrire</a>]
</div>
{/if}

<h1>
  Listes de diffusion publiques
</h1>

<p>
Les listes de diffusion publiques sont visibles par tous les X inscrits à Polytechnique.org.
</p>

{include file='listes/listes.inc.tpl' min=0}

<h1>
  Listes de diffusion privées (et de promo)
</h1>

<p>
Si tu te désinscrit de ces listes, tu ne seras plus capable de t'y réinscrire par toi même !
</p>

{if $smarty.session.perms eq admin}
{include file='listes/listes.inc.tpl' min=1}
{else}
{include file='listes/listes.inc.tpl' min=1 max=4}
{/if}

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
<h1>
  Listes d'administration
</h1>

{include file='listes/listes.inc.tpl' min=2 max=4}

{/perms}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
