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

<h1>Compte Google Apps</h1>

{if $account}
{assign var=a value=$account}
<table class="bicol">
  <col width="50%" />
  <col width="50%" />
  <tr>
    <th colspan="2" style="text-align: left">
      <div style="float: left; text-align: left">
        Compte = <a href="admin/user/{$a->g_account_name}">{$a->g_account_name}</a>
      </div>
      <div style="float: right; text-align: right">
        Google id = {$a->g_account_id}<br />
        Plat/al id = {$user}
      </div>
    </th>
  </tr>

  <tr class="impair">
    <td class="titre">Statut du compte</td>
    <td>
      <strong>{$a->g_status}</strong>
      {if $admin_account}<br /><strong>Compte administrateur de Google Apps</strong>{/if}
    </td>
  </tr>
  {if $a->suspended()}
  <tr class="impair">
    <td class="titre">Raison de suspension</td><td>{$a->g_suspension}</td>
  </tr>
  {/if}
  <tr class="impair">
    <td class="titre">Mots de passes synchronisés</td>
    <td>
      {if $a->sync_password}
        oui (<a href="admin/googleapps/user/{$a->g_account_name}/forcesync">lancer une synchronisation</a>)
      {else}non{/if}
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Redirection des mails</td><td>{if $googleapps_storage}activée{else}désactivee{/if}</td>
  </tr>

  <tr class="pair">
    <td class="titre">Date de création</td><td>{$a->r_creation|date_format:"%Y-%m-%d"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Dernière connexion</td><td>{$a->r_last_login|date_format:"%Y-%m-%d"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Dernière utilisation du webmail</td><td>{$a->r_last_webmail|date_format:"%Y-%m-%d"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Utilisation du quota mail</td><td>{$a->r_disk_usage/1024/1024|string_format:"%.2f"}MB</td>
  </tr>
</table><br />

<table class="bicol" style="text-align: center">
  <tr>
    <th colspan="4" style="text-align: left">Requêtes en attente</th>
  </tr>
  <tr>
    <th>qid</th>
    <th>date</th>
    <th>statut</th>
    <th>type</th>
  </tr>
  {iterate from=$requests item=r}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="admin/googleapps/job/{$r.q_id}">{$r.q_id}</a></td>
    <td>{$r.p_entry_date|date_format:"%Y-%m-%d %H:%M"}</td>
    <td>{$r.p_status}</td>
    <td>{$r.j_type}</td>
  </tr>
  {/iterate}
</table>
{else}
<p><strong>Aucun utilisateur n'a été trouvé.</strong></p>
{/if}

<p>Retourner à la <a href="admin/googleapps">page d'administration de Google Apps</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
