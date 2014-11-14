import requests
import psycopg2
import codecs
from psycopg2 import extras
import re
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii, get_currency, clean_dup_list, db, get_flight_time
import flyup_scrape_import as up

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
new_year=0
maxn=31
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
#Start_orig = datetime.date(2014,11,7)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()

DDD = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True
Dests = []
Dests.append(DDD)

usd=get_currency("usd")

for DST in Dests:
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
  Ret = Start + datetime.timedelta(days=3)
  dict={}
  dict={}
  dict['origin']="TLV"
  dict['destination']=DST
  dict['origin1']="TLV"
  dict['destination1']=DST
  dict['adults']="1"
  dict['infants']="0"
  dict['journeyType']="1"
  dict['departDay']=Start.strftime("%d")
  dict['departMonth']=int(Start.strftime("%m"))-1
  dict['returnDay']=Ret.strftime("%d")
  dict['returnMonth']=int(Ret.strftime("%m"))-1
  dict['returnYear']=Ret.strftime("%Y")
  dict['departureYear']=Start.strftime("%Y")
  dict['lang']="IL"
  dict['returnFrom']=DST
  dict['returnTo']="TLV"
  dict['cabin']="Economy"
  dict['coupons']=""
  dict['SYSTEMID']="7"
  s=requests.session()
  url2='http://booking.elal.co.il/newBooking/urlDirector.do?systemId=7'
  r2=s.post(url2,data=dict)
  posts=up.getPostVals()
  posts.feed(r2.text)
  dict={}
  for i in posts.inputs:
   if 'id' in i:
    dict[i['id']]=i['val']
 
  url3='http://fly2u.sundor.co.il/plnext/SUNDORonlinebooking/Override.action'
  r3=s.post(url3,data=dict)
  if (r3.text.find("generatedJSon") < 0) : 
   Start=Start + datetime.timedelta(days=1)
   continue
  x=r3.text[r3.text.find("generatedJSon"):r3.text.find("\n", r3.text.find("generatedJSon"))]
  y=x[28:-4]
  w=eval(y.replace('false', 'False').replace('true','True'))
  if 'recommendations' in w:
   d=w['recommendations']
  else:
   print "New List again..." + str(Start)
   Start=Start + datetime.timedelta(days=1)
   continue
  d2 = [[x['keyDate'][0:8], x['keyDate'][8:], x['list_price'][0]['formatted_price'], x['list_price'][1]['formatted_price']] for x in d]
  #d3 = set((x[i],x[i+2],i+1) for i in range(2) for x in d2)
  d3 = [[y+1, x[y], x[y+2]] for x in d2 for y in range(2)]
  d3=clean_dup_list(d3)
  d3.sort(key=lambda x: x[1])
  d4=[]
  cur_date=""
  min_price=0.0
  max_price=0.0
  for dirc in range(1,3):
   for i in d3:
    if i[0]==dirc:
     if i[1]==cur_date:
      if (float(i[2].split()[0].replace(',','')) > max_price) : max_price=float(i[2].split()[0].replace(',',''))
      if (float(i[2].split()[0].replace(',','')) < min_price) : min_price=float(i[2].split()[0].replace(',',''))
     else:
      if cur_date:
       tmpd4={}
       tmpd4['direction']=dirc
       tmpd4['price']=int(min_price*usd+0.5)
       tmpd4['maxprice']=int(max_price*usd+0.5)
       tmpd4['year']=cur_date[0:4]
       tmpd4['month']=cur_date[4:6]
       tmpd4['day']=cur_date[6:]
       d4.append(tmpd4)
      cur_date=i[1]
      min_price=max_price=float(i[2].split()[0].replace(',',''))

  if debug_flag:
   print Start.strftime("%d/%m/%Y")
   print Ret.strftime("%d/%m/%Y")
   for i in d4:
    print i
   print '-------'
  flightsList.extend(d4)
  Start=Start + datetime.timedelta(days=1)
 print ""
 if debug_flag:
  print "Debug: efore clean_dup: Out, Inc: "
  print flightsList
 fl2=up.clean_dup_max(flightsList, 1)
 fl3=up.clean_dup_max(flightsList, 2)
 fl2.extend(fl3)
 if debug_flag:
  print "Debug: After clean_dup: Out, Inc: "
  for i in fl2:
   print i

 #db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
 curs = db.cursor()
 curs.execute("select id from companies where name='sundor'")
 company_id=curs.fetchone()[0]
 for i in fl2:
  curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),'00:00',str(company_id)))
  if (len(curs.fetchall()) > 0):
   curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),'00:00',str(company_id)))
  curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), '00:00', '00:00', str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))
  curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), '00:00', '00:00', str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))

 db.commit()

print "Done!"
print datetime.datetime.now()
