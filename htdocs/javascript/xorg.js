/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
// {{{ Search Engine

function canAddSearchEngine()
{
  if (((typeof window.sidebar == "object") && (typeof window.sidebar.addSearchEngine == "function"))
      || ((typeof window.sidebar == "object") && (typeof window.sidebar.addSearchEngine == "function"))) {
      return true;
  }
  return false;
}

function addSearchEngine()
{
  var searchURI = "http://www.polytechnique.org/xorg.opensearch.xml";
  if ((typeof window.sidebar == "object") && (typeof window.sidebar.addSearchEngine == "function")) {
    window.sidebar.addSearchEngine(
      searchURI,
      "http://www.polytechnique.org/images/xorg.png",
      "Annuaire Polytechnique.org",
      "Academic");
  } else {
    try {
        window.external.AddSearchProvider(searchURI);
    } catch(e) {
        alert("Impossible d'installer la barre de recherche"); 
    }
  }
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
    return false;
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
    var dict = {};
    dict[k] = v;
    dynpost(action, dict);
}

// }}}
// {{{ function RegExp.escape()

RegExp.escape = function(text) {
  if (!arguments.callee.sRE) {
    var specials = [
      '/', '.', '*', '+', '?', '|',
      '(', ')', '[', ']', '{', '}',
      '\\', '^' , '$'
    ];
    arguments.callee.sRE = new RegExp(
      '(\\' + specials.join('|\\') + ')', 'g'
    );
  }
  return text.replace(arguments.callee.sRE, '\\$1');
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
// {{{ function goodiesPopup()

function goodiesPopup(node) {
    if (node.href.indexOf('ical') > -1) {
        __goodies_popup(node, __goodies_ical_sites, 'Calendrier iCal');
    } else if (node.href.indexOf('rss') > -1 && (node.href.indexOf('xml') > -1 || node.href.indexOf('hash'))) {
        __goodies_popup(node, __goodies_rss_sites, 'Fil rss');
    }
}

function disableGoodiesPopups() {
    __goodies_active = false;
}

var __goodies_active = true;
var __goodies_ical_sites = [
    {'url_prefix': '',
     'img': 'images/icons/calendar_view_day.gif',
     'title': 'Calendrier iCal'},
    {'url_prefix': 'http://www.google.com/calendar/render?cid=',
     'img': 'images/goodies/add-google-calendar.gif',
     'title': 'Ajouter à Google Calendar'}
];
var __goodies_rss_sites = [
    {'url_prefix': '',
     'img': 'images/icons/feed.gif',
     'title': 'Fil rss'},
    {'url_prefix': 'http://fusion.google.com/add?feedurl=',
     'img': 'images/goodies/add-google.gif',
     'alt': 'Add to Google',
     'title': 'Ajouter à iGoogle/Google Reader'},
    {'url_prefix': 'http://www.netvibes.com/subscribe.php?url=',
     'img': 'images/goodies/add-netvibes.gif',
     'title': 'Ajouter à Netvibes'},
    {'url_prefix': 'http://add.my.yahoo.com/content?.intl=fr&url=',
     'img': 'images/goodies/add-yahoo.gif',
     'alt': 'Add to My Yahoo!',
     'title': 'Ajouter à My Yahoo!'},
    {'url_prefix': 'http://www.newsgator.com/ngs/subscriber/subext.aspx?url=',
     'img': 'images/goodies/add-newsgator.gif',
     'alt': 'Subscribe in NewsGator Online',
     'title': 'Ajouter à Newsgator'}
];

function __goodies_popupText(url, sites) {
    var text = '<div style="text-align: center; line-height: 2.2">';
    for (var site in sites) {
        var s_alt = (sites[site]["alt"] ? sites[site]["alt"] : "");
        var s_img = sites[site]["img"];
        var s_title = (sites[site]["title"] ? sites[site]["title"] : "");
        var s_url = (sites[site]["url_prefix"].length > 0 ? sites[site]["url_prefix"] + escape(url) : url);

        text += '<a href="' + s_url + '"><img src="' + s_img + '" title="' + s_title + '" alt="' + s_alt + '"></a><br />';
    }
    text += '<a href="https://www.polytechnique.org/Xorg/Goodies">Plus de bonus</a> ...</div>'
    return text;
}

function __goodies_popup(node, sites, default_title) {
    var mouseover_cb = function() {
        if (__goodies_active) {
            var rss_text = __goodies_popupText(node.href, sites);
            var rss_title = (node.title ? node.title : default_title);
            return overlib(rss_text, CAPTION, rss_title, CLOSETEXT, 'Fermer', DELAY, 800, STICKY, WIDTH, 150);
        }
    }
    var mouseout_cb = function() {
        nd();
    }

    node.onmouseover = mouseover_cb;
    node.onmouseout = mouseout_cb;
}

// }}}
// {{{ function auto_links()

function auto_links() {
    auto_links_nodes(document.getElementsByTagName('a'));
    auto_links_nodes(document.getElementsByTagName('link'));
}

function auto_links_nodes(nodes) {
    url  = document.URL;
    fqdn = url.replace(/^https?:\/\/([^\/]*)\/.*$/,'$1');
    light = (url.indexOf('display=light') > url.indexOf('?'));
    for(var i=0; i < nodes.length; i++) {
        node = nodes[i];
        if(!node.href || node.className == 'xdx'
           || node.href.indexOf('mailto:') > -1 || node.href.indexOf('javascript:') > -1)
            continue;
        if (node.href.indexOf(fqdn) < 0 || node.className == 'popup') {
            node.onclick = function () { window.open(this.href); return false; };
        }
        if (node.href.indexOf(fqdn) > -1 && light) {
            node.href = node.href.replace(/([^\#\?]*)\??([^\#]*)(\#.*)?/,
                                          "$1?display=light&$2$3");
        }
        if (node.href.indexOf('rss') > -1 || node.href.indexOf('ical') > -1) {
            node.href = node.href.replace(/https/, 'http');
            if (node.href.indexOf('http') < 0) {
                node.href = 'http://' + fqdn + '/' + node.href;
            }
            if (node.nodeName.toLowerCase() == 'a') {
                goodiesPopup(node);
            }
        }
        if(node.className == 'popup2') {
            node.onclick = function () { popWin(this,840,600); return false; };
        }
        if(node.className == 'popup3') {
            node.onclick = function () { popWin(this, 640, 800); return false; };
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

if (!attachEvent(window, 'load', auto_links)) {
    window.onload = auto_links;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
