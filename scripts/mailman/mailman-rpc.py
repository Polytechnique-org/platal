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
#       $Id: mailman-rpc.py,v 1.29 2004-09-23 11:03:20 x2000habouzit Exp $
#***************************************************************************

import base64, MySQLdb, os, getopt, sys, MySQLdb.converters
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

class AuthFailed(Exception): pass

################################################################################
#
# CLASSES
#
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
        new_params.insert(0,self.data)
        return self.server._dispatch(method,new_params)

    def do_POST(self):
        try:
            _, auth   = self.headers["authorization"].split()
            uid, md5  = base64.decodestring(auth).strip().split(':')
            self.data = self.getUser(uid,md5)
            if self.data is None:
                raise AuthFailed
            # Call super.do_POST() to do the actual work
            SimpleXMLRPCRequestHandler.do_POST(self)
        except:
            self.send_response(401)
            self.end_headers()

    def getUser(self, uid, md5):
        mysql.execute ("""SELECT CONCAT(u.prenom, ' ',u.nom),a.alias,u.perms
                           FROM  auth_user_md5 AS u
                     INNER JOIN  aliases       AS a ON a.id=u.user_id
                          WHERE  u.user_id = '%s' AND u.password = '%s'
                          LIMIT  1""" %( uid, md5 ) )
        if int(mysql.rowcount) is 1:
            name,forlife,perms = mysql.fetchone()
            userdesc = UserDesc(forlife+'@polytechnique.org', name, None, 0)
            return (userdesc,perms)
        else:
            return None

################################################################################
#
# XML RPC STUFF
#
#-------------------------------------------------------------------------------
# helpers
#

def connectDB():
    db = MySQLdb.connect(
            db='x4dat',
            user=mm_cfg.MYSQL_USER,
            passwd=mm_cfg.MYSQL_PASS,
            unix_socket='/var/run/mysqld/mysqld.sock')
    db.ping()
    return db.cursor()

def is_admin_on(userdesc,perms,mlist):
    return ( perms == 'admin' ) or ( userdesc.address in mlist.owner )

#-------------------------------------------------------------------------------
# users procedures
#

def get_lists((userdesc,perms)):
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        try:
            mlist = MailList.MailList(name, lock=0)
        except:
            continue
        is_member = userdesc.address in mlist.getRegularMemberKeys()
        is_admin  = mm_cfg.ADMIN_ML_OWNER in mlist.owner
        is_owner  = ( perms == 'admin' and is_admin ) or ( userdesc.address in mlist.owner )
        if mlist.advertised or is_member or is_owner:
            result.append( {
                    'list' : name,
                    'desc' : mlist.description,
                    'diff' : mlist.generic_nonmember_action,
                    'ins'  : mlist.subscribe_policy > 0,
                    'priv' : (1-mlist.advertised)+2*is_admin,
                    'welc' : mlist.welcome_msg,
                    'you'  : is_member + 2*is_owner
                    } )
    return result

def get_members((userdesc,perms),listname):
    try:
        mlist = MailList.MailList(listname, lock=0)
    except:
        return 0
    members = mlist.getRegularMemberKeys()
    is_member = userdesc.address in members
    is_admin  = mm_cfg.ADMIN_ML_OWNER in mlist.owner
    is_owner  = ( perms == 'admin' and is_admin ) or ( userdesc.address in mlist.owner )
    if mlist.advertised or is_member or is_owner or ( perms == 'admin' ):
        members.sort()
        details = { 'addr' : listname+'@polytechnique.org',
                    'desc' : mlist.description,
                    'diff' : mlist.generic_nonmember_action,
                    'ins'  : mlist.subscribe_policy > 0,
                    'priv' : (1-mlist.advertised)+2*is_admin,
                    'welc' : mlist.welcome_msg,
                    'you'  : is_member + 2*is_owner
                  }
        members = map(lambda member: (mlist.getMemberName(member) or '', member), members)
        return (details,members,mlist.owner)
    return 0

def get_members_limit((userdesc,perms),listname,page,nb_per_page):
    try:
        details,members,owners = get_members((userdesc,perms),listname)
    except:
        return 0
    i = (int(page)-1) * int(nb_per_page)
    return (details,members[i:i+int(nb_per_page)],owners,(len(members)-1)/int(nb_per_page)+1)

def subscribe((userdesc,perms),listname):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if ( mlist.subscribe_policy in (0,1) ) or ( userdesc.address in mlist.owner ) or ( mm_cfg.ADMIN_ML_OWNER in mlist.owner ):
            result = 2
            mlist.ApprovedAddMember(userdesc)
        else:
            result = 1
            try:
                mlist.AddMember(userdesc)
            except Errors.MMNeedApproval:
                pass
        mlist.Save()
    except:
        result = 0
    mlist.Unlock()
    return result

def unsubscribe((userdesc,perms),listname):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        mlist.ApprovedDeleteMember(userdesc.address)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# owners procedures
#

def mass_subscribe((userdesc,perms),listname,users):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        
        members = mlist.getRegularMemberKeys()
        added = []
        for user in users:
            mysql.execute ("""SELECT  CONCAT(u.prenom,' ',u.nom), f.alias
                                FROM  auth_user_md5 AS u
                          INNER JOIN  aliases       AS f ON (f.id=u.user_id AND f.type='a_vie')
                          INNER JOIN  aliases       AS a ON (a.id=u.user_id AND a.alias='%s')
                               LIMIT  1""" %( user ) )
            if int(mysql.rowcount) is 1:
                name, forlife = mysql.fetchone()
                if forlife+'@polytechnique.org' in members:
                    continue
                userd = UserDesc(forlife+'@polytechnique.org', name, None, 0)
                mlist.ApprovedAddMember(userd)
                added.append( (userd.fullname, userd.address) )
        mlist.Save()
    finally:
        mlist.Unlock()
        return added

def mass_unsubscribe((userdesc,perms),listname,users):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
    
        map(lambda user: mlist.ApprovedDeleteMember(user+'@polytechnique.org'), users)
        mlist.Save()
    finally:
        mlist.Unlock()
        return users

def add_owner((userdesc,perms),listname,user):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mysql.execute ("""SELECT  f.alias
                            FROM  aliases       AS f
                      INNER JOIN  aliases       AS a ON (a.id=f.id AND a.alias='%s')
                           WHERE  f.type='a_vie'
                           LIMIT  1""" %( user ) )
        if int(mysql.rowcount) is 1:
            forlife = mysql.fetchone()[0]
            if forlife+'@polytechnique.org' not in mlist.owner:
                mlist.owner.append(forlife+'@polytechnique.org')
                mlist.Save()
    finally:
        mlist.Unlock()
        return True

def del_owner((userdesc,perms),listname,user):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        if len(mlist.owner) < 2:
            return 0
        mlist.owner.remove(user+'@polytechnique.org')
        mlist.Save()
    finally:
        mlist.Unlock()
        return True

def set_welcome((userdesc,perms),listname,welcome):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.welcome_msg = welcome
        mlist.Save()
    finally:
        mlist.Unlock()
        return True

def get_pending_ops((userdesc,perms),listname):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        subs = []
        seen = []
        for id in mlist.GetSubscriptionIds():
            time, addr, fullname, passwd, digest, lang = mlist.GetRecord(id)
            if addr in seen:
                mlist.HandleRequest(id, mm_cfg.DISCARD)
                continue
            seen.append(addr)
            subs.append({
                    'id'    : id,
                    'name'  : fullname,
                    'addr'  : addr
                    })

        helds = []
        for id in mlist.GetHeldMessageIds():
            ptime, sender, subject, reason, filename, msgdata = mlist.GetRecord(id)
            try:
                size = os.path.getsize(os.path.join(mm_cfg.DATA_DIR, filename))
            except OSError, e:
                if e.errno <> errno.ENOENT: raise
                continue
            helds.append({
                    'id'    : id,
                    'sender': Utils.oneline(sender,'utf8'),
                    'size'  : size,
                    'subj'  : Utils.oneline(subject,'utf8'),
                    'stamp' : ptime
                    })
        mlist.save();
    except:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return (subs,helds)


def handle_request((userdesc,perms),listname,id,value,comment):
    try:
        mlist = MailList.MailList(listname)
    except:
        raise
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.HandleRequest(int(id),int(value),comment)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        raise
        mlist.Unlock()
        return 0


def get_pending_mail((userdesc,perms),listname,id,raw=0):
    try:
        mlist = MailList.MailList(listname)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        ptime, sender, subject, reason, filename, msgdata = mlist.GetRecord(int(id))
        fpath = os.path.join(mm_cfg.DATA_DIR, filename)
        size = os.path.getsize(fpath)
        msg = readMessage(fpath)
        mlist.Unlock()
        
        if raw:
            return str(msg)
        results = []
        for part in typed_subpart_iterator(msg,'text','plain'):
            results.append (part.get_payload())
        return {'id'    : id,
                'sender': Utils.oneline(sender,'utf8'),
                'size'  : size,
                'subj'  : Utils.oneline(subject,'utf8'),
                'stamp' : ptime,
                'parts' : results }
    except:
        mlist.Unlock()
        return 0

def is_admin((userdesc,perms),listname):
    try:
        mlist = MailList.MailList(listname, lock=0)
    except:
        return 0
    return is_admin_on(userdesc, perms, mlist)

################################################################################
#
# INIT 
#
#-------------------------------------------------------------------------------
# use Mailman user and group (not root)
# fork in background if asked to
#

uid = getpwnam(mm_cfg.MAILMAN_USER)[2]
gid = getgrnam(mm_cfg.MAILMAN_GROUP)[2]

if not os.getuid():
    os.setregid(gid,gid)
    os.setreuid(uid,uid)

if ( os.getuid() is not uid ) or ( os.getgid() is not gid):
    sys.exit(0)

opts, args = getopt.getopt(sys.argv[1:], 'f')
for o, a in opts:
    if o == '-f' and os.fork():
        sys.exit(0)

#-------------------------------------------------------------------------------
# server
#
class FastXMLRPCServer(SimpleXMLRPCServer):
    allow_reuse_address = True

mysql = connectDB()
server = FastXMLRPCServer(("localhost", 4949), BasicAuthXMLRPCRequestHandler)

server.register_function(get_lists)
server.register_function(get_members)
server.register_function(get_members_limit)
server.register_function(subscribe)
server.register_function(unsubscribe)

server.register_function(mass_subscribe)
server.register_function(mass_unsubscribe)
server.register_function(add_owner)
server.register_function(del_owner)
server.register_function(set_welcome)
server.register_function(get_pending_ops)
server.register_function(handle_request)
server.register_function(get_pending_mail)
server.register_function(is_admin)

server.serve_forever()

# vim:set et:
