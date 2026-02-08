#!/usr/bin/python2
import cgi, cgitb, sys, datetime
cgitb.enable()

sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper

print "Content-Type: text/plain\n"

[conn, cursor] = dbhelper.dbget("interviewslots")

form = cgi.FieldStorage()
bookingid = form.getvalue("booking_id")

if not bookingid:
    print "error"
    dbhelper.dbclose(conn, cursor)
    sys.exit()

cursor.execute(""" delete from slots WHERE bookingid = %s""", (bookingid,))
conn.commit()

if cursor.rowcount > 0:
    print "success"
    response_msg = "success"
else:
    print "error"
    response_msg = "error"

dbhelper.dbclose(conn, cursor)

userid = -1

utc_now = datetime.datetime.utcnow()
ist_now = utc_now + datetime.timedelta(hours=5, minutes=30)

log_line = "Executiondate-- %s\t'###'\tExecutiontime-- %s\t'###'\tUserid-- %s\t'###'\tBookingid-- %s\t'###'\tResponse-- %s\t'###'\tdelete\n" % (
    ist_now.strftime("%Y-%m-%d"),
    ist_now.strftime("%H:%M:%S"),
    userid,
    bookingid,
    response_msg
)

with open('/home/code/interviewslots/interviewslots.log', 'a') as f:
    f.write(log_line)
