#!/bin/bash

cd ../logs

SM=$(cat ~/SM)
q=$(mktemp)

for cmp in $(cat ../scripts/SM${SM}/companies) ; do 
 for dst in $(../scripts/get_dst.sh $cmp) ; do
  logfile=${cmp}_${dst}.log
  if ! grep Done $logfile &> /dev/null ; then 
   echo ${logfile} -Failed 
   cat $logfile
   echo '--------------------------------------------'
  fi
 done
done > $q

if [[ $(wc -l $q | awk '{print $1}') -ne 0 ]] ; then
 q1=$(mktemp)
 sed 's///g' $q > $q1
 #new_logs=`date +%d%m%H%M`
 #mkdir $new_logs
 #cp *.log $new_logs
 mail -r "ScrapersManager@2fly.cheap" -s "Scrapers Failed - `hostname` `date`" 2flycheap@kazav.net < $q1
fi
rm -f $q $q1
