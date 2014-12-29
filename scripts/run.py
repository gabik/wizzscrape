#!/usr/bin/python
import os
import psycopg2
import subprocess
import time
from random import shuffle
from random import randint
import sys

max_sleep = 20

arg_month=sys.argv[1]
debug_flag = "" if len(sys.argv) < 3 else "debug"
time.sleep(randint(0,max_sleep*60))
all_runs = []
db= psycopg2.connect( host="gabiscrape.c8f6qy9d6xm4.us-west-2.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
SM=open(os.path.expanduser('~/SM')).readline().strip()
companies = open('SM{0}/companies'.format(SM)).readlines()
shuffle(companies)
for c in companies:
	cur_c = c.strip()
	cur1=db.cursor()
	cur1.execute("select airport from destinations where company='{0}'".format(cur_c))
	dsts = cur1.fetchall()
	shuffle(dsts)
	for d in dsts:
		all_runs.append({'dst': d[0], 'cmp': cur_c})

shuffle(all_runs)
for r in all_runs:
	subprocess.call('unbuffer python ../scrapers/{0}_scrape.py {1} {2} {3} &> ../logs/{0}_{1}.log &'.format(r['cmp'], r['dst'], arg_month, debug_flag), shell=True)
	time.sleep(10)

finish_file = open('/tmp/finished_to_run.scrape', 'w')
finish_file.write('1')
finish_file.close()
