<?php
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

require_once dirname(__FILE__) . '/../include/test.inc.php';

class UserFilterTest extends PlTestCase
{
    private static function checkPlatal()
    {
        global $platal;
        if($platal == null)
        {
            $platal = new Xorg();
        }
    }

    public static function simpleUserProvider()
    {
        self::checkPlatal();
        $tests = array();

        $tests[] = array(
            /* UFC_Hrpid
             */
            array('SELECT  DISTINCT uid
                     FROM  account_profiles
                    WHERE  FIND_IN_SET(\'owner\', perms)',
                  new UFC_HasProfile(), -1),

            /* UFC_Hruid
             */
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  accounts
                                WHERE  hruid = {?}', 'florent.bruneau.2003'),
                  new UFC_Hruid('florent.bruneau.2003'), 1),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  accounts
                                WHERE  hruid = {?}', 'florent.bruneau.2004'),
                  new UFC_Hruid('florent.bruneau.2004'), 0),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  accounts
                                WHERE  hruid IN {?}', array('florent.bruneau.2003',
                                                            'stephane.jacob.2004')),
                  new UFC_Hruid('florent.bruneau.2003', 'stephane.jacob.2004'), 2),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  accounts
                                WHERE  hruid IN {?}', array('florent.bruneau.2004',
                                                            'stephane.jacob.2004')),
                  new UFC_Hruid('florent.bruneau.2004', 'stephane.jacob.2004'), 1),

            /* UFC_Hrpid
             */
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                                WHERE  hrpid = {?}', 'florent.bruneau.2003'),
                  new UFC_Hrpid('florent.bruneau.2003'), 1),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                                WHERE  hrpid = {?}', 'florent.bruneau.2004'),
                  new UFC_Hrpid('florent.bruneau.2004'), 0),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                                WHERE  hrpid IN {?}', array('florent.bruneau.2003',
                                                            'stephane.jacob.2004')),
                  new UFC_Hrpid('florent.bruneau.2003', 'stephane.jacob.2004'), 2),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                                WHERE  hrpid IN {?}', array('florent.bruneau.2004',
                                                            'stephane.jacob.2004')),
                  new UFC_Hrpid('florent.bruneau.2004', 'stephane.jacob.2004'), 1),

            /* UFC_IP
             */
            array(XDB::format('SELECT  DISTINCT a.uid
                                 FROM  log_sessions
                           INNER JOIN  accounts AS a USING(uid)
                                WHERE  ip = {?} OR forward_ip = {?}',
                              ip_to_uint('129.104.247.2'), ip_to_uint('129.104.247.2')),
                  new UFC_Ip('129.104.247.2'), -1),
        );
            /* TODO: UFC_Comment
             */

            /* UFC_Promo
             */
        $tests[] = array(
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                                WHERE  pd.promo = {?}', 'X2004'),
                new UFC_Promo('=', UserFilter::DISPLAY, 'X2004'), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                                WHERE  pd.promo < {?}', 'X2004'),
                new UFC_Promo('<', UserFilter::DISPLAY, 'X2004'), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                                WHERE  pd.promo > {?}', 'X2004'),
                new UFC_Promo('>', UserFilter::DISPLAY, 'X2004'), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                                WHERE  pd.promo < {?}', 'X1900'),
                new UFC_Promo('<', UserFilter::DISPLAY, 'X1900'), 0),

            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.entry_year = {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2004', 'Ing.'),
                new UFC_Promo('=', UserFilter::GRADE_ING, 2004), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.entry_year <= {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '1960', 'Ing.'),
                new UFC_Promo('<=', UserFilter::GRADE_ING, 1960), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.entry_year > {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2004', 'Ing.'),
                new UFC_Promo('>', UserFilter::GRADE_ING, 2004), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.entry_year < {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '1900', 'Ing.'),
                new UFC_Promo('<', UserFilter::GRADE_ING, 1900), 0),

            /* XXX : tests disabled until there are Masters and doctors in the DB
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year = {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'MSc'),
                new UFC_Promo('=', UserFilter::GRADE_MST, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year <= {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'MSc'),
                new UFC_Promo('<=', UserFilter::GRADE_MST, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year > {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'MSc'),
                new UFC_Promo('>', UserFilter::GRADE_MST, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year < {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '1980', 'MSc'),
                new UFC_Promo('<', UserFilter::GRADE_MST, 1980), 0),

            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year = {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'PhD'),
                new UFC_Promo('=', UserFilter::GRADE_PHD, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year <= {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'PhD'),
                new UFC_Promo('<=', UserFilter::GRADE_PHD, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year > {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '2009', 'PhD'),
                new UFC_Promo('>', UserFilter::GRADE_PHD, 2009), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pe.grad_year < {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                '1980', 'PhD'),
                new UFC_Promo('<', UserFilter::GRADE_PHD, 1980), 0),
             */
        );

            /* TODO: UFC_SchoolId
             */
            /* UFC_EducationSchool
             */
        $id_X = XDB::fetchOneCell('SELECT  id
                                     FROM  profile_education_enum
                                    WHERE  abbreviation = {?}', 'X');
        $id_HEC = XDB::fetchOneCell('SELECT  id
                                       FROM  profile_education_enum
                                      WHERE  abbreviation = {?}', 'HEC');
        $tests[] = array(
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                WHERE  pee.abbreviation = {?}', 'X'),
                new UFC_EducationSchool($id_X), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                WHERE  pee.abbreviation IN {?}', array('X', 'HEC')),
                new UFC_EducationSchool($id_X, $id_HEC), -1),
        );

            /* UFC_EducationDegree
             */
        $id_DegreeIng = XDB::fetchOneCell('SELECT  id
                                             FROM  profile_education_degree_enum
                                            WHERE  abbreviation = {?}', 'Ing.');
        $id_DegreePhd = XDB::fetchOneCell('SELECT  id
                                             FROM  profile_education_degree_enum
                                            WHERE  abbreviation = {?}', 'PhD');
        $tests[] = array(
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pede.abbreviation = {?}', 'Ing.'),
                new UFC_EducationDegree($id_DegreeIng), -1),
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                WHERE  pede.abbreviation IN {?}', array('Ing.', 'PhD')),
                new UFC_EducationDegree($id_DegreeIng, $id_DegreePhd), -1),
        );
            /* UFC_EducationField
             */
        $id_FieldInfo = XDB::fetchOneCell('SELECT  id
                                             FROM  profile_education_field_enum
                                            WHERE  field = {?}', 'Informatique');
        $id_FieldDroit = XDB::fetchOneCell('SELECT  id
                                              FROM  profile_education_field_enum
                                             WHERE  field = {?}', 'Droit');
        // FIXME: Replaces 0 by -1 in following queries when profile_education will be filled with fieldids
        $tests[] = array(
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_field_enum AS pefe ON (pe.fieldid = pefe.id)
                                WHERE  pefe.field = {?}', 'Informatique'),
                new UFC_EducationField($id_FieldInfo), 0), // FIXME: should be -1
            array(XDB::format('SELECT  DISTINCT ap.uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profile_education AS pe ON (pe.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                            LEFT JOIN  profile_education_field_enum AS pefe ON (pe.fieldid = pefe.id)
                                WHERE  pefe.field IN {?}', array('Informatique', 'Droit')),
                new UFC_EducationField($id_FieldInfo, $id_FieldDroit), 0), // FIXME: should be -1
        );

        $testcases = array();
        foreach ($tests as $t) {
            $testcases = array_merge($testcases, $t);
        }
        return $testcases;
    }

    /**
     * @dataProvider simpleUserProvider
     */
    public function testSimpleUser($query, $cond, $expcount = null)
    {
        /*
         * @param $query A MySQL query
         * @param $cond  The UFC to test
         * @param $expcount The expected number of results (-1 if that number is unknown)
         */

        self::checkPlatal();

        $query = XDB::query($query);
        $count = $query->numRows();
        if (!is_null($expcount)) {
            if ($expcount < 0) {
                $this->assertNotEquals(0, $count);
            } else {
                $this->assertEquals($expcount, $count);
            }
        }
        $ids = $query->fetchColumn();
        $this->assertEquals($count, count($ids));
        sort($ids);

        $uf = new UserFilter($cond);
        $this->assertEquals($count, $uf->getTotalUserCount());
        $got = $uf->getUIDs();
        $this->assertEquals($count, count($got));
        sort($got);
        $this->assertEquals($ids, $got);

        $uf = new UserFilter($cond);
        $got = $uf->getUIDs();
        $this->assertEquals($count, count($got));
        sort($got);
        $this->assertEquals($ids, $got);
        $this->assertEquals($count, $uf->getTotalCount());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
