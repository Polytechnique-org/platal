{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<div class="row">
  <div id="xOrgLogo" class="col-md-2 col-sm-1 hidden-xs">
      <a href="index"><img src="images/skins/default_headlogo.jpg" alt="[ LOGO ]" class="img-responsive"/></a>
  </div>

  <div class="col-md-6 col-sm-11 col-xs-12">
 
  {if t($smarty.request.quick)}
    {assign var=requestQuick value=$smarty.request.quick|smarty:nodefaults}
  {/if}
  <form class="" action="search" method="get">
    <div class="form-group">
      <div class="input-group">
        {if $smarty.session.auth}
          <input type="text" size="20" name="quick" id="quickSearch" class="form-control" placeholder="Recherche dans l'annuaire polytechnicien" value="{$requestQuick}"/>
        {else}
          <input type="text" size="20" name="quick" id="quickSearch" class="form-control" placeholder="Recherche dans l'annuaire public" value="{$requestQuick}"/>
        {/if}
        <span class="input-group-btn">  
          <button id="quick_button" type="submit" class="btn btn-primary">
            <span class="glyphicon glyphicon-search" aria-label="Search"></span>
          </button>
        </span>
      </div>
    </div>
  </form>
  {if $smarty.session.auth gt AUTH_PUBLIC && $smarty.session.notifs}
  <a href="carnet/panel">{$smarty.session.notifs} événement{if $smarty.session.notifs gt 1}s{/if}</a>
  {/if}
  </div>
  
  <div class="col-md-4">
    <div class="pull-right">
        {if $smarty.session.auth}
          <a href="exit/forget" class="btn btn-danger">Déconnexion</a>
        {else}
          <a href="login" class="btn btn-danger">S'identifier</a>
        {/if}
    </div>
  </div>

</div>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
