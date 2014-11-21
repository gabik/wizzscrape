#!/bin/bash

cd ~/wizz
git pull
cd ~/wizz/scripts
./update_currencies.py &> ../logs/cur.log

if [[ $# -le 3 ]] ; then
 echo Missing arguments. 
 echo Usage $0 Machine DST Month [debug]
fi

echo $1 > ~/SM
sudo hostname SM${1}.2fly.cheap
shift
./run.sh $*

count_timeout=0
run_timeout=20
# check if something still runs
while [[ $(ps -ef | grep scrape | grep -v grep | wc -l) -ne 0 ]] ; do
 sleep 60
 count_timeout=$((count_timeout+1))
 if [[ count_timeout -ge run_timeout ]] ; then
  break
 fi
done

./check_last_run.sh 

sudo poweroff
