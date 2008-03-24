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

Le tableau suivant permet de gérer la mise au ban (le bannissement) de certains utilisateurs aux forums.

Chaque ligne permet de gérer les accès limités à :
<ul>
<li>une plage d'adresses IP donnée (entre ipmin et ipmax),</li>
<li>à tout le monde (uid=0) ou seulement à un utilisateur donné,</li>
<li>en restreignant l'accès en lecture à un ensemble de forums décrits par un masque,</li>
<li>en restreignant l'accès en écriture à un ensemble de forums décrits par un masque.</li>
</ul>

<p>
Dans les masques le <strong>*</strong> remplace n'importe quel texte et le <strong>!</strong> bloque l'accès au lieu de l'autoriser. Par exemple : <code>xorg.*,!xorg.prive.*</code> autorise tous les forums xorg sauf ceux qui s'appellent xorg.prive.qqchose.
</p>

<p>
Les différentes règles sont appliquées par ordre de priorité décroissante.
</p>

{include file="core/table-editor.tpl"}

{literal}
<script type="text/javascript">
  $('#body td table tr').each(function() { 
    var uidcell = $('td:eq(3)',this);
    if (uidcell.length != 1) {
      return;
    }
    var uid = uidcell.text().replace(/^\s+/g,'').replace(/\s+$/g,'');
    uidcell.replaceWith('<'+'a href="admin/user/'+uid+'">'+uid+'</'+'a>');
  });
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

