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

<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS
    xmlns:xrds="xri://$xrds"
    xmlns="xri://$xrd*($v*2.0)"
    xmlns:openid="http://openid.net/xmlns/1.0">
  <XRD>
    <Service priority="10">
      <Type>{$type2}</Type>
      <Type>{$sreg}</Type>
      <URI>{$provider}</URI>
      <LocalID>{$local_id}</LocalID>
    </Service>
    <Service priority="20">
      <Type>{$type1}</Type>
      <Type>{$sreg}</Type>
      <URI>{$provider}</URI>
      <openid:Delegate>{$local_id}</openid:Delegate>
    </Service>
  </XRD>
</xrds:XRDS>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
