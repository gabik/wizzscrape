for i in BUD CLJ KTW PRG SOF VNO WAW OTP; do unbuffer python wizz_scrape.py $i $1 $2 &> logs/$i.log &  done

# (python scrape.py OTP) & my_pid=$!
# wait $my_pid ; echo $?
