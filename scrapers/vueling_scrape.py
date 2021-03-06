import requests
import psycopg2
from psycopg2 import extras
import re
from HTMLParser import HTMLParser
import sys
import datetime
import time
from vueling_scrape_import import getFlight
from general_scrape import find_all, clean_dup, strip_non_ascii, db, max_retries, get_flight_time, write_to_gabi

# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

def getblankdays(m, cal):
 for i in cal:
  if i['Month']==m:
   return i['BlankDays']

cleandone=1
debug_flag=False
maxn=31
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
if arg_month==0: Start_orig = Start_orig + datetime.timedelta(days=1)
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()


DST = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True

s=requests.session()
r1 = s.get('http://www.vueling.com/en');
vsi=r1.text.find('"', r1.text.find('value="', r1.text.find("viewState")))+1
vse=r1.text.find('"', vsi+1)
viewstate=r1.text[vsi:vse]
Start = Start_orig
flightsList = []
n=0


depurl='http://public.vueling.com/Vueling.Cache.WCF.REST.WebService/BlankDaysService.svc/Get?callback=SKYSALES_Util_checkRoutesAndPromoUniversalDepartureCallback&departure=TLV&arrival='+DST+'&year='+str(Start_orig.year)+'&month='+str(Start_orig.month)+'&monthsRange=3'
arrurl='http://public.vueling.com/Vueling.Cache.WCF.REST.WebService/BlankDaysService.svc/Get?callback=SKYSALES_Util_checkRoutesAndPromoUniversalDepartureCallback&departure='+DST+'&arrival=TLV&year='+str(Start_orig.year)+'&month='+str(Start_orig.month)+'&monthsRange=3'
rdepcal=s.get(depurl)
rarrcal=s.get(arrurl)
depcal = {}
arrcal = {}
if rdepcal.status_code != 500:
 depcal=eval(rdepcal.text[rdepcal.text.find('(')+1:rdepcal.text.find(')')].replace('null','""'))['Calendar']

if rarrcal.status_code != 500:
 arrcal=eval(rarrcal.text[rarrcal.text.find('(')+1:rarrcal.text.find(')')].replace('null','""'))['Calendar']

headers={}
headers['Origin']='Origin:http://www.vueling.com'
headers['Referer']='Origin:http://www.vueling.com/en'
headers['User-Agent']='Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36'

print DST
print str(scrape_time)
print str(Start_orig), str(arg_month)

retries=0
n+=1
while Stop > Start:

 dep_cur_m=getblankdays(Start.month, depcal)
 if debug_flag:  print "dep cal: {0}".format(str(dep_cur_m))
 arr_cur_m=getblankdays(Start.month, arrcal)
 if dep_cur_m:
  if dep_cur_m == [y for y in range(dep_cur_m[0],dep_cur_m[-1]+1)] : break
  while Start.day in dep_cur_m:
   Start= Start + datetime.timedelta(days=1)
   if Start > Stop : continue
   dep_cur_m=getblankdays(Start.month, depcal)
   n+=1

 Ret = Start + datetime.timedelta(days=1)
 if dep_cur_m:
  while Ret.day in arr_cur_m:
   Ret = Ret + datetime.timedelta(days=1)
   arr_cur_m=getblankdays(Start.month, arrcal)

 if debug_flag:
  print "Progress: " + str(n) + "/" + str(maxn)
 else:
  sys.stdout.write(" Progress: %d/%d   \r" % (n,maxn) )
  sys.stdout.flush()

 dict={}
 dict['AvailabilitySearchInputXmlSearchView$RadioButtonMarketStructure']="RoundTrip"
 dict['Culture']='en-GB'
 dict['AvailabilitySearchInputXmlSearchView$DropDownListMarketDay1']=Start.day
 dict['AvailabilitySearchInputXmlSearchView$DropDownListMarketMonth1']=str(Start.year)+'-'+str(Start.month)
 dict['AvailabilitySearchInputXmlSearchView$DropDownListMarketDay2']=Ret.day
 dict['AvailabilitySearchInputXmlSearchView$DropDownListMarketMonth2']=str(Ret.year)+'-'+str(Ret.month)
 dict['AvailabilitySearchInputXmlSearchView$DropDownListPassengerType_ADT']='1'
 dict['AvailabilitySearchInputXmlSearchView$DropDownListPassengerType_CHD']='0'
 dict['AvailabilitySearchInputXmlSearchView$DropDownListPassengerType_INFANT']='0'
 dict['departureStationCode1']='TLV'
 dict['arrivalStationCode1']=DST
 dict['AvailabilitySearchInputXmlSearchView$DropDownListSearchBy']='columnView'
 dict['__EVENTTARGET'] = "AvailabilitySearchInputXmlSearchView$LinkButtonNewSearch"
 dict['__EVENTARGUMENT'] = ''
 dict['AvailabilitySearchInputXmlSearchView$TextBoxMarketOrigin1'] = 'Tel Aviv'
 dict['AvailabilitySearchInputXmlSearchView$TextBoxMarketDestination1'] = 'Barcelona'
 dict['date_picker'] = '2014-11-11'
 dict['date_picker'] = '2014-11-15'
 dict['AvailabilitySearchInputXmlSearchView$ResidentFamNumSelector'] = ''
 dict['ErroneousWordOrigin1']=''
 dict['SelectedSuggestionOrigin1']=''
 dict['ErroneousWordDestination1']=''
 dict['SelectedSuggestionDestination1']=''
 dict['departureStationCode2']=''
 dict['arrivalStationCode2']=''
 dict['ErroneousWordOrigin2']=''
 dict['SelectedSuggestionOrigin2']=''
 dict['ErroneousWordDestination2']=''
 dict['SelectedSuggestionDestination2']=''
 dict['__VIEWSTATE'] = viewstate
 dict['pageToken'] = ""

 r2 = s.post('http://tickets.vueling.com/XmlSearch.aspx', data=dict, headers=headers)
 cleanr2 = ""
 if r2.status_code == 200:
  if list(find_all(r2.text, "basicPriceRoute")):
   cleanr2=r2.text[sorted(list(find_all(r2.text, "basicPriceRoute")))[0]-5:r2.text.find('SKYSALES.Util.createObject', sorted(list(find_all(r2.text, "basicPriceRoute")))[0])]
 else:
  retries+=1
  if retries>max_retries:
   print "Start:{0}, Ret:{1}. ".format(str(Start), str(Ret))
   cleandone=0
   Start=Start + datetime.timedelta(days=1)
   n+=1
  continue
 write_to_gabi(cleanr2)


 #try:
  #cleanr2=r2.text[sorted(list(find_all(r2.text, "basicPriceRoute")))[0]-5:r2.text.find('</tbody>', sorted(list(find_all(r2.text, "basicPriceRoute")))[-1])]
 #except IndexError :
  #retries+=1
  #if retries>max_retries:
   #print "Start:{0}, Ret:{1}. ".format(str(Start), str(Ret))
   #if debug_flag:
    #print "basicPriceRoute on HTML: {0}".format(list(find_all(r2.text, "basicPriceRoute")))
   #cleandone=0
   #Start=Start + datetime.timedelta(days=1)
   #n+=1
  #continue

 retries=0
 prP = getFlight()
 prP.feed(cleanr2)

 if debug_flag:
  print Start.strftime("%d/%m/%Y")
  print Ret.strftime("%d/%m/%Y")
  print len(prP.data)
  for i in prP.data : print i
  print '-------'

 flightsList.extend(prP.data)
 Start=Start + datetime.timedelta(days=1)
 n+=1

print ""

flightsList=clean_dup(flightsList)
if debug_flag:
 print "Debug: After clean_dup: "
 for i in flightsList: print i

#db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
curs = db.cursor()
curs.execute("select id from companies where name='vueling'")
company_id=curs.fetchone()[0]

for i in flightsList:
 depp1='23:59' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 23) else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")+datetime.timedelta(minutes=60), "%H:%M")
 depm1='00:00' if (int(i['dep_time'][0:i['dep_time'].find(':')]) == 0)  else datetime.datetime.strftime(datetime.datetime.strptime(i['dep_time'], "%H:%M")-datetime.timedelta(minutes=60), "%H:%M")
 curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s", (i['direction'],DST,str(i['date']),depm1,depp1,str(company_id)))
 if (len(curs.fetchall()) > 0):
  curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time>%s and dep_time<%s and company=%s",  (i['direction'],DST,str(i['date']),depm1,depp1,str(company_id)))
 curs.execute("INSERT INTO flights         (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['date']), get_flight_time(i, DST)))
 curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id),str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'],str(i['date']), get_flight_time(i, DST)))

if cleandone==1:
 curs.execute("delete from flights where company=%s and dst=%s and date>=%s and date<%s and scrape_time<%s", (str(company_id), DST, str(Start_orig.strftime("%Y-%m-%d")), str(Stop.strftime("%Y-%m-%d")), str(scrape_time)))
 print "Done!"

db.commit()

print datetime.datetime.now()
