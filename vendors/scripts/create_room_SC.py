#!/usr/bin/env python
import os
import sys 
import urllib, urllib2
total = len(sys.argv)

cmdargs = str(sys.argv)
print ("Content-Type: text/html")
print ("Number of args passed is: %d " % total)
print ("Args list: %s " % cmdargs)
print ("Script name: %s % str(sys.argv[0]))
print ("First argument: %s % str(sys.argv[1]))
print ("Second argument: %s % str(sys.argv[2]))
print ("Third argument: %s % str(sys.argv[3]))
print ("Fourth argument: %s % str(sys.argv[4]))
print ("Fifth argument: %s % str(sys.argv[5]))
print ("Sixth argument: %s % str(sys.argv[6]))

XML = """\
<ServiceBusMessage>
  <headerHash>
    <AccountNumber>A1001</AccountNumber>
  </headerHash>
  <body>
  </body>
</ServiceBusMessage>
"""

url = "http://live.revxsystems.com/api/serviceRequest.php"
req = urllib2.Request(url)
req.add_header("Content-type", "application/x-www-form-urlencoded")
key = "dc8cf8a56a3548a2455233c32377eb081019e7c9"
svc = "get_account_info_blocked"
strData  = "ApiKey=" + urllib.quote_plus(key) + "&Service=" + urllib.quote_plus(svc) + "&ServiceRequest="  +  urllib.quote_plus(XML);
#try:
#	req.get_method = lambda: 'POST'
#	handle = urllib2.urlopen(req, strData)
#	mesg = handle.read()
#	print mesg
#except IOError, e:
#	print "Well that one sucked mightily . . . where did I go wrong this time?  Oh!-- here it is! {0} ".encode(str(e))
