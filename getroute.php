<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$result = pg_query($db, "select a.date, b.date, c.dst, a.price, b.price, a.price+b.price total , (b.date - a.date) dd from ".$_POST['company']."_flights a join ".$_POST['company']."_flights b on a.dst=b.dst where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']." order by total;");

?>
<Table border=1>
 <tr>
  <th> Destination </th>
  <th> Outgoing </th>
  <th> Ingoing </th>
  <th> Out Price </th>
  <th> In Price </th>
  <th> Total Price </th>
  <th> Days </th>
 </tr>
  <?php  
while ($row = pg_fetch_row($result)) {
 echo '<tr>';
 echo '<td> '.$row[2].' </td>';
 echo '<td> '.$row[0].' </td>';
 echo '<td> '.$row[1].' </td>';
 echo '<td> '.$row[3].' </td>';
 echo '<td> '.$row[4].' </td>';
 echo '<td> '.$row[5].' </td>';
 echo '<td> '.$row[6].' </td>';
 echo '</tr>';
}
?>
</Table>

