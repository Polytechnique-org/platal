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

function send_bug() {
        var h = windowHeight();
        var y = getScrollY();
        y += (h - 470) /2;
	document.getElementById('flyspray_report').style.display = 'block';
        document.getElementById('flyspray_report').style.top = y+'px';
	return false;
}

function getScrollY() {
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
        //Netscape compliant
        scrOfY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        scrOfY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        scrOfY = document.documentElement.scrollTop;
    }
    return scrOfY;
}
function windowHeight() {
    if( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
        return window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
        return document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
        //IE 4 compatible
        return document.body.clientHeight;
    }
    return 0;
}

function close_bug(f,send) {
	var detail = document.getElementById('flyspray_detail');
	detail.value = utf8(detail.value);
	var title = document.getElementById('flyspray_title');
	title.value = utf8(title.value);
	if (send) {
		f.target = '_blank';
		f.submit();
	}
	f.reset();
	document.getElementById('flyspray_report').style.display = 'none';
}

function utf8(isotext)
{
	var utf8text = "";
	for ( i=0; i<isotext.length; i++ )
	{
		unicodchar = isotext.charCodeAt(i);
		
		if(unicodchar < 128){
			utf8text += String.fromCharCode(unicodchar);
		} else if(unicodchar < 0x800) {
			var val1 = 0xC0 + (unicodchar & 0x7C0) / 0x40;		// 0011111000000
			var val2 = 0x80 + (unicodchar & 0x3F);				// 0000000111111
			utf8text += String.fromCharCode(val1,val2);
		} else if(unicodchar < 0x10000) {
			var val1 = 0xE0 + (unicodchar & 0xF000) / 0x1000;	// 001111000000000000
			var val2 = 0x80 + (unicodchar &  0xFC0) / 0x40;		// 000000111111000000
			var val3 = 0x80 + (unicodchar &   0x3F);			// 000000000000111111
			utf8text += String.fromCharCode(val1,val2, val3);
		} else if(unicodchar < 0x200000){
			var val4 = 0x80 + (unicodchar & 0x1C0000) / 0x40000;// 00111000000000000000000
			var val2 = 0x80 + (unicodchar &  0x3F000) / 0x1000;	// 00000111111000000000000
			var val3 = 0x80 + (unicodchar &    0xFC0) / 0x40;	// 00000000000111111000000
			var val4 = 0x80 + (unicodchar &     0x3F);			// 00000000000000000111111
			utf8text += String.fromCharCode(val1,val2, val3, val4);
        }
	}
	return utf8text;
}
