import requests
import re
from HTMLParser import HTMLParser
import sys
import datetime
from general_scrape import find_all, clean_dup, strip_non_ascii

fullpostdict={}

def clean_dup_max(lst, direction):
 if not lst : return []
 lst.sort(key=lambda x: str(x['year'])+str(x['month'])+str(x['day']))
 newlst=[]
 last_elem=lst[0]
 for i in lst:
  if i['direction']==direction:
   if (i['direction']==last_elem['direction']) and (i['year']==last_elem['year']) and (i['month']==last_elem['month']) and (i['day']==last_elem['day']) :
    if i['maxprice']>last_elem['maxprice'] : last_elem['maxprice']=i['maxprice']
    if i['price']>last_elem['price'] : last_elem['price']=i['price']
   else:
    if last_elem['direction']==direction:
     newlst.append(i)
    last_elem=i
 return newlst


class getPostVals(HTMLParser):
 def __init__(self):
  self.inputs=[]
  self._curinput={}
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):
  if tag=="input":
   for i in attrs:
    if i[0]=="id":
     self._curinput['id']=i[1]
    if i[0]=="value":
     self._curinput['val']=i[1]
  if self._curinput: self.inputs.append(self._curinput)
  self._curinput={}

class getFlight(HTMLParser):
 def __init__(self):
  self.table=0
  self.tmp_date = ""
  self.tmp_price = ""
  self.price=0
  self.data = []
  self._vals = {}
  self.day = 0
  self.direction = 0
  HTMLParser.__init__(self)
 def handle_starttag(self, tag, attrs):

  if tag=="table":
   for a,b in attrs:
    if a=="class" and b=="tableFDCT": self.table=1
   for a,b in attrs:
    if a=="id" and self.table==1:
     self.direction=b[-1]

  if tag=="span":
   for a,b in attrs:
    if a=="class" and b=="fdct_price" and self.table==1:
     self.price=1

   for a,b in attrs:
    if a=="id" and self.table==1:
     self.tmp_date=b[-8:]

 def handle_data(self, data):
  if self.price == 1: 
   self.tmp_price=self.tmp_price.data

 def handle_endtag(self, tag):

  if tag == "table":
   self.table=0

  if tag == "span" and self.price==1 :
   self._vals['year'] = str(self.tmp_date[0:4])
   self._vals['weekday'] = ""
   self._vals['day'] = str(self.tmp_date[-2:])
   self._vals['month'] = str(self.tmp_date[-4:-2])
   self._vals['direction'] = self.direction
   self._vals['price'] = self.tmp_price
   self._vals['dep_time'] = '00:00'
   self._vals['arr_time'] = '00:00'
   self.data.append(self._vals)
   self._vals={}
   self.tmp_date = ""
   self.tmp_price=""
   self.price=0

