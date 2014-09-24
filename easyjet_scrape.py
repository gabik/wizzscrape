import requests
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser, HTMLParseError
import sys
import datetime
import time
from easyjet_scrape_import import getFlight
from general_scrape import find_all, clean_dup, strip_non_ascii

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
new_year=0
maxn=31#500
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
cur_year=Start_orig.year
#Start_orig = datetime.date(2015,8,1)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()

DDD = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True
Dests = []
Dests.append(DDD)
Start = Start_orig
s = requests.session()

for DST in Dests:
 Start = Start_orig
 flightsList = []
 n=0
 print DST
 print str(scrape_time)
 while Stop > Start:
  n+=1
  if debug_flag:
   print "Progress: " + str(n) + "/" + str(maxn)
  else:
   sys.stdout.write(" Progress: %d/%d   \r" % (n,maxn) )
   sys.stdout.flush()
  Ret = Start + datetime.timedelta(days=1)
  #url='http://www.easyjet.com/links.mvc?dep=TLV&dest=' + DST +'&dd='+ (str(int(Start.day)) + "/" + str(int(Start.month)) + "/" + str(int(Start.year))) +'&rd='+ (str(int(Ret.day)) + "/" + str(int(Ret.month)) + "/" + str(int(Ret.year))) +'&apax=1&pid=www.easyjet.com&cpax=0&ipax=0&lang=EN&isOneWay=off&searchFrom=SearchPod|/en/'
  url='http://www.easyjet.com/links.mvc?dep=TLV&dest=' + DST +'&dd='+ Start.strftime("%d/%m/%Y") +'&rd='+ Ret.strftime("%d/%m/%Y") +'&apax=1&pid=www.easyjet.com&cpax=0&ipax=0&lang=EN&isOneWay=off&searchFrom=SearchPod|/en/'
  r1 = s.get(url)
  #r2 = s.get('http://www.easyjet.com/EN/Booking.mvc')
  prP = getFlight(cur_year,new_year)
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
  new_year=prP.new_year
  if Start > datetime.date(cur_year+1, 2, 10) : new_year=1
  Start=Ret 
 print ""
 flightsList=clean_dup(flightsList)
 if debug_flag:
  print "Debug: After clean_dup: Out, Inc: "
  for i in flightsList:
   print i
 db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
 curs = db.cursor()
 curs.execute("select id from companies where name='easyjet'")
 company_id=curs.fetchone()[0]
 for i in flightsList:
  curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),i['dep_time'],str(company_id)))
  if (len(curs.fetchall()) > 0):
   curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),i['dep_time'],str(company_id)))
  curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))
  curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))

 db.commit()

print "Done!"
print datetime.datetime.now()
