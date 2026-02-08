#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import datetime
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper

[conn, cursor] = dbhelper.dbget("interviewslots")

date = sys.argv[1]   # e.g. '2025-12-18'
courseid = sys.argv[2]
interviewid = sys.argv[3]
#date = '2025-12-18'
cursor.execute("""
    SELECT bookingid, slt, userid, interviewid, faculty, course FROM slots WHERE slt >= %s AND slt < DATE_ADD(%s, INTERVAL 1 DAY) AND course = %s AND interviewid = %s ORDER BY slt""", (date, date, courseid, interviewid))

rows = cursor.fetchall()

now = datetime.datetime.utcnow() + datetime.timedelta(hours=5, minutes=30)
today = now.date()

am_html, pm_html = [], []

for bookingid, slt, userid, interviewid, faculty, courseid in rows:
    time_str = slt.strftime('%I:%M %p')
    is_past = slt < now
    available = (userid is None)

    cursor.execute("select name from faculty where id=%s", (faculty,))
    faculty_name = cursor.fetchone()[0]

    if available and not is_past:
        css_class = "available"
        disabled = ""
    elif is_past:
        css_class = "past"
        disabled = "disabled"
    else:
        css_class = "booked"
        disabled = ""

    btn = '<button class="slot-btn %s" data-bookingid="%s" data-facultyid="%s" data-facultyname="%s" data-courseid="%s" data-interviewid="%s" %s>%s</button>' % (
        css_class, bookingid, faculty, faculty_name, courseid, interviewid, disabled, time_str
    )

    if slt.time() < datetime.time(12,0):
        am_html.append(btn)
    else:
        pm_html.append(btn)

dbhelper.dbclose(conn, cursor)
# Print only the structure you need
print("""<!-- AM Slots -->
<div class="section">
  <h3>AM Slots</h3>
  <div class="slots">
    {am_slots}
            <em id="amNoSlots" style="display:none;">No AM slots available</em>
  </div>
</div>

<!-- PM Slots -->
<div class="section">
  <h3>PM Slots</h3>
  <div class="slots">
    {pm_slots}
            <em id="pmNoSlots" style="display:none;">No PM slots available</em>
  </div>
</div>
""".format(
    am_slots="\n    ".join(am_html) if am_html else " ",
    pm_slots="\n    ".join(pm_html) if pm_html else " "
))
