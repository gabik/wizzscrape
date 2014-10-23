<?php
include 'db_con_string.php';
$db = pg_pconnect($conn_string);
$companies_result=pg_query($db, "select name from companies");
$companies=Array();
$i=0;
while ($row = pg_fetch_row($companies_result)) {
 $companies[$i]=$row[0];
 $i+=1;
}
?>
<HTML><HEAD><TITLE>Cal View - Wizz Gabi</TITLE>
<script src='lib/analytics.js'></script>
<link rel='stylesheet' href='lib/fullcalendar.min.css' />
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='lib/jquery.qtip.min.js'></script>
<script src='lib/fullcalendar.min.js'></script>
<link rel="stylesheet" type="text/css" href="lib/jquery.qtip.min.css">
<link rel="stylesheet" type="text/css" href="lib/calendar.css">

<style>

<?php

$dst_results=pg_query($db, "select * from destinations");
while ($row = pg_fetch_row($dst_results)){
 echo ".".$row[0].$row[1].", ";
}
echo "fake { float: left; display: inline-block;padding: 3px;  margin-right: 1px; }\n";

pg_result_seek($dst_results, 0);
for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex($i).(string)dechex($i).(string)dechex(7+$i).(string)dechex(7+$i);
}
$i=0;
while ($row = pg_fetch_row($dst_results)){
 if ($row[0]=="wizz") {
 echo ".".$row[0].$row[1]." { background-color: ".$bcolor[$i]."; color: #fff; border: solid 1px ".$bcolor[$i]."; }\n";
  $i+=1;
 }
}
pg_result_seek($dst_results, 0);

for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(3+$i).(string)dechex(3+$i).(string)dechex($i).(string)dechex($i);
}
$i=0;
while ($row = pg_fetch_row($dst_results)){
 if ($row[0]=="easyjet") {
 echo ".".$row[0].$row[1]." { background-color: ".$bcolor[$i]."; color: #fff; border: solid 1px ".$bcolor[$i]."; }\n";
  $i+=1;
 }
}
pg_result_seek($dst_results, 0);

for ($i=0;$i<5;$i+=1) {
$bcolor[$i]="#".(string)dechex($i*2).(string)dechex($i*2).(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(11+$i).(string)dechex(11+$i);
}
$i=0;
while ($row = pg_fetch_row($dst_results)){
 if ($row[0]=="up") {
 echo ".".$row[0].$row[1]." { background-color: ".$bcolor[$i]."; color: #fff; border: solid 1px ".$bcolor[$i]."; }\n";
  $i+=1;
 }
}


?>

</style>
<script>
function update1(val) {document.querySelector('#mindaysoutput').value = val; }
function update2(val) {document.querySelector('#maxdaysoutput').value = val; }
function update3(val) {document.querySelector('#priceoutput').value = val; }
</script>
<SCRIPT>

var curSource = new Array();
var newSource = new Array();
<?php
pg_result_seek($dst_results, 0);
$destinations=Array();

while ($row = pg_fetch_row($dst_results)) {
 if ($destinations[$row[0]]=="") { $destinations[$row[0]]=array(); }
 $cur_dst=array("airport" => $row[1], "destination" => $row[2]);
 array_push($destinations[$row[0]], $cur_dst);
 $jsonurl[$row[0].$row[1]]="/getjson.php?dst=$row[1]&company=$row[0]";
}

$estr="";
foreach ($companies as $cmp) {
 foreach ($destinations[$cmp] as $dcmp) { 
  $estr=$cmp.$dcmp['airport']; 
  echo "curSource['".$estr."'] = '".$jsonurl[$estr]."';\n";
 }
}
?>

$(document).ready(function() {

    var filters=0;
    $('#filters_b').click(
     function() {
      if (filters==0) { 
       $('#dst_filter').animate({top: "-20px"}, 300); 
       filters=1;
       $('#filters_b').text("Hide Filters");
      } else { 
       $('#dst_filter').animate({top: "-230px"}, 300); 
       filters=0;
       $('#filters_b').text("Show filters and destinations list");
      }
     } 
    );

    $('#calendar').fullCalendar({
	height: "auto",
        eventSources: [
<?php
$first=1;
foreach ($companies as $cmp) {
 foreach ($destinations[$cmp] as $dcmp) { 
  if ($first!=1) echo " , "; else $first=0; 
  $estr=$cmp.$dcmp['airport']; 
  echo "curSource['".$estr."']\n";
 }
}
?>
        ],
    eventRender: function(event, element) {
     element.qtip({content: event.tooltip, style: {padding: 5, background: '#A2D959', color: 'black', color: 'black', border: {width: 7,radius: 5,color: '#A2D959'}}});
    }

    }) ; 

    <?php
     $dst_companies = array_keys($destinations);
    ?>
    var destinations = {
    <?php
     foreach ($dst_companies as $cur_dst_cmp) {
      echo '"'.$cur_dst_cmp.'": {';
      $first=1;
      foreach($destinations[$cur_dst_cmp] as $dst) {
       if ($first!=1) { echo ", " ; } else { echo "'ALL':'All', " ; $first=0; }
       echo "'".$dst['airport']."':'".$dst['destination']."' ";
      }
      echo "},\n";
     }
    ?>
    };

    $("#company").on("change", function(e) {
     var $select_dst = $("#dst");
     $select_dst.empty();
     var cur_cmp = destinations[$(this).val()];
     var x;
     for (x in cur_cmp) {
      $select_dst.append($("<option></option>")
       .attr("value", x).text(cur_cmp[x]));
     }
    }); 

<?php
$estr="";
foreach ($companies as $cmp) {
 foreach ($destinations[$cmp] as $dcmp) { 
  $estr=$cmp.$dcmp['airport']; 
  echo '$("#'.$estr.'").change(function() {'."\n";
  echo "newSource['$estr'] = $('#".$estr."').is(':checked') ? '$jsonurl[$estr]' : '';\n";
  echo "$('#calendar').fullCalendar('removeEventSource', curSource['$estr']);\n";
  echo "$('#calendar').fullCalendar('refetchEvents');\n";
  echo "$('#calendar').fullCalendar('addEventSource', newSource['$estr']);\n";
  echo "$('#calendar').fullCalendar('refetchEvents');\n";
  echo "curSource['$estr'] = newSource['$estr'];\n";
  echo "});\n";
 }
 echo "\n\n";
}
?>


});

</SCRIPT>
</HEAD><BODY>
<div id=wrap name=wrap>
 <div name=CalAll id=CalAll>

  <div id="dst_filter">
<?php
	echo "<div name=buttonswrap>\n";
	foreach ($companies as $cmp) {
	 echo '<div name="buttons" id="buttons">'."\n";
	 echo "<div id=cmp_head name=cmp_head>$cmp</div>\n";
	 foreach ($destinations[$cmp] as $dcmp) 
	   echo '<div class='.$cmp.$dcmp['airport'].'> <input type="checkbox" checked="checked" name="'.$cmp.$dcmp['airport'].'" id="'.$cmp.$dcmp['airport'].'" /> <label for="e'.$cmp.$dcmp['airport'].'">'.$dcmp['destination'].'<br><a class=ShowAll href="getpricefordst.php?cmp='.$cmp.'&dst='.$dcmp['airport'].'">Show All</a></label> </div>'."\n";
	 echo "</div>\n";
	}
	echo "</div>\n";
	?>
   <div id=filters_b>
    Show filters and destinations list
   </div>
  </div>
  <div><BR><BR><BR></div>
  <DIV id='calendar'></div>
  </div>
  <div id='route'>
   <p id=rhead>Search by days and price:</p>
   <form name=routeForm method=post action=getroute.php>
   <label >Company: </label><BR>
   <select name=company id=company>
      <option value="ALL">All Companies</option>
      <option value="wizz">WizzAir</option>
      <option value="easyjet">Easyjet</option>
      <option value="up">Up by Elal</option>
   </select>
   <br>
   <label>Destination:</label><BR>
   <select name=dst id=dst>
    <option value="ALL">All Destinations</option>
    <?php
    foreach ($companies as $cmp) {
     foreach ($destinations[$cmp] as $dcmp) { 
      echo '<option value="'.$dcmp['airport'].'">'.$dcmp['destination'].' ('.$dcmp['airport'].')</option>'."\n";
     }
    }
    ?>
   </select>
   <br>
   <label for="minDays">Minimum Days: <output for=minDays id=mindaysoutput>4</output> </label><BR>
   <input type=range name=minDays id=minDays value=4 min=2 max=7 onchange="update1(value)"><BR>
   <label for="maxDays">Maximum Days: <output for=maxDays id=maxdaysoutput>6</output> </label></label><BR>
   <input type=range name=maxDays id=maxDays value=6 min=4 max=14 onchange="update2(value)"><BR>
   <label for="price">Maximum Roundtrip Price: <output for=price id=priceoutput>600</output> </label></label><BR>
   <input type=range name=price id=price value=600 min=200 max=2000 step=50 onchange="update3(value)"><BR>
   <input type=submit value=Search>
   </form>
  <div id=adsense>
   <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
   <!-- 2fly600x300 -->
   <ins class="adsbygoogle" style="display:inline-block;width:300px;height:600px" data-ad-client="ca-pub-3421081986991175" data-ad-slot="8172171649"></ins>
   <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
  </div>
  </div>
 </div>
</BODY></HTML>
