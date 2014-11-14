import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import strip_non_ascii , get_currency

eur=get_currency("eur")

class getFlight(HTMLParser):
 def __init__(self):
  self.price=0
  self.data = []
  self._vals = {}
  self.dep_date = ""
  self.arr_date = ""
  self.header = 0
  self.direction = 0
  self.tmp_direction=""
  self.tmp_price=""
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="tr":
   for a,b in attrs:
    if a=="basicpriceroute" : self.header=1
   if self.header==1:
    for a,b in attrs:
     if a=="departuretime" : self.dep_date = b
     if a=="arrivaltime"   : self.arr_date = b
  if tag=="td" and self.header==1:
   for a,b in attrs:
    if a=="class" and b=="routeCell": self.direction=1
    if a=="class" and b.split(' ')[0]=="price": self.price=1
 def handle_data(self, data):
  if self.price == 1: 
   self.tmp_price+=data+" "
  if self.direction == 1:
   self.tmp_direction+=data+" "
 def handle_endtag(self, tag):
  if tag == "td" and self.header==1: 
   self.direction=0
   self.price=0
  if tag == "tr" and self.header==1: 
   self._vals['date'] = self.dep_date.split('T')[0]
   self._vals['year'] = self._vals['date'].split('-')[0]
   self._vals['month'] = self._vals['date'].split('-')[1]
   self._vals['day'] = self._vals['date'].split('-')[2]
   self._vals['dep_time'] = ":".join(self.dep_date.split('T')[1].split(":")[0:2])
   self._vals['arr_time'] = ":".join(self.arr_date.split('T')[1].split(":")[0:2])
   self._vals['price'] = int((min([int(strip_non_ascii(p.split()[0])[0:-2])+1 for p in [" ".join(y.split()) for y in [x for x in self.tmp_price.split('\r\n')[1:]]]])*eur)+0.5)
   if "TLV" in [strip_non_ascii(x.replace(' ','')) for x in self.tmp_direction.split('\r\n')[1:5]][0] : self._vals['direction']=1
   if "TLV" in [strip_non_ascii(x.replace(' ','')) for x in self.tmp_direction.split('\r\n')[1:5]][2] : self._vals['direction']=2
   self.header=0
   self.data.append(self._vals)
   self._vals = {}
   self.tmp_price=""
   self.tmp_direction=""
   self.direction = 0
   self.price=0
   self.dep_date = ""
   self.arr_date = ""


