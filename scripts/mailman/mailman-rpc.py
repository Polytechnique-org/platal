#!/usr/bin/env python
#***************************************************************************
#*  Copyright (C) 2004 Polytechnique.org                                   *
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
#       $Id: mailman-rpc.py,v 1.7 2004-09-09 09:25:51 x2000habouzit Exp $
#***************************************************************************

import base64, MySQLdb

from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

from Mailman import mm_cfg
from Mailman import MailList
from Mailman import Utils
from Mailman import Errors
from Mailman.i18n import _

class UserDesc: pass

class AuthFailed(Exception): pass

import MySQLdb.converters

#------------------------------------------------
# Manage Basic authentication
#

class BasicAuthXMLRPCRequestHandler(SimpleXMLRPCRequestHandler):

    """XMLRPC Request Handler
    This request handler is used to provide BASIC HTTP user authentication.
    It first overloads the do_POST() function, authenticates the user, then
    calls the super.do_POST().

    Moreover, we override _dispatch, so that we call functions with as first
    argument a UserDesc taken from the database, containing name, email and perms
    """

    def _dispatch(self,method,params):
        # TODO: subclass in SimpleXMLRPCDispatcher and not here.
        new_params = list(params)
        new_params.insert(0,self.userdesc)
        return self.server._dispatch(method,new_params)

    def do_POST(self):
        headers = self.headers
        try:
            if not headers.has_key("authorization"):
                raise AuthFailed
            auth = headers["authorization"]
            _, auth = auth.split()
            uid, md5 = base64.decodestring(auth).strip().split(':')
            self.userdesc = self.getUserDesc(uid,md5)
            if self.userdesc is None:
                raise AuthFailed
            # Call super.do_POST() to do the actual work
            SimpleXMLRPCRequestHandler.do_POST(self)
        except:
            self.send_response(401)
            self.end_headers()

    def getUserDesc(self, uid, md5):
        mysql.execute ("""SELECT  u.prenom,u.nom,a.alias,u.perms
                           FROM  auth_user_md5 AS u
                     INNER JOIN  aliases       AS a ON a.id=u.user_id
                          WHERE  u.user_id = '%s' AND u.password = '%s'
                          LIMIT  1""" %( uid, md5 ) )
        if int(mysql.rowcount) is 1:
            user = mysql.fetchone()
            userdesc = UserDesc()
            userdesc.fullname = user[0]+' '+user[1]
            userdesc.address = user[2]+'@polytechnique.org'
            userdesc.digest = 0
            userdesc.perms = user[3]
            return userdesc
        else:
            return None

#------------------------------------------------
# Procedures
#

def connectDB():
    try:
        db = MySQLdb.connect(
                db='x4dat',
                user=mm_cfg.MYSQL_USER,
                passwd=mm_cfg.MYSQL_PASS,
                unix_socket='/var/run/mysqld/mysqld.sock')
        db.ping()
        return db.cursor()
    except:
        return None

def lists_names(userdesc):
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        try:
            mlist = MailList.MailList(name, lock=0)
        except Errors.MMListError:
            continue
        is_member = False
        is_owner = False
        for member in mlist.getRegularMemberKeys():
            if userdesc.address == member:
                is_member = True
                break
        for owner in mlist.owner:
            if userdesc.address == owner:
                is_owner = True
                break
        if ( mlist.advertised ) or ( userdesc.perms == 'admin' ) or is_member or is_owner:
            result.append( (name,mlist.advertised,is_member,is_owner) )
    return result

def members(userdesc,listname):
    try:
        mlist = MailList.MailList(listname, lock=False)
    except Errors.MMListError, e:
        return None
    members = mlist.getRegularMemberKeys()
    if ( userdesc.perms == 'admin' ) or ( mlist.advertised ):
        return (members,mlist.owners)
    for member in members:
        if member == userdesc.address:
            return (members,mlist.owners)
    for member in mlist.owner:
        if member == userdesc.address:
            return (members,mlist.owners)

def subscribe(userdesc,listname):
    try:
        mlist = MailList.MailList(listname, lock=True)
    except Errors.MMListError, e:
        return 0
    try:
        approved = ( mlist.subscribe_policy in (0,1) ) or ( userdesc.perms == 'admin' )
        if approved is False :
            for owner in mlist.owner:
                if owner == userdesc.address:
                    approved = True
                    break
        if approved:
            result = 2
            mlist.ApprovedAddMember(userdesc)
            mlist.Save()
        else:
            result = 1
            mlist.AddMember(userdesc,'xml-rpc iface')
    except Exception, e:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return result

def unsubscribe(userdesc,listname):
    # here : no rights to verify, because if we can delete us ...
    #        it's that we are in there, else we delete nobody, so no harm
    try:
        mlist = MailList.MailList(listname, lock=True)
    except Errors.MMListError, e:
        return 0
    try:
        mlist.ApprovedDeleteMember(userdesc.address, 'xml-rpc iface', False, False);
        mlist.Save()
    except Exception, e:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return 1

#------------------------------------------------
# server
#

mysql = connectDB()
server = SimpleXMLRPCServer(("localhost", 4949), BasicAuthXMLRPCRequestHandler)
server.register_function(lists_names)
server.register_function(members)
server.register_function(subscribe)
server.register_function(unsubscribe)
#server.register_introspection_functions()
server.serve_forever()

# vim:set et:
