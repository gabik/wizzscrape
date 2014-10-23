. ../env.sh
psql -t -h$DB_HOST -Uroot -w GabiScrape << EOF
select airport from destinations where company='$1';
EOF


