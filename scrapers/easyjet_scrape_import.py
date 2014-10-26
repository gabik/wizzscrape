import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii, get_currency

eur=get_currency("eur")

class getFlight(HTMLParser):
 def __init__(self, req_date):
  self.tmp_date=""
  self.tmp_price=""
  self.tmp_data=""
  self.tmp_time=""
  self.day=0
  self.endday=0
  self.price=0
  self.date=0
  self.time = 0
  self.data = []
  self._vals = {}
  self.header = 0
  self.direction = 0
  self.req_date=req_date
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="div":
   for a,b in attrs:
    if a=="class" and b=="OutboundDaySlider": self.direction=1
    if a=="class" and b=="ReturnDaySlider": self.direction=2
    if a=="class" and b=="day": 
     self.day=1
     self.tmp_date=""
    if a=="class" and b=="day nextDay": self.day=0
  if tag=="span":
   for a,b in attrs:
    if a=="class" and b.split()[0]=="dayDate" : self.date=1
    if a=="class" and b[0:5]=="price" : self.price=1
    if a=="aria-hidden" and b=="true" : self.time=1
 def handle_data(self, data):
  cur_data=data.strip()
  if cur_data!="" and self.day==1 : 
   if self.price==1:
    self.tmp_price += cur_data
   if self.date==1:
    self.tmp_date += cur_data
   if self.time==1:
    self.tmp_time += cur_data + " "
 def handle_endtag(self, tag):
  if tag=="span":
   self.price=0
   self.date=0
   self.time=0
  if tag=="li" and self.day==1:
   if self.tmp_price!="":
    self._vals['weekday']=self.tmp_date.split()[0]
    self._vals['day']=self.tmp_date.split()[1]
    self._vals['month']=datetime.datetime.strptime(self.tmp_date.split()[2], "%b").strftime("%m")
    self._vals['price']=int(float(strip_non_ascii(self.tmp_price))*float(eur)+0.5)
    self._vals['priceE']=strip_non_ascii(self.tmp_price)
    self._vals['direction']=self.direction
    self._vals['dep_time']=self.tmp_time.split()[1]
    self._vals['arr_time']=self.tmp_time.split()[3]
    tmp_year=int(self.req_date.split("-")[2])
    tmp_mon=int(self.req_date.split("-")[1])
    if (tmp_mon==1) and (int(self._vals['month'])==12): tmp_year-=1
    if (tmp_mon==12) and (int(self._vals['month'])==1): tmp_year+=1
    self._vals['year'] = str(tmp_year)
    self.data.append(self._vals)
   self._vals={}
   self.tmp_price=""
   self.tmp_time=""
