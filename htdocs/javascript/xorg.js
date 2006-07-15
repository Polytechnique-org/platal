/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

var is_netscape = (navigator.appName.substring(0,8) == "Netscape");
var is_IE       = (navigator.appName.substring(0,9) == "Microsoft");

// {{{ function getNow()

function getNow() {
    dt = new Date();
    dy = dt.getDay();
    mh = dt.getMonth();
    wd = dt.getDate();
    yr = dt.getYear();
    if (yr<1000) yr += 1900;
    hr = dt.getHours();
    mi = dt.getMinutes();
    
    time   = (mi < 10) ? hr +':0'+mi : hr+':'+mi;
    days   = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
           'août', 'septembre', 'octobre', 'novembre', 'décembre']

    return days[dy]+' '+wd+' '+months[mh]+' '+yr+'<br />'+time;
}

// }}}
// {{{ Events

function eventClosure(obj, methodName) {
    return (function(e) {
            e = e || window.event;
            return obj[methodName](e);
        });
}

function attachEvent(obj, evt, f, useCapture) {
    if (!useCapture) useCapture = false;

    if (obj.addEventListener) {
        obj.addEventListener(evt, f, useCapture);
        return true;
    } else if (obj.attachEvent) {
        return obj.attachEvent("on"+evt, f);
    }
}

// }}}
// {{{ dynpost()

function dynpost(action, values)
{
    var body = document.getElementsByTagName('body')[0];

    var form = document.createElement('form');
    form.action = action;
    form.method = 'post';

    body.appendChild(form);

    for (var k in values) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = values[k];
        form.appendChild(input);
    }

    form.submit();
}

function dynpostkv(action, k, v)
{
    dynpost(action, {k: v});
}

// }}}

/***************************************************************************
 * POPUP THINGS
 */

// {{{ function popWin()

function popWin(theNode,w,h) {
    window.open(theNode.href, '_blank',
	'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width='+w+',height='+h);
}

// }}}
// {{{ function auto_links()

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
	    node.onclick = function () { popWin(this,840,600); return false; };
	}
	if(matches = (/^popup_([0-9]*)x([0-9]*)$/).exec(node.className)) {
	    var w = matches[1], h = matches[2];
	    node.onclick = function () { popWin(this,w,h); return false; };
	}
    }
}

// }}}

/***************************************************************************
 * The real OnLoad
 */

// {{{ function pa_onload

attachEvent(window, 'load', auto_links);

// }}}

