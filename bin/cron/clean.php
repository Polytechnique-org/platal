#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

function query ($sql) {
    XDB::execute($sql);
    if (XDB::errno() != 0) {
    	echo "error in \"$sql\":\n", XDB::error(), "\n";
    }
}

query('DELETE FROM  register_pending
             WHERE  TO_DAYS(NOW()) - TO_DAYS(date) >= 365');
query('DELETE FROM  register_pending
             WHERE  hash = \'INSCRIT\'');

// Sorts a few tables to optimize sorted read acces.
query('ALTER TABLE  profile_binet_enum
          ORDER BY  text');
query('ALTER TABLE  profile_corps_enum
          ORDER BY  name');
query('ALTER TABLE  profile_corps_rank_enum
          ORDER BY  name');
query('ALTER TABLE  profile_education_degree_enum
          ORDER BY  degree');
query('ALTER TABLE  profile_education_field_enum
          ORDER BY  field');
query('ALTER TABLE  profile_job_enum
          ORDER BY  name');
query('ALTER TABLE  profile_langskill_enum
          ORDER BY  language');
query('ALTER TABLE  profile_medal_enum
          ORDER BY  text');
query('ALTER TABLE  profile_networking_enum
          ORDER BY  name');
query('ALTER TABLE  profile_section_enum
          ORDER BY  text');
query('ALTER TABLE  profile_skill_enum
          ORDER BY  text_fr');

// Prunes older autocomplete queries.
query('DELETE FROM  search_autocomplete
             WHERE  generated < DATE_SUB(NOW(), INTERVAL 1 DAY)');

// XXX: cleans profile_merge_issues (to be removed once all merge related issues have been fixed)
query('DELETE FROM  profile_merge_issues
             WHERE  issues IS NULL OR issues = \'\'');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
