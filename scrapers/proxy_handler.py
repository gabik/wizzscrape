#!/usr/bin/python

import re
from bs4 import BeautifulSoup
import requests
import base64

cur_proxy=-1

def decode_ip(enc):
 base=""
 for i in enc :
  if re.match(r'^[a-zA-Z]$', i):
   x=ord(i)
   if i.lower() < 'n':
    x+=13
   else:
    x-=13
   base+=chr(x)
  else:
   base+=i
 ip = base64.b64decode(base)
 return ip

ips_list = []
for p in range(1,3):
 url = 'http://www.cool-proxy.net/proxies/http_proxy_list/sort:download_speed_average/direction:desc/page:{0}'.format(p)
 r = requests.get(url).text
 soup = BeautifulSoup(r)
 a=soup.find_all('tr')
 for i in a[1:-1]:
   if 'str_rot13' in str(i):
    dec64 = i.find_all('td')[0].contents[0].contents[0].split('(')[3].split('"')[1]
    cur = {}
    cur['ip'] = decode_ip(dec64)
    cur['port'] = i.find_all('td')[1].contents[0]
    ips_list.append(cur)
