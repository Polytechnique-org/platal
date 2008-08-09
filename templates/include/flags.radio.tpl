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
{if $withtext}
<a href="Xorg/FAQ?display=light#flags" class="popup_800x240">Quelle couleur ?</a>
{/if}
<label><input type="radio" name="{$name}" value="public" {if $val eq 'public'}checked="checked"{/if}
       {if $disabled}disabled="disabled"{/if}/>
{icon name="flag_green" title="site public"}
{if $withtext}<span class="texte">site public</span>{/if}</label>
<label><input type="radio" name="{$name}" value="ax" {if $val eq 'ax'}checked="checked"{/if}
       {if $disabled}disabled="disabled"{/if}/>
{icon name="flag_orange" title="transmis à l'AX"}
{if $withtext}<span class="texte">transmis à l'AX</span>{/if}</label>
<label><input type="radio" name="{$name}" value="private" {if $val eq 'private' || (!$val && !$disabled)}checked="checked"{/if}
       {if $disabled}disabled="disabled"{/if}/>
{icon name="flag_red" title="privé"}
{if $withtext}<span class="texte">privé</span>{/if}</label>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
