for i in `./get_dst.sh wizz` ; do unbuffer python wizz_scrape.py $i $1 $2 &> logs/wizz_$i.log &  done
sleep 60
for i in `./get_dst.sh easyjet` ; do unbuffer python easyjet_scrape.py $i $1 $2 &> logs/easyjet_$i.log &  done
sleep 60
for i in `./get_dst.sh up` ; do unbuffer python flyup_scrape.py $i $1 $2 &> logs/flyup_$i.log &  done
sleep 60
for i in `./get_dst.sh airmed` ; do unbuffer python airmid_scrape.py $i $1 $2 &> logs/airmid_$i.log &  done
sleep 60
for i in `./get_dst.sh elal` ; do unbuffer python elal_scrape.py $i $1 $2 &> logs/elal_$i.log &  done
sleep 60
for i in `./get_dst.sh elalC` ; do unbuffer python elalC_scrape.py $i $1 $2 &> logs/elalC_$i.log &  done


# (python scrape.py OTP) & my_pid=$!
# wait $my_pid ; echo $?
