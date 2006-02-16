{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<h1>{$asso.nom} :
Evénements
</h1>

{if !$logged}
  <p class="descr">
     Aucune manifestation publique n'a été saisie par ce groupe pour l'instant...
  </p>
{else}

  {if $admin}
  <p class="center">
  [<a href="evt-modif.php?add=1">Annoncer un nouvel événement</a>]
  </p>
  {/if}

  {if $nb_evt eq 0}

  <p class="descr">
    Aucun événement n'a été référencé par les animateurs du groupe.
  </p>

  {else}

  <form action="{$smarty.server.PHP_SELF}" method="post">
  {foreach from=$evenements item=e}
  <table class="tiny" cellspacing="0" cellpadding="0">
    <tr {popup caption=$e.intitule text=$e.descriptif}>
      <th colspan="2">
        {$e.intitule}
        {if $admin}
        <a href="evt-modif.php?mod=1&amp;eid={$e.eid}"><img src="{rel}/images/profil.png" title="Edition de l'événement" alt="Edition de l'événement" /></a>
        <a href="evt-modif.php?sup=1&amp;eid={$e.eid}" onclick="return confirm('Supprimer l\'événement effacera la liste des inscrits et des paiements.\n Es-tu sûr de vouloir supprimer l\'événement ?')"><img src="{rel}/images/del.png" alt="Suppression de {$e.intitule}" title="Suppression de {$e.intitule}" /></a>
        {/if}
      </th>
    </tr>
    <tr {popup caption=$e.intitule text=$e.descriptif}>
      <td class="titre">date :</td>
      <td>
        {if $e.fin and $e.fin neq $e.debut}
          {if $e.debut_day eq $e.fin_day}
            le {$e.debut|date_format:"%d %B %Y"} de {$e.debut|date_format:"%H:%M"} à {$e.fin|date_format:"%H:%M"}
          {else}
            du {$e.debut|date_format:"%d %B %Y à %H:%M"}<br />
            au {$e.fin|date_format:"%d %B %Y à %H:%M"}
          {/if}
        {else}
          le {$e.debut|date_format:"%d %B %Y à %H:%M"}
        {/if}
      </td>
    </tr>
    <tr {popup caption=$e.intitule text=$e.descriptif}>
      <td class="titre">annonceur :</td>
      <td>
        <a href='https://polytechnique.org/fiche.php?user={$e.alias}' class='popup2'>{$e.prenom} {$e.nom} ({$e.promo})</a>
      </td>
    </tr>
    {if $admin || $e.show_participants}
    <tr {popup caption=$e.intitule text=$e.descriptif}>
      <td class="titre" colspan="2">
        <a href="evt-admin.php?eid={$e.eid}">
          Liste des participants
          <img src="{rel}/images/loupe.gif" title="Liste des participants" alt="Liste des participants" />
        </a>
      </td>
    </tr>
    {/if}
    {assign var="montant" value=0}
    {if !$e.membres_only or $is_member or $e.inscrit}
      {if $e.inscr_open}
        <tr>
          <td colspan="2">
            Je viendrai...
            <input type="hidden" name="evt_{counter}" value="{$e.eid}" />
          </td>
        </tr>
      {/if}
      {iterate from=$e.moments item=m}
        {assign var="montant" value=$montant+$m.montant*$m.nb}
        <tr {if $m.titre or $m.details or $m.montant}{popup caption="`$m.titre` (`$m.montant` &#x20AC;)"  text=" `$m.details` "}{/if}>
          <td>{$m.titre}</td>
          <td>
            {if $e.inscr_open}
              <input type="radio" name="moment{$e.eid}_{$m.item_id}" value="0"
              {if !$m.nb}checked="checked"{/if}/>non
              <input type="radio" name="moment{$e.eid}_{$m.item_id}" value="1"
              {if $m.nb eq 1}checked="checked"{/if}/>seul<br />
              <input type="radio" name="moment{$e.eid}_{$m.item_id}" value="2"
              {if $m.nb > 1}checked="checked"{/if}/>avec <input size="2" name="personnes{$e.eid}_{$m.item_id}" value="{if $m.nb > 1}{math equation="x - y" x=$m.nb y=1}{else}1{/if}"/> personnes
            {else}
              {if !$m.nb}
                Je ne viendrai pas.
              {elseif $m.nb eq 1}
                Je viendrai seul.
              {else}
                Je viendrai avec {$m.nb} personne{if $m.nb > 2}s{/if}
              {/if}
            {/if}
          </td>
        </tr>
      {/iterate}
      {if $e.deadline_inscription}
        <tr>
          <td colspan="2" class="center">
            {if $e.inscr_open}
              dernières inscriptions
              le {$e.deadline_inscription|date_format:"%d %B %Y"}
            {else}
              Inscriptions closes.
            {/if}
          </td>
        </tr>
      {/if}
      {if $montant > 0 || $e.paid > 0}
      <tr>
        <td colspan="2" {if $montant > $e.paid}class="erreur"{/if}>
          Tu dois payer {$montant|replace:'.':','}&nbsp;&euro;{if $e.paid > 0}, et tu as déjà payé {$e.paid|replace:'.':','}&nbsp;&euro;{/if}.
          {if $e.paiement_id && $montant > $e.paid}
            [<a href="https://www.polytechnique.org/paiement/?ref={$e.paiement_id}&amp;montant={math equation="x - y" x=$montant y=$e.paid}">Payer en ligne</a>]
          {/if}
        </td>
      </tr>
      {/if}
    {/if}
  </table>
  {if (!$e.membres_only or $is_member or $e.inscrit) and $e.inscr_open}
    <p style="text-align:center">
      <input type="submit" value="Valider mes inscriptions" />
    </p>
  {else}
    <p>&nbsp;</p>
  {/if}
  {/foreach}

  <div>
    <input type="hidden" name="ins" />
  </div>
  </form>
  {/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
