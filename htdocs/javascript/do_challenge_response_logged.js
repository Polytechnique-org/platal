/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

function doChallengeResponse() {
    var new_pass = hash_encrypt(document.forms.login.password.value);

    str = document.forms.loginsub.username.value + ":" +
        hash_encrypt(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = hash_encrypt(str);
    document.forms.loginsub.remember.value = document.forms.login.remember.checked;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
