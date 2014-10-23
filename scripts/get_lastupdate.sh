. ../env.sh
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF
select max(scrape_time) v from flights ;
EOF


