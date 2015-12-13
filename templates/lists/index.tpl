{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<script type="text/javascript">
  //<![CDATA[
  {literal}
  function updateHtml(id, url)
  {
      if ($.browser.msie) {
          return true;
      } else {
          $('#' + id).updateHtml(url);
          return false;
      }
  }
  {/literal}
  //]]>
</script>

<h1>
  Listes de diffusion de Polytechnique.org
</h1>

{if $owner|@count > 0 || $member|@count > 0 || ( hasPerm('lists') && $public|@count
> 0)}
<h2>L'inscription à une liste de diffusion</h2>

<ul>
  {if hasPerm('lists')}
  <li>Pour demander ton inscription à une liste de diffusion, il suffit
    de cliquer sur l'icône {icon name=add} située en fin de ligne.</li>
  <li>Si la liste est à inscription modérée, l'icône {icon name=flag_orange title="en cours"}
    apparaîtra tant que ton inscription n'aura pas été validée par un modérateur.</li>
  {/if}
  <li>Pour te désinscrire d'une liste dont tu es membre, il suffit de cliquer sur la croix
    {icon name=cross title="désinscription"} située en fin de ligne.</li>
</ul>

<h2>La diffusion sur une liste de diffusion</h2>
<p>
La diffusion a trois niveaux de modération.  La diffusion peut être&nbsp;:
</p>
<ul>
  <li><strong>libre&nbsp;:</strong> tout le monde peut y envoyer des emails, la diffusion y est
  immédiate&nbsp;;</li>
  <li><strong>restreinte&nbsp;:</strong> les membres de la liste peuvent envoyer librement des emails,
  les extérieurs sont modérés&nbsp;;</li>
  <li><strong>modérée&nbsp;:</strong> l'envoi d'un email à la liste est alors filtré par des
  modérateurs, eux seuls peuvent accepter un message envoyé à la liste.</li>
</ul>
{else}
<p>
  Tu n'as actuellement accès à aucune liste de diffusion.
</p>
{/if}

{if hasPerm('lists')}
<h1>Demander la création d'une liste de diffusion</h1>

<p>
Nos listes ont pour but de réunir des X autour de thèmes ou centres d'intérêt communs.  C'est un
moyen pratique et efficace de rassembler plusieurs personnes autour d'un projet commun ou d'une
thématique particulière.
</p>

<p class="center">
{icon name=add title="Nouvelle liste"} <a href='lists/create'>
  Tu peux demander la création d'une liste de diffusion sur le thème de ton choix.
</a>
</p>
{/if}

{if $owner|@count}
<h1>Listes dont tu es modérateur</h1>

{include file='lists/listes.inc.tpl' lists=$owner}

<p class='smaller'>
{icon name=wrench title="Modérateur"} indique que tu es modérateur de la liste, les modérateurs jouent également le rôle de gestionnaire.<br />
{icon name=error title="Modérateur mais non-membre"} indique que tu es modérateur de la liste, mais que tu n'en es pas membre.
</p>
{/if}
{if $member|@count}
<h1>Listes dont tu es membre</h1>

{assign var="has_private" value=false}
{include file='lists/listes.inc.tpl' lists=$member}

<p class="smaller">Attention&nbsp;: lorsqu'une liste à laquelle tu es abonné est privée, l'icône {icon name=weather_cloudy} est affichée en début de ligne. Si tu t'en désinscris, il ne te sera pas possible de t'y abonner de nouveau sans l'action d'un modérateur.</p>
{/if}

{if hasPerm('lists')}
<h1>Listes de diffusion publiques auxquelles tu peux t'inscrire</h1>

<p>
Les listes de diffusion publiques sont visibles par tous les X inscrits à Polytechnique.org. Des listes de diffusion destinées aux Groupes X sont par ailleurs disponibles sur <a href="https://www.polytechnique.net/plan">polytechnique.net</a>.
</p>

{if $public|@count}
{include file='lists/listes.inc.tpl' lists=$public}

<br />
{/if}

<form method='post' action='lists'>
  {xsrf_token_field}
  <table class='tinybicol' cellspacing='0' cellpadding='2'>
    <tr>
      <th colspan='2'>Inscription à une liste de diffusion promo</th>
    </tr>
    <tr>
      <td class='titre'>Promotion&nbsp;:</td>
      <td>
        <input type='text' size='4' maxlength='4' name='promo_add' />
        &nbsp;
        <input type='submit' value="m'inscrire" />
      </td>
    </tr>
  </table>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
