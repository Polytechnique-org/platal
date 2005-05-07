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

 
{if !$exalead_data}
<p class="descr">
Cette page de recherche fonctionne grâce au moteur développé par <a href="http://www.exalead.com/">Exalead</a>.
</p>
{/if}

<p class="descr">
Ce moteur est capable de dégager des catégories à partir des résultats qu'il a trouvé, tu peux alors
affiner ta recherche et éliminer les résultats d'une catégorie particulière
(<img style="vertical-align: text-bottom;" src="images/moins.png" alt="[-]"/>)
ou ne demander que les résultats appartenant à une catégorie particulière
(<img style="vertical-align: text-bottom;"  src="images/select.png" alt="[+]"/>).
</p>

<p class="descr">
Ce moteur sait aussi détecter des mots clés qu'il a pu rencontrer parmi les résultats, et permet
d'affiner la recherche sur ces critères, suivant le même fonctionnement que les catégories.
</p>

<span><a href="http://www.exalead.com">Powered by <img src="images/Exalead-logo-Carved-100.gif" alt="Logo Exalead" /></a></span>

{if $exalead_data && $exalead_data->hits}
<table class="exa_data">
  <tr>
    <td class="exa_left">
      <form method="post" action="{$smarty.server.PHP_SELF}">
        <div class="exa_form">
          <div>Rechercher</div>
          <div><input type="text" name="query" value="{$exalead_data->query->query}" size="30" /></div>
          <div><input type="submit" name="chercher" value="Chercher" /></div>
        </div>
      </form>
      {if $exalead_data}

      {if !$exalead_data->hits}
      <p class="erreur">Il n'y a aucun résultat...</p>
      {else}
      <div id="nb_results">
        {if $exalead_data->estimated}
        <div class="nb_results">Votre recherche a retourné <strong>environ {$exalead_data->nmatches}</strong> résultats.</div>
        {else}
        <div class="nb_results">Votre recherche a retourné <strong>{$exalead_data->nmatches}</strong> résultats.</div>
        {/if}
      </div>
      {/if}

      {*
      //Correction orthographique
      {if $exalead_data->spellings|@count > 0}
      <div class="exa_groupe">
        <div class="titre">Voulais-tu dire :</div>
        {foreach from=$exalead_data->spellings item="spelling"}
        <div class="exa_categorie"><a href="exalead.php?query={$spelling->query_href}">{$spelling->display}</a></div>
        {/foreach}
      </div>
      {/if}

      //Categories
      {if $exalead_data->groups|@count > 0}
      {foreach from=$exalead_data->groups item="group"}
      <div class="exa_groupe">
        <div class="titre">{$group->title} :</div>
        {foreach from=$group->categories item="categorie"}
        {if $categorie->reset_href}
        {if $categorie->count == 0}
        <div class="exa_categorie" style="background-color: {cycle values="inherit,inherit"}"><span style="text-decoration: line-through;">
            <a href="?_C={$exalead_data->query->context}/{$categorie->reset_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" /> {$categorie->display}</a></span>
        </div>
        {else}
        <div class="exa_categorie" style="background-color: {cycle values="inherit,inherit"}"><strong>{$categorie->display}</strong>
          <a href="?_C={$exalead_data->query->context}/{$categorie->reset_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
        </div>
        {/if}
        {else}
        <div class="exa_categorie" style="background-color: {cycle values="inherit,inherit"}">
          <a style="text-decoration: none;" href="?_C={$exalead_data->query->context}/{$categorie->refine_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" />{$categorie->display} ({$categorie->count})</a>
          <a href="?_C={$exalead_data->query->context}/{$categorie->exclude_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
        </div>
        {/if}
        {/foreach}
      </div>
      {/foreach}
      {/if}

      *}


      {if $exalead_data->keywords}
      <div class="exa_groupe">
        <div class="titre">Affiner la recherche par mot-clés :</div>
        {foreach from=$exalead_data->keywords item=keyword}
        {if !$keyword->is_normal()}
        {if $keyword->is_excluded()}
        <div class="exa_categorie">
          <span style="text-decoration: line-through;">
            <a href="?_C={$exalead_data->query->context}/{$keyword->reset_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" />
              {$keyword->display}</a>
          </span>
        </div>
        {else}
        <div class="exa_categorie">
          <strong>{$keyword->display}</strong>
          <a href="?_C={$exalead_data->query->context}/{$keyword->reset_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
        </div>
        {/if}
        {else}
        <div class="exa_categorie">
          <a href="?_C={$exalead_data->query->context}/{$keyword->refine_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" />
            {$keyword->display} ({$keyword->count})</a>
          <a href="?_C={$exalead_data->query->context}/{$keyword->exclude_href}&amp;_f=xml2"><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
        </div>
        {/if}
        {/foreach}
      </div>
      {/if}
    </td>
    <td class="exa_right">
      {if $exalead_data->start > 0}
      {if $exalead_data->start < 9}
      <a href=?_C={$exalead_data->query->context}&_s=0">[1-10]</a>
      {else}
      <a href="?_C={$exalead_data->query->context}&_s={$exalead_data->start-10}">[{$exalead_data->start-9}-{$exalead_data->end-9}]</a>
      {/if}
      {/if}

      Classer
      <a href="?_C={$exalead_data->query->context}/_sf=-date&amp;_f=xml2">[par date]</a>
      <a href="?_C={$exalead_data->query->context}/_sf=relevance&amp;_f=xml2">[par pertinence]</a>

      {if $exalead_data->end + 1  < $exalead_data->nhits}
      {if $exalead_data->end + 11 > $exalead_data->nhits}
      <a href="?_C={$exalead_data->query->context}&_s={$exalead_data->start+10}">[{$exalead_data->start+11}-{$exalead_data->nhits}]</a>
      {else}
      <a href="?_C={$exalead_data->query->context}&_s={$exalead_data->start+10}">[{$exalead_data->start+11}-{$exalead_data->end+11}]</a>
      {/if}
      {/if}

      {foreach from=$exalead_data->hits item=hit}
      <div class="exa_result">
        <div class="header">
          <a href="{$hit->url|regex_replace:"!(\?|\&|&amp;)PHPSESSID=.*$!":""}">{$hit->url|regex_replace:"!(\?|\&|&amp;)PHPSESSID=.*$!":""}</a>
        </div>
        {foreach from=$hit->hitgroups  item=hitgroup}
        <div class="field">
          {$hitgroup->title} :
          {if $hitgroup->hitcategories[0]->browsehref}
          <a href="?_C={$exalead_data->query->context}/{$hitgroup->hitcategories[0]->browsehref}">{$hitgroup->hitcategories[0]->display}</a>
          {else}
          {$hitgroup->hitcategories[0]->display}
          {/if}
        </div>
        {/foreach}
      </div>
      {/foreach}
      {/if}
    </td>
  </tr>
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
