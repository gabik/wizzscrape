import requests
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser, HTMLParseError
import sys
import datetime
import time
from easyjet_scrape_import import getFlight
from general_scrape import find_all, clean_dup, strip_non_ascii, db, get_flight_time

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
maxn=31#500
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
#Start_orig = datetime.date(2015,8,1)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()

DST = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True

Start = Start_orig
s = requests.session()

Start = Start_orig
flightsList = []
n=0
print DST
print str(scrape_time)
print str(Start_orig), str(arg_month)
while Stop > Start:
 n+=1
 if debug_flag:
  print "Progress: " + str(n) + "/" + str(maxn)
 else:
  sys.stdout.write(" Progress: %d/%d   \r" % (n,maxn) )
  sys.stdout.flush()
 Ret = Start + datetime.timedelta(days=1)
 url='http://www.easyjet.com/links.mvc?dep=TLV&dest=' + DST +'&dd='+ Start.strftime("%d/%m/%Y") +'&rd='+ Ret.strftime("%d/%m/%Y") +'&apax=1&pid=www.easyjet.com&cpax=0&ipax=0&lang=EN&isOneWay=off&searchFrom=SearchPod|/en/'
 r1 = s.get(url)
 cur_date=Start.strftime("%d-%m-%Y")
 prP = getFlight(cur_date)
 try:
  prP.feed(r1.text)
 except HTMLParseError, err:
  print "HTMLParseError: %s" % err
 if debug_flag:
  print Start.strftime("%d/%m/%Y")
  print Ret.strftime("%d/%m/%Y")
  print len(prP.data)
  for i in prP.data:
   print i
  print r1
  print '-------'
 flightsList.extend(prP.data)
 Start=Ret 
print ""
flightsList=clean_dup(flightsList)
if debug_flag:
 print "Debug: After clean_dup: Out, Inc: "
 for i in flightsList:
  print i
#db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
curs = db.cursor()
curs.execute("select id from companies where name='easyjet'")
company_id=curs.fetchone()[0]
for i in flightsList:
 depp1=datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")+datetime.timedelta(minutes=60), "%H:%M")
 depm1='00:00' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 0) else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")-datetime.timedelta(minutes=60), "%H:%M")
 curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
 if (len(curs.fetchall()) > 0):
  curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
 curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))
 curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))

curs.execute("delete from flights where company=%s and dst=%s and date>=%s and date<%s and scrape_time<%s", (str(company_id), DST, str(Start_orig.strftime("%Y-%m-%d")), str(Stop.strftime("%Y-%m-%d")), str(scrape_time)))
db.commit()

print "Done!"
print datetime.datetime.now()
