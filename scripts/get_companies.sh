psql -t -hmanegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com -Uroot -w GabiScrape << EOF
select airport from destinations where company='$1';
EOF


