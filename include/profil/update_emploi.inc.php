<?php
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

for($i = 0; $i < 2; $i++){

    $globals->xdb->execute("REPLACE INTO entreprises(uid,entrid,entreprise,secteur,ss_secteur,poste,fonction,adr1,adr2,adr3,cp,ville,pays,region,tel,fax,mobile,pub, adr_pub, tel_pub, email, email_pub, web) ".
              "VALUES ({?}, {?}, {?}, ".
	      "{?},".
	      "{?}".
	      ", {?}, {?}, ".
	      "{?}, {?}, {?}, {?}, ".
	      "{?}, {?}, ".
	      "{?}, {?}, {?}, {?}, ".
	      "{?}, {?}, {?}, ".
	      "{?}, {?}, {?})",
	      Session::getInt('uid', -1) , $i , $entreprise[$i] ,
	      ( ($secteur[$i] == "") ? null : $secteur[$i]), //sinon un faux 0 est rentre dans la base
	      ( ($ss_secteur[$i] == "") ? null : $ss_secteur[$i]),
	      $poste[$i], $fonction[$i],
              $adrpro1[$i], $adrpro2[$i], $adrpro3[$i], $cppro[$i],
              $villepro[$i], $payspro[$i],
	      $regionpro[$i], $telpro[$i], $faxpro[$i], $mobilepro[$i],
	      $pubpro[$i], $adr_pubpro[$i], $tel_pubpro[$i],
	      $emailpro[$i], $email_pubpro[$i], $webpro[$i]);
}
$globals->xdb->execute("UPDATE auth_user_md5 set cv= {?} WHERE user_id = {?}", $cv, Session::getInt('uid', -1));
?>
