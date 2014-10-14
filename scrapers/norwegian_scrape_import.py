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
  self.row1=0
  self.row2=0
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="table":
   for a,b in attrs:
    if a=="class" and b=="avadaytable" : self.header=1
  if self.header==1 and tag=="tr":
    for a,b in attrs:
     if a=="class" and "rowinfo1" in b.split() : self.row1=1
     if a=="class" and "rowinfo2" in b.split() : self.row1=1
 def handle_data(self, data):
  if self.row1 == 1 : print "1: "+data
  if self.row1 == 2 : print "1: "+data
 def handle_endtag(self, tag):
  if tag == "tr" and self.header==1: 
   self.row1=0
   self.row2=0
  if tag == "table" and self.header==1: 
   #self._vals['date'] = self.dep_date.split('T')[0]
   #self._vals['dep_time'] = ":".join(self.dep_date.split('T')[1].split(":")[0:2])
   #self._vals['arr_time'] = ":".join(self.arr_date.split('T')[1].split(":")[0:2])
   #self._vals['price'] = int((min([int(strip_non_ascii(p.split()[0])[0:-2])+1 for p in [" ".join(y.split()) for y in [x for x in self.tmp_price.split('\r\n')[1:]]]])*eur)+0.5)
   #if "TLV" in [strip_non_ascii(x.replace(' ','')) for x in self.tmp_direction.split('\r\n')[1:5]][0] : self._vals['direction']=1
   #if "TLV" in [strip_non_ascii(x.replace(' ','')) for x in self.tmp_direction.split('\r\n')[1:5]][2] : self._vals['direction']=2
   self.header=0
   #self.data.append(self._vals)
   self._vals = {}
   self.tmp_price=""
   self.tmp_direction=""
   self.direction = 0
   self.price=0
   self.dep_date = ""
   self.arr_date = ""


