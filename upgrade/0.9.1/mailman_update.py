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

import base64, MySQLdb, os, getopt, sys, MySQLdb.converters, sha

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

names = Utils.list_names()
for listname in names:
    try:
        mlist = MailList.MailList(listname,lock=0)
    except:
        print 'ERROR '+listname
        continue
    try:
        print 'BEGIN '+listname
        mlist.Lock()
        mlist.header_filter_rules = []
        mlist.header_filter_rules.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
        print '      set new bogofilter policy'
        mlist.Save()
        mlist.Unlock()
        print 'END'
    except:
        mlist.Unlock()
        print 'ERROR '+listname


# vim:set et:
