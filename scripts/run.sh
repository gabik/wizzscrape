SM=`cat ~/SM`
for y in `cat SM$SM/companies` ; do
 for i in `./get_dst.sh $y` ; do unbuffer python ../scrapers/${y}_scrape.py $i $1 $2 &> ../logs/${y}_$i.log & sleep 60 ;  done
 #sleep 50
done
