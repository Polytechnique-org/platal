#! /usr/bin/python
# set:encoding=iso-8859-1:

import asyncore
import email
import os, re, sys

from email import Message, MIMEText, MIMEMultipart
from email.Iterators import typed_subpart_iterator, _structure
from smtpd import PureProxy

import ConfigParser
import MySQLdb

IGNORE    = 0
NOTICE    = 1
ERROR     = 2

FROM_PORT = 20024
TO_HOST   = 'olympe.madism.org'
TO_PORT   = 25


################################################################################
#
# Functions
#
#-------------------------------------------------------------------------------

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

def connectDB():
    db = MySQLdb.connect(
            db     = 'x4dat',
            user   = get_config('Core', 'dbuser'),
            passwd = get_config('Core', 'dbpwd'),
            unix_socket='/var/run/mysqld/mysqld.sock')
    db.ping()
    return db.cursor()

def msg_of_str(data): return email.message_from_string(data, _class=BounceMessage)

################################################################################
#
# Classes
#
#-------------------------------------------------------------------------------

class BounceMessage(Message.Message):
    def body(self):
        """this method returns the part that is commonely designed as the 'body'

        for the multipart mails, we go into the first part that have non multipart childs, and then :
               we return its first text/plain part if it exsists
          else we return the first text/* part if it exists
          else we return None else

        for non multipart mails, we just return the current payload
        """
        if self.is_multipart():
            _body = self
            while _body.get_payload(0).is_multipart():
                _body = _body.get_payload(0)
                
            buffer = None
            for part in typed_subpart_iterator(_body):
                if part.get_content_subtype() == 'plain':
                    return part.get_payload(decode=True)
                if buffer is None:
                    buffer = part
            return buffer.get_payload(decode=True)
        return self.get_payload(decode=True)

    def _qmail_attached_mail(self):
        """qmail is a dumb MTA that put the mail that has bounced RAW into the bounce message,
        instead of making a traditionnal message/rfc822 attachement like any other MTA

        it seems to be designed like this :

        =============================================
        [...QMAIL crap...]
        --- Below this line is a copy of the message.

        Return-Path: <...>
        [rest of the embeded mail]
        =============================================

        so we just cut the qmail crap, and build a new message from the rest.
        
        may DJB burn into coder's hell
        """
        msg = self.get_payload(decode=True)
        pos = msg.find("\n--- Below this line is a copy of the message.")
        if pos is -1:
            return None
        pos = msg.find("Return-Path:", pos)
        return msg_of_str(msg[pos:])
        
    def attached_mail(self):
        """returns the attached mail that bounced, if it exists.
        we try this :
        
        is the mail multipart ?
        Yes :
            (1) return the first message/rfc822 part.
            (2) return the first text/rfc822-headers part (AOHell)
            (3) return None (may be a vacation + some disclaimer in attachment)
        No:
            try to return the qmail-style embeded mail (but may be a vacation)
        """
        if self.is_multipart():
            for part in typed_subpart_iterator(self, 'message', 'rfc822'):
                return part
            for part in typed_subpart_iterator(self, 'text', 'rfc822-headers'):
                return part
            return None
        return self._qmail_attached_mail()

    def error_level(self):
        """determine the level of an error:
        IGNORE == drop the mail
        NOTICE == vacation, or any informative message we want to forward as is
        ERROR  == errors, that we want to handle
        """

        body = self.body()
        if not body:
            return (IGNORE, '')
        
        mysql.execute ( "SELECT lvl,re,text FROM emails_bounces_re ORDER BY pos" )
        nb = int(mysql.rowcount)
        for x in range(0,nb):
            row = mysql.fetchone()
            if re.compile(str(row[1]), re.I | re.M).search(body):
                return (int(row[0]), str(row[2]))
       
        return (NOTICE, '')

    def forge_error(self, alias, dest, txt):
        """we have to do our little treatments for the broken mail,
        and then we create an informative message for the original SENDER to :
        - explain to him what happened (the detailed error)
        - try to guess if the user may or may not have had the mail (by another leg)
        - if no other leg, give an information to the SENDER on how he can give to us a real good leg
        and attach any sensible information about the original mail (@see attached_mail)
        """

        mysql.execute("SELECT id FROM aliases WHERE alias='%s' AND type IN ('alias', 'a_vie') LIMIT 1" % (alias))
        if int(mysql.rowcount) is not 1:
            return None
        uid = mysql.fetchone()[0]
        mysql.execute("UPDATE emails SET panne = NOW() WHERE uid='%s' AND email='%s'" % (uid, dest))
        mysql.execute("REPLACE INTO emails_broken (uid,email) VALUES(%s, '%s')" % (uid, dest))
        mysql.execute("""SELECT  COUNT(*),
                                 IFNULL(SUM(panne=0  OR  (last!=0 AND ( TO_DAYS(NOW())-TO_DAYS(last) )>7 AND panne<last)), 0),
                                 IFNULL(SUM(panne!=0 AND last!=0  AND ( TO_DAYS(NOW())-TO_DAYS(last) )<7 AND panne<last) , 0),
                                 IFNULL(SUM(panne!=0 AND (last=0  OR  ( TO_DAYS(NOW())-TO_DAYS(last) )<1)) , 0)
                           FROM  emails
                          WHERE  FIND_IN_SET('active', flags) AND uid=%s AND email!='%s'""" % (uid, dest))

        nb_act, nb_ok, nb_may, nb_bad = map(lambda x: int(x), mysql.fetchone())

        txt = "Une des adresses de redirection de %s\n" % (alias) \
            + "a généré une erreur (qui peut être temporaire) :\n" \
            + "------------------------------------------------------------\n" \
            + "%s\n" % (txt) \
            + "------------------------------------------------------------\n\n"

        if nb_ok + nb_may is 0:
            txt += "Toutes les adresses de redirection de ce correspondant\n" \
                +  "sont cassées à l'heure actuelle.\n\n" \
                +  "Prière de prévenir votre correspondant par d'autres moyens\n" \
                +  "pour lui signaler ce problème et qu'il puisse le corriger !!!"
        elif nb_ok is 0:
            txt += "Ce correspondant possède néanmoins %i autre(s) adresse(s) active(s)\n" % (nb_may) \
                +  "en erreur, mais ayant recu des mails dans les 7 derniers jours,\n" \
                +  "sans -- pour le moment -- avoir créé la moindre nouvelle erreur.\n\n" \
                +  "Ces adresses sont donc peut-être valides.\n"
        else:
            txt += "Ce correspondant a en ce moment %i autre(s) adresse(s) valide(s).\n" % (nb_ok) \
                +  "Rien ne prouve cependant qu'elles étaient actives \n" \
                +  "au moment de l'envoi qui a échoué."

        msg = MIMEMultipart.MIMEMultipart()
        msg['Subject'] = self['Subject']

        attach = self.attached_mail()
        if attach is not None:
            txt += "\nCi-joint le mail dont la livraison a échoué\n"
            msg.attach(MIMEText.MIMEText(txt))
            msg.attach(attach)
        else:
            msg.attach(MIMEText.MIMEText(txt))

        return msg

    def to_bounce(self, alias, dest):
        """this function returns a new Message, the one we really want to send.

        alias holds one valide plat/al alias of the user

        Case 0: the error is IGNORE : return None
        Case 1: the error is NOTICE : we just return self
        Case 2: we have a REAL error: use forge_error
        """
        lvl, txt = self.error_level()

        if   lvl is IGNORE: return None
        elif lvl is NOTICE: return self
        elif lvl is ERROR : return self.forge_error(alias, dest, txt)
        else:               raise


class BounceProxy(PureProxy):
    def __init__(self, localaddr, remoteaddr):
        PureProxy.__init__(self, localaddr, remoteaddr)
        self._rcpt_re = re.compile(r'^([^_]*)__(.*)__([^_+=]*)\+(.*)=([^_+=]*)@bounces.m4x.org$')


    def process_rcpt(self, rcpttos):
        for to in rcpttos:
            m = self._rcpt_re.match(to)
            if m is None: continue
            return ( m.group(1), m.group(2)+'@'+m.group(3), m.group(4)+'@'+m.group(5) )
        return None


    def process_message(self, peer, mailfrom, rcpttos, data):
        try:
            alias, sender, dest = self.process_rcpt(rcpttos)
            bounce = msg_of_str(data).to_bounce(alias, dest)
            if bounce is not None:
                bounce['From'] = """"Serveur de courier Polytechnique.org" <MAILER-DAEMON@bounces.m4x.org>"""
                bounce['To']   = sender
                self._deliver("MAILER-DAEMON@bounces.m4x.org", [sender], bounce.as_string())
        except:
            pass
            # SPAM or broken msg, we just drop it
        return None


################################################################################
#
# Main
#
#-------------------------------------------------------------------------------

mysql = connectDB()
Proxy = BounceProxy(('127.0.0.1', FROM_PORT), (TO_HOST, TO_PORT))
asyncore.loop()

