psql -t -hmanegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com -Uroot -w GabiScrape << EOF
delete from flights where date<(now() - '0 day'::INTERVAL) ;
EOF


