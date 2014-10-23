import re
import requests
import psycopg2
from psycopg2 import extras

db= psycopg2.connect( host="manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com", database="GabiScrape", user="root", password="ManegerDB")

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
 r=requests.get('http://fx-rate.net/'+str(cur)+'/ils')
 #return float(r.text[r.text.find('<div style="font-size:25px;color:black;margin-top:5px">'):r.text.find('<div style="font-size:25px;color:black;margin-top:5px">')+80].split('>')[1].split()[0])
 return float(r.text[r.text.find('Today = ')+8:r.text.find('Today = ')+13].split('<')[0])
