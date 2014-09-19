psql -t -hmanegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com -Uroot -w GabiScrape << EOF
select max(v) a from (select max(scrape_time) v from easyjet_flights union all select max(scrape_time) v from wizz_flights) as qwe;
EOF


