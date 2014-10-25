<HTML>
<HEAD><TITLE>Search results - Gabi</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<script src="lib/jquery.dataTables.min.js"></script>
<!--<link rel="stylesheet" type="text/css" href="lib/table.css">-->
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
?>

<script>

var usd = <?php echo $usd; ?>;
var eur = <?php echo $eur; ?>;
var currency = "ils";
var Gpage = 1;
var perPage=25;
var onload = 1;
var TO_load = function() { if (onload==1) { $(".longwait").show(1500); } };

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

var sorted_flights= [];
var all_flights = [];

var pagesN = 0;

function fill_results(startI) {
 pagesN = Math.ceil(sorted_flights.length / perPage);
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
  var price=flight[currency];
  var data = 
    '<div class="result_row">'+
    '<div class="'+flight['special']+'"></div>'+
    '<img src="images/flight_card.jpg" >'+
    '<div class="result_price"><i class="fa fa-'+currency+'"></i>'+price+'</div>'+
    '<div class="result_company"><img src="images/airlines/'+flight['company']+'.jpg" class="cmp_logo"></div>'+
    '<div class="result_outdate">'+weekday[outdate.getUTCDay()]+" "+outdate.getUTCDate()+"/"+(outdate.getUTCMonth()+1)+"/"+outdate.getUTCFullYear()+' '+outdep+'</div>'+
    '<div class="result_indate">'+weekday[indate.getUTCDay()]+" "+indate.getUTCDate()+"/"+(indate.getUTCMonth()+1)+"/"+indate.getUTCFullYear()+' '+indep+'</div>'+
    '<div class="result_outsrc">Tel Aviv (TLV)</div>'+
    '<div class="result_insrc">'+flight['destination']+' ('+flight['airport']+')</div>'+
    '<div class="result_indst">Tel Aviv (TLV)</div>'+
    '<div class="result_outdst">'+flight['destination']+' ('+flight['airport']+')</div>'+
    '<div class="result_outdur">'+flight['outdur']+'H</div>'+
    '<div class="result_indur">'+flight['indur']+'H</div>'+
    '<div class="result_outarr">'+outarr+'</div>'+
    '<div class="result_inarr">'+inarr+'</div>'+
    '<div class="result_descr">'+flight['destination']+', '+flight['nights']+' Nights</div>'+
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
  var inta = parseInt(a.total);
  var intb = parseInt(b.total);
  if (inta < intb) return -1;
  if (inta > intb) return 1;
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
  if (flight['total'] != last) {
   last=flight['total'];
   n++;
  }
  if (n==4) { break ; }
  sorted_flights[f]['special']="cheapest";
 }
}

$(document).ready(function() {

    setTimeout(TO_load, 10000); 

    $("#waitcancel").click(function() { location.href='/';});

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


    $.ajax( {
        url: "http://2fly.cheap/flightjson.php",
        type: 'post',
        data: <?php echo json_encode($_POST); ?>,
        success: function(data) {
         sorted_flights = jQuery.parseJSON(data);
         all_flights = sorted_flights;
         pagesN = Math.ceil(sorted_flights.length / perPage);
         sortBy('price');
         mark_tops();
         displayPage(1);
	 $(".loader").fadeOut("slow");
         onload=0;
        } } ) ;


    $.ajax( { 
        url: "http://2fly.cheap/dstsumjson.php",
        type: 'post',
        data: <?php echo json_encode($_POST); ?>,
        success: function(data) { 
         var json = jQuery.parseJSON(data); 
         for (var s in json) {
          $('#dstsum').append('<li><a href="#" onclick="filterBy(\'dst\',\''+json[s].airport+'\');"><table style="width:200px;"><tr><td>' + json[s].destination + "</td><td style='text-align:right;'>"+ json[s].total + ' <i class="fa fa-ils "></i></td></tr></table></a></li>' );
         } } }) ;

    $.ajax( { 
        url: "http://2fly.cheap/datesumjson.php",
        type: 'post',
        data: <?php echo json_encode($_POST); ?>,
        success: function(data) {
         var json = jQuery.parseJSON(data); 
         for (var s in json) {
          $('#datesum').append('<li><a href="#" onclick="filterBy(\'date\',\''+json[s].ddate+'\');"><table style="width:200px;"><tr><td>' + json[s].ddate + "</td><td style='text-align:right;'>"+ json[s].total + ' <i class="fa fa-ils "></i></td></tr></table></a></li>' );
         } } }) ;

});

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

function filterBy(kind, filter) {
 var s = document.getElementById("fb_"+kind);
 $(s).addClass("active");
 var cur_flights = sorted_flights;
 sorted_flights = [];
 if (filter!="clear") { 
  for (var i in cur_flights) {
   if (kind=="dst") {
    if (cur_flights[i].airport==filter) {
     sorted_flights.push(cur_flights[i]);
    }
   } else if (kind=="date") {
    var cur_date = new Date(cur_flights[i].outdate);
    var cur_month;
    if (cur_date.getUTCMonth()+1<10) {
     cur_month = cur_date.getUTCFullYear().toString()+"-0"+(cur_date.getUTCMonth()+1).toString();
    } else {
     cur_month = cur_date.getUTCFullYear().toString()+"-"+(cur_date.getUTCMonth()+1).toString();
    }
    if (cur_month==filter) {
     sorted_flights.push(cur_flights[i]);
    }
   }
  }
 } else { 
  sorted_flights = all_flights; 
  $("#head_bar").find("li").each(function() {
   var $this = $(this);
   if ($this.hasClass("fb")) {
    $this.removeClass("active");
   }
  });
 }
 mark_tops();
 sortBy('price');
}

</script>

</HEAD<BODY>
<div class="loader"><h2 class="load_center load_txt">Loading your request...</h2><BR><i class="fa fa-spinner fa-5x fa-spin load_center load_fa"></i>
 <div class=" load_center longwait" style="display:none;">
  Your Request still under control.<BR>It can take few more minutes.<BR>You can cancel the request and run another one with more filters to get it faster,<BR>Or, you can just wait...<BR><button id="waitcancel" name="waitcancel" class="btn btn-primary ">Cancel and Go Back</button>
 </div>
</div>

<div id="route_wrap">
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

 <div class="navbar-header">
  <a class="navbar-brand" href="#">Filters: </a>
 </div>
 <li class="dropdown fb" id="fb_dst" >
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">By Destination<span class="caret"></span></a>
  <ul class="dropdown-menu" role="menu" id="dstsum" name="dstsum">
  </ul>
 </li>
 <li class="dropdown fb" id="fb_date">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">By Month<span class="caret"></span></a>
  <ul class="dropdown-menu" role="menu" id="datesum" name="datesum">
  </ul>
 </li>
 <li onclick="filterBy('', 'clear');"><a href="#">Clear Filters</a></li>
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
