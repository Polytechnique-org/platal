<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

    private static function buildAccountQuery()
    {
        $args = func_get_args();
        $joinsAndWhere = XDB::prepare($args);
        return array('SELECT  DISTINCT a.uid
                        FROM  accounts AS a
                          ' . $joinsAndWhere,
                     'SELECT  DISTINCT p.pid
                        FROM  profiles AS p
                  INNER JOIN  account_profiles AS ap ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                  INNER JOIN  accounts AS a ON (a.uid = ap.uid)
                          ' . $joinsAndWhere);
    }

    private static function buildProfileQuery()
    {
        $args = func_get_args();
        $joinsAndWhere = XDB::prepare($args);
        return array('SELECT  DISTINCT a.uid
                        FROM  accounts AS a
                  INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', perms))
                  INNER JOIN  profiles AS p ON (p.pid = ap.pid)
                           ' . $joinsAndWhere,
                     'SELECT  DISTINCT p.pid
                        FROM  profiles AS p
                          ' . $joinsAndWhere);
    }

    public static function simpleUserProvider()
    {
        self::checkPlatal();
        $tests = array();

        $tests['id'] = array(
            /* UFC_Hrpid
             */
            array(self::buildAccountQuery('INNER JOIN  account_profiles AS ap2 ON (ap2.uid = a.uid)
                                                WHERE  FIND_IN_SET(\'owner\', ap2.perms)'),
                  new UFC_HasProfile(), -1),

            /* UFC_Hruid
             */
            array(self::buildAccountQuery('WHERE  a.hruid = {?}', 'florent.bruneau.2003'),
                  new UFC_Hruid('florent.bruneau.2003'), 1),
            array(self::buildAccountQuery('WHERE  a.hruid = {?}', 'florent.bruneau.2004'),
                  new UFC_Hruid('florent.bruneau.2004'), 0),
            array(self::buildAccountQuery('WHERE  a.hruid IN {?}', array('florent.bruneau.2003',
                                                                       'stephane.jacob.2004')),
                  new UFC_Hruid('florent.bruneau.2003', 'stephane.jacob.2004'), 2),
            array(self::buildAccountQuery('WHERE  a.hruid IN {?}', array('florent.bruneau.2004',
                                                                       'stephane.jacob.2004')),
                  new UFC_Hruid('florent.bruneau.2004', 'stephane.jacob.2004'), 1),

            /* UFC_Hrpid
             */
            array(self::buildProfileQuery('WHERE  p.hrpid = {?}', 'florent.bruneau.2003'),
                  new UFC_Hrpid('florent.bruneau.2003'), 1),
            array(self::buildProfileQuery('WHERE  p.hrpid = {?}', 'florent.bruneau.2004'),
                  new UFC_Hrpid('florent.bruneau.2004'), 0),
            array(self::buildProfileQuery('WHERE  p.hrpid IN {?}', array('florent.bruneau.2003',
                                                                        'stephane.jacob.2004')),
                  new UFC_Hrpid('florent.bruneau.2003', 'stephane.jacob.2004'), 2),
            array(self::buildProfileQuery('WHERE  p.hrpid IN {?}', array('florent.bruneau.2004',
                                                                       'stephane.jacob.2004')),
                  new UFC_Hrpid('florent.bruneau.2004', 'stephane.jacob.2004'), 1),

            /* UFC_IP
             */
            array(self::buildAccountQuery('INNER JOIN  log_sessions AS s ON (s.uid = a.uid)
                                                WHERE  s.ip = {?} OR s.forward_ip = {?}',
                                          ip_to_uint('129.104.247.2'), ip_to_uint('129.104.247.2')),
                  new UFC_Ip('129.104.247.2'), -1),
        );
            /* TODO: UFC_Comment
             */

            /* UFC_Promo
             */
        $tests['promo'] = array(
            array(self::buildProfileQuery('INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)
                                                WHERE  pd.promo = {?}', 'X2004'),
                new UFC_Promo('=', UserFilter::DISPLAY, 'X2004'), -1),

            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                                WHERE  pe.entry_year = {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                          '2004', 'Ing.'),
                new UFC_Promo('=', UserFilter::GRADE_ING, 2004), -1),
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                                WHERE  pe.entry_year <= {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                          '1960', 'Ing.'),
                new UFC_Promo('<=', UserFilter::GRADE_ING, 1960), -1),
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                                WHERE  pe.entry_year > {?} AND pee.abbreviation = \'X\' AND pede.abbreviation = {?}',
                                          '2004', 'Ing.'),
                new UFC_Promo('>', UserFilter::GRADE_ING, 2004), -1),
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
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

            /* UFC_SchoolId
             */
        $tests['schoolid'] = array(
            array(self::buildProfileQuery('WHERE  p.xorg_id = {?}', 20060076),
                new UFC_SchoolId(UFC_SchoolId::Xorg, 20060076), 1),
            array(self::buildProfileQuery('WHERE  p.ax_id = {?}', 20060062),
                new UFC_SchoolId(UFC_SchoolId::AX, 20060062), 1),
            array(self::buildProfileQuery('WHERE  p.xorg_id = {?}', 007),
                new UFC_SchoolId(UFC_SchoolId::Xorg, 007), 0),
            array(self::buildProfileQuery('WHERE  p.ax_id = {?}', 007),
                new UFC_SchoolId(UFC_SchoolId::AX, 007), 0),
            /* FIXME: disabled until we have some examples of school_id
            array(self::buildProfileQuery('WHERE  p.school_id = {?}', 12345678),
                new UFC_SchoolId(UFC_SchoolId::School, 12345678), 1),
            array(self::buildProfileQuery('WHERE  p.school_id = {?}', 007),
                new UFC_SchoolId(UFC_SchoolId::School, 007), 0),
             */
        );
            /* UFC_EducationSchool
             */
        $id_X = XDB::fetchOneCell('SELECT  id
                                     FROM  profile_education_enum
                                    WHERE  abbreviation = {?}', 'X');
        $id_HEC = XDB::fetchOneCell('SELECT  id
                                       FROM  profile_education_enum
                                      WHERE  abbreviation = {?}', 'HEC');
        $tests['school'] = array(
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                                                WHERE  pee.abbreviation = {?}', 'X'),
                new UFC_EducationSchool($id_X), -1),
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
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
        $tests['degree'] = array(
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                                WHERE  pede.abbreviation = {?}', 'Ing.'),
                new UFC_EducationDegree($id_DegreeIng), -1),
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
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
        // FIXME: Replace 0 by -1 in following queries when profile_education will be filled with fieldids
        $tests['edufield'] = array(
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_field_enum AS pefe ON (pe.fieldid = pefe.id)
                                                WHERE  pefe.field = {?}', 'Informatique'),
                new UFC_EducationField($id_FieldInfo), 0), // FIXME: should be -1
            array(self::buildProfileQuery('INNER JOIN  profile_education AS pe ON (pe.pid = p.pid)
                                            LEFT JOIN  profile_education_field_enum AS pefe ON (pe.fieldid = pefe.id)
                                                WHERE  pefe.field IN {?}', array('Informatique', 'Droit')),
                new UFC_EducationField($id_FieldInfo, $id_FieldDroit), 0), // FIXME: should be -1
        );

            /* UFC_Name
             */
        $id_Lastname = DirEnum::getID(DirEnum::NAMETYPES, Profile::LASTNAME);
        $id_Firstname = DirEnum::getID(DirEnum::NAMETYPES, Profile::FIRSTNAME);
        $id_Nickname = DirEnum::getID(DirEnum::NAMETYPES, Profile::NICKNAME);
        $id_Lastname_Marital = DirEnum::getID(DirEnum::NAMETYPES, Profile::LASTNAME . '_' . Profile::VN_MARITAL);
        $id_Lastname_Ordinary = DirEnum::getID(DirEnum::NAMETYPES, Profile::LASTNAME . '_' . Profile::VN_ORDINARY);

        $tests['name'] = array(
            // Lastname
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (pn.pid = p.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid = {?}', 'BARROIS', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'BARROIS', UFC_Name::EXACT), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (pn.pid = p.pid)
                                               WHERE  pn.name LIKE \'BARR%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'BARR', UFC_Name::PREFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (pn.pid = p.pid)
                                               WHERE  pn.name LIKE \'%OIS\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'OIS', UFC_Name::SUFFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%ARRO%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'ARRO', UFC_Name::CONTAINS), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%ZZZZZZ%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'ZZZZZZ', UFC_Name::CONTAINS), 0),

            // Firstname
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid = {?}', 'Raphaël', $id_Firstname),
                new UFC_Name(Profile::FIRSTNAME, 'Raphaël', UFC_Name::EXACT), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'Raph%\' AND pn.typeid = {?}', $id_Firstname),
                new UFC_Name(Profile::FIRSTNAME, 'Raph', UFC_Name::PREFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%aël\' AND pn.typeid = {?}', $id_Firstname),
                new UFC_Name(Profile::FIRSTNAME, 'aël', UFC_Name::SUFFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%apha%\' AND pn.typeid = {?}', $id_Firstname),
                new UFC_Name(Profile::FIRSTNAME, 'apha', UFC_Name::CONTAINS), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%zzzzzz%\' AND pn.typeid = {?}', $id_Firstname),
                new UFC_Name(Profile::FIRSTNAME, 'zzzzzz', UFC_Name::CONTAINS), 0),

            // Nickname
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid = {?}', 'Xelnor', $id_Nickname),
                new UFC_Name(Profile::NICKNAME, 'Xelnor', UFC_Name::EXACT), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'Xel%\' AND pn.typeid = {?}', $id_Nickname),
                new UFC_Name(Profile::NICKNAME, 'Xel', UFC_Name::PREFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%nor\' AND pn.typeid = {?}', $id_Nickname),
                new UFC_Name(Profile::NICKNAME, 'nor', UFC_Name::SUFFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%lno%\' AND pn.typeid = {?}', $id_Nickname),
                new UFC_Name(Profile::NICKNAME, 'lno', UFC_Name::CONTAINS), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE \'%zzzzzz%\' AND pn.typeid = {?}', $id_Nickname),
                new UFC_Name(Profile::NICKNAME, 'zzzzzz', UFC_Name::CONTAINS), 0),

            // Lastname + particle
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  CONCAT(pn.particle, \' \', pn.name) LIKE {?} AND pn.typeid = {?}', 'DE SINGLY', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'DE SINGLY', UFC_Name::PARTICLE | UFC_Name::EXACT), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  CONCAT(pn.particle, \' \', pn.name) LIKE \'DE SI%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'DE SI', UFC_Name::PARTICLE | UFC_Name::PREFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  CONCAT(pn.particle, \' \', pn.name) LIKE \'%GLY\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'GLY', UFC_NAME::PARTICLE | UFC_Name::SUFFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  CONCAT(pn.particle, \' \', pn.name) LIKE \'%E SIN%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'E SIN', UFC_Name::PARTICLE | UFC_Name::CONTAINS), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  CONCAT(pn.particle, \' \', pn.name) LIKE \'%ZZZZZZ%\' AND pn.typeid = {?}', $id_Lastname),
                new UFC_Name(Profile::LASTNAME, 'ZZZZZZ', UFC_Name::PARTICLE | UFC_Name::CONTAINS), 0),

            // Lastname_ordinary
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid IN {?}',
                                'ALBIZZATI', array($id_Lastname, $id_Lastname_Marital, $id_Lastname_Ordinary)),
                new UFC_Name(Profile::LASTNAME, 'ALBIZZATI', UFC_Name::VARIANTS | UFC_Name::EXACT), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid IN {?}',
                                'ALBIZ%', array($id_Lastname, $id_Lastname_Marital, $id_Lastname_Ordinary)),
                new UFC_Name(Profile::LASTNAME, 'ALBIZ', UFC_Name::VARIANTS | UFC_Name::PREFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid IN {?}',
                                '%ZATI', array($id_Lastname, $id_Lastname_Marital, $id_Lastname_Ordinary)),
                new UFC_Name(Profile::LASTNAME, 'ZATI', UFC_NAME::VARIANTS | UFC_Name::SUFFIX), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid IN {?}',
                                '%BIZZ%', array($id_Lastname, $id_Lastname_Marital, $id_Lastname_Ordinary)),
                new UFC_Name(Profile::LASTNAME, 'BIZZ', UFC_Name::VARIANTS | UFC_Name::CONTAINS), -1),
            array(self::buildProfileQuery('LEFT JOIN  profile_name AS pn ON (p.pid = pn.pid)
                                               WHERE  pn.name LIKE {?} AND pn.typeid IN {?}',
                                '%ZZZZZZ%', array($id_Lastname, $id_Lastname_Marital, $id_Lastname_Ordinary)),
                new UFC_Name(Profile::LASTNAME, 'ZZZZZZ', UFC_Name::VARIANTS | UFC_Name::CONTAINS), 0),
        );

            /* UFC_NameTokens
             */
        $tests['nametoken'] = array(
            // !soundex, !exact
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token LIKE \'xelnor%\''),
                new UFC_NameTokens('xelnor'), 1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token LIKE \'xe%\''),
                new UFC_NameTokens('xe'), -1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token LIKE \'xe%\' OR sn.token LIKE \'barr%\''),
                new UFC_NameTokens(array('xe', 'barr')), -1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token LIKE \'zzzzzzzz%\''),
                new UFC_NameTokens('zzzzzzzz'), 0),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token LIKE \'barr%\' AND FIND_IN_SET(\'public\', sn.flags)'),
                new UFC_NameTokens('barr', UFC_NameTokens::FLAG_PUBLIC), -1),

            // !soundex, exact
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token = \'xelnor\''),
                new UFC_NameTokens('xelnor', array(), false, true), 1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token IN (\'xelnor\', \'barrois\')'),
                new UFC_NameTokens(array('xelnor', 'barrois'), array(), false, true), -1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token = \'zzzzzzzz\''),
                new UFC_NameTokens('zzzzzzzz', array(), false, true), 0),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token IN (\'zzzzzzzz\', \'yyyyyyyy\')'),
                new UFC_NameTokens(array('zzzzzzzz', 'yyyyyyyy'), array(), false, true), 0),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.token = \'barrois\' AND FIND_IN_SET(\'public\', sn.flags)'),
                new UFC_NameTokens('barrois', UFC_NameTokens::FLAG_PUBLIC, false, true), -1),

            // soundex, !exact
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.soundex = \'XLNO\''),
                new UFC_NameTokens('xelnor', array(), true), -1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.soundex IN (\'XLNO\', \'BROS\')'),
                new UFC_NameTokens(array('xelnor', 'barrois'), array(), true), -1),
            array(self::buildProfileQuery('LEFT JOIN  search_name AS sn ON (p.pid = sn.pid)
                                               WHERE  sn.soundex = \'BROS\' AND FIND_IN_SET(\'public\', sn.flags)'),
                new UFC_NameTokens('barrois', UFC_NameTokens::FLAG_PUBLIC, true), -1),
        );

        /* UFC_Nationality
         */
        $tests['nationality'] = array(
            array(self::buildProfileQuery('WHERE p.nationality1 IN {?} OR p.nationality2 IN {?} OR p.nationality3 IN {?}', array('BR'), array('BR'), array('BR')),
                new UFC_Nationality('BR'), -1),
            array(self::buildProfileQuery('WHERE p.nationality1 IN {?} OR p.nationality2 IN {?} OR p.nationality3 IN {?}', array('BR', 'US'), array('BR', 'US'), array('BR', 'US')),
                new UFC_Nationality('BR', 'US'), -1),
            array(self::buildProfileQuery('WHERE p.nationality1 IN {?} OR p.nationality2 IN {?} OR p.nationality3 IN {?}', array('__'), array('__'), array('__')),
                new UFC_Nationality('__'), 0),
        );

        /* UFC_Dead
         */
        $tests['dead'] = array(
            array(self::buildProfileQuery('WHERE p.deathdate IS NOT NULL'),
                new UFC_Dead(), -1),
            array(self::buildProfileQuery('WHERE p.deathdate IS NOT NULL AND p.deathdate > {?}', '2008-01-01'),
                new UFC_Dead('>', '2008-01-01'), -1),
            array(self::buildProfileQuery('WHERE p.deathdate IS NOT NULL AND p.deathdate < {?}', '1600-01-01'),
                new UFC_Dead('<', '1600-01-01'), 0),
            array(self::buildProfileQuery('WHERE p.deathdate IS NOT NULL AND p.deathdate > {?}', date('Y-m-d')),
                new UFC_Dead('>', 'now'), 0),
        );

        /* UFC_Registered
         */
        $tests['register'] = array(
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state = \'active\''),
                new UFC_Registered(true), -1),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state != \'pending\''),
                new UFC_Registered(), -1),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state = \'active\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date > {?}', '2008-01-01'),
                new UFC_Registered(true, '>', '2008-01-01'), -1),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state != \'pending\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date > {?}', '2008-01-01'),
                new UFC_Registered(false, '>', '2008-01-01'), -1),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state = \'active\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date < {?}', '1700-01-01'),
                new UFC_Registered(true, '<', '1700-01-01'), 0),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state != \'pending\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date < {?}', '1700-01-01'),
                new UFC_Registered(false, '<', '1700-01-01'), 0),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state = \'active\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date > {?}', date('Y-m-d')),
                new UFC_Registered(true, '>', 'now'), 0),
            array(self::buildAccountQuery('WHERE a.uid IS NOT NULL AND a.state != \'pending\' AND a.registration_date != \'0000-00-00 00:00:00\' AND a.registration_date > {?}', date('Y-m-d')),
                new UFC_Registered(false, '>', 'now'), 0),
        );

        $testcases = array();
        foreach ($tests as $name => $t) {
            foreach ($t as $id => $case) {
                $testcases[$name . '-' . $id] = $case;
            }
        }
        return $testcases;
    }

    /**
     * @dataProvider simpleUserProvider
     */
    public function testSimpleUser($query, $cond, $expcount = null)
    {
        /*
         * @param $query A pair MySQL query (one for user selector, one for profile selector)
         * @param $cond  The UFC to test
         * @param $expcount The expected number of results (-1 if that number is unknown)
         */

        $query = $query[0];

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
        $this->assertEquals($count, $uf->getTotalUserCount());
    }

    /**
     * @dataProvider simpleUserProvider
     */
    public function testSimpleProfile($query, $cond, $expcount = null)
    {
        /*
         * @param $query A pair MySQL query (one for user selector, one for profile selector)
         * @param $cond  The UFC to test
         * @param $expcount The expected number of results (-1 if that number is unknown)
         */

        $query = $query[1];

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
        $this->assertEquals($count, $uf->getTotalProfileCount());
        $got = $uf->getPIDs();
        $this->assertEquals($count, count($got));
        sort($got);
        $this->assertEquals($ids, $got);

        $uf = new UserFilter($cond);
        $got = $uf->getPIDs();
        $this->assertEquals($count, count($got));
        sort($got);
        $this->assertEquals($ids, $got);
        $this->assertEquals($count, $uf->getTotalProfileCount());
    }


    public static function sortProvider()
    {
        return array(
            array(self::buildAccountQuery('ORDER BY  a.uid'), new UFO_Uid()),
            array(self::buildAccountQuery('ORDER BY  a.hruid'), new UFO_Hruid()),
            array(self::buildAccountQuery('ORDER BY  a.uid DESC'), new UFO_Uid(true)),
            array(self::buildAccountQuery('ORDER BY  a.hruid DESC'), new UFO_Hruid(true)),
            array(self::buildProfileQuery('ORDER BY  p.pid'), new UFO_Pid()),
            array(self::buildProfileQuery('ORDER BY  p.hrpid'), new UFO_Hrpid()),
            array(self::buildProfileQuery('ORDER BY  p.pid DESC'), new UFO_Pid(true)),
            array(self::buildProfileQuery('ORDER BY  p.hrpid DESC'), new UFO_Hrpid(true)),
            array(self::buildProfileQuery('WHERE  p.deathdate IS NOT NULL
                                        ORDER BY  p.deathdate, p.pid'),
                                          array(new UFO_Death(), new UFO_Pid()), new UFC_Dead()),
            array(self::buildProfileQuery('WHERE  p.deathdate IS NOT NULL
                                        ORDER BY  p.deathdate DESC, p.pid'),
                                          array(new UFO_Death(true), new UFO_Pid()), new UFC_Dead()),
            array(self::buildProfileQuery('ORDER BY  p.next_birthday, p.pid'),
                  array(new UFO_Birthday(), new UFO_Pid())),
            array(self::buildProfileQuery('ORDER BY  p.next_birthday DESC, p.pid'),
                  array(new UFO_Birthday(true), new UFO_Pid())),
            array(self::buildProfileQuery('ORDER BY  p.last_change, p.pid'),
                  array(new UFO_ProfileUpdate(), new UFO_Pid())),
            array(self::buildProfileQuery('ORDER BY  p.last_change DESC, p.pid'),
                  array(new UFO_ProfileUpdate(true), new UFO_Pid())),
            array(self::buildAccountQuery('ORDER BY  a.registration_date, a.uid'),
                  array(new UFO_Registration(), new UFO_Uid())),
            array(self::buildAccountQuery('ORDER BY  a.registration_date DESC, a.uid'),
                  array(new UFO_Registration(true), new UFO_Uid())),
        );
    }

    /**
     * @dataProvider sortProvider
     */
    public function testUserSortAndLimits($query, $sort, $cond = null)
    {
        self::checkPlatal();

        $query = XDB::query($query[0]);
        $count = $query->numRows();
        $ids = $query->fetchColumn();
        $this->assertSame($count, count($ids));

        if ($cond == null ) {
            $cond = new PFC_True();
        }
        $uf = new UserFilter($cond, $sort);
        for ($i = 0 ; $i < $count ; $i += 7987) {
            $got = $uf->getUIDs(new PlLimit(100, $i));
            $this->assertSame($count, $uf->getTotalUserCount());
            $part = array_slice($ids, $i, 100);
            $this->assertSame($part, $got);
        }
    }

    /**
     * @dataProvider sortProvider
     */
    public function testProfileSortAndLimits($query, $sort, $cond = null)
    {
        self::checkPlatal();

        $query = XDB::query($query[1]);
        $count = $query->numRows();
        $ids = $query->fetchColumn();
        $this->assertSame($count, count($ids));

        if ($cond == null ) {
            $cond = new PFC_True();
        }
        $uf = new UserFilter($cond, $sort);
        for ($i = 0 ; $i < $count ; $i += 7987) {
            $got = $uf->getPIDs(new PlLimit(100, $i));
            $this->assertSame($count, $uf->getTotalProfileCount());
            $part = array_slice($ids, $i, 100);
            $this->assertSame($part, $got);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
