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

<h1>Gestion des utilisateurs</h1>

<form method="post" action="admin/googleapps/user">
<table class="tinybicol" cellspacing="0" cellpadding="2">
  <tr>
    <th>Administrer un utilisateur (Google Apps)</th>
  </tr>
  <tr>
    <td class="center"><input type="text" name="login" size="40" maxlength="255" value="" /></td>
  </tr>
  <tr>
    <td class="center"><input type="submit" value="Valider" /></td>
  </tr>
</table>
</form>

<h1>Queue de requêtes vers Google Apps</h1>

<p>
  Requête nécessitant <a href="Equipe/Infra-GoogleApps#admin-cli">l'intervention manuelle</a>
  d'un administrateur Google Apps {if $googleapps_admin}(tu en es un){/if}&nbsp;:
</p>
<table class="bicol" style="text-align: center">
  <tr>
    <th>qid</th>
    <th>date</th>
    <th>recipient</th>
    <th>type</th>
    <th>parameters</th>
  </tr>
  {foreach from=$admin_requests item=r}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="admin/googleapps/job/{$r.q_id}">{$r.q_id}</a></td>
    <td>{$r.p_entry_date|date_format:"%Y-%m-%d %H:%M"}</td>
    <td>{if $r.alias}<a href="admin/googleapps/user/{$r.alias}">{$r.alias}</a>{else}-{/if}</td>
    <td>{$r.j_type}</td>
    <td>{$r.parameters}</td>
  </tr>
  {/foreach}
</table>

<p>
  Requêtes ayant échoué récemment (plus d'information dans la <a href="Equipe/Infra-GoogleApps">documentation</a>) :
</p>
<table class="bicol" style="text-align: center">
  <tr>
    <th>qid</th>
    <th>date</th>
    <th>recipient</th>
    <th>type</th>
    <th>reason</th>
    <th></th>
  </tr>
  {iterate from=$failed_requests item=r}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="admin/googleapps/job/{$r.q_id}">{$r.q_id}</a></td>
    <td>{$r.p_entry_date|date_format:"%Y-%m-%d %H:%M"}</td>
    <td>{if $r.alias}<a href="admin/googleapps/user/{$r.alias}">{$r.alias}</a>{else}-{/if}</td>
    <td>{$r.j_type}</td>
    <td><code>{$r.r_result}</code></td>
    <td><a href="admin/googleapps/ack/{$r.q_id}">{icon name=cross title="Retirer cet échec"}</a></td>
  </tr>
  {/iterate}
</table>

<h1>Statistiques d'utilisation de Google Apps</h1>

<div style="text-align: center">
  <img src="images/googleapps/activity-monthly.png" alt="Activité Google Apps - 1 mois" width="500 height="250" />
  <br /><em>Utilisation des comptes Google Apps sur les 31 derniers jours</em>.
</div>

<div style="text-align: center">
  <img src="images/googleapps/activity-yearly.png" alt="Activité Google Apps - 1 an" width="500 height="250" />
  <br /><em>Utilisation disque des comptes sur les 12 derniers mois</em>.
</div>

<div style="text-align: center">
  <img src="images/googleapps/usage-monthly.png" alt="Utilisation disque - 1 mois" width="500 height="250" />
  <br /><em>Utilisation des comptes Google Apps sur les 31 derniers jours</em>.
</div>

<div style="text-align: center">
  <img src="images/googleapps/usage-yearly.png" alt="Utilisation disque - 1 an" width="500 height="250" />
  <br /><em>Utilisation disque des comptes sur les 12 derniers mois</em>.
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
