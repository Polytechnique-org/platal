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


{if $formulaire==0 and !$xorg_error->errs|count}
  {if !$advanced}
  {include file='search.quick.form.tpl' show_js=1}
  {else}
  {include file=search.adv.links.tpl do_title=1 with_soundex=$with_soundex}
  {/if}

  <h1 class='right'>
    {if $search_results_nb==0}Aucune{else}{$search_results_nb}{/if} réponse{if $search_results_nb>1}s{/if}.
  </h1>

  {if $search_results_nb > 1}
  <div>
    Trier par :
    {foreach from=$search_order_link item=tri}
    [<a href='{$tri.url}'>
    {if $tri.asc or $tri.desc}<strong>{/if}
    {$tri.text}
    {if  $tri.asc}<img src='{rel}/images/up.png' />{/if}
    {if $tri.desc}<img src='{rel}/images/dn.png' />{/if}
    {if $tri.asc or $tri.desc}</strong>{/if}
    </a>]
    {/foreach}
  </div>
  {/if}

  <div class="contact-list" style="clear:both">
    {capture name=list}
    {iterate item=res from=$search_results}
      {if $res.contact || $res.watch}
        {include file=include/minifiche.tpl c=$res show_action="retirer"}
      {else}
        {include file=include/minifiche.tpl c=$res show_action="ajouter"}
      {/if}
    {/iterate}
    {/capture}
    {$smarty.capture.list|smarty:nodefaults}
  </div>

  {if $search_pages_nb > 1}
  <p>
    {foreach from=$search_pages_link item=l}
    {if $l.i eq $search_page}
    <span class="erreur">{$l.text}</span>
    {else}
    <a href="{$l.u}">{$l.text}</a>
    {/if}
    {/foreach}
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


{* vim:set et sw=2 sts=2 sws=2: *}
