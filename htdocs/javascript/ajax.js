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

Ajax = {
    xml_client: null,
    init: false,

    prepare_client: function()
    {
        if (!Ajax.init) {
            if (window.XMLHttpRequest) {
                Ajax.xml_client = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                try {
                    Ajax.xml_client = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    Ajax.xml_client = new ActiveXObject("Microsoft.XMLHTTP");
                }
            }
            if (Ajax.xml_client == null) {
                alert("Ton client ne supporte pas Ajax, nécessaire pour certaines fonctionalités de cette page");
            }
        }
        Ajax.init = true;
    },

    update_html: function(obj, src)
    {
        Ajax.prepare_client();
        if (Ajax.xml_client == null) {
            return true;
        }
        Ajax.xml_client.abort();
        Ajax.xml_client.onreadystatechange = function()
            {
                if(Ajax.xml_client.readyState == 4) {
                    if (Ajax.xml_client.status == 200) {
                    	if (obj != null) {
                        	document.getElementById(obj).innerHTML = Ajax.xml_client.responseText;
                        }
                    } else if (Ajax.xml_client.status == 403) {
                        window.location.reload();
                    }
                }
            };
        Ajax.xml_client.open ('GET', src, true);
        Ajax.xml_client.send (null);
        return false;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
