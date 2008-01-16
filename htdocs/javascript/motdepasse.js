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

function EnCryptedResponse() {
    pw1 = document.forms.changepass.nouveau.value;
    pw2 = document.forms.changepass.nouveau2.value;
    if (pw1 != pw2) {
        alert ("\nErreur : les deux champs ne sont pas identiques !")
            return false;
        exit;
    }
    if (pw1.length < 6) {
        alert ("\nErreur : le nouveau mot de passe doit faire au moins 6 caractères !")
            return false;
        exit;
    }

    str = hash_encrypt(document.forms.changepass.nouveau.value);
    document.forms.changepass2.response2.value = str;

    alert ("Le mot de passe que tu as rentré va être chiffré avant de nous parvenir par Internet ! Ainsi il ne circulera pas en clair.");
    document.forms.changepass2.submit();
    return true;
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
