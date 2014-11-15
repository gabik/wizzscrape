import requests
import json
import psycopg2
import codecs
from psycopg2 import extras
import re
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii, get_currency, clean_dup_list, db, get_flight_time


# ARGS:
# 1 = DST
# 2 = 0..15
# 3 = debug

debug_flag=False
new_year=0
maxn=31
arg_month=sys.argv[2]
Start_orig = datetime.date.today()
Start_orig += datetime.timedelta(days=(int(maxn)-1)*int(arg_month))
Stop = Start_orig + datetime.timedelta(days=maxn)
scrape_time = datetime.datetime.today()
cleandone=1

DST = sys.argv[1]
if len(sys.argv) >= 4 :
 if sys.argv[3] == "debug" : debug_flag=True

eur=get_currency("eur")

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
 Ret = Start + datetime.timedelta(days=2)
 dict={}
 dict['AllerRetour']='1'
 dict['fournisseur']='AIRMED'
 dict['code_ville_departs']='TLV'
 dict['code_ville_arrivers']=DST
 dict['langue']='FR'
 dict['date_debut']=Start.strftime("%d/%m/%Y")
 dict['date_fin']=Ret.strftime("%d/%m/%Y")
 dict['Adultes']='1'
 dict['Enfants']='0'
 dict['Bebes']='0'
 dict['mineur']='0'
 dict['AllerRetourV2']='1'
 s=requests.session()
 urlT='http://www.air-mediterranee.fr/VOL_TLV_'+DST+'_RT_'+Start.strftime("%d/%m/%Y")+'_'+Ret.strftime("%d/%m/%Y")+'_'+datetime.date.today().strftime("%d/%m/%Y")
 rT=s.post(urlT, data=dict)
 form=rT.text[1312:1798]
 url1=form[form.find('action="')+8:form.find('"',form.find('action="')+8)]
 r1=s.post(url1)
 taskID=r1.text[r1.text.find('tti.taskID = "')+14:r1.text.find('"',r1.text.find('tti.taskID = "')+14)]
 url2='http://ventes.air-mediterranee.fr/TTIDotNet/Transport/TransportNetFO//AirMed/AjaxCommandHttpHandler.ashx?ServiceDescriptor=FlexibleAvailabilityLoadDataCommand&taskId='+taskID
 url2out='{"OutboundDate":3,"ReturnDate":3,"AirTripDirection":0,"SelectedDay":false,"OneWayFares":true}'
 url2inc='{"OutboundDate":3,"ReturnDate":3,"AirTripDirection":1,"SelectedDay":false,"OneWayFares":true}'
 outJsonR=s.post(url2,data=url2out)
 incJsonR=s.post(url2,data=url2inc)
 outJsonT=outJsonR.text.replace("new Date(","").replace("000)","")
 incJsonT=incJsonR.text.replace("new Date(","").replace("000)","")
 outJson=json.loads(outJsonT)
 incJson=json.loads(incJsonT)
 outDict={}
 incDict={}
 if (len(outJson['Proposals']) > 0):
  outDict['direction']=1
  price=[]
  for k in outJson['Proposals'][0]['PricedAirItineraryParts'] :
   price.append(int(k['PricedAirItineraryPartAmountIncludingTaxUpperRounded'].split()[0]))
 
  outDict['price']=int(float(min(price))*eur+0.5)
  dep_date=datetime.datetime.utcfromtimestamp(outJson['Proposals'][0]['AirItineraryPart']['RoutingOptions'][0]['RoutingOption'][0]['RoutingOptionTVLDepartureDateAndHourLT'])
  arr_date=datetime.datetime.utcfromtimestamp(outJson['Proposals'][0]['AirItineraryPart']['RoutingOptions'][0]['RoutingOption'][0]['RoutingOptionTVLArrivalDateAndHourLT'])
  outDict['year']=dep_date.year
  outDict['month']=dep_date.month
  outDict['day']=dep_date.day
  outDict['dep_time']=str(dep_date.hour)+":"+str(dep_date.minute)
  outDict['arr_time']=str(arr_date.hour)+":"+str(arr_date.minute)
 
 if (len(incJson['Proposals']) > 0):
  incDict['direction']=2
  price=[]
  for k in incJson['Proposals'][0]['PricedAirItineraryParts'] :
   price.append(int(k['PricedAirItineraryPartAmountIncludingTaxUpperRounded'].split()[0]))
 
  incDict['price']=int(float(min(price))*eur+0.5)
  dep_date=datetime.datetime.utcfromtimestamp(incJson['Proposals'][0]['AirItineraryPart']['RoutingOptions'][0]['RoutingOption'][0]['RoutingOptionTVLDepartureDateAndHourLT'])
  arr_date=datetime.datetime.utcfromtimestamp(incJson['Proposals'][0]['AirItineraryPart']['RoutingOptions'][0]['RoutingOption'][0]['RoutingOptionTVLArrivalDateAndHourLT'])
  incDict['year']=dep_date.year
  incDict['month']=dep_date.month
  incDict['day']=dep_date.day
  incDict['dep_time']=str(dep_date.hour)+":"+str(dep_date.minute)
  incDict['arr_time']=str(arr_date.hour)+":"+str(arr_date.minute)
 
 flights=[]
 if outDict : flights.append(outDict)
 if incDict : flights.append(incDict)
 
 if debug_flag:
  print Start.strftime("%d/%m/%Y")
  print Ret.strftime("%d/%m/%Y")
  print '-------'
  for i in flights: print i
 flightsList.extend(flights)
 Start=Start + datetime.timedelta(days=1)
print ""
if debug_flag:
 print "Debug: efore clean_dup: Out, Inc: "
 for i in flightsList:
  print i
flightsList2=clean_dup(flightsList)
if debug_flag:
 print "Debug: After clean_dup: Out, Inc: "
 for i in flightsList2:
  print i

#db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")
curs = db.cursor()
curs.execute("select id from companies where name='airmed'")
company_id=curs.fetchone()[0]
for i in flightsList2:
 curs.execute("select * FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),i['dep_time'],str(company_id)))
 if (len(curs.fetchall()) > 0):
  curs.execute("DELETE FROM flights WHERE direction=%s and dst=%s and date=%s and dep_time=%s and company=%s", (i['direction'],DST,str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']),i['dep_time'],str(company_id)))
 curs.execute("INSERT INTO flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))
 curs.execute("INSERT INTO archive_flights (company, scrape_time, direction, dst, price, dep_time, arr_time, date, dur_time) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", (str(company_id), str(scrape_time), i['direction'], DST, int(i['price']), i['dep_time'], i['arr_time'], str(i['year'])+"-"+str(i['month'])+"-"+str(i['day']), get_flight_time(i, DST)))

if cleandone==1:
 curs.execute("delete from flights where company=%s and dst=%s and date>=%s and date<%s and scrape_time<%s", (str(company_id), DST, str(Start_orig.strftime("%Y-%m-%d")), str(Stop.strftime("%Y-%m-%d")), str(scrape_time)))
 print "Done!"

db.commit()

print datetime.datetime.now()
