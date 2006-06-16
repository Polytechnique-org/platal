{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
//<![CDATA[
    applisType = new Array(
{applis_type}
);

applisTypeAll = new Array(
{applis_type_all}
);
{literal}
function fillType( selectCtrl, appli, fill ) {
var i;
var i0=0;

for (i = selectCtrl.options.length; i >=0; i--) {
selectCtrl.options[i] = null;
}

if (fill || appli <0) {
  selectCtrl.options[0] = new Option(' ');
  i0=1;
}
if (appli>=0) 
  for (i=0; i < applisType[appli].length; i++) 
    selectCtrl.options[i0+i] = new Option(applisType[appli][i]);
else if (fill)
  for (i=0; i < applisTypeAll.length; i++) 
    selectCtrl.options[i0+i] = new Option(applisTypeAll[i]);
}

function selectType( selectCtrl, type ) {
  for (i = 0; i < selectCtrl.options.length; i++) {
    if (selectCtrl.options[i].text == type)
      selectCtrl.selectedIndex=i;
  }
}
{/literal}
//]]>
</script>
{* vim:set et sw=2 sts=2 sws=2: *}
