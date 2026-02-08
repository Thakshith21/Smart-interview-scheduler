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
    response_msg = "error no booking id"
    dbhelper.dbclose(conn, cursor)
    sys.exit()

cursor.execute(""" UPDATE slots SET userid = NULL WHERE bookingid = %s""", (bookingid,))
conn.commit()

if cursor.rowcount > 0:
    print "success"
    response_msg = "success"
else:
    print "error"
    response_msg = "error"

dbhelper.dbclose(conn, cursor)

# write into a log file
userid = -1

utc_now = datetime.datetime.utcnow()
ist_now = utc_now + datetime.timedelta(hours=5, minutes=30)

log_line = "Execution_date-- %s\t'###'\tExecution_time--  %s\t'###'\tUserid-- %s\t'###'\tBooking_id-- %s\t'###'\tresponse-- %s\t'###'\tunbook\n" % (
    ist_now.strftime("%Y-%m-%d"),
    ist_now.strftime("%H:%M:%S"),
    userid,
    bookingid,
    response_msg
)

with open('/home/code/interviewslots/interviewslots.log', 'a') as f:
    f.write(log_line)
