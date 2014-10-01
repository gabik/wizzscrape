<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<script src="lib/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="lib/table.css">
<link rel="stylesheet" type="text/css" href="lib/jquery.dataTables.min.css">
<link rel='stylesheet' href='lib/fullcalendar.min.css' />
<script src='lib/jquery.qtip.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='lib/fullcalendar.min.js'></script>
<link rel="stylesheet" type="text/css" href="lib/jquery.qtip.min.css">
<link rel="stylesheet" type="text/css" href="lib/route.css">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="lib/dataTables.bootstrap.js"></script>


<?php
if (array_key_exists('days', $_POST)) {
 $days_a=explode(",", $_POST['days']);
 $minDays=$days_a[0];
 $maxDays=$days_a[1];
} else {
 $minDays=$_POST['minDays'];
 $maxDays=$_POST['maxDays'];
}

if (array_key_exists('AllDates', $_POST)) {
 if ($_POST['AllDates']=="on") {
  $dates_join="";
 } else {
  $dates_join="1";
 }
} else {
 $dates_join="1";
}

if (array_key_exists('AllPrice', $_POST)) {
 if ($_POST['AllPrice']=="on") {
  $price_join="";
 } else {
  $price_join=" and (a.price+b.price)<=".$_POST['price'];
 }
} else {
 $price_join=" and (a.price+b.price)<=".$_POST['price'];
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
if ($dates_join=="1"){
 $dates_join=" and a.date>='".$_POST['dpd1']."' and a.date<='".$_POST['dpd2']."'";
}

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
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd , e.name,a.dst from flights a
join flights b on a.dst=b.dst $flight_join $dates_join
join companies e on e.id=$companies_join
join destinations c on a.dst=c.airport and c.company=$destination_join
where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d
";

#echo $query;

$result = pg_query($db, $query);
pg_close();

function rand_color() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

#$base_colors=array('#3F5D7D', '#279B61', '#993333', '#A3E496', '#95CAE4', '#FFCC33', '#CC6699', '#CC3333', '#008AB8', '#FFFF7A');
#$i=0;
#$colors=array();
#while ($row = pg_fetch_row($result)) {
# if ($color[$row[4]] == "" ) { $color[$row[4]]=rand_color(); } #$base_colors[$i]; $i+=1; }
#}

pg_result_seek($result, 0);
$events=array();
while ($row = pg_fetch_row($result)) {
 $title=$row[4].": ".$row[7];
 #$cur_event=array("title" => $title, "start" => $row[2], "end" => $row[3], "color" => $color[$row[4]]);
 $cur_event=array("title" => $title, "start" => $row[2], "end" => $row[3], "color" => rand_color());
 if ($events[$row[4]] == "") { 
  $events[$row[4]]=array();
 } 
 array_push($events[$row[4]], $cur_event);
}

?>



<script>

var weekday = new Array(7);
weekday[0]=  "Sunday";
weekday[1] = "Monday";
weekday[2] = "Tuesday";
weekday[3] = "Wednesday";
weekday[4] = "Thursday";
weekday[5] = "Friday";
weekday[6] = "Saturday";

var sorted_flights= [
<?php
 pg_result_seek($result, 0);
 $first=1;
 while ($row = pg_fetch_row($result)) {
  if ($first==1) { $first=0; } else { echo ", "; }
  echo '{price:'.$row[7].', company:"'.$row[9].'", outdate:"'.$row[2].'", indate:"'.$row[3].'", outsrc:"Tel Aviv (TLV)", outdst:"'.$row[4].'",outdur:"02:50",indur:"02:40",outarr:"08:10",inarr:"20:00"}'."\n";
 }
?>
];

function fill_results() {
 $("#flight_results").hide("slow");
 $("#flight_results").empty();
 for (var f in sorted_flights) {
  var flight = sorted_flights[f];
  var outdate=new Date(flight['outdate']);
  var indate=new Date(flight['indate']);
  var data = 
    '<div class="result_row">'+
    '<img src="images/flight_card.jpg">'+
    '<div class="result_price"><i class="fa fa-ils"></i>'+flight['price']+'</div>'+
    '<div class="result_company"><img src="images/'+flight['company']+'.jpg" class="cmp_logo"></div>'+
    '<div class="result_outdate">'+weekday[outdate.getUTCDay()]+" "+outdate.getUTCDate()+"/"+outdate.getUTCMonth()+"/"+outdate.getUTCFullYear()+' 00:00</div>'+
    '<div class="result_indate">'+weekday[indate.getUTCDay()]+" "+indate.getUTCDate()+"/"+indate.getUTCMonth()+"/"+indate.getUTCFullYear()+' 00:00</div>'+
    '<div class="result_outsrc">Tel Aviv (TLV)</div>'+
    '<div class="result_insrc">'+flight['outdst']+'</div>'+
    '<div class="result_indst">Tel Aviv (TLV)</div>'+
    '<div class="result_outdst">'+flight['outdst']+'</div>'+
    '<div class="result_outdur">'+"02:50H"+'</div>'+
    '<div class="result_indur">'+"02:50H"+'</div>'+
    '<div class="result_outarr">'+"08:10"+'</div>'+
    '<div class="result_inarr">'+"20:00"+'</div>'+
    '<div class="result_icons"><span class="fa-stack fa-2x"><i class="fa fa-suitcase fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-white"></i></div>'+
    '</div>';
  $("#flight_results").append(data);
 }
 $("#flight_results").show("fast");
}

function SortByprice(a,b) {
  if (a.price < b.price) return -1;
  if (a.price > b.price) return 1;
  return 0;
}

function SortByoutdate(a,b) {
  if (a.outdate < b.outdate) return -1;
  if (a.outdate > b.outdate) return 1;
  return 0;
}

$(document).ready(function() {

    $('.wizz1Remark').tooltip({
     'show': true,
     html: true,
     placement: "left",
     'title': "Remarks:<BR> Include:<BR>Flight out and back.<BR><BR>Exclude:<BR>No Suitcases."
    });

    $('.orderTip').tooltip({
     'show': true,
     html: true,
     placement: "left",
     'title': "Order This Flight Now!"
    });

    $('#calendar').fullCalendar({
        height: "auto",
        events: [
<?php
/*
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
*/
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
      "order": [[ 7 ,"asc" ]]
    });
 
    sorted_flights.sort(SortByprice);
    fill_results();

});

$(window).load(function() {
	$(".loader").fadeOut("slow");
})


function ShowTable(){ 
 $("#cal-tab").hide("fast") ; 
 $("#table-tab").show("fast"); 
 $("#table-li").addClass("active"); 
 $("#cal-li").removeClass("active");
}

function ShowCal(){ 
 $("#table-tab").hide("fast") ; 
 $("#cal-tab").show("fast"); 
 $("#cal-li").addClass("active"); 
 $("#table-li").removeClass("active");
}

function sortBy(col) {
 $("#sort_bar").find("li").each(function() {
  var $this = $(this);
  $this.removeClass("active");
 });
 var s = document.getElementById("sb_"+col);
 $(s).addClass("active");
 sorted_flights.sort(eval("SortBy"+col));
 fill_results();
 
}

</script>

</HEAD<BODY>
<div class="loader"><h2 class="load_center load_txt">Loading your request...</h2><BR><i class="fa fa-spinner fa-5x fa-spin load_center"></i></div>
<div id="route_wrap">
<ul class="nav nav-pills">
  <li id="table-li" class="active" onclick="ShowTable();"><a href="#">Table View</a></li>
  <li id="cal-li" onclick="ShowCal();"><a href="#">Calendar View</a></li>
</ul>

<ul class="nav nav-pills" id="sort_bar">
 <div class="navbar-header">
  <a class="navbar-brand" href="#">Sort By: </a>
 </div>
 <li id="sb_price" class="active" onclick="sortBy('price');"><a href="#">Price</a></li>
 <li id="sb_outdate" onclick="sortBy('outdate');"><a href="#">Departure Date</a></li>
</ul>

<div id="table-tab" class="container">
<div class="row">
<div id="flight_results" name="flight_results" class="col-centered">
</div>
</div></div>
<!--
<div id="cal-tab">
<div id="calendar"></div>
-->
</div>
</div>
</BODY>
</HTML>
