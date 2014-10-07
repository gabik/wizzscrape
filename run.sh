for y in wizz easyjet up airmed elal ; do
 for i in `./get_dst.sh $y` ; do unbuffer python ${y}_scrape.py $i $1 $2 &> logs/${y}_$i.log &  done
 sleep 60
done
