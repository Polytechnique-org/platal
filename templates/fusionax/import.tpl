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

<script type="text/javascript">
{literal}
//<!--
    $(document).ready(function() {
        $('#fusionax_import input').click(function() {
            $('#fusionax_import input').hide();
            $('#fusionax_import').append('Import en cours : récupération du fichier depuis le serveur de l\'AX...<br/>');
            $.getScript('fusionax/import/launch');
        });
    });
//-->
{/literal}
</script> 
<h2><a href="fusionax">Fusion des annuaires X.org - AX</a></h2>

<h2>Import de l'annuaire AX</h2>
{if $lastimport}
<p>Dernier import {$lastimport}</p>
{/if}

{if $keymissing}
<p>Impossible de faire l'import, il manque la clef d'authentification :</p>
<pre>{$keymissing}</pre>
{else}
<div id="fusionax_import">
<input type="button" value="Lancer l'import"/>
</div>
{/if}
