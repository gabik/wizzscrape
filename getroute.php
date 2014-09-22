<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<script src="lib/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="lib/table.css">
<link rel="stylesheet" type="text/css" href="lib/jquery.dataTables.min.css">

<script>
    $(function(){
        $("table").DataTable(
	 {
          "iDisplayLength": 50
	 }
	);
    });
</script>

</HEAD<BODY>
<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

if ($_POST['dst']=="ALL") {
 $query="
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd from ".$_POST['company']."_flights_v a
 join ".$_POST['company']."_flights_v b on a.dst=b.dst
 join destinations c on a.dst=c.airport and c.company='".$_POST['company']."'
 where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']."
 order by total
 ) d
"; 
} else {
 $query="
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd from ".$_POST['company']."_flights_v a
 join ".$_POST['company']."_flights_v b on a.dst=b.dst and a.dst='".$_POST['dst']."'
 join destinations c on a.dst=c.airport and c.company='".$_POST['company']."'
 where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']."
 order by total
 ) d
";
}
#echo $query;
$result = pg_query($db, $query);
pg_close();
?>
<Table id="route" class="table table-striped table-bordered display">
<thead>
 <tr>
  <th data-sort="string"> Destination </th>
  <th data-sort="string"> Outgoing </th>
  <th data-sort="string"> Ingoing </th>
  <th data-sort="int"> Out Price </th>
  <th data-sort="int"> In Price </th>
  <th data-sort="int"> Total Price </th>
  <th data-sort="int"> Days </th>
 </tr>
</thead>
<tbody>
  <?php  
while ($row = pg_fetch_row($result)) {
 echo '<tr>';
 echo '<td> '.$row[4].' </td>';
 echo '<td> '.$row[2].' </td>';
 echo '<td> '.$row[3].' </td>';
 echo '<td> '.$row[5].' </td>';
 echo '<td> '.$row[6].' </td>';
 echo '<td> '.$row[7].' </td>';
 echo '<td> '.$row[8].' </td>';
 echo '</tr>';
}
?>
</tbody>
</Table>
</BODY></HTML>
