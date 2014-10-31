<?php

 $dates_join="1";
 if (isset($_POST['AllDates']) ){
  if (($_POST['AllDates']=="1") or ($_POST['AllDates']=="on")) {
   $dates_join=" ";
  }
 }

 $price_join=" and a.price<=".$_POST['price'];
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

 $companies_join=" ";
 $destination_join=" ";

 if ($_POST['company']!="ALL") {
  $query="select id from companies where name='".$_POST['company']."'";
  $result = pg_query($db, $query);
  $company_id_a=pg_fetch_row($result);
  $company_id = $company_id_a[0];

  $companies_join=" and a.company=".$company_id;
 }

 if ($_POST['dst']!="ALL") {
  $destination_join=" and a.dst='".$_POST['dst']."'";
 }

 $direction_join="";
 if (isset($_POST['direction']) ){
  $direction_join=" and a.direction=".$_POST['direction'];
 }
?>
