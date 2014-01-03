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

<script type="text/javascript">
{literal}
//<!--
    $(function() {
        $('#fusionax_import input').click(function() {
            $('#fusionax input').hide();
            $('#fusionax').append('Lancement de l\'import.<br/>');
            $.getScript('fusionax/import/launch/' + $('#fusionax_filename input').val());
        });
    });
//-->
{/literal}
</script>
<h2><a href="fusionax">Fusion des annuaires X.org - AX</a></h2>

<h2>Import de l'annuaire AX</h2>

<p>
  Pour pouvoir commencer la fusion des annuaires, un root doit dans un premier
  temps créer le dossier spool/fusionax avec les droits en lecture et écrire
  pour l'utilisateur web, puis y déposer l'export fournit par l'AX.<br />
  Attention, pour faciliter les tests, les tables sont d'abord supprimées avant
  d'être remplies.<br />
  De plus la promotion 1920 est aussi ajoutée à notre base.
</p>

<p>
  <span id="fusionax">
    <span id="fusionax_filename"><input type="text" /></span>
    <span id="fusionax_import"><input type="button" value="Lancer l'import"/></span>
  </span>
</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
