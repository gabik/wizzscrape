<?php
$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
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
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='lib/jquery.qtip.min.js'></script>
<script src='fullcalendar/fullcalendar.js'></script>
<link rel="stylesheet" type="text/css" href="lib/jquery.qtip.min.css">
<link rel="stylesheet" type="text/css" href="lib/calendar.css">

<style>

<?php

$destinations=pg_query($db, "select * from destinations");
while ($row = pg_fetch_row($destinations)){
 echo ".".$row[0].$row[1].", ";
}
echo "fake { float: left; display: inline-block;padding: 3px;  margin-right: 1px; }\n";

for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex($i).(string)dechex($i).(string)dechex(7+$i).(string)dechex(7+$i);
}
$i=0;
$destinations=pg_query($db, "select * from destinations");
while ($row = pg_fetch_row($destinations)){
 if ($row[0]=="wizz") {
 echo ".".$row[0].$row[1]." { background-color: ".$bcolor[$i]."; color: #fff; border: solid 1px ".$bcolor[$i]."; }\n";
  $i+=1;
 }
}

for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(3+$i).(string)dechex(3+$i).(string)dechex($i).(string)dechex($i);
}
$destinations=pg_query($db, "select * from destinations");
$i=0;
while ($row = pg_fetch_row($destinations)){
 if ($row[0]=="easyjet") {
 echo ".".$row[0].$row[1]." { background-color: ".$bcolor[$i]."; color: #fff; border: solid 1px ".$bcolor[$i]."; }\n";
  $i+=1;
 }
}

for ($i=0;$i<5;$i+=1) {
$bcolor[$i]="#".(string)dechex($i*2).(string)dechex($i*2).(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(11+$i).(string)dechex(11+$i);
}
$destinations=pg_query($db, "select * from destinations");
$i=0;
while ($row = pg_fetch_row($destinations)){
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
$dst_results=pg_query($db, "select * from destinations");
$destinations=Array();
$i=0;

while ($row = pg_fetch_row($dst_results)) {
 $destinations[$i]=$row;
 $jsonurl[$i]="/getjson.php?dst=$row[1]&company=$row[0]";
 $i+=1;
}
for ($y=0;$y<$i;$y++) {
 echo "curSource[$y] = '".$jsonurl[$y]."';\n";
}
?>

$(document).ready(function() {

    $('#calendar').fullCalendar({
	height: "auto",
        eventSources: [
<?php
for ($y=0;$y<$i-1;$y++) {
 echo "curSource[$y],\n";
}
echo "curSource[$y]\n";
?>
        ],
    eventRender: function(event, element) {
     element.qtip({content: event.tooltip, style: {padding: 5, background: '#A2D959', color: 'black', color: 'black', border: {width: 7,radius: 5,color: '#A2D959'}}});
    }

    })

<?php
$estr="";
for ($y=0;$y<$i-1;$y++) { $estr.="#e$y, "; }
$estr.="#e$y";
echo '$("'.$estr.'").change(function() {'."\n";
for ($y=0;$y<$i;$y++) { 
 echo "newSource[$y] = $('#e$y').is(':checked') ? '$jsonurl[$y]' : '';\n";
 echo "$('#calendar').fullCalendar('removeEventSource', curSource[$y]);\n";
}
?>
        $('#calendar').fullCalendar('refetchEvents');
<?php
for ($y=0;$y<$i;$y++) {
 echo "$('#calendar').fullCalendar('addEventSource', newSource[$y]);\n";
 echo "curSource[$y] = newSource[$y];\n";
}
?>

        $('#calendar').fullCalendar('refetchEvents');

    });
});

</SCRIPT>
</HEAD><BODY>
<div id=wrap name=wrap>
<div name=CalAll id=CalAll>

<?php
echo "<div name=buttonswrap>\n";
foreach ($companies as $cmp) {
 echo '<div name="buttons" id="buttons">'."\n";
 echo "<div id=cmp_head name=cmp_head>$cmp</div>\n";
 for ($y=0;$y<$i;$y++) {
  if ($destinations[$y][0]==$cmp) {
   echo '<div class='.$destinations[$y][0].$destinations[$y][1].'> <input type="checkbox" checked="checked" name="e'.$y.'" id="e'.$y.'" /> <label for="e'.$y.'">'.$destinations[$y][2].'<br><a class=ShowAll href="getpricefordst.php?cmp='.$cmp.'&dst='.$destinations[$y][1].'">Show All</a></label> </div>'."\n";
  }
 }
 echo "</div>\n";
}
echo "</div>\n";
?>

<div><BR><BR><BR></div>
<DIV id='calendar'></div>
</div>
<div id='route'>
<p id=rhead>Search by days and price:</p>
<form name=routeForm method=post action=getroute.php>
<label >Company: </label><BR>
<select name=company id=company>
  <option value="wizz">WizzAir</option>
  <option value="easyjet">Easyjet</option>
  <option value="up">Up by Elal</option>
</select>
<br>
<label>Destination:</label><BR>
<select name=dst id=dst>
<option value="ALL">All</option>
<?php
foreach ($destinations as $dst) {
echo '<option value="'.$dst[1].'">'.$dst[2].'</option>'."\n";
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
</div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 2fly1 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-3421081986991175"
     data-ad-slot="5484560444"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
</BODY></HTML>
