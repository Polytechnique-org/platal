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

function correctUserName() {
    var u = document.forms.login.username;
    // login with no space
    if (u.value.indexOf(' ') < 0) return true;
    var mots = u.value.split(' ');
    // jean paul.du pont -> jean-paul.du-pont
    if (u.value.indexOf('.') > 0) { u.value = mots.join('-'); return true; }
    // jean dupont  -> jean.dupont
    if (mots.length == 2) { u.value = mots[0]+"."+mots[1]; return true; }
    // jean dupont 2001 -> jean.dupont.2001
    if (mots.length == 3 && mots[2] > 1920 && mots[2] < 3000) { u.value = mots.join('.'); return true; }
    // jean de la vallee -> jean.de-la-vallee
    if (mots[1].toUpperCase() == 'DE') { u.value = mots[0]+"."+mots.join('-').substr(mots[0].length+1); return true; }
    // jean paul dupont -> jean-paul.dupont
    if (mots.length == 3 && mots[0].toUpperCase() == 'JEAN') { u.value = mots[0]+"-"+mots[1]+"."+mots[2]; return true; }
    
    alert('Ton email ne doit pas contenir de blanc.\nLe format standard est\n\nprenom.nom.promotion\n\nSi ton nom ou ton prenom est composé,\nsépare les mots par des -');

    return false;
}

function doChallengeResponse() {

    if (!correctUserName()) return false;

    str = document.forms.login.username.value + ":" +
        MD5(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = MD5(str);
    document.forms.loginsub.username.value = document.forms.login.username.value;
    document.forms.loginsub.remember.value = document.forms.login.remember.checked;
    document.forms.loginsub.domain.value = document.forms.login.domain.value;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();

}
