psql -t -hmanegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com -Uroot -w GabiScrape << EOF
select max(scrape_time) v from flights ;
EOF


