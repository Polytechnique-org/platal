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

{config_load file="mails.conf" section="geoloc_error"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=#to#}
{subject text="Adresse impossible à geolocaliser"}
{elseif $mail_part eq 'wiki'}
Un camarade {$smarty.session.forlife} a forcé l'utilisation de l'adresse entrée
à la main et a refusé la version geolocalisée :

'''Version utilisateur (validée) :'''\\
{$text|replace:"\n":"\\\\\n"}

'''Version geoloc (refusée) :'''\\
{$geoloc|replace:"\n":"\\\\\n"}

-- \\
Webmaster de Polytechnique.org
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
