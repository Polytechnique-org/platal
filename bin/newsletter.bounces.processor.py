#!/usr/bin/env python2.5
# -*- coding: utf-8 -*-
#***************************************************************************
#*  Copyright (C) 2003-2013 Polytechnique.org                              *
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

"""
Process as automatically as possible bounces from the newsletter

The goal is to extract the email adresses that actually bounced.
Bounces conforming to RFC 1894 will be automatically processed.

This script uses the X-Spam-Flag header to remove spam and heuristics
to detect out-of-office auto-replies and delivery status notifications.

All emails are saved in different mailboxes to make human post-processing easier.
"""

import email, mailbox, os, re, sys, time

#----------------------------------------------------------------------------#

class MboxProcessor:
    """Applies a series of filters to each message in a mbox."""

    def __init__(self, mbox):
        self.mbox_file = mbox
        self.mbox = mailbox.mbox(self.mbox_file)
        self.filters = [
            DirectBouncesFilter(),
            SpamFilter(),
            UnsureFilter(),
            CheckNonSpamFilter(),
            OutOfOfficeFilter(),
            DeliveryStatusNotificationFilter(),
            CatchAllFilter()
        ]

    def initialize_filters(self):
        for f in self.filters: f.initialize(self.mbox_file)
        self.start_time = time.clock()

    def apply_filters(self, message):
        return any(f.process(message) for f in self.filters)

    def finalize_filters(self):
        duration = time.clock() - self.start_time
        separator = '-' * 80
        print separator
        print 'Processed the %d messages of %s in %.2fs' % (len(self.mbox), self.mbox_file, duration)
        print separator
        for f in self.filters:
            f.finalize();
            print separator

    def run(self):
        self.mbox.lock()
        try:
            self.initialize_filters()
            for message in self.mbox: self.apply_filters(message)
            self.finalize_filters()
        finally:
            self.mbox.unlock()
            self.mbox.close()

#----------------------------------------------------------------------------#

class MboxFilter:
    """Defines an interface for filters."""

    def initialize(self, mbox_file):
        """Called by the processor before processing starts.
        
        This is the place to open descriptors required during processing."""
        pass

    def process(self, message):
        """Called by the processor for each message that reaches this step.
        
        Return true to stop processing, and false to go to the next filter."""
        pass

    def finalize(self):
        """Called by the processor after processing ends.
        
        This is the place to display the results and close all descriptors."""
        pass

#----------------------------------------------------------------------------#

def findSubject(message):
    """Returns the subject of an email.Message as an unicode string."""
    if message['Subject'] is not None:
        try:
            return unicode(email.header.make_header(email.header.decode_header(message['Subject'])))
        except:
            pass
    return None

_recipient_re = re.compile(r'^rfc822; ?(.+)$', re.I | re.U)

def findAddressInBounce(bounce):
    """Finds the faulty email address in a bounced email.
    
    See RFC 1894 for more information.
    Returns None or the email address."""
    # Check that it is a bounce - a few MTA fail to set this correctly :(
    if bounce.get_content_type() != 'multipart/report':
        print '! Not a valid bounce (expected multipart/report, found %s).' % bounce.get_content_type()
        return None
    # Extract the second component of the multipart/report
    num_payloads = len(bounce.get_payload())
    if num_payloads < 2:
        print '! Not a valid bounce (expected at least 2 parts, found %d).' % num_payloads
        return None
    status = bounce.get_payload(1)
    if status.get_content_type() != 'message/delivery-status':
        print '! Not a valid bounce (expected message/delivery-status, found %s).' % bounce.get_content_type()
        return None
    # The per-message-fields don't matter here, get only the per-recipient-fields
    num_payloads = len(status.get_payload())
    if num_payloads < 2:
        print '! Not a valid bounce (expected at least 2 parts, found %d).' % num_payloads
        return None
    content = status.get_payload(1)
    if content.get_content_type() != 'text/plain':
        print '! Not a valid bounce (expected text/plain, found %s).' % bounce.get_content_type
        return None
    # Extract the faulty email address
    recipient_match = _recipient_re.search(content['Final-Recipient'])
    if recipient_match is None:
        print '! Missing final recipient.'
        return None
    email = recipient_match.group(1)
    # Check the action field
    if content['Action'] != 'failed':
        print '! Not a failed action (%s).' % content['Action']
        return None
    # Mail forwarding loops, DNS errors and connection timeouts cause X-Postfix errors
    # Otherwise, the first sub-field should indicate a permanent failure
    postfix_error = content['Diagnostic-Code'] is not None \
                and content['Diagnostic-Code'].startswith('X-Postfix')
    if not postfix_error and int(content['Status'][:1]) != 5:
        print '! Not a permanent failure status (%s).' % content['Status']
        return None
    return email

#----------------------------------------------------------------------------#

class DirectBouncesFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.seen = 0
        self.emails = []
        self.mbox_file = '%s.bounced' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()

    def process(self, message):
        if message['X-Spam-Flag'] is None:
            # During finalization, we will verifiy that all messages were processed
            self.seen += 1
            # Special case: ignore mailman notifications for the mailing-list
            # on which the NL is forwarded
            if message['From'] == 'polytechnique.org_newsletter-externes-bounces@listes.polytechnique.org':
                print '! Dropping a notification from mailman for newsletter-externes@polytechnique.org, this should be OK.'
                self.seen -= 1
                return True
            # Additionnal checks, just to be sure
            elif message['From'] != 'MAILER-DAEMON@polytechnique.org (Mail Delivery System)' \
            or message['Subject'] != 'Undelivered Mail Returned to Sender':
                print '! Not an usual direct bounce (From="%s", Subject="%s").' % (message['From'], message['Subject'])
            else:
                email = findAddressInBounce(message)
                if email is not None:
                    self.emails.append(email)
                    self.mbox.add(message)
                    return True
                else:
                    print '! No email found in direct bounce, this is really bad.'
        return False

    def finalize(self):
        print 'Found %d messages with no X-Spam-Flag header.' % self.seen
        print 'Found %d of them that are confirmed bounces.' % len(self.mbox)
        if self.seen != len(self.mbox):
            print '  /!\ These numbers shoud be equal! We have a problem! /!\\'
        print 'They were saved in %s.' % self.mbox_file
        print ''
        print 'Here is the list of email adresses for these bounces:'
        print ''
        for email in self.emails:
            print email
        print ''
        self.mbox.close()

#----------------------------------------------------------------------------#

class SpamFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.mbox_file = '%s.spam' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()

    def process(self, message):
        if message['X-Spam-Flag'] is not None \
        and message['X-Spam-Flag'].startswith('Yes, tests=bogofilter'):
            self.mbox.add(message)
            return True
        return False

    def finalize(self):
        print 'Found %d spams. This is reliable.' % len(self.mbox)
        print 'They were saved in %s.' % self.mbox_file
        print 'You might check the contents of this mbox.'
        self.mbox.close()

#----------------------------------------------------------------------------#

class UnsureFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.mbox_file = '%s.unsure' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()

    def process(self, message):
        if message['X-Spam-Flag'] is not None \
        and message['X-Spam-Flag'].startswith('Unsure, tests=bogofilter'):
            self.mbox.add(message)
            return True
        return False

    def finalize(self):
        print 'Found %d unclassified messages. Most of them should be spams.' % len(self.mbox)
        print 'They were saved in %s.' % self.mbox_file
        print 'You must check the contents of this mbox and feed the antispam.'
        self.mbox.close()

#----------------------------------------------------------------------------#

class CheckNonSpamFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.seen = 0

    def process(self, message):
        if message['X-Spam-Flag'] is None \
        or not message['X-Spam-Flag'].startswith('No, tests=bogofilter'):
            self.seen += 1
        return False

    def finalize(self):
        if self.seen > 0:
            print 'Encountered %d messages that were neither spam, nor unsure, nor non-spams.' % self.seen
            print 'Please investigate.'
        else:
            print 'All messages were either spam, or unsure, or non-spams. Good.'

#----------------------------------------------------------------------------#

class OutOfOfficeFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.mbox_file = '%s.ooo' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()
        subject_re = [
            r'^Absen(t|ce)',
            r'(est|is) absent',
            r'^Out of (the )?office',
            r'is out of (the )?office',
            r'I am out of town',
            r'automatique d\'absence',
            r'Notification d\'absence'
            u'Réponse automatique :', #unicode!
            r'AutoReply',
        ]
        self.subject_regexes = map(re.compile, subject_re, [re.I | re.U] * len(subject_re))

    def process(self, message):
        subject = findSubject(message)
        if subject is not None and any(regex.search(subject) for regex in self.subject_regexes):
            self.mbox.add(message)
            return True
        return False

    def finalize(self):
        print 'Found %d "out of office". This is generally reliable.' % len(self.mbox)
        print 'They were saved in %s.' % self.mbox_file
        print 'You may check the contents of this mbox.'
        self.mbox.close()

#----------------------------------------------------------------------------#

class DeliveryStatusNotificationFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.emails = []
        self.mbox_file = '%s.dsn' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()

    def process(self, message):
        if message.get_content_type() == 'multipart/report':
            email = findAddressInBounce(message)
            if email is not None:
                self.emails.append(email)
                self.mbox.add(message)
                return True
        return False

    def finalize(self):
        print 'Found %d delivery status notifications. This is generally reliable.' % len(self.mbox)
        print 'They were saved in %s.' % self.mbox_file
        print ''
        print 'Here is the list of email adresses for these bounces:'
        print ''
        for email in self.emails:
            print email
        print ''
        self.mbox.close()

#----------------------------------------------------------------------------#

class CatchAllFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.mbox_file = '%s.catchall' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()

    def process(self, message):
        self.mbox.add(message)
        return True

    def finalize(self):
        if len(self.mbox) > 0:
            print '%d messages reached the catchall.' % len(self.mbox)
            print 'They were saved in %s.' % self.mbox_file
            print 'You must process the contents of this mbox manually.'
            self.mbox.close()
        else:
            print 'No messages reached the catchall. Nice.'
            self.mbox.close()
            os.unlink(self.mbox_file)

#----------------------------------------------------------------------------#

if __name__ == '__main__':

    if len(sys.argv) != 2:
        print 'Usage: %s mbox' % sys.argv[0]
        sys.exit(1)

    if not os.path.exists(sys.argv[1]):
        print 'No such file: %s' % sys.argv[1]
        sys.exit(1)

    processor = MboxProcessor(sys.argv[1])
    processor.run()
