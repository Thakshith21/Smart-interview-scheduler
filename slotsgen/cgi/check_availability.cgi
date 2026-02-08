#!/usr/bin/python2
import cgi, cgitb, sys, datetime
cgitb.enable()

# Add helper path
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper


# Connect to DB
[conn, cursor] = dbhelper.dbget("interviewslots")

# Read form inputs
form = cgi.FieldStorage()
course = form.getvalue("course")
facultyid = form.getvalue("facultyid")
start_date = form.getvalue("start_date")
end_date = form.getvalue("end_date")
start_time = form.getvalue("start_time")
end_time = form.getvalue("end_time")

# Convert to Python date/time
sd = datetime.datetime.strptime(start_date, "%Y-%m-%d").date()
ed = datetime.datetime.strptime(end_date, "%Y-%m-%d").date()
st = datetime.datetime.strptime(start_time, "%H:%M").time()
et = datetime.datetime.strptime(end_time, "%H:%M").time()

now = datetime.datetime.utcnow() + datetime.timedelta(hours=5, minutes=30)

conflict = False
day = sd

while day <= ed and not conflict:
    start_dt = datetime.datetime.combine(day, st)
    end_dt = datetime.datetime.combine(day, et)
    current = start_dt

    while current + datetime.timedelta(minutes=20) <= end_dt:
        # Ignore past slots
        if current < now:
            current += datetime.timedelta(minutes=20)
            continue

        cursor.execute(""" SELECT COUNT(*) FROM slots WHERE faculty = %s AND slt = %s AND slt >= %s """, ( facultyid, current.strftime("%Y-%m-%d %H:%M:%S"), now.strftime("%Y-%m-%d %H:%M:%S") ))

        if cursor.fetchone()[0] > 0:
            conflict = True
            break

        current += datetime.timedelta(minutes=20)

    day += datetime.timedelta(days=1)

dbhelper.dbclose(conn, cursor)

# Output plain text for frontend JS to parse
print "Content-type: text/plain\n"
if conflict:
    print "Conflict detected - faculty already has slots in this time range."
    response_msg = "Conflict detected"
else:
    print "Available - you can generate slots."
    response_msg = "Available"

# writing into a log file

userid = 1

utc_now = datetime.datetime.utcnow()
ist_now = utc_now + datetime.timedelta(hours=5, minutes=30)

log_line = "Execution_date-- %s\t'###'\t Execution_time-- %s\t'###'\tUser_id-- %s\t'###'\tCourse-- %s\t'###'\tFaculty_id-- %s\t'###'\tStart_date-- %s\t'###'\tStart_time-- %s\t'###'\tEnd_time-- %s\t'###'\tResponse-- %s\t'###'\tcheck_availability\n" % (
    ist_now.strftime("%Y-%m-%d"),
    ist_now.strftime("%H:%M:%S"),
    userid,
    course,
    facultyid,
    start_date,
    start_time,
    end_time,
    response_msg
)

with open('/home/code/interviewslots/interviewslots.log', 'a') as f:
    f.write(log_line)
