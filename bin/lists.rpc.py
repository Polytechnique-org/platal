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

import base64, MySQLdb, os, getopt, sys, sha, signal, re, shutil, ConfigParser
import MySQLdb.converters

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

class AuthFailed(Exception): pass

################################################################################
#
# CONFIG
#
#------------------------------------------------

config = ConfigParser.ConfigParser()
config.read(os.path.dirname(__file__)+'/../configs/platal.conf')

def get_config(sec,val,default=None):
    try:
        return config.get(sec, val)[1:-1]
    except ConfigParser.NoOptionError, e:
        if default is None:
            print e
            sys.exit(1)
        else:
            return default

BASEURL        = get_config('Core', 'baseurl')
MYSQL_USER     = get_config('Core', 'dbuser')
MYSQL_PASS     = get_config('Core', 'dbpwd')

PLATAL_DOMAIN  = get_config('Mail', 'domain')
PLATAL_DOMAIN2 = get_config('Mail', 'domain2', '')

ML_OWNER       = get_config('Lists', 'admin_owner')
VHOST_SEP      = get_config('Lists', 'vhost_sep', '-')

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
        new_params.insert(0,self.data[2])
        new_params.insert(0,self.data[1])
        new_params.insert(0,self.data[0])
        return self.server._dispatch(method,new_params)

    def do_POST(self):
        try:
            _, auth   = self.headers["authorization"].split()
            uid, md5  = base64.decodestring(auth).strip().split(':')
            vhost     = self.path.split('/')[1].lower()
            self.data = self.getUser(uid,md5,vhost)
            if self.data is None:
                raise AuthFailed
            # Call super.do_POST() to do the actual work
            SimpleXMLRPCRequestHandler.do_POST(self)
        except:
            self.send_response(401)
            self.end_headers()

    def getUser(self, uid, md5, vhost):
        mysql.execute ("""SELECT CONCAT(u.prenom, ' ',u.nom),a.alias,u.perms
                           FROM  auth_user_md5 AS u
                     INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND a.type='a_vie' )
                          WHERE  u.user_id = '%s' AND u.password = '%s' AND u.perms IN ('admin','user')
                          LIMIT  1""" %( uid, md5 ) )
        if int(mysql.rowcount) is 1:
            name,forlife,perms = mysql.fetchone()
            if vhost != PLATAL_DOMAIN:
                mysql.execute ("""SELECT  uid
                                    FROM  groupex.membres AS m
                              INNER JOIN  groupex.asso    AS a ON (m.asso_id = a.id)
                                   WHERE  perms='admin' AND uid='%s' AND mail_domain='%s'""" %( uid , vhost ) )
                if int(mysql.rowcount) is 1: perms= 'admin'
            userdesc = UserDesc(forlife+'@'+PLATAL_DOMAIN, name, None, 0)
            return (userdesc,perms,vhost)
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
            user=MYSQL_USER,
            passwd=MYSQL_PASS,
            unix_socket='/var/run/mysqld/mysqld.sock')
    db.ping()
    return db.cursor()

def is_owner(userdesc,perms,mlist):
    return ( perms == 'admin' and ML_OWNER in mlist.owner ) or ( userdesc.address in mlist.owner )

def is_admin_on(userdesc,perms,mlist):
    return ( perms == 'admin' ) or ( userdesc.address in mlist.owner )


def quote(s,is_header=False):
    if is_header:
        h = Utils.oneline(s,'iso-8859-1')
    else:
        h = s
    return Utils.uquote(h.replace('&','&amp;').replace('>','&gt;').replace('<','&lt;'))

def to_forlife(email):
    try:
        mbox,fqdn = email.split('@')
    except:
        mbox = email
        fqdn = PLATAL_DOMAIN
    if ( fqdn == PLATAL_DOMAIN ) or ( fqdn == PLATAL_DOMAIN2 ):
        mysql.execute ("""SELECT  CONCAT(f.alias,'@%s'), CONCAT(u.prenom,' ',u.nom)
                            FROM  auth_user_md5 AS u
                      INNER JOIN  aliases       AS f ON (f.id=u.user_id AND f.type='a_vie')
                      INNER JOIN  aliases       AS a ON (a.id=u.user_id AND a.alias='%s' AND a.type!='homonyme')
                           WHERE  u.perms IN ('admin','user')
                           LIMIT  1""" %( PLATAL_DOMAIN, mbox ) )
        if int(mysql.rowcount) is 1:
            return mysql.fetchone()
        else:
            return (None,None)
    return (email,mbox)

##
# see /usr/lib/mailman/bin/rmlist
##
def remove_it(listname, filename):
    if os.path.islink(filename) or os.path.isfile(filename):
        os.unlink(filename)
    elif os.path.isdir(filename):
        shutil.rmtree(filename)
    
#-------------------------------------------------------------------------------
# helpers on lists
#

def get_list_info(userdesc,perms,mlist,front_page=0):
    members    = mlist.getRegularMemberKeys()
    is_member  = userdesc.address in members
    is_admin   = ML_OWNER in mlist.owner
    is_owner   = ( perms == 'admin' and is_admin ) or ( userdesc.address in mlist.owner )
    if mlist.advertised or is_member or is_owner or (not front_page and perms == 'admin'):
        is_pending = False
        if not is_member and (mlist.subscribe_policy > 1):
            try:
                mlist.Lock()
                for id in mlist.GetSubscriptionIds():
                    if userdesc.address == mlist.GetRecord(id)[1]:
                        is_pending = 1
                        break
                mlist.Unlock()
            except:
                mlist.Unlock()
                return 0
        
        details = {
                'list' : mlist.real_name,
                'addr' : mlist.real_name.lower() + '@' + mlist.host_name.lower(),
                'host' : mlist.host_name.lower(),
                'desc' : quote(mlist.description),
                'info' : quote(mlist.info),
                'diff' : (mlist.default_member_moderation>0) + (mlist.generic_nonmember_action>0),
                'ins'  : mlist.subscribe_policy > 1,
                'priv' : (1-mlist.advertised)+2*is_admin,
                'sub'  : 2*is_member + is_pending,
                'own'  : is_owner,
                'nbsub': len(members)
                }
        return (details,members)
    return 0

def get_options(userdesc,perms,vhost,listname,opts):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        options = { }
        for (k,v) in mlist.__dict__.iteritems():
            if k in opts:
                if type(v) is str:
                    options[k] = quote(v)
                else: options[k] = v
        details = get_list_info(userdesc,perms,mlist)[0]
        return (details,options)
    except:
        return 0

def set_options(userdesc,perms,vhost,listname,opts,vals):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.Lock()
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
        return 0

#-------------------------------------------------------------------------------
# users procedures for [ index.php ]
#

def get_lists(userdesc,perms,vhost,email=None):
    if email is None:
        udesc = userdesc
    else:
        udesc = UserDesc(email, email, None, 0)
    prefix = vhost.lower()+VHOST_SEP
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        if not name.startswith(prefix):
            continue
        try:
            mlist = MailList.MailList(name,lock=0)
        except:
            continue
        try:
            details = get_list_info(udesc,perms,mlist,(email is None))[0]
            result.append(details)
        except:
            continue
    return result

def subscribe(userdesc,perms,vhost,listname):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        mlist.Lock()
        if ( mlist.subscribe_policy in (0,1) ) or is_owner(userdesc,perms,mlist):
            mlist.ApprovedAddMember(userdesc)
            result = 2
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

def unsubscribe(userdesc,perms,vhost,listname):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        mlist.Lock()
        mlist.ApprovedDeleteMember(userdesc.address)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# users procedures for [ index.php ]
#

def get_members(userdesc,perms,vhost,listname):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        details,members = get_list_info(userdesc,perms,mlist)
        members.sort()
        members = map(lambda member: (quote(mlist.getMemberName(member)) or '', member), members)
        return (details,members,mlist.owner)
    except:
        return 0

#-------------------------------------------------------------------------------
# users procedures for [ trombi.php ]
#

def get_members_limit(userdesc,perms,vhost,listname,page,nb_per_page):
    try:
        members = get_members(userdesc,perms,vhost,listname.lower())[1]
    except:
        return 0
    i = int(page) * int(nb_per_page)
    return (len(members), members[i:i+int(nb_per_page)])

def get_owners(userdesc,perms,vhost,listname):
    try:
        details,members,owners = get_members(userdesc,perms,vhost,listname.lower())
    except:
        return 0
    return (details,owners)

#-------------------------------------------------------------------------------
# owners procedures [ admin.php ]
#

def mass_subscribe(userdesc,perms,vhost,listname,users):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        
        members = mlist.getRegularMemberKeys()
        added = []
        mlist.Lock()
        for user in users:
            email, name = to_forlife(user)
            if ( email is None ) or ( email in members ):
                continue
            userd = UserDesc(email, name, None, 0)
            mlist.ApprovedAddMember(userd)
            added.append( (quote(userd.fullname), userd.address) )
        mlist.Save()
    except:
        pass
    mlist.Unlock()
    return added

def mass_unsubscribe(userdesc,perms,vhost,listname,users):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        
        mlist.Lock()
        map(lambda user: mlist.ApprovedDeleteMember(user), users)
        mlist.Save()
    except:
        pass
    mlist.Unlock()
    return users

def add_owner(userdesc,perms,vhost,listname,user):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        email = to_forlife(user)[0]
        if email is None:
            return 0
        if email not in mlist.owner:
            mlist.Lock()
            mlist.owner.append(email)
            mlist.Save()
    except:
        pass
    mlist.Unlock()
    return True

def del_owner(userdesc,perms,vhost,listname,user):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        if len(mlist.owner) < 2:
            return 0
        mlist.Lock()
        mlist.owner.remove(user)
        mlist.Save()
    except:
        pass
    mlist.Unlock()
    return True

#-------------------------------------------------------------------------------
# owners procedures [ admin.php ]
#

def get_pending_ops(userdesc,perms,vhost,listname):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
       
        mlist.Lock()
        
        subs = []
        seen = []
        dosave = False
        for id in mlist.GetSubscriptionIds():
            time, addr, fullname, passwd, digest, lang = mlist.GetRecord(id)
            if addr in seen:
                mlist.HandleRequest(id, mm_cfg.DISCARD)
                dosave = True
                continue
            seen.append(addr)
            try:
                login = re.match("^[^.]*\.[^.]*\.\d\d\d\d$", addr.split('@')[0]).group()
                subs.append({'id': id, 'name': quote(fullname), 'addr': addr, 'login': login })
            except:
                subs.append({'id': id, 'name': quote(fullname), 'addr': addr })

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
                    'sender': quote(sender, True),
                    'size'  : size,
                    'subj'  : quote(subject, True),
                    'stamp' : ptime
                    })
        if dosave: mlist.Save()
        mlist.Unlock()
    except:
        mlist.Unlock()
        return 0
    return (subs,helds)


def handle_request(userdesc,perms,vhost,listname,id,value,comment):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.Lock()
        mlist.HandleRequest(int(id),int(value),comment)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0


def get_pending_mail(userdesc,perms,vhost,listname,id,raw=0):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.Lock()
        ptime, sender, subject, reason, filename, msgdata = mlist.GetRecord(int(id))
        fpath = os.path.join(mm_cfg.DATA_DIR, filename)
        size = os.path.getsize(fpath)
        msg = readMessage(fpath)
        mlist.Unlock()
        
        if raw:
            return str(msg)
        results = []
        for part in typed_subpart_iterator(msg,'text','plain'):
            c = part.get_payload()
            if c is not None: results.append (c)
        results = map(lambda x: quote(x), results)
        return {'id'    : id,
                'sender': quote(sender, True),
                'size'  : size,
                'subj'  : quote(subject, True),
                'stamp' : ptime,
                'parts' : results }
    except:
        mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# owner options [ options.php ]
#

owner_opts = ['accept_these_nonmembers', 'admin_notify_mchanges', 'description', \
        'default_member_moderation', 'generic_nonmember_action', 'info', \
        'subject_prefix', 'goodbye_msg', 'send_goodbye_msg', 'subscribe_policy', \
        'welcome_msg']

def get_owner_options(userdesc,perms,vhost,listname):
    return get_options(userdesc,perms,vhost,listname.lower(),owner_opts)

def set_owner_options(userdesc,perms,vhost,listname,values):
    return set_options(userdesc,perms,vhost,listname.lower(),owner_opts,values)

def add_to_wl(userdesc,perms,vhost,listname,addr):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.Lock()
        mlist.accept_these_nonmembers.append(addr)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0

def del_from_wl(userdesc,perms,vhost,listname,addr):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        mlist.Lock()
        mlist.accept_these_nonmembers.remove(addr)
        mlist.Save()
        mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0

def get_bogo_level(userdesc,perms,vhost,listname):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        if mlist.header_filter_rules == []:
            return 0
        action = mlist.header_filter_rules[0][1]
        if action == mm_cfg.HOLD:
            return 1
        if action == mm_cfg.DISCARD:
            return 2
    except:
        return 0

def set_bogo_level(userdesc,perms,vhost,listname,level):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if not is_admin_on(userdesc, perms, mlist):
            return 0
        hfr = []
        if int(level) is 1:
            hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
        elif int(level) is 2:
            hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.DISCARD, False))
        if mlist.header_filter_rules != hfr:
            mlist.Lock()
            mlist.header_filter_rules = hfr
            mlist.Save()
            mlist.Unlock()
        return 1
    except:
        mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# admin procedures [ soptions.php ]
#

admin_opts = [ 'advertised', 'archive', \
        'max_message_size', 'msg_footer', 'msg_header']

def get_admin_options(userdesc,perms,vhost,listname):
    if perms != 'admin':
        return 0
    return get_options(userdesc,perms,vhost,listname.lower(),admin_opts)

def set_admin_options(userdesc,perms,vhost,listname,values):
    if perms != 'admin':
        return 0
    return set_options(userdesc,perms,vhost,listname.lower(),admin_opts,values)

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
    'max_num_recipients'            : 0,
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

def check_options(userdesc,perms,vhost,listname,correct=False):
    try:
        mlist = MailList.MailList(vhost+VHOST_SEP+listname.lower(),lock=0)
    except:
        return 0
    try:
        if perms != 'admin': return 0
        if correct:
            mlist.Lock()
        options = { }
        for (k,v) in check_opts.iteritems():
            if mlist.__dict__[k] != v:
                options[k] = v,mlist.__dict__[k]
                if correct: mlist.__dict__[k] = v
        if mlist.real_name.lower() != listname:
            options['real_name'] = listname, mlist.real_name
            if correct: mlist.real_name = listname
        if mlist.host_name != vhost:
            options['real_name'] = vhost, mlist.host_name
            if correct: mlist.host_name = vhost
        if correct:
            mlist.Save()
            mlist.Unlock()
        details = get_list_info(userdesc,perms,mlist)[0]
        return (details,options)
    except:
        if correct: mlist.Unlock()
        return 0

#-------------------------------------------------------------------------------
# super-admin procedures
#

def get_all_lists(userdesc,perms,vhost):
    prefix = vhost.lower()+VHOST_SEP
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        if not name.startswith(prefix):
            continue
        result.append(name.replace(prefix,''))
    return result

def create_list(userdesc,perms,vhost,listname,desc,advertise,modlevel,inslevel,owners,members):
    if perms != 'admin':
        return 0
    name = vhost.lower()+VHOST_SEP+listname.lower();
    if Utils.list_exists(name):
        return 0
        
    owner = []
    for o in owners:
        email = to_forlife(o)[0]
        if email is not None:
            owner.append(email)
    if len(owner) is 0:
        return 0

    mlist = MailList.MailList()
    try:
        oldmask = os.umask(002)
        pw = sha.new('foobar').hexdigest()
        
        try:
            mlist.Create(name, owner[0], pw)
        finally:
            os.umask(oldmask)

        mlist.real_name = listname
        mlist.host_name = vhost
        mlist.description = desc

        mlist.advertised = int(advertise) is 0
        mlist.default_member_moderation = int(modlevel) is 2
        mlist.generic_nonmember_action = int(modlevel) > 0
        mlist.subscribe_policy = 2 * (int(inslevel) is 1)
        mlist.admin_notify_mchanges = (mlist.subscribe_policy or mlist.generic_nonmember_action or mlist.default_member_moderation or not mlist.advertised)
        
        mlist.owner = owner
        
        mlist.subject_prefix = '['+listname+'] '
        mlist.max_message_size = 0

        mlist.msg_footer = "_______________________________________________\n" \
                         + "Liste de diffusion %(real_name)s\n" \
                         + BASEURL+"/listes/"
        
        mlist.header_filter_rules = []
        mlist.header_filter_rules.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))

        mlist.Save()

        mlist.Unlock()

        check_options(userdesc,perms,vhost,listname.lower(),True)
        mass_subscribe(userdesc,perms,vhost,listname.lower(),members)

        # avoid the "-1 mail to moderate" bug
        mlist = MailList.MailList(name)
        mlist._UpdateRecords()
        mlist.Save()
        mlist.Unlock()
    except:
        try:
            mlist.Unlock()
        except:
            pass
        return 0
    return 1

def delete_list(userdesc,perms,vhost,listname,del_archives=0):
    lname = vhost+VHOST_SEP+listname.lower()
    try:
        mlist = MailList.MailList(lname,lock=0)
    except:
        return 0
    try:
        if perms != 'admin': return 0
        # remove the list
        REMOVABLES = [ os.path.join('lists', lname), ]
        # remove stalled locks
        for filename in os.listdir(mm_cfg.LOCK_DIR):
            fn_lname = filename.split('.')[0]
            if fn_lname == lname:
                REMOVABLES.append(os.path.join(mm_cfg.LOCK_DIR, filename))
        # remove archives ?
        if del_archives:
            REMOVABLES.extend([
                    os.path.join('archives', 'private', lname),
                    os.path.join('archives', 'private', lname+'.mbox'),
                    os.path.join('archives', 'public',  lname),
                    os.path.join('archives', 'public',  lname+'.mbox')
                ])
        map(lambda dir: remove_it(lname, os.path.join(mm_cfg.VAR_PREFIX, dir)), REMOVABLES)
        return 1
    except:
        return 0

def kill(userdesc,perms,vhost,alias,del_from_promo):
    exclude = []
    if not del_from_promo:
        exclude.append(PLATAL_DOMAIN+VHOST_SEP+'promo'+alias[-4:])
    for list in Utils.list_names():
        if list in exclude: continue
        try:
            mlist = MailList.MailList(list,lock=0)
        except:
            continue
        try:
            mlist.Lock()
            mlist.ApprovedDeleteMember(alias+'@'+PLATAL_DOMAIN,None,0,0)
            mlist.Save()
            mlist.Unlock()
        except:
            mlist.Unlock()
    return 1


#-------------------------------------------------------------------------------
# server
#
class FastXMLRPCServer(SimpleXMLRPCServer):
    allow_reuse_address = True 

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

signal.signal(signal.SIGHUP, signal.SIG_IGN)

if ( os.getuid() is not uid ) or ( os.getgid() is not gid):
    sys.exit(0)

opts, args = getopt.getopt(sys.argv[1:], 'f')
for o, a in opts:
    if o == '-f' and os.fork():
        sys.exit(0)

i18n.set_language('fr')
mysql = connectDB()

#-------------------------------------------------------------------------------
# server
#
server = FastXMLRPCServer(("localhost", 4949), BasicAuthXMLRPCRequestHandler)

# index.php
server.register_function(get_lists)
server.register_function(subscribe)
server.register_function(unsubscribe)
# members.php
server.register_function(get_members)
# trombi.php
server.register_function(get_members_limit)
server.register_function(get_owners)
# admin.php
server.register_function(mass_subscribe)
server.register_function(mass_unsubscribe)
server.register_function(add_owner)
server.register_function(del_owner)
# moderate.php
server.register_function(get_pending_ops)
server.register_function(handle_request)
server.register_function(get_pending_mail)
# options.php
server.register_function(get_owner_options)
server.register_function(set_owner_options)
server.register_function(add_to_wl)
server.register_function(del_from_wl)
server.register_function(get_bogo_level)
server.register_function(set_bogo_level)
# soptions.php
server.register_function(get_admin_options)
server.register_function(set_admin_options)
# check.php
server.register_function(check_options)
# create + del
server.register_function(get_all_lists)
server.register_function(create_list)
server.register_function(delete_list)
# utilisateurs.php
server.register_function(kill)

server.serve_forever()

# vim:set et:
