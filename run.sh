for i in BUD CLJ KTW PRG SOF VNO WAW OTP; do python scrape.py $i &> logs/$i.log &  done

# (python scrape.py OTP) & my_pid=$!
# wait $my_pid ; echo $?
