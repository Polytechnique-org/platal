<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

require_once dirname(__FILE__) . '/../include/test.inc.php';

class CheckDB extends PlTestCase
{
    private static function checkPlatal()
    {
        global $platal;
        if ($platal == null)
        {
            $platal = new Xorg();
        }
    }

    public static function dbConsistancyProvider()
    {
        $testcases = array(
            'hruid' =>
                array('SELECT  uid, full_name
                         FROM  accounts
                        WHERE  hruid IS NULL OR hruid = \'\''),

            'hrpid' =>
                array('SELECT  p.pid, pd.public_name, pd.promo
                         FROM  profiles        AS p
                    LEFT JOIN  profile_display AS pd ON (p.pid = pd.pid)
                        WHERE  p.hrpid IS NULL OR p.hrpid = \'\''),

            'name' =>
                array('SELECT  p.pid, p.hrpid
                         FROM  profiles     AS p
                   INNER JOIN  profile_name AS pn ON (p.pid = pn.pid)
                        WHERE  name = \'\''),

            'phone formatting' =>
                array('SELECT DISTINCT  g.phonePrefix
                                  FROM  geoloc_countries AS g
                          WHERE EXISTS  (SELECT  h.phonePrefix
                                           FROM  geoloc_countries AS h
                                          WHERE  h.phonePrefix = g.phonePrefix
                                                 AND h.phoneFormat != (SELECT  i.phoneFormat
                                                                         FROM  geoloc_countries AS i
                                                                        WHERE  i.phonePrefix = g.phonePrefix
                                                                        LIMIT  1))'),

            'missing countries' =>
                array('SELECT  pa.pid, pa.countryId
                         FROM  profile_addresses AS pa
                    LEFT JOIN  geoloc_countries  AS gc ON (pa.countryId = gc.iso_3166_1_a2)
                        WHERE  gc.country IS NULL OR gc.country = \'\''),

            'missing nationalities' =>
                array('SELECT  p.pid, p.nationality1, p.nationality2, p.nationality3
                         FROM  profiles         AS p
                    LEFT JOIN  geoloc_countries AS g1 ON (p.nationality1 = g1.iso_3166_1_a2)
                    LEFT JOIN  geoloc_countries AS g2 ON (p.nationality2 = g2.iso_3166_1_a2)
                    LEFT JOIN  geoloc_countries AS g3 ON (p.nationality3 = g3.iso_3166_1_a2)
                        WHERE  (p.nationality1 IS NOT NULL AND (g1.nationality IS NULL OR g1.nationality = \'\'))
                               OR (p.nationality2 IS NOT NULL AND (g2.nationality IS NULL OR g2.nationality = \'\'))
                               OR (p.nationality3 IS NOT NULL AND (g3.nationality IS NULL OR g3.nationality = \'\'))'),

            'ax_id' =>
                array('SELECT  pid, hrpid, ax_id, COUNT(ax_id) AS c
                         FROM  profiles
                        WHERE  ax_id != \'0\'
                     GROUP BY  ax_id
                       HAVING  c > 1'),

            'google apps' =>
                array('SELECT  s.email, g.g_status, r.redirect
                         FROM  email_redirect_account AS r
                   INNER JOIN  email_source_account   AS s ON (r.uid = s.uid AND s.type = \'forlife\')
                   INNER JOIN  gapps_accounts         AS g ON (g.l_userid = r.uid)
                        WHERE  r.type = \'googleapps\' AND r.flags = \'active\' AND g.g_status != \'active\'')
        );

        $tests = array(
            'profile_binet_enum'            => 'text',
            'profile_corps_enum'            => 'name',
            'profile_corps_rank_enum'       => 'name',
            'profile_education_degree_enum' => 'degree',
            'profile_education_enum'        => 'name',
            'profile_education_field_enum'  => 'field',
            'profile_job_enum'              => 'name',
            'profile_langskill_enum'        => 'langue_fr',
            'profile_medal_enum'            => 'text',
            'profile_name_enum'             => 'name',
            'profile_networking_enum'       => 'name',
            'profile_section_enum'          => 'text',
            'profile_skill_enum'            => 'text_fr',
            'groups'                        => 'nom',
            'forums'                        => 'name',
        );

        foreach ($tests as $table => $field) {
            $testcases[$table . ' description'] =
                array("SELECT  *
                         FROM  $table
                        WHERE  $field IS NULL OR $field = ''");
        }

        $tests = array(
            'profiles' => array(
                'freetext_pub' => array('public', 'private'),
                'medals_pub'   => array('public', 'private'),
                'alias_pub'    => array('public', 'private')
            ),
            'profile_addresses' => array(
                'pub' => array('public', 'ax', 'private')
            ),
            'profile_corps' => array(
                'corps_pub' => array('public', 'ax', 'private')
            ),
            'profile_job' => array(
                'pub'       => array('public', 'ax', 'private'),
                'email_pub' => array('public', 'ax', 'private')
            ),
            'profile_networking' => array(
                'pub' => array('public', 'private')
            ),
            'profile_phones' => array(
                'pub' => array('public', 'ax', 'private')
            ),
            'profile_photos' => array(
                'pub' => array('public', 'private')
            ),
        );

        foreach ($tests as $table => $test) {
            $select = 'p.pid, p.hrpid';
            $where  = array();;
            foreach ($test as $field => $pubs) {
                $select .= ", t.$field";
                $condition = array();
                foreach ($pubs as $pub) {
                    $condition[] = "t.$field != '$pub'";
                }
                $where[] = '(' . implode(' AND ', $condition) . ')';
            }
            $testcases[$table . ' publicity'] =
                array("SELECT  $select
                         FROM  $table   AS t
                   INNER JOIN  profiles AS p ON (t.pid = p.pid)
                        WHERE  " . implode(' OR ', $where));
        }

        return $testcases;
    }

    /**
     * @dataProvider dbConsistancyProvider
     */
    public function testDbConsistancy($query)
    {
        self::checkPlatal();
        $res = XDB::query($query);
        $count = $res->numRows();
        foreach ($res->fetchAllAssoc() as $key => $item) {
            echo "\n" . $key . " => {\n";
            foreach ($item as $field => $value) {
                echo $field . ' => ' . $value . "\n";
            }
            echo "}\n";
        }
        $this->assertEquals(0, $count);
    }

/* TODO: add check on foreign keys for every table! */

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
