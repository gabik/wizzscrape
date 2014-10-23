cd ..
./check_last_run.sh
for y in airmed elal vueling norwegian ; do
 for i in `./get_dst.sh $y` ; do unbuffer python ../scrapers/${y}_scrape.py $i $1 $2 &> ../logs/${y}_$i.log & sleep 45 ;  done
 sleep 50
done
