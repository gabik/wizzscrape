#!/bin/bash
cd ../logs
q=$(mktemp)
for i in *.log ; do if ! grep Done $i &> /dev/null ; then echo ${i}-Failed ; cat $i ; echo '--------------------------------------------';  fi ; done > $q
if [[ $(wc -l $q | awk '{print $1}') -ne 0 ]] ; then
 mail -r "ScrapersManager@2fly.cheap" -s "Scrapers Failed - `date`" 2flycheap@kazav.net < $q
fi
rm -f $q
