import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii

viewstate=""

class getViewState(HTMLParser):
 _viewstate = ""
 _newtoken  = []
 def __init__(self):
  self._vals={}
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  self._vals={}
  if tag=="input":
   for i in attrs:
    if i[0]=="id":
     self._vals['id'] = i[1]
    if i[0]=="type":
     self._vals['type'] = i[1]
    if i[0]=="value":
     self._vals['value'] = i[1]
    if i[0]=="name":
     self._vals['name'] = i[1]
   if ('id' in self._vals ):
    if (self._vals['type'] == "hidden" and self._vals['id'] == "viewState") : 
     if self._vals['value'] != "":
      self._viewstate=self._vals['value']
   else:
    if (self._vals['type'] == "hidden" ):
     if self._vals['value'] != "" and self._vals['name'] != "":
      if re.search(r'.{8}-.{4}-.{4}-.{4}-.{12}$', self._vals['value']):
       self._newtoken=[self._vals['name'], self._vals['value']]

class getFlight(HTMLParser):
 def __init__(self, req_date):
  self.price=0
  self.date=0
  self.time = 0
  self.data = []
  self._vals = {}
  self.tmp_date = ""
  self.header = 0
  self.deep = 0
  self.direction = 0
  self.directionval = 0
  self.req_date=req_date
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag == "h3":
   self.header+=1
  if tag == "h2":
   self.direction=1
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
   self._vals['dep_time'] = strip_non_ascii(data).split()[0]
   self._vals['arr_time'] = strip_non_ascii(data).split()[1]
  if self.direction == 1:
   if data[0:8] == 'Tel-Aviv' : self.directionval=1;
   if data[-8:] == 'Tel-Aviv' : self.directionval=2;
 def handle_endtag(self, tag):
  if tag == "h3" : self.header=0
  if tag == "h2" : self.direction=0
  if tag == "span":
   if self.date == 1:
    tmp_year=int(self.req_date.split("-")[2])
    tmp_mon=int(self.req_date.split("-")[1])
    if (tmp_mon==1) and ("Dec" in self.tmp_date): tmp_year-=1
    if (tmp_mon==12) and ("Jan" in self.tmp_date): tmp_year+=1
    self._vals['year'] = str(tmp_year)
    tmp_full_date = strip_non_ascii(self.tmp_date).split() 
    self._vals['weekday'] = tmp_full_date[0]
    self._vals['day'] = tmp_full_date[1]
    self._vals['month'] = datetime.datetime.strptime(tmp_full_date[2], "%b").strftime("%m") 
  if tag == "label":
   if self._vals:
    self._vals['direction'] = self.directionval
    self.data.append(self._vals)
    self.deep=0
    self._vals={}
    self.tmp_date = ""

