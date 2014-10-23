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

include 'db_con_string.php';
$db = pg_pconnect($conn_string);
$query="select id from companies where name='".$_GET['cmp']."'";
$result = pg_query($db, $query);
$company_id_a=pg_fetch_row($result);
$company_id = $company_id_a[0];

$query="
select a.*, c.direction from (select scrape_time,direction,dst,price,date,dep_time,arr_time from flights where dst='".$_GET["dst"]."' and company='".$company_id."') a join directions c on a.direction=c.id 
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
