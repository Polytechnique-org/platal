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
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

    <title>Les associations polytechniciennes</title>
    <meta name="description" content="Les associations polytechniciennes" />
    <meta name="keywords" content="Ecole polytechnique, associations polytechniciennes, groupes X, binets" />
    <link rel="stylesheet" type="text/css" href="{rel}/css/xnet.css" media="screen" />
    <link rel="icon" type="image/png" href="{rel}/images/favicon.png" />

    <link rel="bookmark" href="http://www.polytechnique.fr/"        title="| École polytechnique" />
    <link rel="bookmark" href="http://www.polytechnique.edu/"       title="| Institutionnal site" />
    <link rel="bookmark" href="http://www.fondationx.org/"          title="| FX" />
    <link rel="bookmark" href="http://www.polytechniciens.com/"     title="| AX" />
    <link rel="bookmark" href="http://www.polytechnique.org/"       title="| Polytechnique.org" />
    <link rel="bookmark" href="http://www.polytechnique.fr/eleves/" title="| Site d'élèves" />

    {foreach from=$xorg_css item=css}
    <link rel="stylesheet" type="text/css" href="{rel}/{$css}" />
    {/foreach}
    {foreach from=$xorg_js item=js}
    <script type="text/javascript" src="{rel}/{$js}"></script>
    {/foreach}
    <script type="text/javascript" src="{rel}/javascript/overlib.js"></script>
    
    {if $xorg_extra_header}
    {$xorg_extra_header|smarty:nodefaults}
    {/if}

    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>
    {include file=skin/common.devel.tpl}
    {include file=skin/common.bandeau.tpl}

    <table id="layout" cellspacing="0" cellpadding="0">
      {if $xnet_type}
      <tr id="top">
        <td>
          <a href="{rel}/"><img src="{rel}/images/asso-montants.png" alt="Logo Assos" /></a>
        </td>
        <td>
          <img src="{rel}/images/logo_{$xnet_type}.png" alt="Logo {$xnet_type}" width="106" height="96" />
        </td>
        <td colspan="2">
          <img src="{rel}/images/texte_{$xnet_type}.jpg" alt="{$xnet_type}" width="490" height="96" />
        </td>
      </tr>
      {else}
      <tr id="top">
        <td>
          <img src="{rel}/images/logo.png" alt="LOGO Assos" />
        </td>
        <td colspan="3" style="text-align: right">
          <a href="https://www.polytechnique.org/"><img src="{rel}/images/logo-xorg.png" alt="LOGO X.Org" /></a>
          <img src="{rel}/images/asso2.png" alt="titre_du_site" />
        </td>
      </tr>
      <tr><td colspan="4"><img src="{rel}/images/barre.png" alt="----------" width="765" /></td></tr>
      {/if}

      {if $menu}
      <tr>
        <td id="menu">
          {foreach from=$menu key=title item=submenu}
          <h1>{$title}</h1>
          {foreach from=$submenu key=tit item=url}
          <a href="{rel}/{$url}">{$tit}</a>
          {/foreach}
          {/foreach}
        </td>
        <td colspan="3">
          <div class="breadcrumb">
            {if $asso}
            <a href="{rel}/groups/{$asso.cat}">{$asso.cat|cat_pp}</a> »
            {if $asso.dom}
            <a href="{rel}/groups/{$asso.cat}/{$asso.dom}">{$asso.domnom}</a> »
            {/if}
            {$asso.nom}
            {elseif $cat}
            <a href="{rel}/groups/{$cat}">{$cat|cat_pp}</a> »
            {if $dom || !$doms}
            Choix de l'Asso
            {else}
            Choix du domaine
            {/if}
            {/if}
          </div>
          {include file="skin/common.content.tpl"}
        </td>
      </tr>
      {else}
      <tr>
        <td colspan="4">
          {include file="skin/common.content.tpl"}
        </td>
      </tr>

      <tr><td colspan="4"><img src="{rel}/images/barre.png" alt="----------" width="765" /></td></tr>

      <tr>
        <td colspan="4">
          <table class="links" summary="liens" cellspacing="0" cellpadding="0">
            <tr>
              <td> <a href="{rel}/plan">PLAN DU SITE</a> </td>
              <td> <a href="{rel}/groups/groupesx">groupes X</a> </td>
              <td> <a href="{rel}/groups/binets">binets</a> </td>
              <td> <a href="{rel}/groups/promotions">promotions</a> </td>
              <td> <a href="{rel}/groups/institutions">institutions</a> </td>
            </tr>
          </table>
        </td>
      </tr>
      {/if}

      <tr><td colspan="4"><img src="{rel}/images/barre.png" alt="----------" width="765" /></td></tr>

      <tr>
        <td colspan="4" id="perso">
          {list_all_my_groups}
          {only_public}
          <div>Me connecter :</div>
          <a class='gp' href="{$smarty.session.loginX}">polytechnicien</a>
          {/only_public}

          <a href="{rel}/manuel" title="Manuel d'aide en ligne" style="float: right"><img src="{rel}/images/manuel.png" alt="manuel" /></a>
        </td>
      </tr>

      <tr><td colspan="4"><img src="{rel}/images/barre.png" alt="----------" width="765" /></td></tr>

      <tr>
        <td colspan="4" id="credits">
          <a href="{rel}/plan">liste des associations</a> -
          <a href="{rel}/services">services proposés</a> -
          <a href="{rel}/about">à propos de ce site</a> -
          {mailto address="contact@polytechnique.org" text="nous contacter" encode="javascript"}
          <br />
          © Copyright 2000-2006 <a href="http://x-org.polytechnique.org/">Association Polytechnique.org</a>
        </td>
      </tr>

    </table>
  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2: *}
