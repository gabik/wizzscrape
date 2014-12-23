import requests
import codecs
from requests.exceptions import ConnectionError
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser
import sys
import datetime
import time
from wizz_scrape_import import getViewState, getFlight
from general_scrape import find_all, clean_dup, strip_non_ascii, db, max_retries, get_flight_time

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
maxn=31#500
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
cur_year=Start_orig.year
#Start_orig = datetime.date(2015,8,1)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()

DST = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True
gotit=0
retries=0
while gotit!=1: 
 try:
  r1 = requests.get('http://wizzair.com/en-GB/Search')
  gotit=1
 except ConnectionError:
  time.sleep(30)
  retries+=1
  print "1 ConnectionError " + str(retries)
  if retries > max_retries: raise Exception('Cannot connect on first POST!');

vsP = getViewState()
vsP.feed(r1.text)
viewstate=vsP._viewstate
new_token=vsP._newtoken
Start = Start_orig

flightsList = []
n=0
print DST
print str(scrape_time)
print str(Start_orig), str(arg_month)

cleandone=1
retries=0
while Stop > Start:
 n+=1
 if debug_flag:
  print "Progress: " + str(n) + "/" + str(maxn)
 else:
  sys.stdout.write(" Progress: %d/%d   \r" % (n,maxn) )
  sys.stdout.flush()
 dict={}
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$OriginStation']="TLV"
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DestinationStation']=DST
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DepartureDate']=Start.strftime("%d/%m/%Y")
 Ret = Start + datetime.timedelta(days=1)
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$ReturnDate']=Ret.strftime("%d/%m/%Y")
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountCHD'] = 0
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountINFANT'] = 0
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountADT'] = 1
 dict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$ButtonSubmit'] = "Search"
 dict['__EVENTTARGET'] = "ControlGroupRibbonAnonHomeView_AvailabilitySearchInputRibbonAnonHomeView_ButtonSubmit"
 dict['__VIEWSTATE'] = viewstate
 dict[new_token[0]] = new_token[1]
 try:
  r2 = requests.post('http://wizzair.com/en-GB/Search', data=dict)
 except ConnectionError:
  time.sleep(30)
  retries+=1
  print "2 ConnectionError " + str(retries)
  if retries>max_retries:
   print str(Start), str(Ret)
   print x
   cleandone=0
   Start=Start + datetime.timedelta(days=1)
  continue

 if r2.status_code != 200 :
  print str(Start), str(Ret)
  print r2.status_code
  cleandone=0
  Start=Start + datetime.timedelta(days=1)
  if debug_flag: 
   fd=codecs.open('/tmp/output_pages/'+sys.argv[0]+'_'+DST+'_'+str(Start)+'-'+str(Ret)+'.html', 'w', encoding='utf-8')
   fd.write(r2.text)
   fd.close()

  continue

 cur_date=Start.strftime("%d-%m-%Y")
 prP = getFlight(cur_date)
 prP.feed(r2.text)
 if debug_flag:
  print Start.strftime("%d/%m/%Y")
  print Ret.strftime("%d/%m/%Y")
  print len(list(find_all(r2.text, "marketColumn")))
  for s in find_all(r2.text, '<span class="price">'):
   print strip_non_ascii(r2.text[s+20:r2.text.find('<', s+20, s+30)])
  print len(prP.data)
  for t in prP.data : print t
  print r2
  print '-------'
 flightsList.extend(prP.data)
 Start=Ret 
print ""
#Out=[]
#Inc=[]
#for i in flightsList:
 #if i['direction'] == 4 : 
  #Out.append(i) 
 #else: 
  #Inc.append(i)
#Out=clean_dup(Out)
#Inc=clean_dup(Inc)
flightsList=clean_dup(flightsList)
if debug_flag:
 print "Debug: After clean_dup: Out, Inc: "
 for i in flightsList : print i
 #print Out
 #print Inc
#Out = sorted(Out, key=lambda k: int(k['price']))
#Inc = sorted(Inc, key=lambda k: int(k['price']))
#if debug_flag:
 #print "Debug: After sorting: Out, Inc: "
 #for i in  Out : print i
 #for i in  Inc : print i
curs = db.cursor()
curs.execute("select id from companies where name='wizz'")
company_id=curs.fetchone()[0]

#for i in Out:
for i in flightsList:
 depp1=datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")+datetime.timedelta(minutes=60), "%H:%M")
 depm1='00:00' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 0) else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")-datetime.timedelta(minutes=60), "%H:%M")
 curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
 if (len(curs.fetchall()) > 0):
  curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
 curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))
 curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))

if cleandone==1:
 curs.execute("delete from flights where company=%s and dst=%s and date>=%s and date<%s and scrape_time<%s", (str(company_id), DST, str(Start_orig.strftime("%Y-%m-%d")), str(Stop.strftime("%Y-%m-%d")), str(scrape_time)))
 print "Done!"

db.commit()

print datetime.datetime.now()
