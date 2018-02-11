#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

require './connect.db.inc.php';

// Set next_birthday according to birthdate, so that people born on February
// 29th get their birthdays the correct day.
XDB::execute('UPDATE  profiles
                 SET  next_birthday = DATE_ADD(birthdate,
                        INTERVAL
                            YEAR(CURDATE()) - YEAR(birthdate) +
                            IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birthdate),1,0)
                        YEAR)
               WHERE  birthdate AND (deathdate IS NULL OR deathdate = 0) AND (
                          next_birthday IS NULL OR
                          next_birthday < CURDATE() OR
                          MONTH(birthdate) != MONTH(next_birthday) OR (
                              DAY(birthdate) != DAY(next_birthday) AND NOT (
                                  DAY(birthdate) = 29 AND
                                  DAY(next_birthday) = 28 AND
                                  MONTH(birthdate) = 2
                              )
                          )
                      )');
$affected = XDB::affectedRows();
//echo "$affected next-birthday updated\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
