/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

function AjaxEngine()
{
    this.xml_client = null;
    this.init = false;
    this.obj  = null;
    this.func = null;

    this.prepare_client = function()
    {
        if (!this.init) {
            if (window.XMLHttpRequest) {
                this.xml_client = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                try {
                    this.xml_client = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    this.xml_client = new ActiveXObject("Microsoft.XMLHTTP");
                }
            }
            if (this.xml_client == null) {
                alert("Ton client ne supporte pas Ajax, nécessaire pour certaines fonctionalités de cette page");
            }
        }
        this.init = true;
    }

    this.update_html = function(obj, src, func)
    {
        this.prepare_client();
        if (this.xml_client == null) {
            return true;
        }
        if (src.match(/^http/i) == null) {
            src = platal_baseurl + src;
        }
        this.obj = obj;
        this.func = func;
        this.xml_client.abort();
        this.xml_client.onreadystatechange = this.apply_update_html(this);
        this.xml_client.open ('GET', src, true);
        this.xml_client.send (null);
        return false;
    }

    this.apply_update_html = function(ajax)
    {
        return function()
        {
            if(ajax.xml_client.readyState == 4) { 
                if (ajax.xml_client.status == 200) { 
                    if (ajax.obj != null) {  
                        document.getElementById(ajax.obj).innerHTML = ajax.xml_client.responseText; 
                    }
                    if (ajax.func != null) { 
                        ajax.func(ajax.xml_client.responseText); 
                    }
                } else if (ajax.xml_client.status == 403) { 
                    window.location.reload(); 
                }
            }
        };
    }
}

var Ajax = new AjaxEngine();

var currentTempMessage = 0;
function setOpacity(obj, opacity)
{
  opacity = (opacity == 100)?99:opacity;
  // IE
  obj.style.filter = "alpha(opacity:"+opacity+")";
  // Safari < 1.2, Konqueror
  obj.style.KHTMLOpacity = opacity/100;
  // Old Mozilla
  obj.style.MozOpacity = opacity/100;
  // Safari >= 1.2, Firefox and Mozilla, CSS3
  obj.style.opacity = opacity/100
}

function _showTempMessage(id, state, back)
{
    var obj = document.getElementById(id);
    if (currentTempMessage != state) {
        return;
    }   
    setOpacity(obj, back * 4);
    if (back > 0) {
        setTimeout("_showTempMessage('" + id + "', " + currentTempMessage + "," + (back-1) + ")", 125);
    } else {
        obj.innerHTML = "";
    }
}

function showTempMessage(id, message, success)
{
    var obj = document.getElementById(id);
    obj.innerHTML = (success ? "<img src='images/icons/wand.gif' alt='' /> "
                             : "<img src='images/icons/error.gif' alt='' /> ") + message;
    obj.style.fontWeight = "bold";
    obj.style.color = (success ? "green" : "red");;
    currentTempMessage++;
    setOpacity(obj, 100);
    setTimeout("_showTempMessage('" + id + "', " + currentTempMessage + ", 25)", 1000);
}

function previewWiki(idFrom, idTo, withTitle, idShow)
{
    var text = encodeURIComponent(document.getElementById(idFrom).value);
    if (text == "") {
        return false;
    }   
    var url  = "wiki_preview";
    if (!withTitle) {
        url += "/notitle";
    }   
    Ajax.update_html(idTo, url + "?text=" + text);
    if (idShow != null) {
        document.getElementById(idShow).style.display = "";
    }   
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
