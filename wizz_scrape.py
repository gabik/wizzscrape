import requests
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser
import sys
import datetime
import time
from wizz_scrape_import import getViewState, getFlight
from general_scrape import find_all, clean_dup, strip_non_ascii

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

DDD = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True
Dests = []
Dests.append(DDD)
#Dests.append("BUD")
#Dests.append("CLJ")
#Dests.append("KTW")
#Dests.append("PRG")
#Dests.append("SOF")
#Dests.append("VNO")
#Dests.append("WAW")
#Dests.append("OTP")
r1 = requests.get('http://wizzair.com/en-GB/Search')
vsP = getViewState()
vsP.feed(r1.text)
viewstate=vsP._viewstate
Start = Start_orig

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
  r2 = requests.post('http://wizzair.com/en-GB/Search', data=dict)
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
   print prP.data
   print r2
   #if (r2.text.find("No flight found for your search") > -1) and (retry_flag==0):
   # print "Only one direction, Trying again..."
   # Ret=Start
   # time.sleep(10)
   # retry_flag=1
   # #filename="logs/"+DST+str(Start.strftime("%d%m%Y"))+".file"
   # #fd1 = open (filename, "w")
   # #fd1.write(strip_non_ascii(r2.text))
   # #fd1.close()
   # #print filename + " output file written"
   # #print "ViewState: Old="+viewstate
   # #r3=None
   # #r3 = requests.get('http://wizzair.com/en-GB/Search')
   # #vsP = getViewState()
   # #vsP.feed(r3.text)
   # #viewstate=vsP._viewstate
   # #print "New="+viewstate
   #else:
   # retry_flag=0
   print '-------'
  flightsList.extend(prP.data)
  Start=Ret 
 print ""
 Out=[]
 Inc=[]
 for i in flightsList:
  if i['direction'] == 4 : 
   Out.append(i) 
  else: 
   Inc.append(i)
 Out=clean_dup(Out)
 Inc=clean_dup(Inc)
 if debug_flag:
  print "Debug: After clean_dup: Out, Inc: "
  print Out
  print Inc
 Out = sorted(Out, key=lambda k: int(k['price']))
 Inc = sorted(Inc, key=lambda k: int(k['price']))
 if debug_flag:
  print "Debug: After sorting: Out, Inc: "
  print Out
  print Inc
 db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
 curs = db.cursor()
 curs.execute("select id from companies where name='wizz'")
 company_id=curs.fetchone()[0]

 for i in Out:
  depp1=datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")+datetime.timedelta(minutes=60), "%H:%M")
  depm1='00:00' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 0) else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")-datetime.timedelta(minutes=60), "%H:%M")
  curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (1,DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
  if (len(curs.fetchall()) > 0):
   curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (1,DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
  curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), 1, DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))
  curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), 1, DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))

 for i in Inc:
  depp1=datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")+datetime.timedelta(minutes=60), "%H:%M")
  depm1='00:00' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 0) else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")-datetime.timedelta(minutes=60), "%H:%M")
  curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (2,DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
  if (len(curs.fetchall()) > 0):
   curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (2,DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),depm1,depp1,str(company_id)))
  curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), 2, DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))
  curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), 2, DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))

 db.commit()

print "Done!"
print datetime.datetime.now()
