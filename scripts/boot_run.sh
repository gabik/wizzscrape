#!/bin/bash

#cd
#rm -rf wizz
#git clone git@github.com:gabik/wizzscrape.git wizz
#cd ~/wizz
#git pull
cd ~/wizz/scripts
./update_currencies.py &> ../logs/cur.log

if [[ $# -lt 2 ]] ; then
 echo Missing arguments. 
 echo Usage $0 Machine Month [debug]
 exit 1
fi

echo $1 > ~/SM
sudo hostname SM${1}.2fly.cheap
shift
./run.sh $*

count_timeout=0
run_timeout=35
# check if something still runs
while [[ $(ps -ef | grep scrape | grep -v grep | grep -v git | wc -l) -ne 0 ]] ; do
 sleep 60
 count_timeout=$((count_timeout+1))
 if [[ count_timeout -ge run_timeout ]] ; then
  break
 fi
done

./check_last_run.sh 

sudo poweroff
