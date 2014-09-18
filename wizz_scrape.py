import requests
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser
import sys
import datetime
import time
from wizz_scrape_import import find_all, clean_dup, strip_non_ascii, getViewState, getFlight

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
new_year=0
maxn=31#500
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
#Start_orig = datetime.date(2015,8,1)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
cur_year=Start_orig.year
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
  prP = getFlight(cur_year,new_year)
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
  new_year=prP.new_year
  if Start > datetime.date(cur_year+1, 2, 10) : new_year=1
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
 for i in Out:
  curs.execute("INSERT INTO wizz_flights (scrape_time, direction, dst, price, time, date) VALUES (%s, %s, %s, %s, %s, %s)", (str(scrape_time), 1, DST, int(i['price']), i['time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))

 for i in Inc:
  curs.execute("INSERT INTO wizz_flights (scrape_time, direction, dst, price, time, date) VALUES (%s, %s, %s, %s, %s, %s)", (str(scrape_time), 2, DST, int(i['price']), i['time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day'])))

 db.commit()

 fd = open("output/"+DST, "w")
 fd.write( "Outgoing: \n")
 for i in Out:
  new_date = str(i['day']) + "/" + str(i['month']) + "/" + str(i['year'])
  fd.write("{0:<15} {1:<15} {2:<25} {3:<15}".format(i['weekday'],new_date,i['time'],i['price']))
  fd.write("\n")
 fd.write("\n")
 fd.write( "___\n")
 fd.write( "Incoming: \n")
 for i in Inc:
  new_date = str(i['day']) + "/" + str(i['month']) + "/" + str(i['year'])
  fd.write("{0:<15} {1:<15} {2:<25} {3:<15}".format(i['weekday'],new_date,i['time'],i['price']))
  fd.write("\n")
 fd.close()

print "Done!"
print datetime.datetime.now()
