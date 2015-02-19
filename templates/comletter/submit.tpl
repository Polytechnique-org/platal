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

<h1>Proposer un article pour la prochaine Lettre de la communauté</h1>

{if t($submited)}

<p>
Ton article a bien été pris en compte.
</p>

<p>
Nous te recontacterons éventuellement si nous avons des renseignements à te demander à son sujet&nbsp;!
</p>

{else}


{if t($art)}

{if !$art->check()}
<p class='erreur'>
L'article que tu as proposé est trop long&nbsp;!<br />
Il te faut te limiter à 8 lignes de 68 caractères.
</p>
{/if}

<form action="comletter/submit" method='post'>
  <table class='bicol'>
    <tr><th>Version texte</th></tr>
    <tr id='text'>
    <td><pre>{$art->toText()}</pre></td>
    </tr>
    <tr><th>Version html</th></tr>
    <tr id='html'>
      <td>
        <div class='nl'>
          {$art->toHtml()|smarty:nodefaults}
        </div>
      </td>
    </tr>
    {if $art->check()}
    <tr>
      <th>Soumettre</th>
    </tr>
    <tr>
      <td>
        Si tu es content de ton article, tu peux le soumettre.
        Sinon, tu peux continuer à l'éditer en dessous.
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' value="{$smarty.request.title}" name='title' />
        <input type='hidden' value="{$art->body()}" name="body" />
        <input type='hidden' value="{$art->append()}" name='append' />
        <input type='submit' name='valid' value='Soumettre' />
      </td>
    </tr>
    {/if}
  </table>
</form>

<br />

{/if}

<h2>Proposer un article</h2>

<p>
Tu peux <a href='comletter/submit#conseils'>lire les conseils de rédaction</a> avant de proposer ton article.
</p>
<form action="comletter/submit" method='post'>
  <table class="bicol" cellpadding="3" cellspacing="0" summary="proposer un article">
    <tr>
      <th colspan='2'>Proposer un article</th>
    </tr>
    <tr class="impair">
        <td class='titre'>Sujet</td>
      <td>
        <input size='60' maxlength='60' type='text' value='{$smarty.request.title}' name='title' />
      </td>
    </tr>
    <tr class="pair">
      <td class='titre'>Contenu</td>
      <td>
        <textarea onchange="{literal}$.post('comletter/remaining/', {'body': this.value}, function(data) {$('#remaining').html(data)}){/literal}"
                  onkeyup="{literal}$.post('comletter/remaining/', {'body': this.value}, function(data) {$('#remaining').html(data)}){/literal}"
                  cols="68" rows="8" name="body" >{if t($art)}{$art->body()}{/if}</textarea>
      </td>
    </tr>
    <tr class="pair">
      <td id="remaining" class="center" colspan="2"></td>
    </tr>
    <tr class="impair">
      <td class='titre'>Ajouts (emails, contacts, tarifs, site web&hellip;)</td>
      <td>
        <textarea cols="68" rows="3" name='append'>{if t($art)}{$art->append()}{/if}</textarea>
      </td>
    </tr>
    <tr class="pair smaller">
      <td></td>
      <td>
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir les marqueurs de mise en forme autorisés
        </a>
      </td>
    </tr>
    <tr class='pair'>
      <td colspan='2' class='center'>
        <input type='submit' name='see' value='Visualiser' />
      </td>
    </tr>
  </table>
</form>

<a id='conseils'></a>
{include wiki=Xorg.LettreCommunaute}

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
