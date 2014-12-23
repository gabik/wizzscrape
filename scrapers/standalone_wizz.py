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
from general_scrape import find_all, clean_dup, strip_non_ascii, db, max_retries, get_flight_time, write_to_gabi
debug_flag=True
maxn=31
Start_orig = datetime.date.today()
cur_year=Start_orig.year
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()
DST = "OTP"
s = requests.Session()
r1 = s.get('http://wizzair.com/en-GB/Search')
vsP = getViewState()
vsP.feed(r1.text)
viewstate=vsP._viewstate
new_token=vsP._newtoken
Start = Start_orig
flightsList = []
headers={}
headers['Origin']='https://wizzair.com'
headers['Referer']='https://wizzair.com/en-GB/Search'
headers['User-Agent']='Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36'
ddict={}
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$OriginStation']="TLV"
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DestinationStation']=DST
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DepartureDate']=Start.strftime("%d/%m/%Y")
Ret = Start + datetime.timedelta(days=1)
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$ReturnDate']=Ret.strftime("%d/%m/%Y")
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountCHD'] = 0
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountINFANT'] = 0
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountADT'] = 1
ddict['ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$ButtonSubmit'] = "Search"
ddict['__EVENTTARGET'] = "ControlGroupRibbonAnonHomeView_AvailabilitySearchInputRibbonAnonHomeView_ButtonSubmit"
ddict['__VIEWSTATE'] = viewstate
ddict[new_token[0]] = new_token[1]
r2 = s.post('https://wizzair.com/en-GB/Search', data=ddict, headers=headers)
#r3 = s.get('https://wizzair.com/en-GB/Select', headers=headers)
#r4 = s.get('https://wizzair.com/en-GB/Select', headers=headers)
write_to_gabi(r2.text)

cur_date=Start.strftime("%d-%m-%Y")
prP = getFlight(cur_date)
prP.feed(r2.text)
flightsList.extend(prP.data)
flightsList=clean_dup(flightsList)
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
