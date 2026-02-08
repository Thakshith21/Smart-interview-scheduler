import sys
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper
import urllib

[conn, cursor] = dbhelper.dbget("interviewslots")

cursor.execute('SELECT DISTINCT course FROM slots')

# Fetch all distinct courses
courses = cursor.fetchall()

htmlcourse = ''

for row in courses:
    htmlcourse += '<option value=%s>%s</option>' %(row[0], row[0],)

print(htmlcourse)
dbhelper.dbclose(conn, cursor)

