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
        $Id: search.tpl,v 1.24 2004-11-04 13:50:45 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $formulaire==0 and !$error}
  {if !$advanced}
  {include file='search.quick.form.tpl'}
  {else}
  {include file=search.adv.links.tpl do_title=1}
  {/if}

  <h1 class='right'>
    {if $nb_resultats_total==0}Aucune{else}{$nb_resultats_total}{/if} réponse{if $nb_resultats_total>1}s{/if}.
  </h1>

  <div class="contact-list" style="clear:both">
    {section name=resultat loop=$resultats}
      {if !$resultats[resultat].inscrit || $resultats[resultat].decede}<div class='grayed'>{/if}
      {if $resultats[resultat].contact!=""}
        {assign var="show_action" value="retirer"}
      {else}
        {assign var="show_action" value="ajouter"}
      {/if}
      {include file=include/minifiche.tpl c=$resultats[resultat] show_action=$show_action inscrit=$resultats[resultat].inscrit}
      {if !$resultats[resultat].inscrit || $resultats[resultat].decede}</div>{/if}
    {/section}
  </div>

  {if $perpage < $nb_resultats_total}
  <p>
    {if $offset!=0}
    <a href="{$smarty.server.PHP_SELF}?with_soundex={$with_soundex}&amp;rechercher=1&amp;{$url_args}&amp;offset={$offset-$perpage}&amp;mod_date_sort={$mod_date_sort}">Précédent</a>
    &nbsp;
    {/if}
    {section name=offset loop=$offsets}
      {if $offset!=$smarty.section.offset.index*$perpage}
      <a href="{$smarty.server.PHP_SELF}?with_soundex={$with_soundex}&amp;rechercher=1&amp;{$url_args}&amp;offset={$smarty.section.offset.index*$perpage}&amp;mod_date_sort={$mod_date_sort}">{$smarty.section.offset.index+1}</a>
      {else}
      <span class="error">{$smarty.section.offset.index+1}</span>
      {/if}
      &nbsp;
    {/section}
    {if $offset < $nb_resultats_total-$perpage}
    <a href="{$smarty.server.PHP_SELF}?with_soundex={$with_soundex}&amp;rechercher=1&amp;{$url_args}&amp;offset={$offset+$perpage}&amp;mod_date_sort={$mod_date_sort}">Suivant</a>
    &nbsp;
    {/if}
  </p>
  {/if}

  {min_auth level='cookie'}
  <br />
  <h1>Astuce</h1>
  
  {if $advanced}{include file=search.adv.links.tpl do_title=0}{/if}
  
  <p>Si tu survoles une fiche, tu sauras quand elle a été mise à jour la dernière fois !</p>
  {/min_auth}
{else}
  {if $advanced}
  {include file="search.adv.form.tpl"}
  {else}
  {include file="search.quick.tpl"}
  {/if}
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
