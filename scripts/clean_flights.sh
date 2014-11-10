. ../env.sh
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF
delete from flights where date<(now() - '0 day'::INTERVAL) ;
EOF

today=`date +%Y-%m-%d -d "yesterday"`
q=`mktemp`
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF > $q
select * from flights where scrape_time<'$today' order by scrape_time
EOF
q1=$(mktemp)
if [[ $(cat $q) != "" ]] ; then
 sed 's/^M//g' $q > $q1
 new_logs=`date +%d%m%H%M`
 mail -r "ScrapersManager@2fly.cheap" -s "Beta daily status - `date`" 2flycheap@kazav.net < $q1
fi
rm -f $q $q1

