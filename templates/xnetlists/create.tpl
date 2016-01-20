{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: Création d'une liste de diffusion</h1>

<p class="descr">
<strong>Note&nbsp;:</strong> les listes de diffusion sont un outil particulièrement adapté pour des
échanges entre 6 personnes ou plus (newsletter, débat interne au groupe&hellip;). En revanche, elles
s'avèrent peu praticables pour des discussions plus restreintes.  Il est alors préférable
d'utiliser <a href="{$platal->ns}alias/create">un alias</a>, à la gestion beaucoup plus souple.
</p>
<p class="descr">
D'autre part, il est impossible d'inscrire une liste de diffusion à une autre liste de diffusion.
Si tu as besoin de cette fonctionnalité, il faut alors <strong>impérativement</strong> utiliser
<a href="{$platal->ns}alias/create">un alias</a> qui, lui, est capable de regrouper plusieurs listes.
</p>
<form action='{$platal->ns}lists/create' method='post'>
  {xsrf_token_field}
  <table class="large">
    <tr>
      <th colspan='4'>Caractéristiques de la Liste</th>
    </tr>
    <tr>
      <td><strong>Adresse&nbsp;souhaitée&nbsp;:</strong></td>
      <td colspan='3'>
        <input type='text' name='liste' value='{$smarty.post.liste}' />@{$asso->mail_domain}
      </td>
    </tr>
    <tr>
      <td><strong>Sujet (bref)&nbsp;:</strong></td>
      <td colspan='3'>
        <input type='text' name='desc' size='40' value="{$smarty.post.desc}" />
      </td>
    </tr>
    <tr>
      <td style="border: 0; border-right: 1px solid gray"><strong>Propriétés&nbsp;:</strong></td>
      <td colspan='3' style="border: 0"></td>
    </tr>
    <tr>
      <td style="border: 0; border-right: 1px solid gray">
        visibilité&nbsp;:<br/><span style='font-size: xx-small;'>
        (qui peut la voir dans la liste des listes&nbsp;?)</span>
      </td>
      <td style="border: 0">
        <label><input type='radio' name='advertise' value='0'
        {if $smarty.post.advertise && $smarty.post}checked='checked'{/if} />publique<br/>
        <span style='font-size: xx-small;'>(tous les membres)</span></label>
      </td>
      <td colspan='2' style="border: 0">
        <label><input type='radio' name='advertise' value='1'
        {if !$smarty.post.advertise || !$smarty.post}checked='checked'{/if} />privée<br/>
        <span style='font-size: xx-small;'>(seuls ceux inscrits à cette liste)</span></label>
      </td>
    </tr>
    <tr>
      <td style="border: 0; border-right: 1px solid gray">
        diffusion&nbsp;:<br/><span style='font-size: xx-small;'>
        (l'envoi d'un email à cette liste est-il modéré&nbsp;?)</span>
      </td>
      <td style="border: 0">
        <label><input type='radio' name='modlevel' value='0'
        {if !$smarty.post.modlevel}checked='checked'{/if} />libre<br/><small>(non)</small></label>
      </td>
      <td style="border: 0">
        <label><input type='radio' name='modlevel' value='1'
        {if $smarty.post.modlevel eq 1}checked='checked'{/if} />restreinte<br/>
        <small>(oui, si l'expéditeur n'appartient pas à la liste)</small></label>
      </td>
      <td style="border: 0">
        <label><input type='radio' name='modlevel' value='2'
        {if $smarty.post.modlevel eq 2}checked='checked'{/if} />modérée<br/>
        <small>(oui, tout le temps)</small></label>
      </td>
    </tr>
    <tr>
      <td style="border: 0; border-right: 1px solid gray">
        inscription&nbsp;:<br/><span style='font-size: xx-small;'>
        (l'inscription à cette liste est-elle modérée&nbsp;?)</span>
      </td>
      <td style="border: 0">
        <label><input type='radio' name='inslevel' value='0'
        {if !$smarty.post.inslevel && $smarty.post}checked='checked'{/if} />libre<br/>
        <small>(non)</small></label>
      </td>
      <td colspan='2' style="border: 0">
        <label><input type='radio' name='inslevel' value='1'
        {if $smarty.post.inslevel || !$smarty.post}checked='checked'{/if} />modérée<br/>
        <small>(oui)</small></label>
      </td>
    </tr>
  </table>
  <p class="center"><input name='submit' type='submit' value="Créer !" /></p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
