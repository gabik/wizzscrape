. ../env.sh
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF
delete from flights where date<(now() - '0 day'::INTERVAL) ;
EOF


