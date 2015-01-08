for i in {0..12} ; do ./run.py $i &  done
watch "ps -ef | grep run.py | grep -v grep | grep -v watch ; ps -ef | grep python | grep expect | grep -v watch | awk '{print  \$12, \$14}' | sort | uniq "
