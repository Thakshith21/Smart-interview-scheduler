#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import datetime

sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper


IST_OFFSET = datetime.timedelta(hours=5, minutes=30)


# Input (IST date)

ist_date_str = sys.argv[1]   # YYYY-MM-DD
ist_date = datetime.datetime.strptime(ist_date_str, "%Y-%m-%d")

# Convert IST date → UTC range
utc_start = ist_date - IST_OFFSET
utc_end   = utc_start + datetime.timedelta(days=1)

# DB Query (UTC)

[conn, cursor] = dbhelper.dbget("interviewslots")

cursor.execute("""
    SELECT bookingid, slt, userid
    FROM slots
    WHERE slt >= %s AND slt < %s
    ORDER BY slt
""", (utc_start, utc_end))

rows = cursor.fetchall()
dbhelper.dbclose(conn, cursor)

# Time handling

now_utc = datetime.datetime.utcnow()

am_html, pm_html = [], []

for bookingid, slt_utc, userid in rows:
    # Convert UTC → IST for display
    slt_ist = slt_utc + IST_OFFSET
    time_str = slt_ist.strftime('%I:%M %p')

    # Past logic (only for today IST)
    is_past = (
        slt_ist.date() == datetime.datetime.now().date() and
        slt_utc < now_utc
    )

    available = (userid is None)

    if available and not is_past:
        css_class, disabled = "available", ""
    elif is_past:
        css_class, disabled = "past", "disabled"
    else:
        css_class, disabled = "booked", "disabled"

    btn = '<button class="slot-btn %s" data-bookingid="%s" %s>%s</button>' % (
        css_class, bookingid, disabled, time_str
    )

    # AM / PM based on IST
    if slt_ist.time() < datetime.time(12, 0):
        am_html.append(btn)
    else:
        pm_html.append(btn)

# Output HTML

print """<div class="section">
  <h3>AM Slots</h3>
  <div class="slots">%s</div>
</div>

<div class="section">
  <h3>PM Slots</h3>
  <div class="slots">%s</div>
</div>
""" % (
    "\n".join(am_html) if am_html else "<em>No AM slots</em>",
    "\n".join(pm_html) if pm_html else "<em>No PM slots</em>"
)

