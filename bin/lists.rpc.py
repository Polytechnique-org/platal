#!/usr/bin/env python
#***************************************************************************
#*  Copyright (C) 2004-2008 polytechnique.org                              *
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
import SocketServer

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
from threading import Lock

class AuthFailed(Exception): pass

################################################################################
#
# CONFIG
#
#------------------------------------------------

config = ConfigParser.ConfigParser()
config.read(os.path.dirname(__file__)+'/../configs/platal.ini')
config.read(os.path.dirname(__file__)+'/../configs/platal.conf')

def get_config(sec, val, default=None):
    try:
        return config.get(sec, val)[1:-1]
    except ConfigParser.NoOptionError, e:
        if default is None:
            print e
            sys.exit(1)
        else:
            return default

MYSQL_USER     = get_config('Core', 'dbuser')
MYSQL_PASS     = get_config('Core', 'dbpwd')

PLATAL_DOMAIN  = get_config('Mail', 'domain')
PLATAL_DOMAIN2 = get_config('Mail', 'domain2', '')

VHOST_SEP      = get_config('Lists', 'vhost_sep', '_')
ON_CREATE_CMD  = get_config('Lists', 'on_create', '')

SRV_HOST       = get_config('Lists', 'rpchost', 'localhost')
SRV_PORT       = int(get_config('Lists', 'rpcport', '4949'))

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

    def _get_function(self, method):
        try:
            # check to see if a matching function has been registered
            return self.server.funcs[method]
        except:
            raise Exception('method "%s" is not supported' % method)


    def _dispatch(self, method, params):
        new_params = list(params)
        return list_call_dispatcher(self._get_function(method), self.data[0], self.data[1], self.data[2], *params)

    def do_POST(self):
        try:
            _, auth   = self.headers["authorization"].split()
            uid, md5  = base64.decodestring(auth).strip().split(':')
            vhost     = self.path.split('/')[1].lower()
            self.data = self.getUser(uid, md5, vhost)
            if self.data is None:
                raise AuthFailed
            # Call super.do_POST() to do the actual work
            SimpleXMLRPCRequestHandler.do_POST(self)
        except:
            self.send_response(401)
            self.end_headers()

    def getUser(self, uid, md5, vhost):
        res = mysql_fetchone ("""SELECT  CONCAT(u.prenom, ' ', u.nom), a.alias, u.perms
                                   FROM  auth_user_md5 AS u
                             INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND a.type='a_vie' )
                                  WHERE  u.user_id = '%s' AND u.password = '%s' AND u.perms IN ('admin', 'user')
                                  LIMIT  1""" %( uid, md5 ) )
        if res:
            name, forlife, perms = res
            if vhost != PLATAL_DOMAIN:
                res = mysql_fetchone ("""SELECT  uid
                                          FROM  groupex.membres AS m
                                    INNER JOIN  groupex.asso    AS a ON (m.asso_id = a.id)
                                         WHERE  perms='admin' AND uid='%s' AND mail_domain='%s'""" %( uid , vhost ) )
                if res: perms= 'admin'
            userdesc = UserDesc(forlife+'@'+PLATAL_DOMAIN, name, None, 0)
            return (userdesc, perms, vhost)
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

def mysql_fetchone(query):
    ret = None
    try:
        lock.acquire()
        mysql.execute(query)
        if int(mysql.rowcount) > 0:
            ret = mysql.fetchone()
    finally:
        lock.release()
    return ret

def is_admin_on(userdesc, perms, mlist):
    return ( perms == 'admin' ) or ( userdesc.address in mlist.owner )


def quote(s, is_header=False):
    if is_header:
        h = Utils.oneline(s, 'iso-8859-1')
    else:
        h = s
    h = str('').join(re.split('[\x00-\x08\x0B-\x1f]+', h))
    return Utils.uquote(h.replace('&', '&amp;').replace('>', '&gt;').replace('<', '&lt;'))

def to_forlife(email):
    try:
        mbox, fqdn = email.split('@')
    except:
        mbox = email
        fqdn = PLATAL_DOMAIN
    if ( fqdn == PLATAL_DOMAIN ) or ( fqdn == PLATAL_DOMAIN2 ):
        res = mysql_fetchone("""SELECT  CONCAT(f.alias, '@%s'), CONCAT(u.prenom, ' ', u.nom)
                                  FROM  auth_user_md5 AS u
                            INNER JOIN  aliases       AS f ON (f.id=u.user_id AND f.type='a_vie')
                            INNER JOIN  aliases       AS a ON (a.id=u.user_id AND a.alias='%s' AND a.type!='homonyme')
                                 WHERE  u.perms IN ('admin', 'user')
                                 LIMIT  1""" %( PLATAL_DOMAIN, mbox ) )
        if res:
            return res
        else:
            return (None, None)
    return (email.lower(), mbox)

##
# see /usr/lib/mailman/bin/rmlist
##
def remove_it(listname, filename):
    if os.path.islink(filename) or os.path.isfile(filename):
        os.unlink(filename)
    elif os.path.isdir(filename):
        shutil.rmtree(filename)

##
# Call dispatcher
##

def has_annotation(method, name):
    """ Check if the method contains the given annoation.
    """
    return method.__doc__ and method.__doc__.find("@%s" % name) > -1

def list_call_dispatcher(method, userdesc, perms, vhost, *arg):
    """Dispatch the call to the right handler.
    This function checks the options of the called method the set the environment of the call.
    The dispatcher uses method annotation (special tokens in the documentation of the method) to
    guess the requested environment:
        @mlist: the handler requires a mlist object instead of the vhost/listname couple
        @lock:  the handler requires the mlist to be locked (@mlist MUST be specified)
        @edit:  the handler edit the mlist (@mlist MUST be specified)
        @admin: the handler requires admin rights on the list (@mlist MUST be specified)
        @root:  the handler requires site admin rights
    """
    try:
        if has_annotation(method, "root") and perms != "admin":
            return 0
        if has_annotation(method, "mlist"):
            listname = arg[0]
            arg = arg[1:]
            mlist = MailList.MailList(vhost + VHOST_SEP + listname.lower(), lock=0)
            if has_annotation(method, "admin") and not is_admin_on(userdesc, perms, mlist):
                return 0
            if has_annotation(method, "edit") or has_annotation(method, "lock"):
                return list_call_locked(method, userdesc, perms, mlist, has_annotation(method, "edit"), *arg)
            else:
                return method(userdesc, perms, mlist, *arg)
        else:
            return method(userdesc, perms, vhost, *arg)
    except Exception, e:
        raise e
        return 0

def list_call_locked(method, userdesc, perms, mlist, edit, *arg):
    """Call the given method after locking the mlist.
    """
    try:
        mlist.Lock()
        ret = method(userdesc, perms, mlist, *arg)
        if edit:
            mlist.Save()
        mlist.Unlock()
        return ret
    except:
        mlist.Unlock()
        return 0
    # TODO: use finally when switching to python 2.5

#-------------------------------------------------------------------------------
# helpers on lists
#

def is_subscription_pending(userdesc, perms, mlist, edit):
    for id in mlist.GetSubscriptionIds():
        if userdesc.address == mlist.GetRecord(id)[1]:
            return True
    return False

def get_list_info(userdesc, perms, mlist, front_page=0):
    members    = mlist.getRegularMemberKeys()
    is_member  = userdesc.address in members
    is_owner   = userdesc.address in mlist.owner
    if mlist.advertised or is_member or is_owner or (not front_page and perms == 'admin'):
        is_pending = False
        if not is_member and (mlist.subscribe_policy > 1):
            is_pending = list_call_locked(userdesc, perms, mlist, is_subscription_pending, False)
            if is_pending is 0:
                return 0

        host = mlist.internal_name().split(VHOST_SEP)[0].lower()
        details = {
                'list' : mlist.real_name,
                'addr' : mlist.real_name.lower() + '@' + host,
                'host' : host,
                'desc' : quote(mlist.description),
                'info' : quote(mlist.info),
                'diff' : (mlist.default_member_moderation>0) + (mlist.generic_nonmember_action>0),
                'ins'  : mlist.subscribe_policy > 1,
                'priv' : 1-mlist.advertised,
                'sub'  : 2*is_member + is_pending,
                'own'  : is_owner,
                'nbsub': len(members)
                }
        return (details, members)
    return 0

def get_options(userdesc, perms, mlist, opts):
    """ Get the options of a list.
            @mlist
            @admin
    """
    options = { }
    for (k, v) in mlist.__dict__.iteritems():
        if k in opts:
            if type(v) is str:
                options[k] = quote(v)
            else: options[k] = v
    details = get_list_info(userdesc, perms, mlist)[0]
    return (details, options)

def set_options(userdesc, perms, mlist, vals):
    """ Set the options of a list.
            @mlist
            @edit
            @admin
    """
    for (k, v) in vals.iteritems():
        if k not in opts:
            continue
        if k == 'default_member_moderation':
            for member in mlist.getMembers():
                mlist.setMemberOption(member, mm_cfg.Moderate, int(v))
        t = type(mlist.__dict__[k])
        if   t is bool: mlist.__dict__[k] = bool(v)
        elif t is int:  mlist.__dict__[k] = int(v)
        elif t is str:  mlist.__dict__[k] = Utils.uncanonstr(v, 'fr')
        else:           mlist.__dict__[k] = v
    return 1

#-------------------------------------------------------------------------------
# users procedures for [ index.php ]
#

def get_lists(userdesc, perms, vhost, email=None):
    """ List available lists for the given vhost
    """
    if email is None:
        udesc = userdesc
    else:
        udesc = UserDesc(email.lower(), email.lower(), None, 0)
    prefix = vhost.lower()+VHOST_SEP
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        if not name.startswith(prefix):
            continue
        try:
            mlist = MailList.MailList(name, lock=0)
        except:
            continue
        try:
            details = get_list_info(udesc, perms, mlist, (email is None and vhost == PLATAL_DOMAIN))[0]
            result.append(details)
        except:
            continue
    return result

def subscribe(userdesc, perms, mlist):
    """ Subscribe to a list.
            @mlist
            @edit
    """
    if ( mlist.subscribe_policy in (0, 1) ) or userdesc.address in mlist.owner:
        mlist.ApprovedAddMember(userdesc)
        result = 2
    else:
        result = 1
        try:
            mlist.AddMember(userdesc)
        except Errors.MMNeedApproval:
            pass
    return result

def unsubscribe(userdesc, perms, mlist):
    """ Unsubscribe from a list
            @mlist
            @edit
    """
    mlist.ApprovedDeleteMember(userdesc.address)
    return 1

#-------------------------------------------------------------------------------
# users procedures for [ index.php ]
#

def get_name(member):
    try:
        return quote(mlist.getMemberName(member))
    except:
        return ''

def get_members(userdesc, perms, mlist):
    """ List the members of a list.
            @mlist
    """
    details, members = get_list_info(userdesc, perms, mlist)
    members.sort()
    members = map(lambda member: (get_name(member), member), members)
    return (details, members, mlist.owner)


#-------------------------------------------------------------------------------
# users procedures for [ trombi.php ]
#

def get_members_limit(userdesc, perms, mlist, page, nb_per_page):
    """ Get a range of members of the list.
            @mlist
    """
    members = get_members(userdesc, perms, mlist)[1]
    i = int(page) * int(nb_per_page)
    return (len(members), members[i:i+int(nb_per_page)])

def get_owners(userdesc, perms, mlist):
    """ Get the owners of the list.
            @mlist
    """
    details, members, owners = get_members(userdesc, perms, mlist)
    return (details, owners)


#-------------------------------------------------------------------------------
# owners procedures [ admin.php ]
#

def replace_email(userdesc, perms, mlist, from_email, to_email):
    """ Replace the address of a member by another one.
            @mlist
            @edit
            @admin
    """
    mlist.ApprovedChangeMemberAddress(from_email.lower(), to_email.lower(), 0)
    return 1

def mass_subscribe(userdesc, perms, mlist, users):
    """ Add a list of users to the list.
            @mlist
            @edit
            @admin
    """
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
    return added

def mass_unsubscribe(userdesc, perms, mlist, users):
    """ Remove a list of users from the list.
            @mlist
            @edit
            @admin
    """
    map(lambda user: mlist.ApprovedDeleteMember(user), users)
    return users

def add_owner(userdesc, perms, mlist, user):
    """ Add a owner to the list.
            @mlist
            @edit
            @admin
    """
    email = to_forlife(user)[0]
    if email is None:
        return 0
    if email not in mlist.owner:
        mlist.owner.append(email)
    return True

def del_owner(userdesc, perms, mlist, user):
    """ Remove a owner of the list.
            @mlist
            @edit
            @admin
    """
    if len(mlist.owner) < 2:
        return 0
    mlist.owner.remove(user)
    return True

#-------------------------------------------------------------------------------
# owners procedures [ admin.php ]
#

def get_pending_ops(userdesc, perms, mlist):
    """ Get the list of operation waiting for an action from the owners.
            @mlist
            @lock
            @admin
    """
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
        fpath = os.path.join(mm_cfg.DATA_DIR, filename)
        try:
            size = os.path.getsize(fpath)
        except OSError, e:
            if e.errno <> errno.ENOENT: raise
            continue
        try:
            msg = readMessage(fpath)
            fromX = msg.has_key("X-Org-Mail")
        except:
            pass
        helds.append({
                'id'    : id,
                'sender': quote(sender, True),
                'size'  : size,
                'subj'  : quote(subject, True),
                'stamp' : ptime,
                'fromx' : fromX
                })
    if dosave:
        mlist.Save()
    return (subs, helds)

def handle_request(userdesc, perms, mlist, id, value, comment):
    """ Handle a moderation request.
            @mlist
            @edit
            @admin
    """
    mlist.HandleRequest(int(id), int(value), comment)
    return 1

def get_pending_sub(userdesc, perms, mlist, id):
    """ Get informations about a given subscription moderation.
            @mlist
            @lock
            @admin
    """
    sub = 0
    id = int(id)
    if id in mlist.GetSubscriptionIds():
        time, addr, fullname, passwd, digest, lang = mlist.GetRecord(id)
        try:
            login = re.match("^[^.]*\.[^.]*\.\d\d\d\d$", addr.split('@')[0]).group()
            sub = {'id': id, 'name': quote(fullname), 'addr': addr, 'login': login }
        except:
            sub = {'id': id, 'name': quote(fullname), 'addr': addr }
    return sub

def get_pending_mail(userdesc, perms, mlist, id, raw=0):
    """ Get informations about a given mail moderation.
            @mlist
            @lock
            @admin
    """
    ptime, sender, subject, reason, filename, msgdata = mlist.GetRecord(int(id))
    fpath = os.path.join(mm_cfg.DATA_DIR, filename)
    size = os.path.getsize(fpath)
    msg = readMessage(fpath)

    if raw:
        return quote(str(msg))
    results_plain = []
    results_html  = []
    for part in typed_subpart_iterator(msg, 'text', 'plain'):
        c = part.get_payload()
        if c is not None: results_plain.append (c)
    results_plain = map(lambda x: quote(x), results_plain)
    for part in typed_subpart_iterator(msg, 'text', 'html'):
        c = part.get_payload()
        if c is not None: results_html.append (c)
    results_html = map(lambda x: quote(x), results_html)
    return {'id'    : id,
            'sender': quote(sender, True),
            'size'  : size,
            'subj'  : quote(subject, True),
            'stamp' : ptime,
            'parts_plain' : results_plain,
            'parts_html': results_html }

#-------------------------------------------------------------------------------
# owner options [ options.php ]
#

owner_opts = ['accept_these_nonmembers', 'admin_notify_mchanges', 'description', \
        'default_member_moderation', 'generic_nonmember_action', 'info', \
        'subject_prefix', 'goodbye_msg', 'send_goodbye_msg', 'subscribe_policy', \
        'welcome_msg']

def get_owner_options(userdesc, perms, mlist):
    """ Get the owner options of a list.
            @mlist
            @admin
    """
    return get_options(userdesc, perms, mlist, owner_opts)

def set_owner_options(userdesc, perms, mlist, values):
    """ Set the owner options of a list.
            @mlist
            @edit
            @admin
    """
    return set_options(userdesc, perms, mlist, owner_opts, values)

def add_to_wl(userdesc, perms, mlist, addr):
    """ Add addr to the whitelist
            @mlist
            @edit
            @admin
    """
    mlist.accept_these_nonmembers.append(addr)
    return 1

def del_from_wl(userdesc, perms, mlist, addr):
    """ Remove an address from the whitelist
            @mlist
            @edit
            @admin
    """
    mlist.accept_these_nonmembers.remove(addr)
    return 1

def get_bogo_level(userdesc, perms, mlist):
    """ Compute bogo level from the filtering rules set up on the list.
            @mlist
            @admin
    """
    if len(mlist.header_filter_rules) == 0:
        return 0

    unsurelevel = 0
    filterlevel = 0
    filterbase = 0

    # The first rule filters Unsure mails
    if mlist.header_filter_rules[0][0] == 'X-Spam-Flag: Unsure, tests=bogofilter':
        unsurelevel = 1
        filterbase = 1

    # Check the other rules:
    #  - we have 2 rules: this is level 2 (drop > 0.999999, moderate Yes)
    #  - we have only one rule with HOLD directive : this is level 1 (moderate spams)
    #  - we have only one rule with DISCARD directive : this is level 3 (drop spams)
    try:
        action = mlist.header_filter_rules[filterbase + 1][1]
        filterlevel = 2
    except:
        action = mlist.header_filter_rules[filterbase][1]
        if action == mm_cfg.HOLD:
            filterlevel = 1
        elif action == mm_cfg.DISCARD:
            filterlevel = 3
    return (filterlevel << 1) + unsurelevel

def set_bogo_level(userdesc, perms, vhost, listname, level):
    """ Set filter to the specified level.
            @mlist
            @edit
            @admin
    """
    hfr = []

    # The level is a combination of a spam filtering level and unsure filtering level
    #   - the unsure filtering level is only 1 bit (1 = HOLD unsures, 0 = Accept unsures)
    #   - the spam filtering level is a number growing with filtering strength
    #     (0 = no filtering, 1 = moderate spam, 2 = drop 0.999999 and moderate others, 3 = drop spams)
    bogolevel = int(level)
    filterlevel = bogolevel >> 1
    unsurelevel = bogolevel & 1

    # Set up unusre filtering
    if unsurelevel == 1:
        hfr.append(('X-Spam-Flag: Unsure, tests=bogofilter', mm_cfg.HOLD, False))

    # Set up spam filtering
    if filterlevel is 1:
        hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
    elif filterlevel is 2:
        hfr.append(('X-Spam-Flag: Yes, tests=bogofilter, spamicity=(0\.999999|1\.000000)', mm_cfg.DISCARD, False))
        hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
    elif filterlevel is 3:
        hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.DISCARD, False))

    # save configuration
    if mlist.header_filter_rules != hfr:
        mlist.header_filter_rules = hfr
    return 1

#-------------------------------------------------------------------------------
# admin procedures [ soptions.php ]
#

admin_opts = [ 'advertised', 'archive', \
        'max_message_size', 'msg_footer', 'msg_header']

def get_admin_options(userdesc, perms, mlist):
    """ Get administrator options.
            @mlist
            @root
    """
    return get_options(userdesc, perms, mlist, admin_opts)

def set_admin_options(userdesc, perms, mlist, values):
    """ Set administrator options.
            @mlist
            @edit
            @root
    """
    return set_options(userdesc, perms, mlist, admin_opts, values)

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
    'hold_these_nonmembers'         : [],
    'host_name'                     : 'listes.polytechnique.org',
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

def check_options_runner(userdesc, perms, mlist, listname, correct):
    options = { }
    for (k, v) in check_opts.iteritems():
        if mlist.__dict__[k] != v:
            options[k] = v, mlist.__dict__[k]
            if correct: mlist.__dict__[k] = v
    if mlist.real_name.lower() != listname:
        options['real_name'] = listname, mlist.real_name
        if correct: mlist.real_name = listname
    details = get_list_info(userdesc, perms, mlist)[0]
    return (details, options)


def check_options(userdesc, perms, vhost, listname, correct=False):
    """ Check the list.
            @root
    """
    listname = listname.lower()
    mlist = MailList.MailList(vhost + VHOST_SEP + listname, lock=0)
    if correct:
        return list_call_locked(check_options_runner, userdesc, perms, mlist, True, listname, True)
    else:
        return check_options_runner(userdesc, perms, mlist, listname, False)

#-------------------------------------------------------------------------------
# super-admin procedures
#

def get_all_lists(userdesc, perms, vhost):
    """ Get all the list for the given vhost
    """
    prefix = vhost.lower()+VHOST_SEP
    names = Utils.list_names()
    names.sort()
    result = []
    for name in names:
        if not name.startswith(prefix):
            continue
        result.append(name.replace(prefix, ''))
    return result

def create_list(userdesc, perms, vhost, listname, desc, advertise, modlevel, inslevel, owners, members):
    """ Create a new list.
            @root
    """
    name = vhost.lower() + VHOST_SEP + listname.lower();
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
        mlist.host_name = 'listes.polytechnique.org'
        mlist.description = desc

        mlist.advertised = int(advertise) is 0
        mlist.default_member_moderation = int(modlevel) is 2
        mlist.generic_nonmember_action = int(modlevel) > 0
        mlist.subscribe_policy = 2 * (int(inslevel) is 1)
        mlist.admin_notify_mchanges = (mlist.subscribe_policy or mlist.generic_nonmember_action or mlist.default_member_moderation or not mlist.advertised)

        mlist.owner = owner

        mlist.subject_prefix = '['+listname+'] '
        mlist.max_message_size = 0

        inverted_listname = listname.lower() + '_' + vhost.lower()
        mlist.msg_footer = "_______________________________________________\n" \
                         + "Liste de diffusion %(real_name)s\n" \
                         + "http://listes.polytechnique.org/members/" + inverted_listname

        mlist.header_filter_rules = []
        mlist.header_filter_rules.append(('X-Spam-Flag: Unsure, tests=bogofilter', mm_cfg.HOLD, False))
        mlist.header_filter_rules.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
        mlist.Save()
        mlist.Unlock()

        if ON_CREATE_CMD != '':
            try:    os.system(ON_CREATE_CMD + ' ' + name)
            except: pass

        check_options(userdesc, perms, mlist, True)
        mass_subscribe(userdesc, perms, mlist, members)

        # avoid the "-1 mail to moderate" bug
        mlist = MailList.MailList(name)
        mlist._UpdateRecords()
        mlist.Save()

        return 1
    finally:
        mlist.Unlock()
    return 0

def delete_list(userdesc, perms, mlist, del_archives=0):
    """ Delete the list.
            @mlist
            @admin
    """
    lname = mlist.internal_name()
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

def kill(userdesc, perms, vhost, alias, del_from_promo):
    """ Remove a user from all the lists.
    """
    exclude = []
    if not del_from_promo:
        exclude.append(PLATAL_DOMAIN + VHOST_SEP + 'promo' + alias[-4:])
    for list in Utils.list_names():
        if list in exclude:
            continue
        try:
            mlist = MailList.MailList(list, lock=0)
        except:
            continue
        try:
            mlist.Lock()
            mlist.ApprovedDeleteMember(alias+'@'+PLATAL_DOMAIN, None, 0, 0)
            mlist.Save()
            mlist.Unlock()
        except:
            mlist.Unlock()
    return 1


#-------------------------------------------------------------------------------
# server
#
class FastXMLRPCServer(SocketServer.ThreadingMixIn, SimpleXMLRPCServer):
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
    os.setregid(gid, gid)
    os.setreuid(uid, uid)

signal.signal(signal.SIGHUP, signal.SIG_IGN)

if ( os.getuid() is not uid ) or ( os.getgid() is not gid):
    sys.exit(0)

opts, args = getopt.getopt(sys.argv[1:], 'f')
for o, a in opts:
    if o == '-f' and os.fork():
        sys.exit(0)

i18n.set_language('fr')
mysql = connectDB()
lock = Lock()

#-------------------------------------------------------------------------------
# server
#
server = FastXMLRPCServer((SRV_HOST, SRV_PORT), BasicAuthXMLRPCRequestHandler)

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
server.register_function(replace_email)
server.register_function(mass_subscribe)
server.register_function(mass_unsubscribe)
server.register_function(add_owner)
server.register_function(del_owner)
# moderate.php
server.register_function(get_pending_ops)
server.register_function(handle_request)
server.register_function(get_pending_sub)
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

# vim:set et sw=4 sts=4 sws=4:
