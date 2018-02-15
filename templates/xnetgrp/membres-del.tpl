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

{if t($smarty.post.confirm)}

<p class="descr">
{if !$self}
<a href="{$platal->ns}annuaire">retour à l'annuaire</a>
{else}
<a href="">retour à l'accueil</a>
{/if}
</p>

{else}

<h1>{$asso->nom}&nbsp;: gestion des membres</h1>

<h2>
  Suppression du membre&nbsp;: {profile user=$user groupperms=false sex=false promo=true}
</h2>


<form method="post" action="{$platal->pl_self()}">
  {xsrf_token_field}
  <div class="center">
    <p class="descr">
    {if $self}
    Êtes-vous sûr de vouloir vous désinscrire du groupe {$asso->nom} et de toutes
    les listes de diffusion associées&nbsp;?
    {else}
    Êtes-vous sûr de vouloir supprimer {$user->fullName()} du groupe,
    lui retirer tous les droits associés à son statut de membre
    et le désabonner de toutes les listes de diffusion du groupe&nbsp;?
    {/if}
    </p>
    {if $self}
    <label><input type="checkbox" name="remember" />Empêcher ma réinscription au groupe par un animateur.</label><br /><br />
    {/if}
    <input type="submit" name="confirm" value="Oui, je {if $self}me{else}le{/if} désinscris complètement du groupe !" />
  </div>
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
