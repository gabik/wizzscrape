#!/usr/bin/python

import requests 
curs= [ 
"usd",
"eur"
]

for cur in curs:
 r=requests.get('http://fx-rate.net/'+str(cur)+'/ils')
 rate=(r.text[r.text.find('Today = ')+8:r.text.find('Today = ')+13])
 file = open("../currencies/"+cur, "w")
 file.write(str(rate))
 file.close
