/***************************************************************************
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
 ***************************************************************************/

function getNow() {
    dt=new Date();
    dy=dt.getDay();
    mh=dt.getMonth();
    wd=dt.getDate();
    yr=dt.getYear();
    if (yr<1000) yr += 1900;
    hr=dt.getHours();
    mi=dt.getMinutes();
    if (mi<10)
        time=hr+":0"+mi;
    else
        time=hr+":"+mi;
    days=new Array ("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
    months=new Array ("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
    return days[dy]+" "+wd+" "+months[mh]+" "+yr+"<br />"+time;
}


function popWin(theNode,w,h) {
    window.open(theNode.href, '_blank',
	'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width='+w+',height='+h);
}

function popWin2(theNode) { popWin(theNode,900,700); }

function auto_links() {
    nodes = document.getElementsByTagName('a');
    fqdn = document.URL;
    fqdn = fqdn.replace(/^https?:\/\/([^\/]*)\/.*$/,'$1');
    for(var i=0; i<nodes.length; i++) {
	node = nodes[i];
	if(!node.href || node.className == 'xdx' || node.href.indexOf('mailto:') > -1 || node.href.indexOf('javascript:')>-1) continue;
	if(node.href.indexOf(fqdn)<0 || node.className == 'popup') {
	    node.onclick = function () { window.open(this.href); return false; };
	}
	if(node.className == 'popup2') {
	    node.onclick = function () { popWin2(this); return false; };
	}
	if(matches = (/^popup_([0-9]*)x([0-9]*)$/).exec(node.className)) {
	    var w = matches[1], h = matches[2];
	    node.onclick = function () { popWin(this,w,h); return false; };
	}
    }
}

