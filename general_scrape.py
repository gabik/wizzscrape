import re

def find_all(a_str, sub):
 start = 0
 while True:
  start = a_str.find(sub, start)
  if start == -1: return
  yield start
  start += len(sub) # use start += 1 to find overlapping matches

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

