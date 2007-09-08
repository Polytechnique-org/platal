{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

{assign var=jobid value="job_`$i`"}
{assign var=jobpref value="job[`$i`]"}
<table class="bicol" cellspacing="0" cellpadding="0" summary="Entreprise n°{$i+1}">
  <tr>
    <th colspan="2" style="text-align: right">
      <div class="flags" style="float: left; text-align: left">
        {include file="include/flags.radio.tpl" notable=true display="div" name="`$jobpref`[pub]" value=$job.pub}
      </div>
      Entreprise n°{$i+1}&nbsp;:
      <input type="text" size="35" maxlength="100" name="{$jobpref}[name]" value="{$job.name}" />
    </th>
  </tr>
  <tr>
    <td class="titre">Page Web</td>
    <td><input type="text" size="35" maxlength="255" name="{$jobpref}[web]" value="{$job.web}" /></td>
  </tr>
  <tr>
    <td class="titre">Secteur d'activité</td>
    <td>
      <select name="{$jobpref}[secteur]" onchange="this.form.submit();">
        {select_secteur secteur=$job.secteur}
      </select>
    </td>
  </tr>
  <tr>
    <td class="titre">Sous-Secteur d'activité</td>
    <td>
      <select name="{$jobpref}[ss_secteur]">
        {select_ss_secteur secteur=$job.secteur ss_secteur=$job.ss_secteur}
      </select>
    </td> 
  </tr>
  <tr>
    <td class="titre">Poste occupé</td>
    <td>
      <input type="text" size="35" maxlength="120" name="{$jobpref}[poste]" value="{$job.poste}" />
    </td>
  </tr>
  <tr>
    <td class="titre">Fonction occupée</td>
    <td>
      <select name="{$jobpref}[fonction]">
        {select_fonction fonction=$job.fonction}
      </select>
    </td>
  </tr>
  <tr class="pair">
    <td colspan="2">
      <div style="float: left">
        <div class="flags" style="float: right">
          {include file="include/flags.radio.tpl" name="`$jobpref`[adr][pub]" val=$job.adr.pub display="div"}
        </div>
        <div class="titre">Adresse</div>
        <div style="margin-top: 20px; clear: both">
          {include file="geoloc/form.address.tpl" name="`$jobpref`[adr]" id="`$jobpref`_adr" adr=$job.adr}
        </div>
      </div>
      <div style="float: right; width: 50%">
        <div class="flags" style="float: right">
          {include file="include/flags.radio.tpl" name="`$jobpref`[tel_pub]" val=$job.tel_pub display="div"}
        </div>
        <span class="titre">Téléphone</span>
        <table style="clear: both">
          <tr>
            <td>Bureau&nbsp;:</td>
            <td><input type="text" size="18" maxlength="18" name="{$jobpref}[tel_office]" value="{$job.tel_office}" /></td>
          </tr>
          <tr>
            <td>Fax&nbsp;:</td>
            <td><input type="text" size="18" maxlength="18" name="{$jobpref}[tel_fax]" value="{$job.tel_fax}" /></td>
          </tr>
          <tr>
            <td>Mobile&nbsp;:</td>
            <td><input type="text" size="18" maxlength="18" name="{$jobpref}[tel_mobile]" value="{$job.tel_mobile}" /></td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td colspan="2">
      <div class="flags" style="float: right">
        {include file="include/flags.radio.tpl" name="`$jobpref`[email_pub]" val=$job.mail_pub display="div"}
      </div>
      <span class="titre">E-mail&nbsp;:</span>
      <input type="text" size="30" maxlength="60" name="{$jobpref}[email]" value="{$job.email}" />
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
