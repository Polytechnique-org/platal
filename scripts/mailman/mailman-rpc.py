#!/usr/bin/env python

from SimpleXMLRPCServer import SimpleXMLRPCServer

from Mailman import mm_cfg
from Mailman import MailList
from Mailman import Utils
from Mailman import Errors
from Mailman.i18n import _

class UserDesc: pass

def lists():
    names = Utils.list_names()
    return names
   
def members(listname):
    try:
        mlist = MailList.MailList(listname, lock=False)
    except Errors.MMListError, e:
        return None
    return mlist.getRegularMemberKeys()

def subscribe(listname,name,mail):
    try:
        mlist = MailList.MailList(listname, lock=True)
    except Errors.MMListError, e:
        return 0
    userdesc = UserDesc()
    userdesc.fullname, userdesc.address = (name,mail)
    userdesc.digest = 0
    try:
        mlist.AddMember(userdesc)
        mlist.Save()
    except Exception, e:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return 1


server = SimpleXMLRPCServer(("localhost", 4949))
server.register_function(lists)
server.register_function(members)
server.register_function(subscribe)
server.register_introspection_functions()
server.serve_forever()

