{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Entreprises</h1>

{if $jobs}
<p>
  Liste des entreprises correspondant à ta recherche&nbsp;:
  <ul>
    {foreach from=$jobs item=job}
    <li>{$job.name}{if $job.acronym} ({$job.acronym}){/if}&nbsp;
      <a href="admin/jobs/{$job.id}">{icon name="page_edit" title="Éditer"}</a></li>
    {/foreach}
  </ul>
{/if}

{if $selectedJob}
<form action="admin/jobs/{$selectedJob.id}" method="post">
{xsrf_token_field}
  <table class="bicol">
    <tr>
      <th colspan="2">Entreprise n° {$selectedJob.id}</th>
    </tr>
    <tr>
      <td>Nom</td>
      <td><input type="text" name="name" value="{$selectedJob.name}" /></td>
    </tr>
    <tr>
      <td>Acronyme</td>
      <td><input type="text" name="acronym" value="{$selectedJob.acronym}" /></td>
    </tr>
    <tr>
      <td>Page web</td>
      <td><input type="text" name="url" value="{$selectedJob.url}" /></td>
    </tr>
    <tr>
      <td>Adresse email</td>
      <td><input type="text" name="email" value="{$selectedJob.email}" /></td>
    </tr>
    <tr>
      <td>Code SIREN</td>
      <td><input type="text" name="SIREN" value="{$selectedJob.SIREN}" /></td>
    </tr>
    <tr>
      <td>Code NAF</td>
      <td><input type="text" name="NAF_code" value="{$selectedJob.NAF_code}" /></td>
    </tr>
    <tr>
      <td>Code AX</td>
      <td><input type="text" name="AX_code" value="{$selectedJob.AX_code}" /></td>
    </tr>
    <tr>
      <td>Identifiant de la holding</td>
      <td><input type="text" name="holdingId" value="{$selectedJob.holdingId}" /></td>
    </tr>
    <tr>
      <td>Nom de la holding</td>
      <td>{$selectedJob.holdingName}</td>
    </tr>
    <tr>
      <td>Acronyme de la holding</td>
      <td>{$selectedJob.holdingAcronym}</td>
    </tr>
    <tr>
      <td>Adresse</td>
      <td><textarea cols="30" rows="4" name="address">{$selectedJob.address}</textarea></td>
    </tr>
    <tr>
      <td>Téléphone</td>
      <td><input type="text" name="tel" value="{$selectedJob.tel}" /></td>
    </tr>
    <tr>
      <td>Fax</td>
      <td><input type="text" name="fax" value="{$selectedJob.fax}" /></td>
    </tr>
    <tr>
      <td>Remplacer par l'entreprise n°</td>
      <td><input type="text" name="newJobId" /></td>
    </tr>
  </table>
  <p>
    Confirmation du remplacement de cette entreprise&nbsp;:&nbsp;
    <input type="checkbox" name="change" /><br />
    <input type="submit" name="edit" value="Éditer" />
  </p>
</form>
{/if}

<form action="" method="get">
  <p>
    Nom ou acronyme de l'entreprise&nbsp;:
    <input type="text" name="job" value="{$askedJob}" /><br />
    <input type="submit" name="search" value="Rechercher" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
