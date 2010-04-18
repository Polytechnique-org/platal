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
    public static function simpleUserProvider()
    {
        return array(
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
                  new UFC_Hruid(array('florent.bruneau.2003', 'stephane.jacob.2004')), 2),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  accounts
                                WHERE  hruid IN {?}', array('florent.bruneau.2004',
                                                            'stephane.jacob.2004')),
                  new UFC_Hruid(array('florent.bruneau.2004', 'stephane.jacob.2004')), 1),

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
                  new UFC_Hrpid(array('florent.bruneau.2003', 'stephane.jacob.2004')), 2),
            array(XDB::format('SELECT  DISTINCT uid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', perms))
                                WHERE  hrpid IN {?}', array('florent.bruneau.2004',
                                                            'stephane.jacob.2004')),
                  new UFC_Hrpid(array('florent.bruneau.2004', 'stephane.jacob.2004')), 1),

            /* UFC_IP
             */
            array(XDB::format('SELECT  DISTINCT a.uid
                                 FROM  log_sessions
                           INNER JOIN  accounts AS a USING(uid)
                                WHERE  ip = {?} OR forward_ip = {?}',
                              ip_to_uint('129.104.247.2'), ip_to_uint('129.104.247.2')),
                  new UFC_Ip('129.104.247.2'), -1),
        );
    }

    /**
     * @dataProvider simpleUserProvider
     */
    public function testSimpleUser($query, $cond, $expcount = null)
    {
        global $globals, $platal;
        $platal = new Xorg();

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
        /* XXX: API issue, there's no guarantee getTotalCount()
           returns the number of users.
        */
        //$this->assertEquals($count, $uf->getTotalCount());
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
