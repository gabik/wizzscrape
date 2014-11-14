import re
import datetime, pytz
import requests
import psycopg2
from psycopg2 import extras

db= psycopg2.connect( host="gabiscrape.c8f6qy9d6xm4.us-west-2.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")

max_retries=3

def find_all(a_str, sub):
 start = 0
 while True:
  start = a_str.find(sub, start)
  if start == -1: return
  yield start
  start += len(sub) # use start += 1 to find overlapping matches

def clean_dup_list(seq):
 noDupes = []
 [noDupes.append(i) for i in seq if not noDupes.count(i)]
 return noDupes

def clean_dup(lst):
 lst.sort()
 newlst=[]
 last_elem={}
 for i in lst:
   if i==last_elem:
    continue
   else:
    if "price" in i:
     newlst.append(i)
    last_elem=i
 return newlst
  

def strip_non_ascii(string):
 stripped = (c for c in string if 0 < ord(c) < 127)
 newstr=''.join(stripped)
 return re.sub(',', '', newstr)

def get_currency(cur):
 #r=requests.get('http://fx-rate.net/'+str(cur)+'/ils')
 #return float(r.text[r.text.find('<div style="font-size:25px;color:black;margin-top:5px">'):r.text.find('<div style="font-size:25px;color:black;margin-top:5px">')+80].split('>')[1].split()[0])
 #return float(r.text[r.text.find('Today = ')+8:r.text.find('Today = ')+13].split('<')[0])
 return float(open("../currencies/"+str(cur),"r").read())

def tz_to_utc(airport, tzdate):
 curstz = db.cursor()
 curstz.execute("select tz FROM timezones WHERE airport=%s", (airport,))
 tz=curstz.fetchall()[0][0]
 ptz = pytz.timezone(tz)
 ptz1 = ptz.localize(tzdate, is_dst=None)
 utc_tz = ptz1.astimezone(pytz.utc)
 return utc_tz

def check_if_tz_is_const(airport, start, end):
 tz1=tz_to_utc(airport, start)
 tz2=tz_to_utc(airport, end)
 if tz1==tz2 : return  True
 return False

def get_flight_time(flight, airport): 
 date=str(flight['year'])+"-"+str(flight['month'])+"-"+str(flight['day'])
 depstr=str(date) + " " + str(flight['dep_time'])
 arrstr=str(date) + " " + str(flight['arr_time'])
 depapt='TLV' if flight['direction'] == 1 else airport
 arrapt=airport if flight['direction'] == 1 else 'TLV'
 deputc=tz_to_utc(depapt, datetime.datetime.strptime(depstr, '%Y-%m-%d %H:%M'))
 arrutc=tz_to_utc(arrapt, datetime.datetime.strptime(arrstr, '%Y-%m-%d %H:%M'))
 dur=arrutc-deputc
 dur+=datetime.timedelta(days=1) if dur.days<0 else 0
 return ':'.join(str(dur).split(':')[0:2])

#import codecs
#fd=codecs.open('../gabi.html', 'w', encoding='utf-8')
#fd.write(r1.text)
