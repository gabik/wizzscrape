<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '-1'); 

$usd=fgets(fopen("currencies/usd", "r"));
$eur=fgets(fopen("currencies/eur", "r"));

$json=array();
$i=1;
$na = 'N/A';

include 'db_con_string.php';
$db = pg_pconnect($conn_string);
$stat_query = "insert into smart_phones_serch_stat (device) values ($device)";
$result = pg_query($db, $stat_query);

?>
