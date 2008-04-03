#!/usr/bin/python

import sys
sys.path.append('/usr/lib/mailman/bin')
import paths
from Mailman import MailList
from Mailman import Utils
from Mailman import mm_cfg

def get_bogo_level(mlist):
  """ Retreive the old style bogo level """
  try:
    if mlist.header_filter_rules == []:
      return 0
    try:
      action = mlist.header_filter_rules[1][1]
      return 2
    except:
      action = mlist.header_filter_rules[0][1]
      if action == mm_cfg.HOLD:
        return 1
      elif action == mm_cfg.DISCARD:
        return 3
  except:
    return 0

def set_bogo_level(mlist, level):
  """ Convert bogo level to the new level structure """
  if level == 0:
    return
  hfr = []
  if level == 1:
    hfr.append(('X-Spam-Flag: Unsure, tests=bogofilter', mm_cfg.HOLD, False))
    hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
  elif level == 2:
    hfr.append(('X-Spam-Flag: Unsure, tests=bogofilter', mm_cfg.HOLD, False))
    hfr.append(('X-Spam-Flag: Yes, tests=bogofilter, spamicity=(0\.999999|1\.000000)', mm_cfg.DISCARD, False))
    hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.HOLD, False))
  elif level == 3:
    hfr.append(('X-Spam-Flag: Unsure, tests=bogofilter', mm_cfg.HOLD, False))
    hfr.append(('X-Spam-Flag: Yes, tests=bogofilter', mm_cfg.DISCARD, False))
  mlist.Lock()
  mlist.header_filter_rules = hfr
  mlist.Save()
  mlist.Unlock()


names = Utils.list_names()
names.sort()
for name in names:
  mlist = MailList.MailList(name, lock=0)
  set_bogo_level(mlist, get_bogo_level(mlist))

