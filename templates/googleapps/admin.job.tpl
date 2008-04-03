{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<h1>Requête de la queue Google Apps</h1>

{if $job}
<table class="bicol">
  <col width="50%" />
  <col width="50%" />
  <tr>
    <th style="text-align: left" colspan="2">Queue id: {$job.q_id}</th>
  </tr>

  <tr class="impair">
    <td class="titre">Propriétaire</td><td>{if $job.q_owner}{$job.q_owner}{else}<em>none</em>{/if}</td>
  </tr>
  <tr class="impair">
    <td class="titre">Destinataire</td><td>{if $job.q_recipient}{$job.q_recipient}{else}<em>none</em>{/if}</td>
  </tr>

  <tr class="pair">
    <td class="titre">Statut</td><td><code>{$job.p_status}</code></td>
  </tr>
  <tr class="pair">
    <td class="titre">Priorité</td><td><code>{$job.p_priority}</code></td>
  </tr>
  <tr class="pair">
    <td class="titre">Requête administrateur ?</td><td>{if $job.p_admin_request}oui{else}non{/if}</td>
  </tr>

  <tr class="impair">
    <td class="titre">Entrée dans la queue</td><td>{$job.p_entry_date}</td>
  </tr>
  <tr class="impair">
    <td class="titre">Date d'activation</td><td>{$job.p_notbefore_date}</td>
  </tr>
  <tr class="impair">
    <td class="titre">Début de traitement</td><td>{if $job.p_start_date}{$job.p_start_date}{else}<em>none</em>{/if}</td>
  </tr>
  <tr class="impair">
    <td class="titre">Fin de traitement</td><td>{if $job.p_end_date}{$job.p_end_date}{else}<em>none</em>{/if}</td>
  </tr>

  <tr class="pair">
    <td class="titre">Erreurs récupérables</td><td>{$job.r_softfail_count}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Dernière erreur récupérable</td><td><code>{if $job.r_softfail_date}{$job.r_softfail_date}{else}<em>none</em>{/if}</code></td>
  </tr>
  <tr class="pair">
    <td class="titre">Résultat du traitement</td><td><code>{if $job.r_result}{$job.r_result}{else}<em>none</em>{/if}</code></td>
  </tr>

  <tr class="impair">
    <td class="titre">Type de requête</td><td><code>{$job.j_type}</code></td>
  </tr>
  <tr class="impair">
    <td class="titre">Paramètres</td><td><pre>{$job.decoded_parameters}</pre></td>
  </tr>
</table>
{else}
<p><strong>Aucune requête n'a été trouvée.</strong></p>
{/if}

<p>Retourner à la <a href="admin/googleapps">page d'administration de Google Apps</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
