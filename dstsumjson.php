<?php

$usd=fgets(fopen("currencies/usd", "r"));
$eur=fgets(fopen("currencies/eur", "r"));

$json=array();
$i=1;
$na = 'N/A';

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

if ($_POST['kind'] == 2) {

 if (isset($_POST['AllDates']) ){
  if (($_POST['AllDates']=="1") or ($_POST['AllDates']=="on")) {
   $dates_join="";
  } else {
   $dates_join="1";
  }
 }
 
 if (isset($_POST['AllPrice'])) {
  if (($_POST['AllPrice']=="1") or ($_POST['AllPrice']=="on")) {
   $price_join="";
  } else {
   $price_join=" and (a.price+b.price)<=".$_POST['price'];
  }
 }
 
 if ($dates_join=="1"){
  $dates_join="";
  if (isset($_POST['dpd1'])) {
   $dates_join=$dates_join." and a.date>='".$_POST['dpd1']. "' ";
  }else if (isset($_POST['start'])) {
   $dates_join=$dates_join." and a.date>='".$_POST['start']."' ";
  }
  if (isset($_POST['dpd2'])) {
   $dates_join=$dates_join." and a.date<='".$_POST['dpd2']."' ";
  }else if (isset($_POST['stop'])) {
   $dates_join=$dates_join." and a.date<='".$_POST['stop']."' ";
  }
 }
 
 $flight_join=" ";
 $companies_join="";
 $destination_join="";
 
 $minDays=$_POST['minDays'];
 $maxDays=$_POST['maxDays'];
 
 if ($_POST['company']=="ALL") {
  $flight_join=" and a.company=b.company";
  $companies_join="a.company";
  $destination_join="e.name";
 } else {
  $query="select id from companies where name='".$_POST['company']."'";
  $result = pg_query($db, $query);
  $company_id_a=pg_fetch_row($result);
  $company_id = $company_id_a[0];

  $companies_join="'".$company_id."'";
  $destination_join="'".$_POST['company']."'";
 }

 if ($_POST['dst']!=="ALL") {
  $flight_join=$flight_join." and a.dst='".$_POST['dst']."'";
 }
 
 $query="
  select distinct f.dst, ff.destination, f.total from (select d.dst, min(d.total) total from ( select a.date adt, b.date bdt, c.destination cdst,a.price+b.price total,(b.date - a.date) dd ,e.name,a.dst dst from flights a
  join flights b on a.dst=b.dst $flight_join $dates_join
  join companies e on e.id=$companies_join and e.id=a.company and e.id=b.company
  join destinations c on a.dst=c.airport and c.company=$destination_join
  where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d group by dst) f 
  join destinations ff on ff.airport=f.dst
  order by total
 ";

 #echo $query;
 $result = pg_query($db, $query);
 while ($row = pg_fetch_row($result)) {
  $cur_elem=array('id' => $i,'destination' => $row[1],'airport' => $row[0],'total' => $row[2],'ils' => $row[2],'eur' => floor($row[2]/$eur),'usd' => floor($row[2]/$usd));
  array_push($json, $cur_elem);
  $i+=1;
 }

} else if ($_POST['kind'] == 1) {

 if ($_POST['AllPrice']=="1") {
  $price_filter="";
 } else {
  $price_filter=" and a.price<=".$_POST['price'];
 }

 if ($_POST['AllDates']=="1") {
  $dates_filter=" ";
 } else {
  $dates_filter=" and a.date>='".$_POST['start']."' and a.date<='".$_POST['stop']."'";
 }

 if ($_POST['company']=="ALL") {
  $companies_filter=" ";
 } else {
  $query="select id from companies where name='".$_POST['company']."'";
  $result = pg_query($db, $query);
  $company_id_a=pg_fetch_row($result);
  $company_id = $company_id_a[0];

  $companies_filter=" and a.company=".$company_id;
 }

 if ($_POST['dst']=="ALL") {
  $destination_filter=" ";
 } else {
  $destination_filter=" and a.dst='".$_POST['dst']."'";
 }

 $query="
  select a.company acmp, a.direction adir, a.dst adst, a.price aprice, a.date adt, a.dep_time adep, a.arr_time aarr, d.direction ddir, c.name cname, b.destination bdst from flights a
  join companies c on c.id=a.company
  join directions d on d.id=a.direction
  join destinations b on a.dst=b.airport
  where 1=1 $price_filter $dates_filter $companies_filter $destination_filter
 ";

 $result = pg_query($db, $query);
 while ($row = pg_fetch_row($result)) {
  $cur_elem=array('id' => $i, 'company' => $row[8], 'destination' => $row[9], 'airport' => $row[2], 'outdate' => $row[4], 'indate' => $na, 'outprice' => $row[3], $na => $row[5], 'total' => $na, 'nights' => $na, 'outarr' => $row[6],'inarr' => $na,'outdep' => $row[5],'indep' => $na, 'indur' => $na, 'outdur' => 'N/A', 'direction' => $row[7], 'ils' => $row[3], 'eur' => floor($row[3]/$eur), 'usd' => floor($row[3]/$usd) );
  array_push($json, $cur_elem);
  $i+=1;
 }
}

#echo $query;

pg_close();
echo json_encode($json);
?>
