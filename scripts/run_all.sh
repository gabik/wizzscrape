if [ $# -ne 2 ] ; then
 echo run_all.sh START STOP
 exit 1
fi

echo 'watch "ps -ef | grep run.py | grep -v grep | grep -v watch ; ps -ef | grep python | grep expect | grep -v watch | awk '"'"'{print  \$12, \$13, \$14}'"'"' | sort"'
for i in $(eval echo {$1..$2}) ; do 
 ./run.py $i debug &
 sleep 2
 while ps -ef | egrep "run.py|expect" | grep -v grep | grep -v watch &> /dev/null ; do
  sleep 10
 done
done
