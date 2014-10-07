<HTML><HEAD><TITLE>2 Fly Cheap</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<!-- <link href="lib/slider/css/slider.css" rel="stylesheet">
<script src="lib/slider/js/bootstrap-slider.js"></script> -->
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<link href="lib/datepicker/css/datepicker3.css" rel="stylesheet">
<link href="lib/searchMain.css" rel="stylesheet">
<script src="lib/datepicker/js/bootstrap-datepicker.js"></script>
<script src="lib/jquery.nouislider.min.js"></script>
<script src="lib/jquery.liblink.js"></script>
<link href="lib/jquery.nouislider.min.css" rel="stylesheet">


<?php
 $conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
 $db = pg_pconnect($conn_string);
 $companies_result=pg_query($db, "select name from companies");
 $companies=Array();
 while ($row = pg_fetch_row($companies_result)) {
  array_push($companies, $row[0]);
 }
 $dst_results=pg_query($db, "select * from destinations order by destination");
 pg_result_seek($dst_results, 0);
 $destinations=Array();
 
 while ($row = pg_fetch_row($dst_results)) {
  if ($destinations[$row[0]]=="") { $destinations[$row[0]]=array(); }
  $cur_dst=array("airport" => $row[1], "destination" => $row[2]);
  array_push($destinations[$row[0]], $cur_dst);
 }
$all_dst_r=pg_query($db, "select distinct airport, destination from destinations order by destination");
$destinations['ALL']=array();
while ($row = pg_fetch_row($all_dst_r)) {
 $cur_dst=array("airport" => $row[0], "destination" => $row[1]);
 array_push($destinations['ALL'], $cur_dst);
}
 
?>
 
<script>

var nowTemp = new Date();
var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

$(document).ready(function() {

    $('#submitSearch').click(function() {
     $('#routeForm')[0].submit();
    });

/*
    $('#price').slider({
    })
    .on('slide', function(e) { 
     var cur_val=$('#price').val();
     $('#priceval').text(cur_val);
    });

    $('#AllPrice').change(function() {
     if (this.checked) {
      $('#price').slider("disable");
     } else {
      $('#price').slider("enable");
     }
    });
*/

    $('#AllPrice').change(function() {
     if (this.checked) {
      $('#priceslider').attr("disabled", 'disabled');
     } else {
      $('#priceslider').removeAttr("disabled");
     }
    });

$('#priceslider').noUiSlider({
 start: [ 6 ],
 range: { 'min': 2, 'max': 200 },
 connect: 'lower',
 step: 1,
 format: { to: function(val) { return parseInt(val)*100; }, from: function(val) { return val; }}
});
$('#priceslider').Link('lower').to($('#priceval'));
$('#priceslider').Link('lower').to($('#price'));

$('#dayslider').noUiSlider({
 start: [ 4, 6 ],
 connect: true,
 range: { 'min': 2, 'max': 20 },
 step: 1,
 format: { to: function(val) { return parseInt(val); }, from: function(val) { return val; }}
});
$('#dayslider').Link('lower').to($('#minDaysval'));
$('#dayslider').Link('lower').to($('#minDays'));
$('#dayslider').Link('upper').to($('#maxDaysval'));
$('#dayslider').Link('upper').to($('#maxDays'));



    $('#AllDates').change(function() {
     if (this.checked) {
      $('#dpd1').attr("disabled", "disabled");
      $('#dpd2').attr("disabled", "disabled");
     } else {
      $('#dpd1').removeAttr("disabled");
      $('#dpd2').removeAttr("disabled");
      var todayDate = new Date();
      $('#dpd1').datepicker('setDate', todayDate);
      $('#dpd2').datepicker('setDate', todayDate);
     }
    });

    $('#dpd1').change( function() {
      var endDate = new Date($(this).val());
      endDate.setDate(endDate.getDate() + 1);
      $('#dpd2').datepicker('setDate', endDate);
    });
 
    $('.dtpicker').datepicker({
      startDate: '0',
      autoclose: true,
      todayBtn: "linked",
      format: "yyyy-mm-dd",
      todayHighlight: true
    });

/*
    $('#days').slider({
    })
    .on('slide', function(e) { 
     var cur_val=$('#days').val().split(",");
     $('#minDaysval').text(cur_val[0]);
     $('#maxDaysval').text(cur_val[1]);
    });
*/
    <?php
     $dst_companies = array_keys($destinations);
    ?>
    var destinations = {
    <?php
     foreach ($dst_companies as $cur_dst_cmp) {
      echo '"'.$cur_dst_cmp.'": {';
      $first=1;
      foreach($destinations[$cur_dst_cmp] as $dst) {
       if ($first!=1) { echo ", " ; } else { $first=0; }
       echo "'".$dst['airport']."':'".$dst['destination']."' ";
      }
      echo "},\n";
     }
     echo '"ALL": {';
     $first=1;
     foreach($destinations['ALL'] as $dst) {
      if ($first!=1) { echo ", " ; } else { $first=0; }
      echo "'".$dst['airport']."':'".$dst['destination']."' ";
     }
     echo "},\n";
    ?>
    };

    $("#company").on("change", function(e) {
     var $select_dst = $("#dst");
     $select_dst.empty();
     $select_dst.append($("<option></option>").attr("value", "ALL").text("All Destinations"));
 //    if ($(this).val()=="ALL") {
 //     var cur_key;
 //     for (cur_key in destinations) {
 //      var cur_cmp = destinations[cur_key];
 //      var x;
 //      for (x in cur_cmp) {
 //       $select_dst.append($("<option></option>").attr("value", x).text(cur_cmp[x]+" ("+x+")"));
 //      }
 //     }
 //    } else {
      var cur_cmp = destinations[$(this).val()];
      var x;
      for (x in cur_cmp) {
       $select_dst.append($("<option></option>")
        .attr("value", x).text(cur_cmp[x]+" ("+x+")"));
 //     }
     }
    }); 
});

</SCRIPT>
</HEAD><BODY>
<nav class="navbar navbar-default navbar-fixed-top custom-nav" role="navigation">
   <div class="navbar-header">
      <a class="navbar-brand" href="#">Search By: </a>
   </div>
   <div>
      <ul class="nav navbar-nav">
         <li class="active"><a href="#">Cheapest RoundTrip</a></li>
         <li><a href="#">One Way Tickets</a></li>
         <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
               Test
               <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
               <li><a href="#">Gabi A</a></li>
               <li><a href="#">Gabi B</a></li>
               <li><a href="#">Gabi C</a></li>
               <li class="divider"></li>
               <li><a href="#">Gabi D</a></li>
               <li class="divider"></li>
               <li><a href="#">Gabi E</a></li>
            </ul>
         </li>
      </ul>
   </div>
   <div>
    <p class="navbar-text navbar-right">Test Right
     <a href="#" class="navbar-link">Link</a>
    </p>
   </div>
</nav>
<div class=row>
 <div class="jumbotron jumbotron-form">
  <div class=container>
   <div class="row">
    <div class="col-md-12">
     <h1 class="text-capitalize head1"><B>Find The Cheapest Flights From Tel Aviv!</B></h1>
    </div>
   </div>
   <div class=col-md-4>
    <div id=adsense-left>
     <!-- 2fly600x300 
     <ins class="adsbygoogle" style="display:inline-block;width:300px;height:600px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="8172171649"></ins>
     <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
-->
    </div>
   </div>
   <div class=col-md-6>
    <p class="lead text-capitalize white-txt">No matter when, No matter where, Only price matter.</p>

    <div class="well form-well">
    <form name=routeForm method=post action=getroute.php class="form-horizontal" id=routeForm>

    <div class="form-group well">
     <div class=col-sm-2>
      <label for="company" class="control-label">Company: </label>
     </div>
     <div class="col-sm-10">
      <select name=company id=company class="form-control">
       <option value="ALL">All Companies</option>
       <option value="wizz">WizzAir</option>
       <option value="easyjet">Easyjet</option>
       <option value="up">Up by Elal</option>
       <option value="elal">Elal</option>
       <option value="sundor">SunDor</option>
       <option value="airmed">Air Mediterranee</option>
      </select>
     </div>
    </div>

    <div class="form-group well">
     <div class=col-sm-2>
      <label for="dst" class="control-label">Destination:</label><BR>
     </div>
     <div class="col-sm-10">
      <select name=dst id=dst class="form-control">
       <option value="ALL">All Destinations</option>
       <?php
        foreach ($destinations['ALL'] as $dcmp) { 
         echo '<option value="'.$dcmp['airport'].'">'.$dcmp['destination'].' ('.$dcmp['airport'].')</option>'."\n";
        }
       ?>
      </select>
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2>
      <b>Nights: </b>
     </div>
     <div class="col-sm-2">
      Min <span id=minDaysval>4</span> <input type=hidden name=minDays id=minDays>
     </div>
     <div class="col-sm-6">
      <div id="dayslider"></div>
<!--      <input type="text" value="4,6" data-slider-min="2" data-slider-max="14" data-slider-step="1" data-slider-value="[4,6]" id="days" name="days"> -->
     </div>
     <div class="col-sm-2">
      Max <span id=maxDaysval>6</span> <input type=hidden name=maxDays id=maxDays>
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2>
      <b>Price: </b>
     </div>
     <div class=col-sm-2 style=" padding-top: 6px;">
      <label> <input type="checkbox" checked id="AllPrice" name="AllPrice">All</label>
     </div>
     <div class="col-sm-2">
      <span id="priceval"></span><input type="hidden" id="price" name="price"> <i class="fa fa-ils"></i>
     </div>
     <div class="col-sm-6">
      <div id="priceslider" disabled></div>
<!--      <input type="text" value="600" data-slider-min="200" data-slider-max="10000" data-slider-step="100" data-slider-value="600" id="price" name="price" data-slider-enabled="false"> -->
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2 style=" padding-top: 6px;">
      <b>Dates: </b>
     </div>
     <div class=col-sm-2 style=" padding-top: 6px;">
      <label> <input type="checkbox" checked id="AllDates" name="AllDates">All</label>
     </div>
     <div class="col-sm-8 ">
      <div class="input-group" id="datepicker"> 
       <span class="input-group-addon datespan">From</span>
       <input type="text" class="form-control dtpicker" name="dpd1" id="dpd1" disabled/> 
       <span class="input-group-addon datespan">To</span>
       <input type="text" class="form-control dtpicker" name="dpd2" id="dpd2" disabled/> 
      </div>
     </div>
    </div>

    <div class="form-group custom_btn_col">
     <div class="col-sm-12 text-center">
      <button id='submitSearch' class="btn btn-primary btn-lg custom_btn form-control"> Search <i class="fa fa-paper-plane-o"></i> </button>
     </div>
    </div>

    </form>
    </div>
    <div class=row>
     <div id=adsense-foot>
      <!-- 2fly-footer 
      <ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="8200694443"></ins>
      <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
-->
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
</BODY></HTML>
