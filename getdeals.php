
<?php
$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$delaquery="
 select distinct d2.mttl , e2.destination, d2.adst from
 (select d.adst, min(d.total) mttl from ( select a.date adt, b.date bdt, a.price+b.price total , (b.date - a.date) dd , a.dst adst, a.company company
 from flights a join flights b on a.dst=b.dst and a.company=b.company where a.direction=1 and b.direction=2 and (b.date - a.date)>=2 and (b.date - a.date)<=9 and (a.price+b.price)<=1000 order by total) d
 group by adst order by mttl limit 3 ) d2 join destinations e2 on d2.adst=e2.airport order by mttl
";
$dealsresult=pg_query($db, $delaquery);
$deals=array();
while ($row = pg_fetch_row($dealsresult)) {
 $cur_deal=array("airport" => $row[2], "destination" => $row[1], "price" => $row[0]);
 array_push($deals, $cur_deal);
}

$constdealq="
select mttl, destination, adst from (
 select distinct d2.mttl , e2.destination, d2.adst from (
  select d.adst, min(d.total) mttl from (

   select s.* from (
    select a.date adt, b.date bdt, a.price+b.price total , (b.date - a.date) dd , a.dst adst, a.company company from flights a join flights b on a.dst=b.dst and a.company=b.company where a.direction=1 and b.direction=2 and (b.date - a.date)>=4 and (b.date - a.date)<=9  and (a.dst='LGW' or a.dst='LTN') order by total limit 1) s union all

   select s.* from (
    select a.date adt, b.date bdt, a.price+b.price total , (b.date - a.date) dd , a.dst adst, a.company company from flights a join flights b on a.dst=b.dst and a.company=b.company where a.direction=1 and b.direction=2 and (b.date - a.date)>=4 and (b.date - a.date)<=9  and (a.dst='BER' or a.dst='SXF') order by total limit 1) s union all

   select s.* from (
    select a.date adt, b.date bdt, a.price+b.price total , (b.date - a.date) dd , a.dst adst, a.company company from flights a join flights b on a.dst=b.dst and a.company=b.company where a.direction=1 and b.direction=2 and (b.date - a.date)>=4 and (b.date - a.date)<=9  and (a.dst='BCN') order by total limit 1) s

) d group by adst order by mttl ) d2 join destinations e2 on d2.adst=e2.airport order by mttl
) d3
";
$constdealr=pg_query($db, $constdealq);
while ($row = pg_fetch_row($constdealr)) {
 $cur_deal=array("airport" => $row[2], "destination" => $row[1], "price" => $row[0]);
 array_push($deals, $cur_deal);
}

pg_close();

echo json_encode($deals);
?>
