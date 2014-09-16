import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime

def find_all(a_str, sub):
 start = 0
 while True:
  start = a_str.find(sub, start)
  if start == -1: return
  yield start
  start += len(sub) # use start += 1 to find overlapping matches

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

