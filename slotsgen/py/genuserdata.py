#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
sys.path.append('/var/www/html/thakshith/obivsinventory/py')
import dbhelper
import datetime

# Inputs
date = sys.argv[1]      # 2025-12-13
course = sys.argv[2]    # 1964

[conn, cursor] = dbhelper.dbget("interviewslots")


cursor.execute(""" SELECT DISTINCT userid FROM slots WHERE course = %s""", (course))

booked_users = [str(row[0]) for row in cursor.fetchall()]

dbhelper.dbclose(conn, cursor)

already_users = {}

with open('/home/code/interviewslots/emails_%s.txt' %(course), 'r') as f:
    for line in f:
        line = line.strip()
        if not line:
            continue

        # Split userid from rest (multiple spaces possible)
        parts = line.split(None, 1)   # split on any whitespace
        if len(parts) < 2:
            continue

        userid = parts[0]
        rest = parts[1]

        # Extract email before <br>
        email = rest.split('<br>')[0].strip()

        already_users[userid] = email

booked_set  = set(booked_users)
already_set = set(already_users.keys())

# Users who are in file but NOT booked
not_booked_userids = list(already_set - booked_set)

htmluser = ''.join(
    '<option value="%s">%s</option>' % (uid, already_users[uid])
    for uid in sorted(not_booked_userids, key=lambda u: already_users[u].lower())
)

print(htmluser)

