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
$("#priceslider").Link('lower').to('-inline-<div class="pricetooltip"></div>', function ( value ) { $(this).html( '<span>' + value + ' <i class="fa fa-ils"></i></span>'); });
$('#priceslider').Link('lower').to($('#price'));

$('#dayslider').noUiSlider({
 start: [ 4, 6 ],
 connect: true,
 range: { 'min': 2, 'max': 20 },
 step: 1,
 format: { to: function(val) { return parseInt(val); }, from: function(val) { return val; }}
});
$("#dayslider").Link('lower').to('-inline-<div class="mindaytip"></div>', function ( value ) { $(this).html( '<span>' + value + ' Nights</i></span>'); });
$("#dayslider").Link('upper').to('-inline-<div class="maxdaytip"></div>', function ( value ) { $(this).html( '<span>' + value + ' Nights</i></span>'); });
$('#dayslider').Link('lower').to($('#minDays'));
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
<div class="custom-nav" role="navigation">
   <div class="navbar-header">
      <a href="/"><img src="images/paperplane.png" width="150px"></a>
   </div>
   <div class="navbar-header navbar-header-txt">
      <a href="/"><span class="txth1">CHEAPEST </span><span class="txth2">FLIGHTS</span></a>
   </div>
   <span class="bar-square">
    <span class="square_elem">
     FLIGHTS
    </span>
    <span class="square_elem">
     HOTELS
    </span>
    <span class="square_elem">
     EXPLORE
    </span>
   </span>
</div>
<div class=row>
 <div class="jumbotron jumbotron-form">
  <div class=container>
   <div class="row">
    <div class="col-md-12">
     <h1 class="text-capitalize head1"><B>Find The Cheapest Flights From Tel Aviv!</B></h1>
    </div>
   </div>
   
   <div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-1 midrow"></div>
    <div class=col-md-6>
<link href='http://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
     <span class="text-capitalize head2">No matter when, No matter where, Only price matter.</span>
    </div>
    <div class="col-md-1 midrow"></div>
    <div class="col-md-2"></div>
   </div>

   <div class="row">
    <div class="well form-well">
     <form name=routeForm method=post action=getroute.php class="form-horizontal" id=routeForm>

      <div class="row">
       <div class="buttons-one-route">
        <span class="activebutton"><button type='button' class="roundtripBtn buttonup" id="roundtrip" name="roundtrip"><i class="fa fa-plane fa-lg"></i> Round-trip</button></span><button class="onewayBtn buttonup" id="oneway" name="oneway" type='button'><i class="fa fa-plane fa-lg"></i> One-way</button>
       </div>
      </div>
    
      <div class="row informrow">
       <div class="col-sm-4">
        <div class="input-group" id="companygroup">
         <span class="input-group-addon companyspan">Airline</span>
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

       <div class="col-sm-4">
        <div class="input-group" id="fromgroup">
         <span class="input-group-addon fromspan">From</span>
         <select name=src id=src class="form-control "> <option value="TLV">Tel Aviv (TLV)</option> </select>
        </div>
       </div>

       <div class="col-sm-4">
        <div class="input-group" id="fromgroup">
         <span class="input-group-addon tospan">To</span>
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

      </div>

      <div class="row informrow">
        <div class="col-lg-12">
         <div class="input-group" id="datesgroup">
          <span class="input-group-addon datespan datetxtspan">Dates</span>
          <div class="datespanin">
           <div class="row datesrow">
            <div class="col-lg-2">
             <div class="checkboxP"><input type="checkbox" checked id="AllDates" name="AllDates"><label for="AllDates"></label><span class="alldatestxt">All</span></div>
            </div>
            <div class="col-lg-5">
             <div class="input-group">
              <span class="input-group-addon datespans dpd1span">Start</span>
              <input type="text" class="form-control dtpicker" name="dpd1" id="dpd1" disabled/> 
             </div>
            </div>
            <div class="col-lg-5">
             <div class="input-group">
              <span class="input-group-addon datespane dpd2span">End</span>
              <input type="text" class="form-control dtpicker" name="dpd2" id="dpd2" disabled/> 
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
      </div>

      <div class="row informrow">
        <div class="col-lg-12">
         <div class="input-group" id="nightsgroup">
          <span class="input-group-addon nightspan nightstxtspan">Nights</span>
          <div class="nightspanin">
           <div class="row nightsrow">
            <div class="col-sm-1">
            </div>
            <div class="col-sm-10">
             <div id="dayslider"></div>
             <input type='hidden' id='minDays' name='minDays'>
             <input type='hidden' id='maxDays' name='maxDays'>
            </div>
            <div class="col-sm-1">
            </div>
           </div>
          </div>
         </div>
        </div>
      </div>

      <div class="row informrow">
        <div class="col-lg-12">
         <div class="input-group" id="pricegroup">
          <span class="input-group-addon pricespan pricetxtspan">Price</span>
          <div class="pricespanin">
           <div class="row pricerow">
            <div class="col-lg-2">
             <div class="checkboxP"><input type="checkbox" checked id="AllPrice" name="AllPrice"><label for="AllPrice"></label><span class="allpricetxt">All</span></div>
            </div>
       <div class="col-sm-8">
        <div id="priceslider" disabled></div>
        <input type='hidden' id='price' name='price'>
       </div>
           </div>
          </div>
         </div>
        </div>
      </div>
  
      <div class="form-group custom_btn_col">
       <div class="col-sm-12 text-center">
        <button id='submitSearch' class="btn btn-primary btn-lg search_btn custom_btn form-control"> SEARCH FLIGHTS <i class="fa fa-paper-plane-o"></i> </button>
       </div>

       <div class="row">
        <div class="col-sm-12">
         <div class="form-footer">
          Bla Bla Bla
         </div>
        </div>
       </div>
      </div>
  
     </form>
    </div>
   </div>
   </div>
  </div>
 </div>
</div>
</BODY></HTML>
