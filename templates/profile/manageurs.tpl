{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<div> {icon name=information title="Afficher ma fiche Manageurs"}Tu peux consulter ta <a href="http://www.manageurs.com/anciens_mesCV.php">fiche professionnelle</a> sur Manageurs.com.
</div>
<table class="bicol" id="manageurs_profile" style="margin-bottom: 1em">
  <tr>
    <th colspan='2'>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesManageurs" checked="checked" disabled="disabled" />
        {icon name="vcard" title="Manageurs"}
      </div>
      Profil Manageurs.com
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Intitulé du profil&nbsp;:</span>
    </td>
    <td>
      <input type="text" name="manageurs_title" value="{$manageurs_title}" size="49" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Année de début d'activité professionnelle&nbsp;:</span>
    </td>
    <td>
      <input type="text" name="manageurs_entry_year" value="{$manageurs_entry_year}" size="4" maxlength="4" />
      <small>(par exemple&nbsp;: 2008)</small>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Projet professionnel</span>
    </td>
    <td>
      <textarea rows="8" cols="60" name="manageurs_project">{$manageurs_project}</textarea>
    </td>
  </tr>
</table>
<table class="bicol" id="manageurs_prefs" style="magin-bottom: 1em">
  <tr>
    <th colspan='2'>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesManageurs" checked="checked" disabled="disabled" />
        {icon name="vcard" title="Manageurs"}
      </div>
      Préférences Manageurs.com
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Mon profil doit être&nbsp;:</span>
    </td>
    <td>
      <div>
        <label>
          <input type="radio" name="manageurs_anonymity" value="0"
            {if !$manageurs_anonymity}checked="checked"{/if} />
          nominatif
        </label>
        <label>
          <input type="radio" name="manageurs_anonymity" value="1"
            {if $manageurs_anonymity}checked="checked"{/if} />
          anonyme
        </label>
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Visibilité auprès des entreprises&nbsp;:</span>
    </td>
    <td>
      <div>
        <label>
          <input type="radio" name="manageurs_visibility" value="1"
            {if $manageurs_visibility="visible"}checked="checked"{/if} />
          Toutes les entreprises peuvent voir mon profil et me contacter.
        </label>
        <br />
        <!-- TODO: implement a list of firms that are blacklisted
        <label>
          <input type="radio" name="manageurs_visibility" value="1"
            {if $manageurs_visibility="visible_exceptions"}checked="checked"{/if} />
          Les entreprises peuvent voir mon profil à l'exception de :
        </label>
        <br />
        -->
        <label>
          <input type="radio" name="manageurs_visibility" value="0"
            {if $manageurs_visibility="blocked"}checked="checked"{/if} />
          Les entreprises ne peuvent pas voir mon profil.
        </label>
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Email de contact</span>
      <small>Les offres d'entreprises, les messages de diplômés et la communication Manageurs seront envoyés à cette adresse.</small>
    </td>
    <td>
      <input type="text" name="manageurs_email" value="{$manageurs_email}" size="40" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Manageurs peut m'envoyer des emails&nbsp;</span>
    </td>
    <td>
      <div>
        <label>
          <input type="checkbox" name="manageurs_novelty" {if $manageurs_novelty}checked="checked"{/if} />
          sur les nouvelles fonctionnalités du site (ponctuellement).
        </label>
        <br />
        <label>
          <input type="checkbox" name="manageurs_nl" {if $manageurs_nl}checked="checked"{/if} />
          dans une newsletter mensuelle (agenda, news, articles de fond…).
        </label>
        <br />
        <label>
          <input type="checkbox" name="manageurs_survey" {if $manageurs_survey}checked="checked"{/if} />
          pour des sondages pour améliorer le site et mieux connaître la communauté.
        </label>
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Offres personnalisées&nbsp;:</span>
    </td>
    <td>
      <div>
        <label>
          <input type="radio" name="manageurs_push" value="0"
            {if $manageurs_push="unique"}checked="checked"{/if} />
          recevoir un email par offre.
        </label>
        <br />
        <label>
          <input type="radio" name="manageurs_push" value="1"
            {if $manageurs_push="weekly"}checked="checked"{/if} />
          recevoir une compilation des offres 1 fois par semaine.
        </label>
        <br />
        <label>
          <input type="radio" name="manageurs_push" value="2"
            {if $manageurs_push="never"}checked="checked"{/if} />
          ne jamais recevoir d'email.
        </label>
      </div>
    </td>
  </tr>
  <tr>
    <td>
     <span class="titre">Partage avec les autres diplômés&nbsp;:</span>
    </td>
    <td>
      <div> 
        <label>
          <input type="radio" name="manageurs_network" value="1"
            {if $manageurs_network}checked="checked"{/if} />
          partage de mon profil et accès aux profils partagés.
        </label>
        <br />
        <label>
          <input type="radio" name="manageurs_network" value="0"
            {if !$manageurs_network}checked="checked"{/if} />
          pas de partage de mon profil et pas d'accès aux profils partagés.
        </label>
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
