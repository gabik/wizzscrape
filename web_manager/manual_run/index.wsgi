#!/usr/bin/python
# -*- coding: UTF-8 -*-

import glob
import os
import subprocess
import psycopg2

process = subprocess.Popen("ps ax | grep run_sm_instance.py | grep python | grep -v grep", stdout=subprocess.PIPE, shell=True)
stdout_list = process.communicate()[0].split('\n')
ps_list = ""
for i in stdout_list: ps_list+=' '.join(i.split()[5:])+'<BR>'

db= psycopg2.connect( host="gabiscrape.c8f6qy9d6xm4.us-west-2.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
cur1=db.cursor()
cur1.execute("select min(a.scrape_time), a.company, max(b.name) cmp, (a.date-current_date)/31 mon  from flights a join companies b on a.company=b.id where a.scrape_time<current_date group by a.company, mon order by cmp, mon")
old1 = cur1.fetchall()
old_flights = ""
for i in old1:
	old_flights+=', '.join([str(a) for a in i])+'<BR>'


SM_folds = '~ec2-user/wizz/scripts/SM'
folds = glob.glob(os.path.expanduser('{0}*'.format(SM_folds)))
folds.sort()
comps = ""
for f in folds:
	comps += "<tr>"
	c_id = f[-1]
	comps += "<td>{0}</td>".format(c_id)
	comp_fd = open('{0}/companies'.format(f), 'r')
	cur_comps = comp_fd.readlines()
	comp_fd.close()
	c_str = ', '.join([x.strip() for x in cur_comps])
	comps += "<td>{0}</td>".format(c_str)
	comps += "</tr>"

html="""
<HTML>
<HEAD><TITLE>Manual Runner</TITLE>
<BODY>
Choose company list to run and month to run on.
company lists are:
<table>
{0}
</table>
<form action="/run_do" method=post name=runner id=runner>
company list: <input id=cmp_list name=cmp_list type=text><BR>
month (0-12): <input id=month name=month type=text><BR>
<input type=submit >
</form>
<BR>
Current running scrapers: (cmd, month, companies)<BR>
{1}
<BR><BR>
Old flights on the DB:<BR>
Timestamp , Company ID , Company Name , Month <BR>
{2}
</BODY></HTML>
""".format(comps, ps_list, old_flights)


def application (env, r):
    body = html
    status = '200 OK'
    response_headers = [ ('Content-Type', 'text/html'), ('Content-Length', str (len (body) ) ) ]
    r (status, response_headers)
    return [body]

