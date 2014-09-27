<HTML><HEAD><TITLE>2 Fly Cheap</TITLE>
<script src='lib/analytics.js'></script>
<script src='lib/jquery.min.js'></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<link href="lib/slider/css/slider.css" rel="stylesheet">
<script src="lib/slider/js/bootstrap-slider.js"></script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<link href="lib/datepicker/css/datepicker3.css" rel="stylesheet">
<script src="lib/datepicker/js/bootstrap-datepicker.js"></script>

<style>
body { width:100%; padding:64px 20px 0 20px;}
#days { width: 300px; }
#price { width: 400px; }
.container {
   padding-right: 15px;
   padding-left: 15px;
   margin-right: 15px;
   margin-left: 15px;
}
.jumbotron-form {
 padding-top:0px;
 padding-bottom:10px;
 margin-bottom: 0px;
 border-bottom-left-radius: 10px;
 border-bottom-right-radius : 10px;
}
.custom_btn { width : 250px ; height: 50px ;  margin:10px 0 0 250px; }
#adsense-left { padding-top: 10px;}
#adsense-foot { padding-top: 10px; margin: 30px 0 0 5px;}
.well { margin-bottom: 5px; }
.jumbotron-head {
 margin: 0px; 
 padding:0px;
 border-top-left-radius: 10px;
 border-top-right-radius: 10px;
}
.navbar-nav>.active>a { border-radius: 5px; }
.cnt-head {padding: 0 0 0 60px; width: 100%;}
.custom-nav { padding: 5px 20px 5px 20px ;}
h1.head1 {margin-top:5px; }

.slider-handle {
width: 25px;
height: 25px;
top: -3px;
}
.slider-handle.round {
border-radius: 10px;
}

.slider-selection {
background-image: linear-gradient(to bottom, #ABC2FF, #B3B7FF);
}
</style>


<?php
 $conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
 $db = pg_pconnect($conn_string);
 $companies_result=pg_query($db, "select name from companies");
 $companies=Array();
 while ($row = pg_fetch_row($companies_result)) {
  array_push($companies, $row[0]);
 }
 $dst_results=pg_query($db, "select * from destinations");
 pg_result_seek($dst_results, 0);
 $destinations=Array();
 
 while ($row = pg_fetch_row($dst_results)) {
  if ($destinations[$row[0]]=="") { $destinations[$row[0]]=array(); }
  $cur_dst=array("airport" => $row[1], "destination" => $row[2]);
  array_push($destinations[$row[0]], $cur_dst);
 }
 
?>
 
<script>

var nowTemp = new Date();
var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

$(document).ready(function() {

    $('#AllDates').change(function() {
     if (this.checked) {
      $('#dpd1').attr("disabled", "disabled");
      $('#dpd2').attr("disabled", "disabled");
     } else {
      $('#dpd1').removeAttr("disabled");
      $('#dpd2').removeAttr("disabled");
      var todayDate = new Date();
      var endDate = new Date();
      endDate.setMonth(endDate.getMonth() + 6);
      $('#dpd1').datepicker('setDate', todayDate);
      $('#dpd2').datepicker('setDate', endDate);
     }
    });
 
    $('.dtpicker').datepicker({
      startDate: '0',
      autoclose: true,
      todayBtn: "linked",
      format: "yyyy-mm-dd",
      todayHighlight: true
    });

    $('#days').slider({
    })
    .on('slide', function(e) { 
     var cur_val=$('#days').val().split(",");
     $('#minDaysval').text(cur_val[0]);
     $('#maxDaysval').text(cur_val[1]);
    });

    $('#price').slider({
    })
    .on('slide', function(e) { 
     var cur_val=$('#price').val();
     $('#priceval').text(cur_val);
    });

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
    ?>
    };

    $("#company").on("change", function(e) {
     var $select_dst = $("#dst");
     $select_dst.empty();
     $select_dst.append($("<option></option>").attr("value", "ALL").text("All Destinations"));
     if ($(this).val()=="ALL") {
      var cur_key;
      for (cur_key in destinations) {
       var cur_cmp = destinations[cur_key];
       var x;
       for (x in cur_cmp) {
        $select_dst.append($("<option></option>").attr("value", x).text(cur_cmp[x]+" ("+x+")"));
       }
      }
     } else {
      var cur_cmp = destinations[$(this).val()];
      var x;
      for (x in cur_cmp) {
       $select_dst.append($("<option></option>")
        .attr("value", x).text(cur_cmp[x]+" ("+x+")"));
      }
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
<div class="row ">
 <div class="jumbotron jumbotron-head">
  <div class="container cnt-head">
   <h1 class="text-capitalize head1">Find The Cheapest Flights From Tel Aviv!</h1>
  </div>
 </div>
</div>
<div class=row>
 <div class="jumbotron jumbotron-form">
  <div class=container>
   <div class=col-md-4>
    <div id=adsense-left>
     <!-- 2fly-336x280 
     <ins class="adsbygoogle" style="display:inline-block;width:336px;height:280px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="3770494841"></ins>
-->
<!-- 2fly600x300 -->
<ins class="adsbygoogle" style="display:inline-block;width:300px;height:600px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="8172171649"></ins>
     <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
    </div>
   </div>
   <div class=col-md-8>
    <p class="lead text-capitalize">No matter when, No matter where, Only price matter.</p>

    <form name=routeForm method=post action=getroute.php class="form-horizontal">

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
       foreach ($companies as $cmp) {
        foreach ($destinations[$cmp] as $dcmp) { 
         echo '<option value="'.$dcmp['airport'].'">'.$dcmp['destination'].' ('.$dcmp['airport'].')</option>'."\n";
        }
       }
       ?>
      </select>
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2>
      <b>Select Days: </b>
     </div>
     <div class="col-sm-10">
      Minimum <span id=minDaysval>4</span> Days
      <input type="text" value="4,6" data-slider-min="2" data-slider-max="14" data-slider-step="1" data-slider-value="[4,6]" id="days" name="days">
      Maximum <span id=maxDaysval>6</span> days
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2>
      <b>Max price: </b>
     </div>
     <div class="col-sm-10">
      <span id="priceval">600</span>
      <input type="text" value="600" data-slider-min="200" data-slider-max="2000" data-slider-step="50" data-slider-value="600" id="price" name="price" >
      Shekels
     </div>
    </div>

    <div class="well form-group">
     <div class=col-sm-2 style=" padding-top: 6px;">
      <b>Dates Limit: </b>
     </div>
     <div class=col-sm-2 style=" padding-top: 6px;">
<label>
<input type="checkbox" checked id="AllDates" name="AllDates"> All Dates
</label>
     </div>
     <div class="col-sm-8">
      <div class="input-group" id="datepicker"> 
       <span class="input-group-addon">From</span>
       <input type="text" class="form-control dtpicker" name="dpd1" id="dpd1" disabled/> 
       <span class="input-group-addon">To</span>
       <input type="text" class="form-control dtpicker" name="dpd2" id="dpd2" disabled/> 
      </div>
     </div>
    </div>

    <input type=submit value=Search class="btn btn-primary btn-lg custom_btn">

    </form>
    <div class=row>
     <div id=adsense-foot>
      <!-- 2fly-footer -->
      <ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="8200694443"></ins>
      <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>

</BODY></HTML>
