. ../env.sh
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF
delete from flights where date<(now() - '0 day'::INTERVAL) ;
EOF

q=`mktemp`
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF > $q
select max(a.scrape_time), a.company, max(b.name) cmp, (a.date-current_date)/31 mon  from flights a join companies b on a.company=b.id where a.scrape_time<current_date group by a.company, mon order by cmp, mon;
EOF
q1=$(mktemp)
if [[ $(cat $q) != "" ]] ; then
 sed 's/^M//g' $q > $q1
 mail -r "ScrapersManager@2fly.cheap" -s "Old flights in DB daily status - `date`" 2flycheap@kazav.net < $q1
fi
rm -f $q $q1

q=`mktemp`
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF > $q
select c.name company, c.airport , min(date) mind, max(date) maxd, count(d.price) count from (select b.id company, a.airport , b.name  from destinations a join companies b on a.company=b.name) c left join flights d on c.airport=d.dst and c.company=d.company group by name, airport order by count;
EOF
q1=$(mktemp)
sed 's/^M//g' $q > $q1
mail -r "ScrapersManager@2fly.cheap" -s "Daily flights monitoring - `date`" 2flycheap@kazav.net < $q1
rm -f $q $q1

