#!/usr/bin/env python
#***************************************************************************
#*  Copyright (C) 2004 polytechnique.org                                   *
#*  http://opensource.polytechnique.org/                                   *
#*                                                                         *
#*  This program is free software; you can redistribute it and/or modify   *
#*  it under the terms of the GNU General Public License as published by   *
#*  the Free Software Foundation; either version 2 of the License, or      *
#*  (at your option) any later version.                                    *
#*                                                                         *
#*  This program is distributed in the hope that it will be useful,        *
#*  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
#*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
#*  GNU General Public License for more details.                           *
#*                                                                         *
#*  You should have received a copy of the GNU General Public License      *
#*  along with this program; if not, write to the Free Software            *
#*  Foundation, Inc.,                                                      *
#*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
#***************************************************************************
#   $Id: foreach_template.py,v 1.2 2004-10-28 21:42:51 x2000habouzit Exp $
#***************************************************************************

import base64, MySQLdb, os, getopt, sys, MySQLdb.converters, sha, signal

sys.path.append('/usr/lib/mailman/bin')

from pwd import getpwnam
from grp import getgrnam

from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

import paths
from Mailman import MailList
from Mailman import Utils
from Mailman import Message
from Mailman import Errors
from Mailman import mm_cfg
from Mailman import i18n
from Mailman.UserDesc import UserDesc
from Mailman.ListAdmin import readMessage
from email.Iterators import typed_subpart_iterator


uid = getpwnam(mm_cfg.MAILMAN_USER)[2]
gid = getgrnam(mm_cfg.MAILMAN_GROUP)[2]

if not os.getuid():
    os.setregid(gid,gid)
    os.setreuid(uid,uid)

if ( os.getuid() is not uid ) or ( os.getgid() is not gid):
    sys.exit(0)


for listname in Utils.list_names():
    try:
        mlist = MailList.MailList(listname,lock=0)
    except:
        print 'ERROR for '+listname
        continue
    try:
        mlist.Lock()

        ############################################
        # do treatement here
        ############################################

        mlist.Save()
        mlist.Unlock()
        print 'OK    for '+listname
    except:
        print 'ERROR for '+listname
        continue

print 'DONE'

# vim:set et:
