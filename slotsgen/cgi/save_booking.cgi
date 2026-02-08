#!/usr/bin/python2
import cgi, cgitb, sys, datetime
cgitb.enable()

sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper

[conn, cursor] = dbhelper.dbget("interviewslots")

form = cgi.FieldStorage()
bookingid = form.getvalue("booking_id")
interviewid = form.getvalue("interview_id")
userid = form.getvalue("user_id")

print "Content-type: text/plain\n"

if bookingid and interviewid and userid:
    bookingid = int(bookingid)
    interviewid = int(interviewid)
    userid = int(userid)

    # Check duplicate constraint before update
    cursor.execute("""SELECT COUNT(*) FROM slots WHERE userid=%s AND interviewid=%s AND course=( SELECT course FROM slots WHERE bookingid=%s )""", (userid, interviewid, bookingid))
    duplicate_count = cursor.fetchone()[0]

    if duplicate_count > 0:
        print "duplicate"
        response_msg = "duplicate"
    else:
        sql = """UPDATE slots SET userid = %s, interviewid = %s WHERE bookingid = %s"""
        cursor.execute(sql, (userid, interviewid, bookingid))
        conn.commit()

        if cursor.rowcount > 0:
            print "success"
            response_msg = "success"
        else:
            print "notfound"
            response_msg = "notfound"
else:
    print "missing"

dbhelper.dbclose(conn, cursor)

# write into a log file
userid = -1

utc_now = datetime.datetime.utcnow()
ist_now = utc_now + datetime.timedelta(hours=5, minutes=30)

log_line = "Execution_date-- %s\t'###'\tExecution_time-- %s\t'###'\tUserid-- %s\t'###'\tBooking_id-- %s\t'###'\tInterview_id-- %s\t'###'\tResponse-- %s\t'###'\tsave_booking\n" % (
    ist_now.strftime("%Y-%m-%d"),
    ist_now.strftime("%H:%M:%S"),
    userid,
    bookingid,
    interviewid,
    response_msg
)

with open('/home/code/interviewslots/interviewslots.log', 'a') as f:
    f.write(log_line)
