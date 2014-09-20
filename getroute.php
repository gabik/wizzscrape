<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/jquery.min.js'></script>
<script src="lib/stupidtable.min.js?dev"></script>
<style>

body {
    width: 900px;
    margin: 40px auto;
    font-family: 'trebuchet MS', 'Lucida sans', Arial;
    font-size: 14px;
    color: #444;
}

table {
    *border-collapse: collapse; /* IE7 and lower */
    border-spacing: 0;
    width: 100%;    
}

.bordered {
    border: solid #ccc 1px;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    -webkit-box-shadow: 0 1px 1px #ccc; 
    -moz-box-shadow: 0 1px 1px #ccc; 
    box-shadow: 0 1px 1px #ccc;         
}

.bordered tr:hover {
    background: #fbf8e9;
    -o-transition: all 0.1s ease-in-out;
    -webkit-transition: all 0.1s ease-in-out;
    -moz-transition: all 0.1s ease-in-out;
    -ms-transition: all 0.1s ease-in-out;
    transition: all 0.1s ease-in-out;     
}    
    
.bordered td, .bordered th {
    border-left: 1px solid #ccc;
    border-top: 1px solid #ccc;
    padding: 10px;
    text-align: left;    
}

.bordered th {
    background-color: #dce9f9;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#ebf3fc), to(#dce9f9));
    background-image: -webkit-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:    -moz-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:     -ms-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:      -o-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:         linear-gradient(top, #ebf3fc, #dce9f9);
    -webkit-box-shadow: 0 1px 0 rgba(255,255,255,.8) inset; 
    -moz-box-shadow:0 1px 0 rgba(255,255,255,.8) inset;  
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset;        
    border-top: none;
    text-shadow: 0 1px 0 rgba(255,255,255,.5); 
}

.bordered td:first-child, .bordered th:first-child {
    border-left: none;
}

.bordered th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;
}

.bordered th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
}

.bordered th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
}

.bordered tr:last-child td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 6px;
}

.bordered tr:last-child td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 6px 0;
}



/*----------------------*/

.zebra td, .zebra th {
    padding: 10px;
    border-bottom: 1px solid #f2f2f2;    
}

.zebra tbody tr:nth-child(even) {
    background: #f5f5f5;
    -webkit-box-shadow: 0 1px 0 rgba(255,255,255,.8) inset; 
    -moz-box-shadow:0 1px 0 rgba(255,255,255,.8) inset;  
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset;        
}

.zebra th {
    text-align: left;
    text-shadow: 0 1px 0 rgba(255,255,255,.5); 
    border-bottom: 1px solid #ccc;
    background-color: #eee;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#f5f5f5), to(#eee));
    background-image: -webkit-linear-gradient(top, #f5f5f5, #eee);
    background-image:    -moz-linear-gradient(top, #f5f5f5, #eee);
    background-image:     -ms-linear-gradient(top, #f5f5f5, #eee);
    background-image:      -o-linear-gradient(top, #f5f5f5, #eee); 
    background-image:         linear-gradient(top, #f5f5f5, #eee);
}

.zebra th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;  
}

.zebra th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
}

.zebra th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
}

.zebra tfoot td {
    border-bottom: 0;
    border-top: 1px solid #fff;
    background-color: #f1f1f1;  
}

.zebra tfoot td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 6px;
}

.zebra tfoot td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 6px 0;
}

.zebra tfoot td:only-child{
    -moz-border-radius: 0 0 6px 6px;
    -webkit-border-radius: 0 0 6px 6px
    border-radius: 0 0 6px 6px
}
  
</style>

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
