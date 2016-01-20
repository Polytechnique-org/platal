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

{config_load file="mails.conf" section="intervention_admin"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=#to#}
{subject text="INTERVENTION de $admin"}
{elseif $mail_part eq 'wiki'}

Le compte de l'utilisateur {$hruid} a été édité.\\
Les champs suivants ont été changés :
{foreach from=$diff item=values key=field}
* '''{$field}''' : {$values.0} -> {$values.1}
{/foreach}

Et ceux qui n'ont pas changé :
{foreach from=$oldValues item=value key=field}
* '''{$field}''' : {$value}
{/foreach}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
