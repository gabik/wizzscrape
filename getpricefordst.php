<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/jquery.min.js'></script>
<script src="lib/stupidtable.min.js?dev"></script>
<link rel="stylesheet" type="text/css" href="lib/table.css">

<script>
    $(function(){
        $("table").stupidtable();
    });
</script>

</HEAD<BODY>
<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$result = pg_query($db, "select b.*, c.direction from (select max(scrape_time) max_scrape, date max_date, direction from ".$_GET["cmp"]."_flights where dst='".$_GET["dst"]."' group by date,direction ) a join ".$_GET["cmp"]."_flights b on a.max_date=b.date and a.max_scrape=b.scrape_time and a.direction=b.direction join directions c on a.direction=c.id order by price");

pg_close();
?>
<Table class="bordered" id="route">
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
