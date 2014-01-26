#!/usr/bin/env python
# -*- coding: utf-8 -*-
#***************************************************************************
#*  Copyright (C) 2003-2014 Polytechnique.org                              *
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

import email
import mailbox
import os
import re
import sys
import time

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
        print(separator)
        print('Processed the %d messages of %s in %.2fs' % (len(self.mbox), self.mbox_file, duration))
        print(separator)
        for f in self.filters:
            f.finalize()
            print(separator)

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
    if message['Subject'] is None:
        return None

    # decode_header returns a list of (decoded_string, charset) pairs
    decoded_seq = email.header.decode_header(message['Subject'])
    decoded_seq = [(subj, enc or 'utf-8') for subj, enc in decoded_seq]
    header = email.header.make_header(decoded_seq)
    # Be Python 2 & 3 compatible
    return unicode(header) if sys.version_info < (3,) else str(header)


_recipient_re = re.compile(r'^rfc822; ?(.+)$', re.I | re.U)
# Some MTA set the Final-Recipient with "LOCAL;" instead of "rfc822;"
_recipient_re2 = re.compile(r'^local; ?(.+)$', re.I | re.U)


def findAddressInBounce(bounce):
    """Finds the faulty email address in a bounced email.

    See RFC 1894 for more information.
    Returns None or the email address."""

    # Check that it is a bounce - a few MTA fail to set this correctly :(
    if bounce.get_content_type() != 'multipart/report':
        print('! Not a valid bounce (expected multipart/report, found %s).' % bounce.get_content_type())
        return None
    # Extract the second component of the multipart/report
    num_payloads = len(bounce.get_payload())
    if num_payloads < 2:
        print('! Not a valid bounce (expected at least 2 parts, found %d).' % num_payloads)
        return None
    status = bounce.get_payload(1)

    # If the second part is of type "message/rfc822" it is the undelivered message.
    # Let's try to understand the text part
    if status.get_content_type() == 'message/rfc822':
        text_bounce = bounce.get_payload(0)
        if text_bounce.get_content_type() == 'text/plain':
            return findAddressInPlainBounce(text_bounce, bounce)
        # If it's not a text message, let's continue to the next error message

    if status.get_content_type() != 'message/delivery-status':
        print('! Not a valid bounce (expected message/delivery-status, found %s).' % status.get_content_type())
        return None
    # The per-message-fields don't matter here, get only the per-recipient-fields
    num_payloads = len(status.get_payload())
    if num_payloads < 2:
        print('! Not a valid bounce (expected at least 2 parts, found %d).' % num_payloads)
        return None
    content = status.get_payload(1)
    if content.get_content_type() != 'text/plain':
        print('! Not a valid bounce (expected text/plain, found %s).' % content.get_content_type())
        return None
    # Extract the faulty email address
    # Some MTA don't set Final-Recipient but use Remote-Recipient instead
    if 'Final-Recipient' in content:
        final_recipient = content['Final-Recipient']
    elif 'Remote-Recipient' in content:
        final_recipient = content['Remote-Recipient']
    else:
        print('! Not a valid bounce (no Final-Recipient).')
        return None
    recipient_match = _recipient_re.search(final_recipient)
    if recipient_match is None:
        # Be nice, test another regexp
        recipient_match = _recipient_re2.search(final_recipient)
        if recipient_match is None:
            print('! Missing final recipient.')
            return None
    email = recipient_match.group(1)
    # Check the action field
    if content['Action'].lower().strip() != 'failed':
        print('! Not a failed action (%s).' % content['Action'])
        return None

    status = content['Status']
    diag_code = content['Diagnostic-Code']

    # Permanent failure state
    if int(status[:1]) == 5:
        return email

    # Mail forwarding loops, DNS errors and connection timeouts cause X-Postfix errors
    if diag_code is not None and diag_code.startswith('X-Postfix'):
        return email

    failure_hints = [
        "insufficient system storage",
        "mailbox full",
        "requested action aborted: local error in processing",
        "user unknown",
        ]
    if 'quota' in status.lower():
        return email
    if diag_code is not None:
        ldiag_code = diag_code.lower()
        if any(hint in ldiag_code for hint in failure_hints):
            return email

    print('! Not a permanent failure status (%s).' % status)
    if diag_code is not None:
        print('! Diagnostic code was: %s' % diag_code)
    return None


def findAddressInWeirdDeliveryStatus(message):
    """Finds the faulty email address in the delivery-status part of an email

    Unlikely to findAddressInBounce, the status does NOT follow RFC 1894, so
    try to learn to get data nevertheless...
    Returns None or the email address.
    """
    if message.get_content_type() != 'message/delivery-status':
        print('! Not a valid weird bounce (expected message/delivery-status, found %s).' % message.get_content_type())
        return None
    # The per-message-fields don't matter here, get only the per-recipient-fields
    num_payloads = len(message.get_payload())
    if num_payloads < 2:
        print('! Not a valid weird bounce (expected at least 2 parts, found %d).' % num_payloads)
        return None
    content = message.get_payload(1)
    # The content may be missing, but interesting headers still present in the first payload...
    if not content:
        content = message.get_payload(0)
        if 'Action' not in content:
            print('! Not a valid weird bounce (unable to find content).')
            return None
    elif content.get_content_type() != 'text/plain':
        print('! Not a valid weird bounce (expected text/plain, found %s).' % content.get_content_type())
        return None

    # Extract the faulty email address
    if 'Final-Recipient' in content:
        recipient_match = _recipient_re.search(content['Final-Recipient'])
        if recipient_match is None:
            # Be nice, test another regexp
            recipient_match = _recipient_re2.search(content['Final-Recipient'])
            if recipient_match is None:
                print('! Unknown final recipient in weird bounce.')
                return None
        email = recipient_match.group(1)
    elif 'Original-Recipient' in content:
        recipient = content['Original-Recipient']
        recipient_match = _recipient_re.search(recipient)
        if recipient_match is None:
            # Be nice, test another regexp
            recipient_match = _recipient_re2.search(recipient)
            if recipient_match is None:
                recipient_match = re.match(r'<([^>]+@[^@>]+)>', recipient)
                if recipient_match is None:
                    print('! Unknown original recipient in weird bounce.')
                    return None
        email = recipient_match.group(1)
    else:
        print('! Missing recipient in weird bounce.')
        return None

    # Check the action field
    if content['Action'].lower() != 'failed':
        print('! Not a failed action (%s).' % content['Action'])
        return None

    status = content['Status']
    diag_code = content['Diagnostic-Code']

    # Permanent failure state
    if status and int(status[:1]) == 5:
        return email

    # Mail forwarding loops, DNS errors and connection timeouts cause X-Postfix errors
    if diag_code is not None and diag_code.startswith('X-Postfix'):
        return email

    failure_hints = [
        "insufficient system storage",
        "mailbox full",
        "requested action aborted: local error in processing",
        "sender address rejected",
        "user unknown",
        ]
    if status and 'quota' in status.lower():
        return email
    if diag_code is not None:
        ldiag_code = diag_code.lower()
        if any(hint in ldiag_code for hint in failure_hints):
            return email

    print('! Not a permanent failure status (%s).' % status)
    if diag_code is not None:
        print('! Diagnostic code was: %s' % diag_code)
    return None


def findAddressInPlainBounce(bounce, real_bounce=None):
    """Finds the faulty email address in a non-RFC-1894 bounced email
    """
    # real_bounce is the full email and bounce only the text/plain part, if email have several MIME parts
    real_bounce = real_bounce or bounce
    lower_from = real_bounce['From'].lower()
    if 'mailer-daemon@' not in lower_from and 'postmaster' not in lower_from:
        print('! Not a valid plain bounce (expected from MAILER-DAEMON or postmaster, found %s).' % bounce['From'])
        return None
    if bounce.get_content_type() != 'text/plain':
        print('! Not a valid plain bounce (expected text/plain, found %s).' % bounce.get_content_type())
        return None
    subject = findSubject(real_bounce).lower()
    known_subjects = [
        "delivery status notification (failure)",
        "failure notice",
        "mail delivery failure",
        "returned mail: see transcript for details",
        "undeliverable message",
        "undelivered mail returned to sender",
        ]
    if subject not in known_subjects and not subject.startswith('mail delivery failed'):
        print('! Not a valid plain bounce (unknown subject: %s).' % subject)
        return None

    # Read the 15 first lines of content and find some relevant keywords to validate the bounce
    lines = bounce.get_payload().splitlines()[:15]

    # ALTOSPAM is a service which requires to click on a link when sending an email
    # Don't consider the "554 5.0.0 Service unavailable" returned by ALTOSPAM as a failure
    # but put this message in the dsn-temp mailbox so that it can be processed by hand.
    if any("ALTOSPAM which is used by the person" in line for line in lines):
        print('! ALTOSPAM has been detected. Moving this message to the dsn-temp mbox')
        return None

    # Match:
    #   A message that you sent could not be delivered to one or more of its recipients.
    #   I'm afraid I wasn't able to deliver your message to the following addresses.
    #   The following message to <email@example.com> was undeliverable.
    non_delivery_hints = [
        "could not be delivered to",
        "Delivery to the following recipient failed permanently",
        "I'm sorry to have to inform you that your message could not",
        "I wasn't able to deliver your message",
        "try to send your message again at a later time",
        "> was undeliverable.",
        "we were unable to deliver your message",
    ]
    if not any(any(hint in line for hint in non_delivery_hints) for line in lines):
        print('! Unknown mailer-daemon message, unable to find an hint for non-delivery in message:')
        print('\n'.join(lines))
        return None

    # Match:
    #   This is a permanent error; I've given up. Sorry it didn't work out.
    #   5.1.0 - Unknown address error 550-'email@example.com... No such user'
    permanent_error_hints = [
        "Delivery to the following recipient failed permanently",
        "failed due to an unavailable mailbox",
        "I'm sorry to have to inform you that your message could not",
        "This is a permanent error",
        "Unknown address error",
        "unreachable for too long",
        "550 Requested action not taken",
    ]
    if not any(any(hint in line for hint in permanent_error_hints) for line in lines):
        print('! Unknown mailer-daemon message, unable to find an hint for permanent error in message:')
        print('\n'.join(lines))
        return None

    # Retrieve the first occurence of <email@example.com>
    for line in lines:
        match = re.match(r'.*?<([0-9a-zA-Z_.-]+@[0-9a-zA-Z_.-]+)>', line)
        if match is None:
            match = re.match(r'^\s*"?([0-9a-zA-Z_.-]+@[0-9a-zA-Z_.-]+)"?\s*$', line)
        if match is not None:
            email = match.group(1)
            if email.endswith('@polytechnique.org'):
                # First valid mail is something like <info_newsletter@polytechnique.org>, so we missed the real one
                break
            return email

    print('! Unknown mailer-daemon message, unable to find email address:')
    print('\n'.join(lines))
    return None

#----------------------------------------------------------------------------#

class DirectBouncesFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.seen = 0
        self.bad_problems = 0
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
            if message['From'] == 'newsletter-externes-owner@polytechnique.org':
                print('! Dropping a notification from mailman for newsletter-externes@polytechnique.org, this should be OK.')
                self.seen -= 1
                return True
            # Additionnal checks, just to be sure
            elif message['From'] != 'MAILER-DAEMON@polytechnique.org (Mail Delivery System)' \
            or message['Subject'] != 'Undelivered Mail Returned to Sender':
                print('! Not an usual direct bounce (From="%s", Subject="%s").' % (message['From'], message['Subject']))
            else:
                email = findAddressInBounce(message)
                if email is not None:
                    self.emails.append(email)
                    self.mbox.add(message)
                    return True
                else:
                    print('! => No email found in direct bounce, this is really bad.')
                    self.bad_problems += 1
        return False

    def finalize(self):
        print('Found %d messages with no X-Spam-Flag header.' % self.seen)
        print('Found %d of them that are confirmed bounces.' % len(self.mbox))
        print('They were saved in %s.' % self.mbox_file)
        if self.bad_problems:
            print('Found %d of them that are invalid.' % self.bad_problems)
        if self.seen != len(self.mbox) + self.bad_problems:
            print('  /!\ These numbers shoud be equal! We have a problem! /!\\')
        print('')
        print('Here is the list of email adresses for these bounces:')
        print('')
        for email in self.emails:
            print(email)
        print('')
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
        print('Found %d spams. This is reliable.' % len(self.mbox))
        print('They were saved in %s.' % self.mbox_file)
        print('You might check the contents of this mbox.')
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
        print('Found %d unclassified messages. Most of them should be spams.' % len(self.mbox))
        print('They were saved in %s.' % self.mbox_file)
        print('You must check the contents of this mbox and feed the antispam.')
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
            print('Encountered %d messages that were neither spam, nor unsure, nor non-spams.' % self.seen)
            print('Please investigate.')
        else:
            print('All messages were either spam, or unsure, or non-spams. Good.')

#----------------------------------------------------------------------------#

class OutOfOfficeFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.mbox_file = '%s.ooo' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()
        subject_re = [
            r'^Absen(t|ce)',
            r'^(AUTO: )?Out of (the )?office',
            r'^Auto( ?): ',
            r'^AutoRe( ?):',
            r'^Automatic reply: ',
            r'automatique d\'absence',
            r'AutoReply',
            r'(est|is) absent',
            r'^En dehors du bureau',
            r'I am out of town',
            r'I am currently away',
            r'(am|is) out of (the )?office',
            r'Notification d\'absence',
            r'^Out of email reach',
            r'R.{1,2}ponse automatique( :)?',  # There may be encoding error of e acute
            r'^Respuesta de Estoy ausente:',
        ]
        self.subject_regexes = [re.compile(sre, re.I | re.U) for sre in subject_re]

    def process(self, message):
        subject = findSubject(message)
        if subject is not None and any(regex.search(subject) for regex in self.subject_regexes):
            self.mbox.add(message)
            return True

        # Some systems reply with "Re: ". Be smart here!
        if subject is not None and subject.startswith('Re: '):
            # Delivered-To: Autoresponder
            if 'Autoresponder' in message.get_all('Delivered-To'):
                self.mbox.add(message)
                return True
            #  Parse content if it is simple enough
            if message.get_content_type() == 'text/plain':
                firstline = message.get_payload().splitlines()[0].lower()
                if (' absent du bureau ' in firstline
                    or ' away from my office ' in firstline):
                    self.mbox.add(message)
                    return True

        return False

    def finalize(self):
        print('Found %d "out of office". This is generally reliable.' % len(self.mbox))
        print('They were saved in %s.' % self.mbox_file)
        print('You may check the contents of this mbox.')
        self.mbox.close()

#----------------------------------------------------------------------------#

class DeliveryStatusNotificationFilter(MboxFilter):

    def initialize(self, mbox_file):
        self.emails = []
        self.mbox_file = '%s.dsn' % mbox_file
        self.mbox = mailbox.mbox(self.mbox_file)
        self.mbox.clear()
        self.mbox_temp_file = '%s.dsn-temp' % mbox_file
        self.mbox_temp = mailbox.mbox(self.mbox_temp_file)
        self.mbox_temp.clear()

    def process(self, message):
        # Don't modify message variable for "self.mbox.add(message)"
        report_message = message
        # Find real report inside attachment
        if message.get_content_type() == 'multipart/mixed':
            # Some MTA confuse multipart/mixed with multipart/report
            # Let's try to find a report!
            if len(message.get_payload()) >= 2:
                try_status = message.get_payload(1)
                if try_status.get_content_type() == 'message/delivery-status':
                    # The world would be a nice place if delivery-status were
                    # formatted as expected...
                    email = findAddressInWeirdDeliveryStatus(try_status)
                    if email is not None:
                        self.emails.append(email)
                        self.mbox.add(message)
                        return True
                try_status = None
            report_message = message.get_payload(0)

        # Process report if its type is correct
        if report_message.get_content_type() == 'multipart/report':
            email = findAddressInBounce(report_message)
            if email is not None:
                self.emails.append(email)
                self.mbox.add(message)
            else:
                print("! => Moved to temporary DSN mailbox")
                self.mbox_temp.add(message)
            return True

        # Detect ill-formatted reports, sent as plain text email
        if report_message.get_content_type() == 'text/plain' and (
            'MAILER-DAEMON@' in message.get('From', '').upper() or
            'mail delivery failure' == message.get('Subject', '').lower()
            ):
            email = findAddressInPlainBounce(report_message)
            if email is not None:
                self.emails.append(email)
                self.mbox.add(message)
                return True
        return False

    def finalize(self):
        print('Found %d delivery status notifications. This is generally reliable.' % len(self.mbox))
        print('They were saved in %s.' % self.mbox_file)
        print('')
        print('Here is the list of email adresses for these bounces:')
        print('')
        for email in self.emails:
            print(email)
        print('')
        self.mbox.close()
        print('Found %d temporary and invalid delivery status notifications.' % len(self.mbox_temp))
        print('They were saved in %s.' % self.mbox_temp_file)
        self.mbox_temp.close()

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
            print('%d messages reached the catchall.' % len(self.mbox))
            print('They were saved in %s.' % self.mbox_file)
            print('You must process the contents of this mbox manually.')
            self.mbox.close()
        else:
            print('No messages reached the catchall. Nice.')
            self.mbox.close()
            os.unlink(self.mbox_file)

#----------------------------------------------------------------------------#

if __name__ == '__main__':

    if len(sys.argv) != 2:
        print('Usage: %s mbox' % sys.argv[0])
        sys.exit(1)

    if not os.path.exists(sys.argv[1]):
        print('No such file: %s' % sys.argv[1])
        sys.exit(1)

    processor = MboxProcessor(sys.argv[1])
    processor.run()
