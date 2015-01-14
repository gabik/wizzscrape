<?php

include 'query_head.php';

if ($_POST['kind'] == 2) {

 include 'query_builder_route.php'; 

 $query="
  select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd , e.name,a.dst, a.dep_time, a.arr_time, b.dep_time, b.arr_time, a.dst, a.dur_time adurtime, b.dur_time bdurtime from flights a
  join flights b on a.dst=b.dst $flight_join $dates_join
  join companies e on e.id=$companies_join and e.id=a.company and e.id=b.company
  join destinations c on a.dst=c.airport and c.company=$destination_join
  where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d
 ";

 $result = pg_query($db, $query);
 while ($row = pg_fetch_row($result)) {
  $outdur = "";
  $indur = "";
  if ($row[16]=="") { $outdur = 'N/A'; } else {list($a,$b)=explode(':', $row[16]); $outdur=$a.':'.$b; }
  if ($row[17]=="") { $indur = 'N/A'; } else {list($a,$b)=explode(':', $row[17]); $indur=$a.':'.$b; }
  list($outmon1, $outmon2) = explode("-", $row[2]);
   $outmon = $outmon1."-".$outmon2;
  $cur_elem=array('id' => $i, 'company' => $row[9], 'destination' => $row[4], 'airport' => $row[10], 'outdate' => $row[2], 'indate' => $row[3], 'outprice' => $row[5], 'inprice' => $row[6], 'total' => $row[7], 'nights' => $row[8], 'outarr' => $row[12],'inarr' => $row[14],'outdep' => $row[11],'indep' => $row[13], 'indur' => $indur, 'outdur' => $outdur, 'direction' => $na , 'ils' => $row[7], 'eur' => floor($row[7]/$eur), 'usd' => floor($row[7]/$usd), 'outmon' => $outmon);
  array_push($json, $cur_elem);
  $i+=1;
 }

} else if ($_POST['kind'] == 1) {

 include 'query_builder_oneway.php'; 

 $query="
  select a.company acmp, a.direction adir, a.dst adst, a.price aprice, a.date adt, a.dep_time adep, a.arr_time aarr, d.direction ddir, c.name cname, b.destination bdst, a.dur_time adurtime from flights a
  join companies c on c.id=a.company
  join directions d on d.id=a.direction
  join destinations b on a.dst=b.airport
  where 1=1 $price_join $dates_join $companies_join $destination_join $direction_join
 ";

 $result = pg_query($db, $query);
 while ($row = pg_fetch_row($result)) {
  $cur_elem=array('id' => $i, 'company' => $row[8], 'destination' => $row[9], 'airport' => $row[2], 'outdate' => $row[4], 'indate' => $na, 'outprice' => $row[3], $na => $row[5], 'total' => $na, 'nights' => $na, 'outarr' => $row[6],'inarr' => $na,'outdep' => $row[5],'indep' => $na, 'indur' => $na, 'outdur' => 'N/A', 'direction' => $row[7], 'ils' => $row[3], 'eur' => floor($row[3]/$eur), 'usd' => floor($row[3]/$usd) );
  array_push($json, $cur_elem);
  $i+=1;
 }
}

#echo $query;

$stat_query = "insert into smart_phones_serch_stat (device) values ('$device')";
$result = pg_query($db, $stat_query);

pg_close();
echo json_encode($json);
?>
