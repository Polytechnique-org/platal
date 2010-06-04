{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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
{if $lang eq "en"}
"Subject","Start Date","End Date","All day event","Categories","Description","Private"
{else}
"Objet","{"Début"|utf8_decode}","Fin","{"Journée entière"|utf8_decode}","{"Catégories"|utf8_decode}","{"Privé"|utf8_decode}"
{/if}
{iterate from=$events item=e}
{foreach from=$years item=year}
"{$e.summary|addslashes|utf8_decode}","{$e.timestamp|date_format:"%m/%d/"}{$year}","{$e.timestamp|date_format:"%m/%d/"}{$year}","True","{if $lang eq
"en"}Birthday{else}Anniversaire{/if}","False"
{/foreach}
{/iterate}
