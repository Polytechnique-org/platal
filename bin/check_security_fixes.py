#!/usr/bin/env python
#***************************************************************************
#*  Copyright (C) 2003-2015 Polytechnique.org                              *
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
#***************************************************************************/

"""Checks that a working copy of plat/al has all the latest security patches
applied. It uses the local SECURITY file to determine the list of mandatory
patches.

Important notice: do not execute this script directly from an automatic checkout
of plat/al. It would be extremely unwise to execute it with root privileges from
a place where everybody can change it!

Usage (-w updates the local .htaccess to disable guilty working copies):
  check_security_fixes.py [-w] -b REFERENCE_PLATAL PLATAL_TO_CHECK...
"""

import optparse
import os
import re
import sys
import time


class WorkingCopy(object):
  """Helper class for analyzing the state of a working copy, and eventually
  disabling it if an issue is found.

  It disables the local checkout by updating its .htaccess file to deny all
  requests with an explicit message which states how to fix the issue.
  """

  CORE_SECURITY_FILE = 'core/SECURITY'
  MASTER_SECURITY_FILE = 'SECURITY'
  SECURITY_FIX_RE = re.compile('^-[0-9]{4}')

  HTACCESS_FILE = 'htdocs/.htaccess'
  HTACCESS_TEMPLATE = 'Deny from all\nErrorDocument 403 "%s"\n'
  HTACCESS_MTIME_DELTA = 86400 * 365 * 10
  ERROR_MESSAGE_LINE = '<li>%s</li>\n'
  ERROR_MESSAGE_TEMPLATE = """
    Your local checkout of plat/al has been disabled for security reasons. It
    appears that several critical flaws known in the plat/al codebase have not
    been patched in your working copy. These flaws are listed below:
    <ul>%s</ul>

    Please have a look at the SECURITY and core/SECURITY files in any recent
    plat/al checkout to get more details on which commits did fix those flaws.
    <br/><br/>

    <em>Note:</em> you can re-enable your working copy by typing
    <code>make</code> in the root directory of your checkout (usually in
    <code>~/dev/platal</code>).
  """

  def __init__(self, reference_path, checkout_path):
    self.reference_path = reference_path
    self.checkout_path = checkout_path

  def GetPartialSecurityDiff(self, security_file):
    """Diffs the reference and a local SECURITY file to find missing security
    fixes. It filters out the diff result to extract the list of fixes."""

    ref_file = os.path.join(self.reference_path, security_file)
    wc_file = os.path.join(self.checkout_path, security_file)

    diff = os.popen('diff -NBw -U 0 %s %s' % (ref_file, wc_file))
    for line in diff.readlines():
      if self.SECURITY_FIX_RE.match(line):
        yield line[1:-1]

  def GetSecurityDiff(self):
    """Retrieves the missing security patches for various parts of plat/al."""

    missing_fixes = []
    missing_fixes.extend(self.GetPartialSecurityDiff(self.CORE_SECURITY_FILE))
    missing_fixes.extend(self.GetPartialSecurityDiff(self.MASTER_SECURITY_FILE))
    return missing_fixes

  def GetErrorMessage(self, missing_fixes):
    """Returns a the .htaccess HTML error message.

    It builds an HTML message explaining why the working copy was disabled, how
    to fix the underlying issues, and how to re-enable it."""

    fixes_list = map(lambda item: self.ERROR_MESSAGE_LINE % item, missing_fixes)
    return self.ERROR_MESSAGE_TEMPLATE % '\n'.join(fixes_list)

  def Write403Htaccess(self, html_content):
    """Updates the .htaccess to disable all requests, using |html_content| as
    the error message. It also sets a modification time in the past to ensure
    that any subsquent call to 'make' on the wc will actually overwrite the
    .htaccess file."""

    htaccess = os.path.join(self.checkout_path, self.HTACCESS_FILE)
    ht_fd = open(htaccess, 'w')
    ht_fd.write(self.HTACCESS_TEMPLATE % (html_content
        .replace('\\', '\\\\')
        .replace('"', '\\"')
        .replace('\n', '\\\n')))
    ht_fd.close()

    mtime = time.time() - self.HTACCESS_MTIME_DELTA
    os.utime(htaccess, (mtime, mtime))

  def CheckAndDisableWorkingCopy(self, disable_when_flawed):
    """Checks that the local working copy is in a sane state. If not, warns the
    user by printing a message to the console, and disables the wc if
    |disable_when_flawed| is set to true."""

    missing_fixes = self.GetSecurityDiff()
    if len(missing_fixes):
      # Warn the user on the standard output.
      print "Found %d missing security fixes in %s:" % (len(missing_fixes),
                                                        self.checkout_path)
      for issue in missing_fixes:
        print "  * %s" % issue

      # Disable the working copy.
      if disable_when_flawed:
        print "Disabling working copy in %s." % self.checkout_path
        self.Write403Htaccess(self.GetErrorMessage(missing_fixes))

def SelfCheckIsLatestVersion(base_path):
  """Checks that this script is the latest available by comparing itself to
  the reference script in |base_path|. It is important to do that check as
  most deployment will want to execute this script with root privileges,
  which implies that this script is deployed in a safe directory, and not
  just executed from an automatically updated checkout of plat/al (how
  unsafe would that be...)."""

  base_script = os.path.join(base_path, 'bin/check_security_fixes.py')
  local_script = os.path.abspath(sys.argv[0])

  if os.system('diff -q %s %s' % (base_script, local_script)) != 0:
    sys.stderr.write('Please upgrade this script to the latest version.\n')

def main():
  parser = optparse.OptionParser()
  parser.add_option('-b', '--base_path', action='store', dest='base_path')
  parser.add_option('-w', '--write_htaccess', action='store_true',
                    dest='write_htaccess', default=False)
  (options, args) = parser.parse_args()

  if options.base_path is None:
    print "Error: option --base_path (or -b) is required for the script to run."
    sys.exit(1)
  if not os.path.exists(os.path.join(options.base_path,
                                     WorkingCopy.MASTER_SECURITY_FILE)):
    print "The base plat/al (%s) is too old to be used." % options.base_path
    sys.exit(1)

  SelfCheckIsLatestVersion(options.base_path)
  for platal in args:
    wc = WorkingCopy(options.base_path, platal)
    wc.CheckAndDisableWorkingCopy(options.write_htaccess)

if __name__ == '__main__':
  main()

# vim:set et sw=2 sts=2 sws=2 fenc=utf-8:
