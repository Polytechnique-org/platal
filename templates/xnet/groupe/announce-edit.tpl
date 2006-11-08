{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<script type="text/javascript">
{literal}
function visibilityChange(box)
{
    var state = (box.checked ? 'none' : 'normal');
    document.getElementById('promo_titre').style.display = state;
    document.getElementById('promo_min').style.display = state;
    document.getElementById('promo_max').style.display = state;
    document.getElementById('promo_desc').style.display = state;
}
{/literal}
</script>
<h1>{$asso.nom} : Edition d'une annonce</h1>

{if $art.texte}
<div>
{include file="xnet/groupe/form.announce.tpl" admin=true}
<br />
</div>
{/if}

<form method="post" action="{$platal->ns}announce/{if $new}new{else}edit/{$art.id}{/if}">
<div>
  <table class="bicol">
    <tr>
      <th colspan="2">Editer une annonce</th>
    </tr>
    <tr class="pair">
      <td class="titre">Titre :</td>
      <td><input type="text" name="titre" value="{$art.titre}" size="50" maxlength="200" /></td>
    </tr>
    <tr>
      <td class="titre">Contenu de l'annonce :</td>
      <td>
        <small>
          Le contenu est destiné à recevoir la descriptioin de ce qui est annoncé.
          Il faut éviter d'y mettre des adresses mails ou web (surtout si l'annonce est publique),
          qui devront être placées dans la section "contacts".
        </small>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <textarea name="texte" rows="10" cols="60" onfocus="update_texte_count(this.form)">{$art.texte}</textarea>
        <br />
        <script type="text/javascript">
          var form_propose_texte = false;
          {literal}
          function update_texte_count(f) {
            if (!form_propose_texte && f) form_propose_texte = f;
            form_propose_texte.texte_count.value=form_propose_texte.texte.value.length;
            setTimeout("update_texte_count(0)", 100);
          }
          {/literal}
        </script>
        <small>
          Essaie de faire un <strong>texte court</strong>, une annonce ne doit pas excéder 600 caractères soit une dizaine de ligne.
          Tu en es déjà à <input type='text' name='texte_count' size="4"/> caractères.
          Si tu veux proposer cette annonce pour la Lettre Mensuelle, il faut te limiter à 8 lignes.
        </small>
      </td>
    </tr>
    <tr style="border-top: 1px solid gray">
      <td class="titre">Contacts :</td>
      <td>
        <small>
          La section "contacts" sert à noter les informations telles que les adresses mails de contact, les sites web.
          Elle n'est accessible qu'aux personnes authentifiées. Si l'annonce est attachée à une événement, un lien vers
          la page d'inscription est automatiquement ajouté.
        </small>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <textarea cols="60" rows="6" name='contacts'>{$art.contacts}</textarea>
      </td>
    </tr>
  </table>
  <br />

  <table class="tinybicol">
    <tr>
      <td class="titre">Date de péremption :</td>
      <td>
        <select name="peremption">
          {$select|smarty:nodefaults}
        </select>
      </td>
    </tr>
    {if $events}
    <tr>
      <td class="titre">Attacher à un événement :</td>
      <td>
        <select name="event">
          <option value="" {if !$art.event}selected="selected"{/if}>-- Aucun --</option>
          {iterate item=evt from=$events}
          <option value="{$evt.short_name|default:$evt.eid}" 
            {if $art.event eq $evt.short_name|default:$evt.eid}selected="selected"{/if}>
            {$evt.intitule}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    {/if}
    <tr>
      <td class="titre">Visibilité :</td>
      <td>
        <input type="checkbox" name="public" {if $art.public}checked="checked"{/if} onchange="visibilityChange(this)" />
        Rendre cette annonce publique
      </td>
    </tr>
    <tr id="promo_titre" {if $art.public}style="display: none"{/if}>
      <th colspan="2">Promotions cibles</th>
    </tr>
    <tr id="promo_min"  {if $art.public}style="display: none"{/if}>
      <td class="titre">Promotion minimale :</td>
      <td>
        <input type="text" size="4" maxlength="4" name="promo_min" value="{$art.promo_min|default:0}" />
        incluse*  (ex : 1980)
      </td>
    </tr>
    <tr id="promo_max"  {if $art.public}style="display: none"{/if}>
      <td class="titre">Promotion minimale :</td>
      <td>
        <input type="text" size="4" maxlength="4" name="promo_max" value="{$art.promo_max|default:0}" />
        incluse*  (ex : 2000)
      </td>
    </tr>
    <tr class="pair" id="promo_desc"  {if $art.public}style="display: none"{/if}>
      <td colspan="2">
        <small>* 0 signifie qu'il n'y a pas de limite</small>
      </td>
    </tr>
    {if $new}
    <tr>
      <th colspan="2">Demandes de publication</th>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="xorg" {if $art.xorg}checked="checked"{/if} />
        sur la page d'accueil de Polytechnique.org
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="nl" {if $art.nl}checked="checked"{/if} />
        dans la Lettre Mensuelle de Polytechnique.org
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        <small>Les demandes de publication sont soumises à validation par l'équipe
        de Polytechnique.org</small>
      </td>
    </tr>
    {/if}
  </table>

  <div class="center">
    {if $art.id}
    <input type="hidden" name="id" value="{$art.id}" />
    {/if}
    <input type="submit" name="valid" value="Visualiser" /><br />
    {if $art.texte}
    <input type="submit" name="valid" value="Enregistrer" /> 
    {if !$new}
    <input type="submit" name="valid" value="Annuler" />
    {/if}
    {/if}
  </div>
</div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
