
<?php
if ($_POST['AllDates']=="1") {
 $dates_join="";
} else {
 $dates_join="1";
}

if ($_POST['AllPrice']=="1") {
 $price_join="";
} else {
 $price_join=" and (a.price+b.price)<=".$_POST['price'];
}

if ($dates_join=="1"){
 $dates_join=" and a.date>='".$_POST['start']."' and a.date<='".$_POST['stop']."'";
}

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$query="select id from companies where name='".$_POST['company']."'";
$result = pg_query($db, $query);
$company_id_a=pg_fetch_row($result);
$company_id = $company_id_a[0];

$flight_join="";
$companies_join="";
$destination_join="";

$minDays=$_POST['minDays'];
$maxDays=$_POST['maxDays'];

if ($_POST['company']=="ALL") {
 $flight_join=" and a.company=b.company";
 $companies_join="a.company";
 $destination_join="e.name";
 if ($_POST['dst']!=="ALL") {
  $flight_join=$flight_join." and a.dst='".$_POST['dst']."'";
 }
} else {
 $flight_join=" ";
 $companies_join="'".$company_id."'";
 $destination_join="'".$_POST['company']."'";
 if ($_POST['dst']!="ALL") {
  $flight_join=$flight_join." and a.dst='".$_POST['dst']."'";
 }
}

$query="
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd , e.name,a.dst, a.dep_time, a.arr_time, b.dep_time, b.arr_time, a.dst from flights a
join flights b on a.dst=b.dst $flight_join $dates_join
join companies e on e.id=$companies_join
join destinations c on a.dst=c.airport and c.company=$destination_join
where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d
";

$result = pg_query($db, $query);
pg_close();


$json=array();
$i=1;
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('id' => $i, 'company' => $row[9], 'destination' => $row[4], 'airport' => $row[10], 'outdate' => $row[2], 'indate' => $row[3], 'outprice' => $row[5], 'inprice' => $row[6], 'total' => $row[7], 'nights' => $row[8], 'outarr' => $row[12],'inarr' => $row[14],'outdep' => $row[11],'indep' => $row[13], 'indur' => 'N/A', 'outdur' => 'N/A' );
 array_push($json, $cur_elem);
 $i+=1;
}
echo json_encode($json);
?>
