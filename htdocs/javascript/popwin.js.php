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

var popup=null;
function popupWin(theURL,theSize) {
    if (theURL.indexOf('?')==-1)
        a = '?';
    else
        a = '&';
    theURL += <?php echo (isset($_COOKIE[session_name()]) ? "\"\"" : "a +\"".SID."\"");?>;
    window.open(theURL,'_blank',theSize);
    window.name="main";
    if(popup != null) {
        popup.location=popupURL;
        if(navigator.appName.substring(0,8)=="Netscape") {
            popup.location=popupURL;
            popup.opener=self;
        }
        if(navigator.appName=="Netscape" ) {
            popup.window.focus();
        }
        self.name="main";
    }
}
function popWin(theURL) {
    popupWin(theURL,'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=900,height=700');
}
function popWin2(theURL) {
    popupWin(theURL,'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=200,height=100');
}
function popSimple(theURL) {
    popupWin(theURL,'');
}
function x() { return; }

function remote(url){
    window.opener.location=url
}
