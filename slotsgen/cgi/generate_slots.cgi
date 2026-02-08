#!/usr/bin/python2
import cgi
import cgitb
import sys
import datetime

# Enable error reporting
cgitb.enable()

# Add your helper path
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper

# Connect to DB
[conn, cursor] = dbhelper.dbget('interviewslots')

# Read form inputs
form = cgi.FieldStorage()
course = form.getvalue("course")
facultyid = form.getvalue("facultyid")
interviewid = form.getvalue("interviewid")
start_date = form.getvalue("start_date")   # yyyy-mm-dd
end_date = form.getvalue("end_date")
start_time = form.getvalue("start_time")   # HH:MM
end_time = form.getvalue("end_time")       # HH:MM


# Convert to Python date/time
sd = datetime.datetime.strptime(start_date, "%Y-%m-%d").date()
ed = datetime.datetime.strptime(end_date, "%Y-%m-%d").date()
st = datetime.datetime.strptime(start_time, "%H:%M").time()
et = datetime.datetime.strptime(end_time, "%H:%M").time()

created = 0
day = sd
while day <= ed:
    start_dt = datetime.datetime.combine(day, st)
    end_dt = datetime.datetime.combine(day, et)
    current = start_dt
    while current + datetime.timedelta(minutes=20) <= end_dt:
        # Check if slot already exists for this faculty
        cursor.execute("""
            SELECT COUNT(*) FROM slots WHERE slt = %s AND faculty = %s """, (current.strftime("%Y-%m-%d %H:%M:%S"), facultyid))
        exists = cursor.fetchone()[0]

        if exists == 0:
            cursor.execute("""
                INSERT INTO slots (slt, userid, interviewid, course, faculty)
                VALUES (%s, NULL, %s, %s, %s)
            """, (current.strftime("%Y-%m-%d %H:%M:%S"), interviewid, course, facultyid))
            conn.commit()
            created += 1

        current += datetime.timedelta(minutes=20)
    day += datetime.timedelta(days=1)



# get faculty name

cursor.execute('select name from faculty where id = %s' %(facultyid,))
facname = cursor.fetchone()[0]
# Close DB
dbhelper.dbclose(conn, cursor)

status = "success" if created > 0 else "duplicate"
# Output HTML response
print "Content-type: text/html\n"
print "<h3>Generated %d slots for faculty %s for the date %s from %s to %s.</h3>" % (created, facname, start_date, start_time, end_time)


#write into a log file
userid = -1
utc_now = datetime.datetime.utcnow()
ist_now = utc_now + datetime.timedelta(hours=5, minutes=30)

log_line = "Execution_date-- %s\t'###'\tExecution_time-- %s\t'###'\tUserid-- %s\t'###'\tCourse-- %s\t'###'\tFaculty_id-- %s\t'###'\tStart_date-- %s\t'###'\tStart_time-- %s\t'###'\tEnd_time-- %s\t'###'\tResponse-- %s\t'###'\tgenerate_slots\n" % (
    ist_now.strftime("%Y-%m-%d"),
    ist_now.strftime("%H:%M:%S"),
    userid,
    course,
    facultyid,
    start_date,
    start_time,
    end_time,
    status
)

with open('/home/code/interviewslots/interviewslots.log', 'a') as f:
    f.write(log_line)
