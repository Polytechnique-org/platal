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
 ***************************************************************************}


<h1>
  Proposer un article à sur la prochaine Lettre mensuelle
</h1>

{dynamic}

{if $submited}

<p>
ton article a bien été pris en compte.
</p>

<p>
Nous te recontacterons éventuellement (certainement vers la fin du mois) si nous avons des
renseignements à te demander à son sujet !
</p>

{else}


{if $art}

{if !$art->check()}
<p class='erreur'>
article trop long !<br />
il faut te limiter à 8 lignes de 68 caractères.
</p>
{/if}

<form action="{$smarty.server.PHP_SELF}" method='post'>
  <table class='tinybicol'>
    <tr><th>Version texte</th></tr>
    <tr id='text'>
    <td><pre>{$art->toText()}</pre></td>
    </tr>
    {if $art->check()}
    <tr><th>Version html</th></tr>
    <tr id='html'>
      <td>
        <div class='nl'>
          {$art->toHtml()|smarty:nodefaults}
        </div>
      </td>
    </tr>
    <tr>
      <th>Soumettre</th>
    </tr>
    <tr>
      <td>
        Si tu es content de ton article, tu peux le soummetre.
        Sinon, tu peux continuer à l'éditer en dessous
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' value="{$smarty.request.title}" name='title' />
        <input type='hidden' value="{$art->body()}" name="body" />
        <input type='hidden' value="{$art->append()}" name='append' />
        <input type='submit' name='valid' value='soumettre' />
      </td>
    </tr>
    {/if}
  </table>
</form>

<br />

{/if}

<p>
Il faut absolument que ton article fasse moins de 8 lignes (non vides) de 68 caractères.
</p>

<p>
Les contacts, prix, adresses mail utiles, liens web, ...  sont en sus, et sont à placer dans la case "Ajouts"
</p>

<form action="{$smarty.server.PHP_SELF}" method='post'>
  <table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
    <tr>
      <th colspan='2'>proposer un article</th>
    </tr>
    <tr class="impair">
      <td class='titre'>Sujet</td>
      <td>
        <input size='60' type='text' value='{$smarty.request.title}' name='title' />
      </td>
    </tr>
    <tr class="pair">
      <td class='titre'>Contenu</td>
      <td>
        <textarea cols="68" rows="10" name='body'>{if $art}{$art->body()}{/if}</textarea>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Ajouts (emails, contacts, tarifs, site web, ...)</td>
      <td>
        <textarea cols="68" rows="6" name='append'>{if $art}{$art->append()}{/if}</textarea>
      </td>
    </tr>
    <tr class='pair'>
      <td colspan='2' class='center'>
        <input type='submit' name='see' value='visualiser' />
      </td>
    </tr>
  </table>
</form>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
