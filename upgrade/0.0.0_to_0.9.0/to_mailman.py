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
#   $Id: to_mailman.py,v 1.1 2004-11-20 22:53:52 x2000habouzit Exp $
#***************************************************************************

import base64, MySQLdb, os, getopt, sys, MySQLdb.converters, sha
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

def set_options(vhost,listname,opts,vals):
    try:
        mlist = MailList.MailList(vhost+'-'+listname)
    except:
        return 0
    try:
        for (k,v) in vals.iteritems():
            if k not in opts:
                continue
            if k == 'default_member_moderation':
                for member in mlist.getMembers():
                    mlist.setMemberOption(member, mm_cfg.Moderate, int(v))
            t = type(mlist.__dict__[k])
            if   t is bool: mlist.__dict__[k] = bool(v)
            elif t is int:  mlist.__dict__[k] = int(v)
            elif t is str:  mlist.__dict__[k] = Utils.uncanonstr(v,'fr')
            else:           mlist.__dict__[k] = v
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        raise
        return 0

def mass_subscribe(vhost,listname,users):
    try:
        mlist = MailList.MailList(vhost+'-'+listname)
    except:
        return 0
    try:
        members = mlist.getRegularMemberKeys()
        added = []
        for user in users:
            name, forlife = user;
            if forlife+'@polytechnique.org' in members:
                continue
            userd = UserDesc(forlife+'@polytechnique.org', name, None, 0)
            mlist.ApprovedAddMember(userd, 0, 0)
            added.append( (userd.fullname, userd.address) )
        mlist.Save()
    finally:
        mlist.Unlock()
        return added

def add_owner(vhost,listname,forlife):
    try:
        mlist = MailList.MailList(vhost+'-'+listname)
    except:
        return 0
    try:
        if forlife+'@polytechnique.org' not in mlist.owner:
            mlist.owner.append(forlife+'@polytechnique.org')
            mlist.Save()
    finally:
        mlist.Unlock()
        return True

#-------------------------------------------------------------------------------
# admin procedures [ check.php ]
#

check_opts = {
    'acceptable_aliases'            : '',
    'admin_immed_notify'            : True,
    'administrivia'                 : True,
    'anonymous_list'                : False,
    'autorespond_admin'             : False,
    'autorespond_postings'          : False,
    'autorespond_requests'          : False,
    'available_languages'           : ['fr'],
    'ban_list'                      : [],
    'bounce_matching_headers'       : '',
    'bounce_processing'             : False,
    'convert_html_to_plaintext'     : False,
    'digestable'                    : False,
    'digest_is_default'             : False,
    'discard_these_nonmembers'      : [],
    'emergency'                     : False,
    'encode_ascii_prefixes'         : 2,
    'filter_content'                : False,
    'first_strip_reply_to'          : False,
    'forward_auto_discards'         : True,
    'header_filter_rules'           : [],
    'hold_these_nonmembers'         : [],
    'include_list_post_header'      : False,
    'include_rfc2369_headers'       : False,
    'new_member_options'            : 256,
    'nondigestable'                 : True,
    'obscure_addresses'             : True,
    'preferred_language'            : 'fr',
    'reject_these_nonmembers'       : [],
    'reply_goes_to_list'            : 0,
    'reply_to_address'              : '',
    'require_explicit_destination'  : False,
    'send_reminders'                : 0,
    'send_welcome_msg'              : True,
    'topics_enabled'                : False,
    'umbrella_list'                 : False,
    'unsubscribe_policy'            : 0,
}

def check_options(vhost,listname,correct=False):
    try:
        mlist = MailList.MailList(vhost+'-'+listname)
    except:
        return 0
    try:
        options = { }
        for (k,v) in check_opts.iteritems():
            if mlist.__dict__[k] != v:
                options[k] = v,mlist.__dict__[k]
                if correct: mlist.__dict__[k] = v
        if mlist.real_name != listname:
            options['real_name'] = listname, mlist.real_name
            if correct: mlist.real_name = listname
        if mlist.host_name != vhost:
            options['real_name'] = vhost, mlist.host_name
            if correct: mlist.host_name = vhost
        if correct: mlist.Save()
        mlist.Unlock()
        return (details,options)
    except:
        mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# admin procedures [ soptions.php ]
#

def create_list(vhost,listname,desc,advertise,modlevel,inslevel,owners,members):
    name = vhost+'-'+listname;
    if Utils.list_exists(name):
        return 0

    mlist = MailList.MailList()
    try:
        oldmask = os.umask(002)
        pw = sha.new('foobar').hexdigest()
        try:
            mlist.Create(name, owners[0], pw)
        finally:
            os.umask(oldmask)

        mlist.real_name = listname
        mlist.host_name = vhost
        mlist.description = desc

        mlist.advertised = int(advertise) > 0
        mlist.default_member_moderation = int(modlevel) is 2
        mlist.generic_nonmember_action = int(modlevel) > 0
        mlist.subscribe_policy = 2 * (int(inslevel) is 1)
        
        mlist.owner = owners
        
        mlist.subject_prefix = '['+listname+'] '
        mlist.max_message_size = 0

        mlist.Save()
        mlist.Unlock()
        check_options(vhost,listname,True)
        mass_subscribe(vhost,listname,members)
    except:
        raise
        return 0
    return 1

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

mysql = connectDB()

#-------------------- LOGIC ---------------------

mysql.execute ("""SELECT  a.id, a.alias, d.topic, d.bienvenue,
                          FIND_IN_SET('publique',d.type) AND NOT FIND_IN_SET('promo',d.type) AS advertised,
                          (1-FIND_IN_SET('freeins',d.type)) AS inslevel,
                          (1-FIND_IN_SET('libre',d.type))*2 AS modlevel
                    FROM  aliases    AS a
              INNER JOIN  listes_def AS d USING(id)
                   WHERE  a.type='liste'""")
lists = mysql.fetchall()
i=0
l=len(lists)

for id,alias,desc,welcome,advertise,inslevel,modlevel in lists:
    mysql.execute ( """SELECT  a.alias
                         FROM  auth_user_md5 AS u
                   INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND a.type='a_vie' )
                   INNER JOIN  listes_mod    AS m ON ( m.idu=u.user_id AND m.idl='%i' )""" %(id) )
    owners = map(lambda x: x[0]+'@polytechnique.org', mysql.fetchall())
    if owners == []: owners = ['listes@m4x.org']
    if desc.startswith('ADMIN/'): owners=[mm_cfg.ADMIN_ML_OWNER]
    mysql.execute ( """SELECT  CONCAT(u.prenom, ' ',u.nom),a.alias
                         FROM  auth_user_md5 AS u
                   INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND a.type='a_vie' )
                   INNER JOIN  listes_ins    AS m ON ( m.idu=u.user_id AND m.idl='%i' )""" %(id) )
    members = mysql.fetchall()
    create_list('polytechnique.org',alias,desc,advertise,modlevel,inslevel,owners,members)
    set_options('polytechnique.org',alias,['welcome_msg'],{'welcome_msg':welcome})
    i = i+1
    print ("""[%3i/%i] '%s' created""" %(i,l,alias))

# vim:set et:
