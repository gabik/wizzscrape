import requests
import traceback
import socket
import psycopg2
import codecs
from psycopg2 import extras
import re
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii, get_currency, clean_dup_list, db, max_retries, get_flight_time, write_to_gabi, dummy, replace_proxy
import up_scrape_import as up
import randomizer as rz

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

cleandone=1
debug_flag=False
new_year=0
maxn=31
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
cur_year=Start_orig.year
#Start_orig = datetime.date(2015,8,1)
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()

def get_proxy():
 s=requests.session()
 test_url='http://fly.elal.co.il/plnext/ELALonlinebooking/Override.action'
 test2_url='http://booking.elal.co.il/newBooking/urlDirector.do'
 good=False
 while good is False:
  cur_proxy = replace_proxy()
  print "Need Proxy... {0}".format(cur_proxy)
  try:
   test=s.get(test_url)
  except:
   test = dummy()
  #if 'Access Denied' not in test.text and test.status_code == 200 and 'Manual Runner' not in test.text:
  if 'Access Denied' not in test.text and 'Manual Runner' not in test.text:
   good = True

s=requests.session()
test_url='http://fly.elal.co.il/plnext/ELALonlinebooking/Override.action'
test2_url='http://booking.elal.co.il/newBooking/urlDirector.do'
test=s.get(test_url)
test2=s.get(test2_url)
if 'Access Denied' in test.text or  'Manual Runner' in test.text:
 get_proxy()


DST = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True

usd=get_currency("usd")

Start = Start_orig
flightsList = []
n=0
print DST
print str(scrape_time)
print str(Start_orig), str(arg_month)
retries=0
rz.init_randomizer(Start_orig, maxn)
#while Stop > Start:
Start = rz.get_date_from_list()
headers={}
headers['User-Agent']='Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36'
while not rz.is_empty():
 if Start > datetime.date.today()+datetime.timedelta(days=362) : break 
 if retries == 0:
  n+=1
 if debug_flag:
  print "Progress: " + str(n) + "/" + str(maxn)
 else:
  sys.stdout.write(" Progress: %d/%d   \r" % (n,maxn) )
  sys.stdout.flush()
 Ret = Start + datetime.timedelta(days=2)
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
 dict['systemId']="24"
 urlT='http://booking.elal.co.il/newBooking/JavaScriptServlet'
 url1='http://booking.elal.co.il/newBooking/elalInit.do?LANG=IL&systemId=24&campaignCode=04535'
 s=requests.session()
 x=""
 r2=dummy()
 r3=dummy()
 try:
  rT=s.get(urlT)
  token=rT.text[rT.text.find("OWASP_CSRFTOKEN")+19:rT.text.find("OWASP_CSRFTOKEN")+58]
  r1=s.get(url1, headers=headers)
  url2='http://booking.elal.co.il/newBooking/urlDirector.do?OWASP_CSRFTOKEN='+token
  r2=s.post(url2,data=dict, headers=headers)
  posts=up.getPostVals()
  posts.feed(r2.text)
  dict={}
  for i in posts.inputs:
   if 'id' in i:
    dict[i['id']]=i['val']
  
  url3='http://fly.elal.co.il/plnext/ELALspof/Override.action'
  r3=s.post(url3,data=dict, headers=headers)
  x=r3.text[r3.text.find("generatedJSon"):r3.text.find("\n", r3.text.find("generatedJSon"))]
  y=x[28:-4]
  w=eval(y.replace('false', 'False').replace('true','True'))
 except Exception ,e :
  retries+=1
  if retries>1:
   proxy = get_proxy()
  if retries>max_retries:
   print str(Start), str(Ret)
   traceback.print_exc()
   print "exception: " + str(e)
   print "Json: " + x
   #print "page: " + r3.text
   write_to_gabi(r3.text)
   cleandone=0
   Start = rz.get_date_from_list()
   retries=0
   # Start=Start + datetime.timedelta(days=1)
  continue


 retries=0
 d=w['recommendations']
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
     if (float(i[2].split()[0]) > max_price) : max_price=float(i[2].split()[0])
     if (float(i[2].split()[0]) < min_price) : min_price=float(i[2].split()[0])
    else:
     if cur_date:
      tmpd4={}
      tmpd4['direction']=dirc
      tmpd4['price']=int(float(min_price)*float(usd)+0.5)
      tmpd4['maxprice']=int(float(max_price)*float(usd)+0.5)
      tmpd4['year']=cur_date[0:4]
      tmpd4['month']=cur_date[4:6]
      tmpd4['day']=cur_date[6:]
      tmpd4['dep_time']='00:00'
      tmpd4['arr_time']='00:00'
      d4.append(tmpd4)
     cur_date=i[1]
     min_price=max_price=float(i[2].split()[0])

 if debug_flag:
  print Start.strftime("%d/%m/%Y")
  print Ret.strftime("%d/%m/%Y")
  for i in d4: print i
  print '-------'
 flightsList.extend(d4)
 Start = rz.get_date_from_list()
 #Start=Start + datetime.timedelta(days=1)
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
curs.execute("select id from companies where name='up'")
company_id=curs.fetchone()[0]
for i in fl2:
 curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),str(i['dep_time']), str(company_id)))
 if (len(curs.fetchall()) > 0):
  curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),str(i['dep_time']), str(company_id)))
 curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), str(i['dep_time']),  str(i['arr_time']),  str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))
 curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), str(i['dep_time']),  str(i['arr_time']),  str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))

if cleandone==1:
 curs.execute("delete from flights where company=%s and dst=%s and date>=%s and date<%s and scrape_time<%s", (str(company_id), DST, str(Start_orig.strftime("%Y-%m-%d")), str(Stop.strftime("%Y-%m-%d")), str(scrape_time)))
 print "Done!"

db.commit()
 
print datetime.datetime.now()
