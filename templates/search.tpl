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

{dynamic}

{if $formulaire==0 and !$xorg_error->errs|count}
  {if !$advanced}
  {include file='search.quick.form.tpl'}
  {else}
  {include file=search.adv.links.tpl do_title=1}
  {/if}

  <h1 class='right'>
    {if $nb_resultats_total==0}Aucune{else}{$nb_resultats_total}{/if} réponse{if $nb_resultats_total>1}s{/if}.
  </h1>

  <div class="contact-list" style="clear:both">
    {capture name=list}
    {section name=resultat loop=$resultats}
        {if $resultats[resultat].contact || $resultats[resultat].watch}
        {assign var="show_action" value="retirer"}
      {else}
        {assign var="show_action" value="ajouter"}
      {/if}
      {include file=include/minifiche.tpl c=$resultats[resultat] show_action=$show_action}
    {/section}
    {/capture}
    {$smarty.capture.list|smarty:nodefaults}
  </div>

  {if $perpage < $nb_resultats_total}
  <p>
    {if $offset!=0}
    <a href="{$smarty.server.PHP_SELF}?{$url_args}&amp;offset={$offset-$perpage}">Précédent</a>
    &nbsp;
    {/if}
    {section name=offset loop=$offsets}
      {if $offset!=$smarty.section.offset.index*$perpage}
      <a href="{$smarty.server.PHP_SELF}?{$url_args}&amp;offset={$smarty.section.offset.index*$perpage}">{$smarty.section.offset.index+1}</a>
      {else}
      <span class="erreur">{$smarty.section.offset.index+1}</span>
      {/if}
      &nbsp;
    {/section}
    {if $offset < $nb_resultats_total-$perpage}
    <a href="{$smarty.server.PHP_SELF}?{$url_args}&amp;offset={$offset+$perpage}">Suivant</a>
    &nbsp;
    {/if}
  </p>
  {/if}

  {min_auth level='cookie'}
  <br />
  {if $smarty.capture.list|smarty:nodefaults|display_lines > 20}
  {if $advanced}
  {include file=search.adv.links.tpl do_title=1}
  {else}
  {include file='search.quick.form.tpl'}
  {/if}
  {/if}
  
  <p>
  <strong>Astuce:</strong>
  Si tu survoles une fiche, tu sauras quand elle a été mise à jour la dernière fois !</p>
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
