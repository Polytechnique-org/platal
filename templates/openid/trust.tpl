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

<h1>Demande d'identification OpenId</h1>

<p>Le site <strong>{$relying_party}</strong> demande à confirmer votre identité.</p>

{if $sreg_data neq null}
<p>Les informations suivantes lui seront transmises :</p>
<ul>
{foreach from=$sreg_data key=field item=value}
<li><i>{$field}</i> : {$value}</li>
{/foreach}
</ul>
{/if}


<p><strong>Souhaitez-vous confirmer votre identité ?</strong></p>

<div class="form">
  <form method="post" action="openid/trust">
    <input type="checkbox" name="always" /> Toujours faire confiance à ce site<br />
    <input type="submit" name="trust" value="Confirmer" />
    <input type="submit" value="Annuler" />
  </form>
</div>
