#! /usr/bin/python

import asyncore
import email
import email.Message
from email.Iterators import typed_subpart_iterator, _structure
import re

from smtpd import PureProxy

IGNORE    = 0
NOTICE    = 1
TEMPORARY = 2
PERMANENT = 3

FROM_PORT = 20024
TO_PORT   = 20025

def msg_of_str(data): return email.message_from_string(data, _class=BounceMessage)

class BounceMessage(email.Message.Message):
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
        NOTICE    == vacation, or any informative message we want to forward as is
        TEMPORARY == temporary failure, fixable (e.g.  over quota)
        PERMANENT == permanent failure, broken for good (e.g. account do not exists)
        """
        raise NotImplementedError

    def to_bounce(self, alias, dest):
        """this function returns a new Message, the one we really want to send.

        alias holds one valide plat/al alias of the user
        

        Case 0: the error is IGNORE : return None
        Case 1: the error is NOTICE : we just return self

        Case 2: we have to do our little treatments for the broken mail,
                and then we create an informative message for the original SENDER to :
                - explain to him what happened (the detailed error)
                - try to guess if the user may or may not have had the mail (by another leg)
                - if no other leg, give an information to the SENDER on how he can give to us a real good leg
                and attach any sensible information about the original mail (@see attached_mail)
        """
        if   self.error_level() is IGNORE:
            return None
        elif self.error_level() is NOTICE:
            return self
        elif self.error_level() in [ TEMPORARY , PERMANENT ] :
            raise NotImplementedError
        else:
            raise

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
        except:
            # SPAM or broken msg, we just drop it
            # if we want to return an error uncomment this line :
            #return { int_code: "some error message" }
            return { }
        
        bounce = msg_of_str(data).to_bounce(alias, dest)
        if bounce is None:
            return { }
        else:
            return self._deliver("MAILER-DAEMON@bounces.m4x.org", sender, bounce)


Proxy = BounceProxy(('127.0.0.1', FROM_PORT), ('127.0.0.1',TO_PORT))
asyncore.loop()

