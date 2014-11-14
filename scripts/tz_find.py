#!/usr/bin/python

# The webid is for http://www.timeanddate.com/worldclock/converter.html

import pytz, sys
import psycopg2

db= psycopg2.connect( host="gabiscrape.c8f6qy9d6xm4.us-west-2.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")

cur1=db.cursor()
cur1.execute("select destination FROM destinations WHERE airport=%s", (sys.argv[1],))
dst=cur1.fetchone()[0]

print dst
print "insert into timezones values('"+sys.argv[1]+"', '');"
print " " 

for tz in pytz.all_timezones:
  if dst in tz:
   print tz + ": " 
   print "insert into timezones values('"+sys.argv[1]+"', '"+tz+"');"

print '-----'
print "To see missings:"
print "select a.airport, max(destination) from destinations a  left join timezones t on a.airport=t.airport where t.airport is null group by a.airport;"
