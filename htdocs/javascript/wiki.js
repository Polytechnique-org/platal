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


Nix = {
  map: null,
  convert: function(a) {
      Nix.init();
      var s = '';
      for (i = 0; i < a.length ; i++) {
          var b = a.charAt(i);
          s += ((b >= 'A' && b <= 'Z') || (b >= 'a' && b <= 'z') ? Nix.map[b] : b);
      }
      return s;
  },
  init: function() {
            if (Nix.map != null)
                return;
            var map = new Array();
            var s='abcdefghijklmnopqrstuvwxyz';
            for (i = 0; i < s.length; i++)
                map[s.charAt(i)] = s.charAt((i+13)%26);
            for (i=0; i<s.length; i++)map[s.charAt(i).toUpperCase()] = s.charAt((i+13)%26).toUpperCase();
            Nix.map = map;
        },
  decode: function(a) {
              document.write(Nix.convert(a));
          }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
