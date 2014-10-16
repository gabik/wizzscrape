<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '-1'); 

$usd=fgets(fopen("currencies/usd", "r"));
$eur=fgets(fopen("currencies/eur", "r"));

$json=array();
$i=1;
$na = 'N/A';

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

?>
