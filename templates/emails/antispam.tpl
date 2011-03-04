{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{include wiki=Xorg.Antispam part=1}

<script type="text/javascript">//<![CDATA[
  {literal}
  $(function() {
      var url = '{/literal}{$globals->baseurl}/emails/antispam/{literal}';
      var msg = "Le changement de réglage de l'antispam a bien été effectué pour toutes tes redirections.";
      $(':radio[name=filter_status]').change(function() {
          var val = $(this).val();
          $(':radio[name*=filter_status_]').removeAttr('checked');
          $(':radio[name*=filter_status_]').attr('checked', function(i, v) {
              if ($(this).val() == val) {
                  return 'checked';
              }
          });
          $("#bogo-msg").successMessage(url + val, msg);
      });
  });
  $(function() {
      var url = '{/literal}{$globals->baseurl}/emails/antispam/{literal}';
      var msg = "Le changement de réglage de l'antispam a bien été effectué pour ";
      $(':radio[name*=filter_status_]').change(function() {
          var id = $(this).attr('name').replace('filter_status_', '');
          var redirection = $('#bogo_' + id).val();
          $(':radio[name=filter_status]').removeAttr('checked');
          $("#bogo-msg-mult").successMessage(url + $(this).val() + "/" + redirection, msg + redirection.replace('googleapps', 'ton compte Google Apps') + ".");
      });
  });
  {/literal}
//]]></script>
<fieldset>
  <legend><strong>Choisis ton propre réglage&nbsp;:</strong></legend>
  {if !$single_state}<span class="erreur">
    Attention, tu as actuellement un réglage spécifique pour chacune de tes redirections.
    Les modifications dans ce cadre sont globales et entraineront une uniformisation de
    l'antispam pour toutes tes redirections au niveau demandé.
  </span><br />{/if}
  <input id="s0" type="radio" name="filter_status" value="0" {if $single_state && $filter eq 0}checked="checked"{/if} />
  <label for="s0"><strong>(0) fais confiance à Polytechnique.org et utilise le réglage recommandé</strong>
  (actuellement, le niveau {#globals.mail.antispam#})</label>
  <br />
  <input id="s1" type="radio" name="filter_status" value="1" {if $single_state && $filter eq 1}checked="checked"{/if} />
  <label for="s1">(1) le filtre anti-spam n'agit pas sur tes emails</label>
  <br />
  <input id="s2" type="radio" name="filter_status" value="2" {if $single_state && $filter eq 2}checked="checked"{/if} />
  <label for="s2">(2) le filtre anti-spam marque les emails</label>
  <br />
  <input id="s3" type="radio" name="filter_status" value="3" {if $single_state && $filter eq 3}checked="checked"{/if} />
  <label for="s3">(3) le filtre anti-spam marque les emails, et élimine les spams avec des notes les plus hautes</label>
  <br />
  <input id="s4" type="radio" name="filter_status" value="4" {if $single_state && $filter eq 4}checked="checked"{/if} />
  <label for="s4">(4) le filtre anti-spam élimine les emails détectés comme spams</label>
</fieldset>

<div id="bogo-msg" style="position:absolute;"></div><br />

{if !$single_redirection}
<h1>Réglages avancés</h1>
<p>
  Si tu le souhaites, tu peux adapter le niveau de ton antispam pour chacune de tes redirections. Par exemple,
  tu peux éliminer tous les spams (niveau 4) vers ton adresse professionnelle, mais ne faire que marquer comme
  spams (niveau 2) de tels emails vers ton adresse personnelle.
</p>

<div id="bogo-msg-mult" style="position:absolute;"></div><br />

<table class="bicol">
  <tr>
    <th>Redirection</th>
    <th>Niveau recommandé</th>
    <th>Niveau 1</th>
    <th>Niveau 2</th>
    <th>Niveau 3</th>
    <th>Niveau 4</th>
  </tr>
  {foreach from=$redirections key=i item=redirection}
  <tr>
    <td class="titre">
      {$redirection.redirect|replace:'googleapps':'Compte Google Apps'}
      <input id="bogo_{$i}" type="hidden" value="{$redirection.redirect}" />
    </td>
    <td class="center">
      <input id="s0_{$i}" type="radio" name="filter_status_{$i}" value="0" {if $redirection.filter eq 0}checked="checked"{/if} />
    </td>
    <td class="center">
      <input id="s1_{$i}" type="radio" name="filter_status_{$i}" value="1" {if $redirection.filter eq 1}checked="checked"{/if} />
    </td>
    <td class="center">
      <input id="s2_{$i}" type="radio" name="filter_status_{$i}" value="2" {if $redirection.filter eq 2}checked="checked"{/if} />
    </td>
    <td class="center">
      <input id="s3_{$i}" type="radio" name="filter_status_{$i}" value="3" {if $redirection.filter eq 3}checked="checked"{/if} />
    </td>
    <td class="center">
      <input id="s4_{$i}" type="radio" name="filter_status_{$i}" value="4" {if $redirection.filter eq 4}checked="checked"{/if} />
    </td>
  </tr>
  {/foreach}
</table>

<h2>Légende</h2>
<ul>
  <li>
    <strong>Niveau recommandé&nbsp;: fais confiance à Polytechnique.org et utilise le réglage que nous recommandons</strong>
    (actuellement, le niveau {#globals.mail.antispam#}).
  </li>
  <li>
    Niveau 1&nbsp;: le filtre anti-spam n'agit pas sur tes emails.
  </li>
  <li>
    Niveau 2&nbsp;: le filtre anti-spam marque les emails.
  </li>
  <li>
    Niveau 3&nbsp;: le filtre anti-spam marque les emails, et élimine les spams avec des notes les plus hautes.
  </li>
  <li>
    Niveau 4&nbsp;: le filtre anti-spam élimine les emails détectés comme spams.
  </li>
</ul>
{/if}

{include wiki=Xorg.Antispam part=2}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
