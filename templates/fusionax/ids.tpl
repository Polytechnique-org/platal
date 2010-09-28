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

<h2><a href="fusionax">Fusion des annuaires X.org - AX</a> / Identifiants</h2>

<p>
Le préalable à toute fusion de renseignements pour une personne entre ce
que contient la base AX et ce que contient ce site est bien évidemment de
trouver une correspondance entre les personnes renseignés dans ces annuaires.<br /><br />

{if t($nbMissingInAX)}
<strong>Anciens manquants à l'AX&nbsp;:</strong>
<a href="fusionax/ids/missingInAX">{$nbMissingInAX} ancien{if $nbMissingInAX > 1}s{/if}</a>.<br />
{/if}

{if t($nbMissingInXorg)}
<strong>Anciens manquants à x.org&nbsp;:</strong>
<a href="fusionax/ids/missingInXorg">{$nbMissingInXorg} ancien{if $nbMissingInXorg > 1}s{/if}</a>.<br />
{/if}

{if t($wrongInXorg)}
<strong>Anciens ayant un ax_id sur Xorg ne correspondant à rien dans la base de l'AX&nbsp;:</strong>
<a href="fusionax/ids/wrongInXorg">{$wrongInXorg} ancien{if $wrongInXorg > 1}s{/if}</a>.
{/if}
</p>

<h3>Mettre en correspondance</h3>
<form action="fusionax/ids/lier" method="post">
  <p>
    Matricule AX : <input type="text" name="ax_id" /><br/>
    User ID X.org : <input type="text" name="pid" /><br/>
    <input type="submit" value="Lier" />
  </p>
</form>

<p></p>
<div id="autolink">
<h3>Mise en correspondance automatique</h3>
{if t($easyToLink)}
<p>
  Ces anciens sont probablement les mêmes (à peu près mêmes nom, prénom, promo)<br />
  {$nbMatch} correspondances trouvées.
</p>

{include file="fusionax/listFusion.tpl" fusionList=$easyToLink fusionAction="fusionax/ids/link" name="lier" field1="display_name_ax" namefield1="Ancien AX"}
<p><a href="fusionax/ids/linknext">Lier toutes les fiches affichées</a> <span id="fusion-reload" style="display:none"> - <a href="fusionax/ids#autolink">Trouver d'autres correspondances</a></span></p>
<script type="text/javascript">
{literal}
//<!--
$(document).ready(function() {
    $('#autolink a.fusion-action').click(function(a){
        $.get(a.currentTarget.href,{},function(){
            $(a.currentTarget).hide();
            $('#fusion-reload').show();
            $('#fusion-reload a').click(function(a) {
                document.location = a.currentTarget.href;
                document.location.reload();
            });
        });
        return false;
    });
});
//-->
{/literal}
</script>
{else}
<p>Aucune correspondance automatique n'a été trouvée (mêmes nom, prénom, promo d'étude).</p>
{/if}
</div>
