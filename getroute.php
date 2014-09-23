<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<script src="lib/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="lib/table.css">
<link rel="stylesheet" type="text/css" href="lib/jquery.dataTables.min.css">
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<script src='lib/jquery.qtip.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='lib/fullcalendar.min.js'></script>
<link rel="stylesheet" type="text/css" href="lib/jquery.qtip.min.css">


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

$base_colors=array('#3F5D7D', '#279B61', '#993333', '#A3E496', '#95CAE4', '#FFCC33', '#CC6699', '#CC3333', '#008AB8', '#FFFF7A');
$i=0;
$colors=array();
while ($row = pg_fetch_row($result)) {
 if ($color[$row[4]] == "" ) { $color[$row[4]]=$base_colors[$i]; $i+=1; }
}

pg_result_seek($result, 0);
$events=array();
while ($row = pg_fetch_row($result)) {
 $title=$row[4].": ".$row[7];
 $cur_event=array("title" => $title, "start" => $row[2], "end" => $row[3], "color" => $color[$row[4]]);
 if ($events[$row[4]] == "") { 
  $events[$row[4]]=array();
 } 
 array_push($events[$row[4]], $cur_event);
}
?>


<script>


$(document).ready(function() {
    $('#calendar').fullCalendar({
        height: "auto",
        events: [
<?php
#while ($row = pg_fetch_row($result)) {
foreach ($events as $event) {
 foreach ($event as $cur_e) {
  echo "{\n";
  echo "title : '".$cur_e['title']."',\n";
  echo "start : '".$cur_e['start']."',\n";
  echo "end:    '".$cur_e['end']."',\n";
  echo "color:  '".$cur_e['color']."'\n";
  echo "},\n";
 }
}
pg_result_seek($result, 0);
?>
        ],
    eventRender: function(event, element) {
     element.qtip({content: event.tooltip, style: {padding: 5, background: '#A2D959', color: 'black', color: 'black', border: {width: 7,radius: 5,color: '#A2D959'}}});
    }
    });

    $("table").DataTable(
    {
      "iDisplayLength": 50,
      "order": [[ 5 ,"asc" ]]
    });


});

function ShowTable(){ $("#cal-tab").hide("fast") ; $("#table-tab").show("fast"); }
function ShowCal(){ $("#table-tab").hide("fast") ; $("#cal-tab").show("fast"); }


</script>

</HEAD<BODY>
<div id="route_wrap">
<button onclick="ShowTable();"  >TableView</button>
<button  onclick="ShowCal();">CalView</button>

<div id="table-tab"><P>
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
</P>
</div>
<div id="cal-tab">
<div id="calendar"></div>
</div>
</div>
</BODY></HTML>
