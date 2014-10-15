import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import strip_non_ascii , get_currency

eur=get_currency("eur")

class getFlight(HTMLParser):
 def __init__(self, start,ret):
  self.price=0
  self.prices=[]
  self.data = []
  self._vals = {}
  self.dep_date = ""
  self.arr_date = ""
  self.direction = 0
  self.tmp_direction=""
  self.tmp_price=""
  self.row=0
  self.arr_time=0
  self.dep_time=0 
  self.dur=0
  self.connection=0
  self.start=start
  self.ret=ret
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="tr":
    for a,b in attrs:
     if a=="class" and "rowinfo1" in b.split() : self.row=1
     if a=="class" and "rowinfo2" in b.split() : self.row=2
     if a=="class" and "lastrow" in b.split() : self.row=3
     if a=="class" and "lastrow" in b.split() and "rowinfo2" in b.split(): self.row=4
  if self.row==1 and tag=="td":
    for a,b in attrs:
     if a=="class" and "depdest" in b.split() : self.dep_time=1
     if a=="class" and "arrdest" in b.split() : self.arr_time=1
     if a=="class" and "fareselect" in b.split() : self.price=1
     if a=="class" and "duration" in b.split() : self.connection=1
  if (self.row==2 or self.row==4) and tag=="td":
    for a,b in attrs:
     if a=="class" and "depdest" in b.split() : self.direction=1
     if a=="class" and "duration" in b.split() : self.dur=1
 def handle_data(self, data):
  if self.dep_time==1 : self._vals['dep_time'] = data
  if self.arr_time==1 : self._vals['arr_time'] = data
  if self.dur==1 : self._vals['duretion'] = data.split(":")[1].replace("h ",":").replace("m","").replace(" ","")
  if self.direction==1 : 
   self._vals['direction'] = 1 if "Tel" in data else 2
   self.direction=0
  if self.price==1 : self.prices.append(float(data))
  if self.connection==1: self.connection=data
 def handle_endtag(self, tag):
  if tag == "td" and self.row>0:
   self.arr_time=0
   self.dep_time=0
   self.dur=0
   self.price=0
   self.direction = 0
  if tag == "tr" and self.row>0: 
   if self.row>=3:
    if self.prices: self._vals['price'] = int(min(self.prices)*eur)
    self.prices=[]
    if self._vals: 
     self._vals['date'] = self.start if self._vals['direction']==1 else self.ret
     self.data.append(self._vals)
    self._vals = {}
    self.direction = 0
    self.price=0
    self.arr_time=0
    self.dep_time=0
    self.dur=0
   self.row=0
   
