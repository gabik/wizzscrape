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
          "iDisplayLength": 50,
	  "order": [[ 5 , "asc"]]
         }
        );
    });
</script>

</HEAD<BODY>
<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$query="
select a.*, c.direction from (select scrape_time,direction,dst,price,date,dep_time,arr_time from ".$_GET["cmp"]."_flights_v where dst='".$_GET["dst"]."') a join directions c on a.direction=c.id order by price
";
#echo $query;
$result = pg_query($db, $query);

pg_close();
?>
<Table id="route" class="table table-striped table-bordered display">
<thead>
 <tr>
  <th data-sort="string"> Destination </th>
  <th data-sort="string"> Direction </th>
  <th data-sort="string"> Date </th>
  <th data-sort="string"> Dep Time </th>
  <th data-sort="string"> Arr Time </th>
  <th data-sort="int"> Price </th>
 </tr>
</thead>
<tbody>
  <?php  
while ($row = pg_fetch_row($result)) {
 echo '<tr>';
 echo '<td> '.$row[2].' </td>';
 echo '<td> '.$row[7].' </td>';
 echo '<td> '.$row[4].' </td>';
 echo '<td> '.$row[5].' </td>';
 echo '<td> '.$row[6].' </td>';
 echo '<td> '.$row[3].' </td>';
 echo '</tr>';
}
?>
</tbody>
</Table>
</BODY></HTML>