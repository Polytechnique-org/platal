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

<form action="{$reminder->baseurl()}/suscribe" method="post">
  {xsrf_token_field}
  Des camarades souhaitent que tu t'inscrives aux listes suivantes&nbsp;:
  <dl>
    {foreach from=$lists key=list item=details}
    <dt>
      <label>
        <input type='checkbox' value='1' checked="checked" name="sub_ml[{$list}]" />
        {$list}&nbsp;: {$details.desc}
      </label>
    </dt>
    {if $details.info}
    <dd>
      {$details.info|nl2br}
    </dd>
    {/if}
    {/foreach}
  </dl>

  <div class="center">
    <input type="submit" value="M'inscrire aux listes" /> -
    <a href="reminder/no" onclick="$('#reminder').updateHtml('{$reminder->baseurl()}/no'); return false" style="text-decoration: none">
      {icon name=delete} Ne pas m'inscrire
    </a> -
    <a href="reminder/later" onclick="$('#reminder').updateHtml('{$reminder->baseurl()}/dismiss'); return false" style="text-decoration: none">
      {icon name=cross} Décider plus tard
    </a>
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
