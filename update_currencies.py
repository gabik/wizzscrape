import requests 
curs= [ 
"usd",
"eur"
]

for cur in curs:
 r=requests.get('http://fx-rate.net/'+str(cur)+'/ils')
 rate= float(r.text[r.text.find('<div style="font-size:25px;color:black;margin-top:5px">'):r.text.find('<div style="font-size:25px;color:black;margin-top:5px">')+80].split('>')[1].split()[0])
 file = open("currencies/"+cur, "w")
 file.write(str(rate))
 file.close
