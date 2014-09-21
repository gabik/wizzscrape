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

if ($_POST['dst']=="ALL") {
 $result = pg_query($db, "
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd from (
  select b1.* from (
   select max(scrape_time) max_scrape, date max_date, direction from ".$_POST['company']."_flights group by date,direction
  ) a1
 join
 ".$_POST['company']."_flights b1
  on a1.max_date=b1.date and a1.max_scrape=b1.scrape_time and a1.direction=b1.direction
  order by price
 ) a
 join
 (
  select b1.* from (
   select max(scrape_time) max_scrape, date max_date, direction from ".$_POST['company']."_flights group by date,direction
 ) a1
 join
 ".$_POST['company']."_flights b1
  on a1.max_date=b1.date and a1.max_scrape=b1.scrape_time and a1.direction=b1.direction
  order by price
 ) b
  on a.dst=b.dst
 join ( select * from destinations where company='".$_POST['company']."' ) c
  on a.dst=c.airport
 where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']."
 order by total
 ) d
");
#select distinct a.date, b.date, c.destination, a.price, b.price, a.price+b.price total , (b.date - a.date) dd from ".$_POST['company']."_flights a join ".$_POST['company']."_flights b on a.dst=b.dst join (select * from destinations where company='".$_POST['company']."') c on a.dst=c.airport  where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']." order by total;");
} else {
 $result = pg_query($db, "
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd from ( 
  select b1.* from (
   select max(scrape_time) max_scrape, date max_date, direction from ".$_POST['company']."_flights where dst='".$_POST['dst']."' group by date,direction
  ) a1
 join
 ".$_POST['company']."_flights b1
  on a1.max_date=b1.date and a1.max_scrape=b1.scrape_time and a1.direction=b1.direction
  order by price
 ) a
 join
 (
  select b1.* from (
   select max(scrape_time) max_scrape, date max_date, direction from ".$_POST['company']."_flights where dst='".$_POST['dst']."' group by date,direction
 ) a1
 join
 ".$_POST['company']."_flights b1
  on a1.max_date=b1.date and a1.max_scrape=b1.scrape_time and a1.direction=b1.direction
  order by price
 ) b
  on a.dst=b.dst 
 join ( select * from destinations where company='".$_POST['company']."' ) c
  on a.dst=c.airport
 where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']."
 order by total
 ) d"
);
#select distinct a.date, b.date, c.destination, a.price, b.price, a.price+b.price total , (b.date - a.date) dd from ".$_POST['company']."_flights a join ".$_POST['company']."_flights b on a.dst=b.dst join (select * from destinations where company='".$_POST['company']."' and airport='".$_POST['dst']."') c on a.dst=c.airport  where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$_POST['minDays']." and (b.date - a.date)<=".$_POST['maxDays']." and (a.price+b.price)<=".$_POST['price']." order by total;");
}

pg_close();
?>
<Table class="bordered" id="route">
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
