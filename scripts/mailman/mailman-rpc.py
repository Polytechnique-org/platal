#!/usr/bin/env python

from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

from Mailman import mm_cfg
from Mailman import MailList
from Mailman import Utils
from Mailman import Errors
from Mailman.i18n import _

class UserDesc: pass

#------------------------------------------------
# Manage Basic authentication
#

class BasicAuthXMLRPCRequestHandler(SimpleXMLRPCServer.SimpleXMLRPCRequestHandler):

    """XMLRPC Request Handler
    This request handler is used to provide BASIC HTTP user authentication.
    It first overloads the do_POST() function, authenticates the user, then
    calls the super.do_POST().
    """

    def do_POST(self):
	headers = self.headers
	print headers
	if not headers.has_key("authorization"):
	    self.send_response(401)
	    self.end_headers()
	auth = headers["authorization"]
	auth = auth.replace("Basic ","")
	decoded_auth = base64.decodestring(auth)
	try:
	    user, passwd = decoded_auth.split(':')
	except:
	
	# Call super.do_POST() to do the actual work
	SimpleXMLRPCServer.SimpleXMLRPCRequestHandler.do_POST(self)


#------------------------------------------------
# Procedures
#

def lists_names():
    names = Utils.list_names()
    names.sort()
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
        mlist.ApprovedAddMember(userdesc)
        mlist.Save()
    except Exception, e:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return 1

def unsubscribe(listname,mail):
    try:
        mlist = MailList.MailList(listname, lock=True)
    except Errors.MMListError, e:
        return 0
    try:
        mlist.ApprovedDeleteMember(mail, 'xml-rpc iface', False, False);
        mlist.Save()
    except Exception, e:
        mlist.Unlock()
        return 0
    mlist.Unlock()
    return 1

#------------------------------------------------
# server
#

server = SimpleXMLRPCServer(("localhost", 4949))
server.register_function(lists_names)
server.register_function(members)
server.register_function(subscribe)
server.register_function(unsubscribe)
server.register_introspection_functions()
server.serve_forever()

