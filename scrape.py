import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime

debug_flag=False
new_year=0
maxn=30#500
#Start_orig = datetime.date.today()
Start_orig = datetime.date(2015,5,1)
Stop = Start_orig + datetime.timedelta(days=maxn)
cur_year=Start_orig.year



DDD = sys.argv[1]
if len(sys.argv) >= 3 :
 if sys.argv[2] == "debug" : debug_flag=True
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

viewstate=""

class getViewState(HTMLParser):
 _viewstate = ""
 def __init__(self):
  self._vals={}
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="input":
   for i in attrs:
    if i[0]=="id":
     self._vals['id'] = i[1]
    if i[0]=="type":
     self._vals['type'] = i[1]
    if i[0]=="value":
     self._vals['value'] = i[1]
   if (self._vals['type'] == "hidden" and self._vals['id'] == "viewState") : 
    if self._vals['value'] != "":
     self._viewstate=self._vals['value']

class getFlight(HTMLParser):
 def __init__(self, year, new_year):
  self.price=0
  self.date=0
  self.time = 0
  self.data = []
  self._vals = {}
  self.tmp_date = ""
  self.header = 0
  self.deep = 0
  self.direction = 0
  self.year=year
  self.new_year=new_year
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag == "h3":
   self.header+=1
  if tag == "h2":
   self.direction+=1
  if self.header > 0 : return
  if tag == "label" : self.deep+=1
  if tag != "br":
   self.date=0
   self.time = 0
  if tag=="span":
   for a,b in attrs:
    if a=="class" and b=="flight-fare-wizzclub sub": self.price=1
    if a=="class" and b=="price" and self.price == 1: self.price=2
    if a=="class" and b=="flight-date": self.date+=1
    if a=="class" and b=="flight-time": self.time+=1
 def handle_data(self, data):
  if self.price == 2: 
   tmpprice = strip_non_ascii(data)
   tmpprice = tmpprice[0:tmpprice.find('.')]
   self._vals['price'] = tmpprice
   self.price=0
  if self.date == 1: 
   self.tmp_date += data + " "
  if self.time == 1: 
   self._vals['time'] = strip_non_ascii(data)
 def handle_endtag(self, tag):
  if tag == "h3" : self.header=0
  if tag == "span":
   if self.date == 1:
    tmp_year=self.year
    if self.new_year == 0:
     if "Jan" in self.tmp_date:
      tmp_year=self.year+1
     elif "Feb" in self.tmp_date:
      self.new_year=1
      tmp_year=self.year+1
     self._vals['year'] = str(tmp_year)
    else:
     self._vals['year'] = str(tmp_year+1)
    tmp_full_date = strip_non_ascii(self.tmp_date).split() 
    self._vals['weekday'] = tmp_full_date[0]
    self._vals['day'] = tmp_full_date[1]
    self._vals['month'] = datetime.datetime.strptime(tmp_full_date[2], "%b").strftime("%m") 
  if tag == "label":
   if self._vals:
    self._vals['direction'] = self.direction
    self.data.append(self._vals)
    self.deep=0
    self._vals={}
    self.tmp_date = ""

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
 while Stop > Start:
  n+=1
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
   print prP.data
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
