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
$usd=fgets(fopen("currencies/usd", "r"));
$eur=fgets(fopen("currencies/eur", "r"));

#if (array_key_exists('days', $_POST)) {
 #$days_a=explode(",", $_POST['days']);
 #$minDays=$days_a[0];
 #$maxDays=$days_a[1];
#} else {
 $minDays=$_POST['minDays'];
 $maxDays=$_POST['maxDays'];
#}

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
select d.* from ( select a.scrape_time ast, b.scrape_time bst, a.date adt, b.date bdt, c.destination cdst, a.price apr, b.price bpr, a.price+b.price total , (b.date - a.date) dd , e.name,a.dst, a.dep_time, a.arr_time, b.dep_time, b.arr_time, a.dst from flights a
join flights b on a.dst=b.dst $flight_join $dates_join
join companies e on e.id=$companies_join and e.id=a.company and e.id=b.company
join destinations c on a.dst=c.airport and c.company=$destination_join
where a.direction=1 and b.direction=2 and (b.date - a.date)>=".$minDays." and (b.date - a.date)<=".$maxDays." ".$price_join.") d
";

echo $query;

$result = pg_query($db, $query);
pg_close();

function rand_color() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

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

var usd = <?php echo $usd; ?>;
var eur = <?php echo $eur; ?>;
var currency = "ils";
var Gpage = 1;
var perPage=25;

function displayPage(page) {
 Gpage=page;
 var startI = (page-1)*perPage; 
 fill_results(startI); 
}

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
  echo '{price:'.$row[7].', company:"'.$row[9].'", outdate:"'.$row[2].'", indate:"'.$row[3].'", outsrc:"Tel Aviv (TLV)", outdst:"'.$row[4].'", outairport:"'.$row[15].'",outdur:"XXX",indur:"XXX",outdep:"'.$row[11].'",outarr:"'.$row[12].'",indep:"'.$row[13].'",inarr:"'.$row[14].'", special:"", nights:'.$row[8].', usd:'.floor($row[7]/$usd).', eur:'.floor($row[7]/$eur).', ils:'.$row[7].'}'."\n";
 }
?>
];

var pagesN = Math.ceil(sorted_flights.length / perPage);

function fill_results(startI) {
 $("#flight_results").empty();
 var stopI=startI+perPage;
 if (stopI > sorted_flights.length) { stopI=sorted_flights.length-1; }
 for (f=startI ; f<stopI ; f++) {
  var flight = sorted_flights[f];
  var outdate=new Date(flight['outdate']);
  var indate=new Date(flight['indate']);
  var outdep = flight['outdep'].split(":")[0]+":"+flight['outdep'].split(":")[1]
  var indep = flight['indep'].split(":")[0]+":"+flight['indep'].split(":")[1]
  var outarr = flight['outarr'].split(":")[0]+":"+flight['outarr'].split(":")[1]
  var inarr = flight['inarr'].split(":")[0]+":"+flight['inarr'].split(":")[1]
  var price= price=flight[currency];
  var data = 
    '<div class="result_row">'+
    '<div class="'+flight['special']+'"></div>'+
    '<img src="images/flight_card.jpg" >'+
    '<div class="result_price"><i class="fa fa-'+currency+'"></i>'+price+'</div>'+
    '<div class="result_company"><img src="images/'+flight['company']+'.jpg" class="cmp_logo"></div>'+
    '<div class="result_outdate">'+weekday[outdate.getUTCDay()]+" "+outdate.getUTCDate()+"/"+(outdate.getUTCMonth()+1)+"/"+outdate.getUTCFullYear()+' '+outdep+'</div>'+
    '<div class="result_indate">'+weekday[indate.getUTCDay()]+" "+indate.getUTCDate()+"/"+(indate.getUTCMonth()+1)+"/"+indate.getUTCFullYear()+' '+indep+'</div>'+
    '<div class="result_outsrc">Tel Aviv (TLV)</div>'+
    '<div class="result_insrc">'+flight['outdst']+' ('+flight['outairport']+')</div>'+
    '<div class="result_indst">Tel Aviv (TLV)</div>'+
    '<div class="result_outdst">'+flight['outdst']+' ('+flight['outairport']+')</div>'+
    '<div class="result_outdur">'+flight['outdur']+'H</div>'+
    '<div class="result_indur">'+flight['indur']+'H</div>'+
    '<div class="result_outarr">'+outarr+'</div>'+
    '<div class="result_inarr">'+inarr+'</div>'+
    '<div class="result_descr">'+flight['outdst']+', '+flight['nights']+' Nights</div>'+
    '<div class="result_icons"><span class="fa-stack fa-2x"><i class="fa fa-suitcase fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-white"></i></div>'+
    '</div>';
  $("#flight_results").append(data);
 }
 fill_pages();
}

function fill_pages() {
 $("#pagination").empty();
 var data = "";
 var lastdata= "";

 if (Gpage>1) {
  data=data+'<li><a href="#" onclick="displayPage(Gpage-1);">&laquo;</a></li>';
 }

 if (Gpage>3) {
  data=data+'<li><a href="#" onclick="displayPage(1);">1</a></li>';
  data=data+'<li><a href="#">...</a></li>';
 } else if (Gpage==3) {
  data=data+'<li><a href="#" onclick="displayPage(1);">1</a></li>';
 }

 if (Gpage>1) { 
  data=data+'<li><a href="#" id="page_'+(Gpage-1)+'" onclick="displayPage('+(Gpage-1)+');">'+(Gpage-1)+'</a></li>'; 
 } else {
  data=data+'<li class="disabled"><a href="#" onclick="displayPage(Gpage-1);">&laquo;</a></li>';
 }

 data=data+'<li class="active"><a href="#" id="page_'+Gpage+'" onclick="displayPage('+Gpage+');" class="active">'+Gpage+'</a></li>';

 if (Gpage<pagesN-1) { 
  data=data+'<li><a href="#" id="page_'+(Gpage+1)+'" onclick="displayPage('+(Gpage+1)+');">'+(Gpage+1)+'</a></li>'; 
  lastdata='<li><a href="#" onclick="displayPage('+(Gpage+1)+');">&raquo;</a></li>';
 } else if (Gpage==pagesN-1) { 
  lastdata='<li><a href="#" onclick="displayPage('+(Gpage+1)+');">&raquo;</a></li>';
 } else {
  lastdata='<li class="disabled"><a href="#" onclick="displayPage('+(Gpage+1)+');">&raquo;</a></li>';
 }

 if (Gpage < pagesN-2) {
  data=data+'<li><a href="#">...</a></li>';
  data=data+'<li><a href="#" onclick="displayPage('+pagesN+');">'+pagesN+'</a></li>';
 } else {
  if (Gpage == pagesN-1) {
   data=data+'<li><a href="#" id="page_'+(Gpage+1)+'" onclick="displayPage('+(Gpage+1)+');">'+(Gpage+1)+'</a></li>';
  } else if (Gpage != pagesN){
   data=data+'<li><a href="#" id="page_'+(Gpage+2)+'" onclick="displayPage('+(Gpage+2)+');">'+(Gpage+2)+'</a></li>';
  }
 }
 data=data+lastdata;
 $("#pagination").append(data);
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

function mark_tops() {
 sorted_flights.sort(SortByoutdate);
 var n=0;
 var last=0;
 for (var f in sorted_flights) {
  var flight = sorted_flights[f];
  if (flight['outdate'] != last) {
   last=flight['outdate'];
   n++;
  }
  if (n==4) { break ; }
  sorted_flights[f]['special']="earliest";
 }
 sorted_flights.sort(SortByprice);
 var n=0;
 var last=0;
 for (var f in sorted_flights) {
  var flight = sorted_flights[f];
  if (flight['price'] != last) {
   last=flight['price'];
   n++;
  }
  if (n==4) { break ; }
  sorted_flights[f]['special']="cheapest";
 }
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
 
    mark_tops();
    //sorted_flights.sort(SortByprice);
    displayPage(1);

});

$(window).load(function() {
	$(".loader").fadeOut("slow");
})


function changeCur(cur) {
 currency = cur;
 displayPage(Gpage);
 $("#head_bar").find("li").each(function() {
  var $this = $(this);
  if ($this.hasClass("cur")) {
   $this.removeClass("active");
  }
 });
 var s = document.getElementById("cur_"+cur);
 $(s).addClass("active");
}

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
 $("#head_bar").find("li").each(function() {
  var $this = $(this);
  if ($this.hasClass("sb")) {
   $this.removeClass("active");
  }
 });
 var s = document.getElementById("sb_"+col);
 $(s).addClass("active");
 sorted_flights.sort(eval("SortBy"+col));
 displayPage(1);
 
}

</script>

</HEAD<BODY>
<div class="loader"><h2 class="load_center load_txt">Loading your request...</h2><BR><i class="fa fa-spinner fa-5x fa-spin load_center"></i></div>
<div id="route_wrap">
<!--
<ul class="nav nav-pills">
  <li id="table-li" class="active" onclick="ShowTable();"><a href="#">Table View</a></li>
  <li id="cal-li" onclick="ShowCal();"><a href="#">Calendar View</a></li>
</ul>
-->

<ul class="nav nav-pills" id="head_bar">
 <div class="navbar-header">
  <a class="navbar-brand" href="#">Sort By: </a>
 </div>
 <li id="sb_price" class="sb active" onclick="sortBy('price');"><a href="#">Price</a></li>
 <li id="sb_outdate" class="sb" onclick="sortBy('outdate');"><a href="#">Departure Date</a></li>
 
 <div class="navbar-header">
  <a class="navbar-brand" href="#">Currency: </a>
 </div>
 <li id="cur_ils" class="cur active" onclick="changeCur('ils');"><a href="#"><i class="fa fa-ils "></i></a></li>
 <li id="cur_usd" class="cur" onclick="changeCur('usd');"><a href="#"><i class="fa fa-dollar "></i></a></li>
 <li id="cur_eur" class="cur" onclick="changeCur('eur');"><a href="#"><i class="fa fa-euro"></i></a></li>
</ul>

<div id="table-tab" class="container1">
<ul id="pagination" class="pagination ">
</ul>
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
