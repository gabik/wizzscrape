<?php

 if (isset($_POST['device']) ){
  $device=$_POST['device'];
 } else {
  $device='web';
 }

 $dates_join="1";
 if (isset($_POST['AllDates']) ){
  if (($_POST['AllDates']=="1") or ($_POST['AllDates']=="on")) {
   $dates_join="";
  }
 }
 
 $price_join=" and (a.price+b.price)<=".$_POST['price'];
 if (isset($_POST['AllPrice'])) {
  if (($_POST['AllPrice']=="1") or ($_POST['AllPrice']=="on")) {
   $price_join="";
  }
 }

 if ($dates_join=="1"){
  $dates_join="";
  if (isset($_POST['dpd1'])) {
   if ($_POST['dpd1']!="") { $dates_join=$dates_join." and a.date>='".$_POST['dpd1']. "' "; }
  }else if (isset($_POST['start'])) {
   if ($_POST['start']!="") {$dates_join=$dates_join." and a.date>='".$_POST['start']."' ";}
  }
  if (isset($_POST['dpd2'])) {
   if ($_POST['dpd2']!="") {$dates_join=$dates_join." and a.date<='".$_POST['dpd2']."' ";}
  }else if (isset($_POST['stop'])) {
   if ($_POST['stop']!="") {$dates_join=$dates_join." and a.date<='".$_POST['stop']."' ";}
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
 
?>
