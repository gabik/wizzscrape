for y in wizz easyjet up airmed elal vueling ; do
 for i in `./get_dst.sh $y` ; do unbuffer python ../scrapers/${y}_scrape.py $i $1 $2 &> ../logs/${y}_$i.log &  done
 sleep 60
done
