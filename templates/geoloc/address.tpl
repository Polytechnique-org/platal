{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}


      {if $address.adr1 || $address.pays || $geoloc_address.ville || $address.tel || $address.fax || $address.mobile}
      <div class="adresse">
        {if $titre && ($address.adr1 || $address.ville || $address.pays)}
          {if $titre_div}
            <div class="titre">
              {$titre}
            </div>
          {else}
            <em>{$titre}</em><br />
          {/if}
        {/if}
        {if $address.adr1}<strong>{$address.adr1}</strong><br />{/if}
        {if $address.adr2}<strong>{$address.adr2}</strong><br />{/if}
        {if $address.adr3}<strong>{$address.adr3}</strong><br />{/if}
        {if $address.ville}<strong>{$address.cp} {$address.ville}</strong><br />{/if}
        {if $address.pays}
        <strong>{$address.pays}{if $address.region} ({$address.region}){/if}</strong>
        {/if}
        
        {if $address.tel}
        <div>
          <em>Tél : </em>
          <strong>{$address.tel}</strong>
        </div>
        {/if}

        {if $address.fax}
        <div>
          <em>Fax : </em>
          <strong>{$address.fax}</strong>
        </div>
        {/if}
        
        {if $address.mobile}
        <div>
          <em>Mobile : </em>
          <strong>{$address.mobile}</strong>
        </div>
        {/if}

      </div>
      {/if}

{* vim:set et sw=2 sts=2 sws=2: *}
