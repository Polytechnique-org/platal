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
{if t($withtext)}
<a href="Xorg/FAQ?display=light#flags" class="popup_800x240">Quelle couleur ?</a><br />
{/if}
{if !t($val)}{assign var=val value='ax'}{/if}
{if t($disabled)}<input type="hidden" name="{$name}" value="private" />{/if}
<label><input type="radio" name="{$name}" value="public"{if $val eq 'public'} checked="checked"{/if}
       {if t($disabled)}disabled="disabled"{/if}{if t($mainField)} onchange="updatePublicity('{$mainField}','{$mainId}','{$subField}','{$subId}')"{/if} />
{icon name="flag_green" title="site public"}
{if t($withtext)}<span class="texte">annuaire papier</span>{/if}</label>
<label><input type="radio" name="{$name}" value="ax"{if $val eq 'ax'} checked="checked"{/if}
       {if t($disabled)}disabled="disabled"{/if}{if t($mainField)} onchange="updatePublicity('{$mainField}','{$mainId}','{$subField}','{$subId}')"{/if} />
{icon name="flag_orange" title="visible dans l'annuaire papier"}
{if t($withtext)}<span class="texte">visible dans l'annuaire papier</span>{/if}</label>
<label><input type="radio" name="{$name}" value="private"{if $val eq 'private'} checked="checked"{/if}
       {if t($disabled)}disabled="disabled"{/if}{if t($mainField)} onchange="updatePublicity('{$mainField}','{$mainId}','{$subField}','{$subId}')"{/if} />
{icon name="flag_red" title="authentifié"}
{if t($withtext)}<span class="texte">diplômés de l'X</span>{/if}</label>
<label><input type="radio" name="{$name}" value="hidden"{if $val eq 'hidden'} checked="checked"{/if}
       {if t($disabled)}disabled="disabled"{/if}{if t($mainField)} onchange="updatePublicity('{$mainField}','{$mainId}','{$subField}','{$subId}')"{/if} />
{icon name="flag_black" title="administrateurs"}
{if t($withtext)}<span class="texte">administrateurs</span>{/if}</label>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
