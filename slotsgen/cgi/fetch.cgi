#!/usr/bin/python2
import cgi
import cgitb
import sys
import json

cgitb.enable()

# Helper path
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper

print("Content-Type: application/json\n")

# Read input
form = cgi.FieldStorage()
bookingid = form.getfirst('bookingid')
courseid = form.getvalue("courseid")

if not bookingid or not courseid:
    print(json.dumps({
        "status": "error",
        "message": "bookingid or courseid missing"
    }))
    sys.exit()

# DB connection
[conn, cursor] = dbhelper.dbget('interviewslots')

# Fetch slot data


query = """ SELECT bookingid, userid, interviewid, faculty, course, DATE_FORMAT(slt, '%%h:%%i %%p') AS slottime FROM slots WHERE bookingid = %s LIMIT 1"""


cursor.execute(query, (bookingid,))
row = cursor.fetchone()

if not row:
    print(json.dumps({
        "status": "error",
        "message": "Booking not found"
    }))
    dbhelper.dbclose(conn, cursor)
    sys.exit()

bookingid, userid, interviewid, facultyid, courseid, slottime = row

if interviewid == 0:
    interviewidname = 'Mock 1 Interview'
elif interviewid == 1:
    interviewidname = 'Mock 2 Interview'

# Fetch user email from file using userid + course

cursor.execute("select name from faculty where id = %s", (facultyid,))
facultyname = cursor.fetchone()[0]

email = ""

email_file = '/home/code/interviewslots/emails_%s.txt' % courseid

with open(email_file, 'r') as f:
    for line in f:
        line = line.strip()
        if not line:
            continue

        parts = line.split()
        if parts[0] == str(userid):
            # parts[1] contains email<br>phone
            email = parts[1].split('<br>')[0]
            break

# Response
print(json.dumps({
    "status": "success",
    "data": {
        "bookingid": bookingid,
        "useremail": email,
        "interviewid": interviewidname,
        "facultyname": facultyname,
        "courseid": courseid,
        "slottime": slottime
    }
}))

dbhelper.dbclose(conn, cursor)

