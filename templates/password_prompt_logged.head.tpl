{***************************************************************************
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
 ***************************************************************************}


    <script src="{"javascript/md5.js"|url}" type="text/javascript"></script>
    <script type="text/javascript">//<![CDATA[
      function doChallengeResponse() {ldelim}
        str = "{$smarty.cookies.ORGuid}:" +
        MD5(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

        document.forms.loginsub.response.value = MD5(str);
        document.forms.login.password.value = "";
        document.forms.loginsub.submit();
      {rdelim}
      //]]>
    </script>

{* vim:set et sw=2 sts=2 sws=2: *}
