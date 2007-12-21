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


<h1>Regexps pour les détections de bounces</h1>

<p>
Rappel sur les niveaux&nbsp;:
</p>
<ul>
  <li>0: IGNORE == ignorer le bounce</li>
  <li>1: NOTICE == forwarder le bounce (typiquement vacation)</li>
  <li>2: ERREUR == erreur</li>
</ul>


<form action="admin/postfix/regexp_bounces" method="post">
  <table class="bicol" cellpadding='0' cellspacing='0'>
    <tr>
      <th>Position/Niveau</th>
      <th>Regexp/Raison</th>
    </tr>
    {if $new}
    <tr class="impair">
      <td>
        <input type='text' name='pos[NULL]' value='' size='4' maxlength='4' />
      </td>
      <td>
        <input type="text" size="82" name='re[NULL]'   value="{$re.re}" />
      </td>
    </tr>
    <tr class="impair">
      <td style="white-space: nowrap">
        <input type='radio' name='lvl[NULL]' value='0' {if $re.lvl eq 0}checked="checked"{/if} />
        <input type='radio' name='lvl[NULL]' value='1' {if $re.lvl eq 1}checked="checked"{/if} />
        <input type='radio' name='lvl[NULL]' value='2' {if $re.lvl eq 2}checked="checked"{/if} />
      </td>
      <td>
        <input type="text" size="32" name='text[NULL]' value="{$re.text}" />
      </td>
    </tr>
    {else}
    <tr class="impair">
      <td colspan="2" class="right action">
        <a href="admin/postfix/regexp_bounces/new">nouveau</a>
      </td>
    </tr>
    {/if}
    {iterate from=$bre item=re}
    <tr class="{cycle values="pair,pair,impair,impair"}">
      <td>
        <input type='text' name='pos[{$re.id}]' value='{$re.pos}' size='4' maxlength='4' />
      </td>
      <td>
        <input type="text" size="82" name='re[{$re.id}]'   value="{$re.re}" />
      </td>
    </tr>
    <tr class="{cycle values="pair,pair,impair,impair"}">
      <td style="white-space: nowrap">
        <input type='radio' name='lvl[{$re.id}]' value='0' {if $re.lvl eq 0}checked="checked"{/if} />
        <input type='radio' name='lvl[{$re.id}]' value='1' {if $re.lvl eq 1}checked="checked"{/if} />
        <input type='radio' name='lvl[{$re.id}]' value='2' {if $re.lvl eq 2}checked="checked"{/if} />
      </td>
      <td>
        <input type="text" size="32" name='text[{$re.id}]' value="{$re.text}" /><br />
      </td>
    </tr>
    {/iterate}
    <tr class="{cycle values="pair,impair"}">
      <td colspan="2" class="center">
        <input type="submit" value="valider" name="submit" />
      </td>
    </tr>
  </table>
</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
