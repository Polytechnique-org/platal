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

function getNow() {
    dt=new Date();
    dy=dt.getDay();
    mh=dt.getMonth();
    wd=dt.getDate();
    yr=dt.getYear();
    if (yr<1000) yr += 1900;
    hr=dt.getHours();
    mi=dt.getMinutes();
    if (mi<10)
        time=hr+":0"+mi;
    else
        time=hr+":"+mi;
    days=new Array ("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
    months=new Array ("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
    return days[dy]+" "+wd+" "+months[mh]+" "+yr+"<br />"+time;
}

function popup(an) { window.open(an.href); return false; }
