import sys
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper
import urllib
[conn, cursor] = dbhelper.dbget("interviewslots")

htmlfac = ''

cursor.execute("select id, name from faculty")
for row in cursor.fetchall():
    [pid, name] = row
    htmlfac +='<option value="%s">%s</option>' %(pid, name,)

print(htmlfac)

dbhelper.dbclose(conn, cursor)

