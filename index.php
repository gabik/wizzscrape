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
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='lib/jquery.qtip.min.js'></script>
<script src='fullcalendar/fullcalendar.js'></script>
<link rel="stylesheet" type="text/css" href="lib/jquery.qtip.min.css">
<link rel="stylesheet" type="text/css" href="lib/calendar.css">

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
        eventSources: [
<?php
for ($y=0;$y<$i-1;$y++) {
 echo "curSource[$y],\n";
}
echo "curSource[$y]\n";
?>
        ],
    eventRender: function(event, element) {
     element.qtip({content: event.tooltip});
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
<p> <?php print "Last Updated: " . exec('./get_lastupdate.sh | head -1'); ?> </p>
<div name="byprice" id="byprice">
Sort by Price:
<a href="output/BUD">Budapest</a>  
<a href="output/CLJ">Cluj-Napoca</a> 
<a href="output/KTW">Katowice</a> 
<a href="output/OTP">Bucharest</a> 
<a href="output/PRG">Prague</a> 
<a href="output/SOF">Sofia</a>  
<a href="output/VNO">Vilnius</a> 
<a href="output/WAW">Warsaw</a>  
</div>

<?php
foreach ($companies as $cmp) {
 echo '<div name="buttons" id="buttons">'."\n";
 echo "<div id=cmp_head name=cmp_head>$cmp</div>\n";
 for ($y=0;$y<$i;$y++) {
  if ($destinations[$y][0]==$cmp) {
   echo '<div class='.$destinations[$y][1].'> <input type="checkbox" checked="checked" name="e'.$y.'" id="e'.$y.'" /> <label for="e'.$y.'">'.$destinations[$y][2].'</label> </div>'."\n";
  }
 }
 echo "</div>\n\n<BR>";
}
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
</div>
</BODY></HTML>
