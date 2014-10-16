<?php

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

?>
