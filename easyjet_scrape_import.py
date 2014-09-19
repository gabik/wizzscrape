import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii, get_currency

eur=get_currency("eur")

class getFlight(HTMLParser):
 def __init__(self, year, new_year):
  self.tmp_date=""
  self.tmp_price=""
  self.tmp_data=""
  self.day=0
  self.endday=0
  self.price=0
  self.date=0
  self.time = 0
  self.data = []
  self._vals = {}
  self.header = 0
  self.direction = 0
  self.year=year
  self.new_year=new_year
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="div":
   for a,b in attrs:
    if a=="class" and b=="OutboundDaySlider": self.direction=1
    if a=="class" and b=="ReturnDaySlider ": self.direction=2
    if a=="class" and b=="day": self.day=1
    if a=="class" and b=="day nextDay": self.day=0
  if tag=="span":
   for a,b in attrs:
    if a=="class" and b.split()[0]=="dayDate" : self.date=1
    if a=="class" and b[0:5]=="price" : self.price=1
 def handle_data(self, data):
  cur_data=data.strip()
  if cur_data!="" and self.day==1 : 
   if self.price==1:
    self.tmp_price += cur_data
   if self.date==1:
    self.tmp_date += cur_data
 def handle_endtag(self, tag):
  if tag=="span":
   self.price=0
   self.date=0
  if tag=="ul" and self.day==1:
   if self.tmp_price!="":
    self._vals['weekday']=self.tmp_date.split()[0]
    self._vals['day']=self.tmp_date.split()[1]
    self._vals['month']=datetime.datetime.strptime(self.tmp_date.split()[2], "%b").strftime("%m")
    self._vals['price']=int(strip_non_ascii(self.tmp_price)*eur+0.5)
    self._vals['priceE']=strip_non_ascii(self.tmp_price)
    self._vals['direction']=self.direction
    tmp_year=self.year
    if self.new_year==1:
     self._vals['year']=str(tmp_year+1)
    else:
     if int(self._vals['month'])==1 : 
      tmp_year=self.year+1
     elif int(self._vals['month'])==2 :
      self.new_year=1
      tmp_year=self.year+1
     self._vals['year']=str(tmp_year)
    self.data.append(self._vals)
   self._vals={}
   self.tmp_date=""
   self.tmp_price=""
