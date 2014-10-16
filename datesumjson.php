<?php

include 'query_head.php';

if ($_POST['kind'] == 2) {

 include 'query_builder_route.php';
 
 $query="
select f.adt, min(f.total) total from (
 select date_part('year', d.adt) || '-' || date_part('month', d.adt) adt, d.total total from (
  select a.date adt, b.date bdt, c.destination cdst,a.price+b.price total,(b.date - a.date) dd ,e.name,a.dst dst from flights a
   join flights b on a.dst=b.dst $flight_join $dates_join
   join companies e on e.id=$companies_join and e.id=a.company and e.id=b.company
   join destinations c on a.dst=c.airport and c.company=$destination_join
   where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d ) f group by adt order by adt 
 ";

 #echo $query;
 $result = pg_query($db, $query);
 while ($row = pg_fetch_row($result)) {
  $cur_elem=array('id' => $i,'ddate' => $row[0],'total' => $row[1],'ils' => $row[1],'eur' => floor($row[1]/$eur),'usd' => floor($row[1]/$usd));
  array_push($json, $cur_elem);
  $i+=1;
 }

} else if ($_POST['kind'] == 1) {

 include 'query_builder_oneway.php';

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
