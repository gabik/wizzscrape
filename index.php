<HTML><HEAD><TITLE>Cal View - Wizz Gabi</TITLE>
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='fullcalendar/fullcalendar.js'></script>

<script>
function update1(val) {document.querySelector('#mindaysoutput').value = val; }
function update2(val) {document.querySelector('#maxdaysoutput').value = val; }
function update3(val) {document.querySelector('#priceoutput').value = val; }
</script>
<SCRIPT>

var curSource = new Array();
curSource[0] = '/getjson.php?dst=BUD&company=wizz'
curSource[1] = '/getjson.php?dst=CLJ&company=wizz'
curSource[2] = '/getjson.php?dst=KTW&company=wizz'
curSource[3] = '/getjson.php?dst=OTP&company=wizz'
curSource[4] = '/getjson.php?dst=PRG&company=wizz'
curSource[5] = '/getjson.php?dst=SOF&company=wizz'
curSource[6] = '/getjson.php?dst=VNO&company=wizz'
curSource[7] = '/getjson.php?dst=WAW&company=wizz'
var newSource = new Array();

$(document).ready(function() {

    // page is now ready, initialize the calendar...

    $('#calendar').fullCalendar({
        // put your options and callbacks here
        eventSources: [
            curSource[0],
            curSource[1],
            curSource[2],
            curSource[3],
            curSource[4],
            curSource[5],
            curSource[6],
            curSource[7]
        ]
    })

    $("#e1, #e2, #e3, #e4, #e5, #e6, #e7, #e8").change(function() {
        newSource[0] = $('#e1').is(':checked') ? '/getjson.php?dst=BUD&company=wizz' : '';
        newSource[1] = $('#e2').is(':checked') ? '/getjson.php?dst=CLJ&company=wizz' : '';
        newSource[2] = $('#e3').is(':checked') ? '/getjson.php?dst=KTW&company=wizz' : '';
        newSource[3] = $('#e4').is(':checked') ? '/getjson.php?dst=OTP&company=wizz' : '';
        newSource[4] = $('#e5').is(':checked') ? '/getjson.php?dst=PRG&company=wizz' : '';
        newSource[5] = $('#e6').is(':checked') ? '/getjson.php?dst=SOF&company=wizz' : '';
        newSource[6] = $('#e7').is(':checked') ? '/getjson.php?dst=VNO&company=wizz' : '';
        newSource[7] = $('#e8').is(':checked') ? '/getjson.php?dst=WAW&company=wizz' : '';

        $('#calendar').fullCalendar('removeEventSource', curSource[0]);
        $('#calendar').fullCalendar('removeEventSource', curSource[1]);
        $('#calendar').fullCalendar('removeEventSource', curSource[2]);
        $('#calendar').fullCalendar('removeEventSource', curSource[3]);
        $('#calendar').fullCalendar('removeEventSource', curSource[4]);
        $('#calendar').fullCalendar('removeEventSource', curSource[5]);
        $('#calendar').fullCalendar('removeEventSource', curSource[6]);
        $('#calendar').fullCalendar('removeEventSource', curSource[7]);
        $('#calendar').fullCalendar('refetchEvents');

        //attach the new eventSources
        $('#calendar').fullCalendar('addEventSource', newSource[0]);
        $('#calendar').fullCalendar('addEventSource', newSource[1]);
        $('#calendar').fullCalendar('addEventSource', newSource[2]);
        $('#calendar').fullCalendar('addEventSource', newSource[3]);
        $('#calendar').fullCalendar('addEventSource', newSource[4]);
        $('#calendar').fullCalendar('addEventSource', newSource[5]);
        $('#calendar').fullCalendar('addEventSource', newSource[6]);
        $('#calendar').fullCalendar('addEventSource', newSource[7]);
        $('#calendar').fullCalendar('refetchEvents');

        curSource[0] = newSource[0];
        curSource[1] = newSource[1];
        curSource[2] = newSource[2];
        curSource[3] = newSource[3];
        curSource[4] = newSource[4];
        curSource[5] = newSource[5];
        curSource[6] = newSource[6];
        curSource[7] = newSource[7];
    });
});

</SCRIPT>
<style type='text/css'>

	body {
		margin-top: 10px;
		text-align: center;
		font-size: 14px;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	}

#buttons { width: 900px; margin: 0 auto; }
#calendar { width: 900px; margin: 0 auto; display: inline-block; }
#CalAll {float: center; display: inline-block;}
#route { margin: 50px; float: left; display: inline-block;  text-align: left;}

.BUD, .CLJ, .KTW, .OTP, .PRG, .SOF, .VNO, .WAW {
  float: left; display: inline-block;padding: 8px;  margin-right: 10px;
}

.BUD { background-color: #99ABEA; color: #fff; border: solid 1px #5173DA; }
.CLJ { background-color: #F55656; color: #fff; border: solid 1px #963636; }
.KTW { background-color: #F073E5; color: #fff; border: solid 1px #874081; }
.PRG { background-color: #72A9F2; color: #fff; border: solid 1px #3E5D85; }
.SOF { background-color: #71F0C3; color: #fff; border: solid 1px #3C8269; }
.VNO { background-color: #85F277; color: #fff; border: solid 1px #477A40; }
.WAW { background-color: #EBF582; color: #fff; border: solid 1px #535730; }
.OTP { background-color: #EDC38C; color: #fff; border: solid 1px #735F45; }
#byprice { background-color:#F5AF2C; color: #000; border: solid 1px #99FFFF; width: 900px; margin: 10px auto; padding: 5px;}
#byprice a:link { color: #000;  padding: 5px }
#byprice a:visited { color: #000;  padding: 5px }


input[type=range]{
    -webkit-appearance: none;
    margin: 5px 10px 20px 0;
}

input[type=range]::-webkit-slider-runnable-track {
    width: 500px;
    height: 8px;
    background: #ddd;
    border: none;
    border-radius: 3px;
}

input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    border: none;
    height: 16px;
    width: 26px;
    border-radius: 25%;
    background: #baa;
    margin-top: -4px;
}

input[type=range]:focus {
    outline: none;
}

input[type=range]:focus::-webkit-slider-runnable-track {
    background: #ccc;
}

input[type=submit] {
   width: 130px;
   height: 30px;
   overflow: hidden;
   border: 1px solid #ccc;
   margin: 0px 0px 10px 0;
-webkit-box-shadow: 1px 1px 5px 0px rgba(50, 50, 50, 0.68);
-moz-box-shadow:    1px 1px 5px 0px rgba(50, 50, 50, 0.68);
box-shadow:         1px 1px 5px 0px rgba(50, 50, 50, 0.68);
}

select {
   width: 140px;
   height: 30px;
   overflow: hidden;
   background: #ddd;
   border: 1px solid #ccc;
   margin: 0px 0px 10px 0;
}

p[id=rhead]{
font-weight: bold;
}
</style>
</HEAD><BODY>
<div id=wrap name=wrap>
<div name=CalAll id=CalAll>
<p> <?php print "Last Updated: " . exec('ls -rt logs/ | tail -1 | xargs -I {} tail -1  logs/{}'); ?> </p>
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

<div name="buttons" id="buttons">
<div class="BUD">
    <input type="checkbox" checked="checked" name="e1" id="e1" />
    <label for="e1">Budapest</label>
</div>
<div class="CLJ">
    <input type="checkbox" checked="checked" name="e2" id="e2" />
    <label for="e2">Cluj-Napoca</label>
</div>
<div class="KTW">
    <input type="checkbox" checked="checked" name="e3" id="e3" />
    <label for="e3">Katowice</label>
</div>
<div class="OTP">
    <input type="checkbox" checked="checked" name="e4" id="e4" />
    <label for="e4">Bucharest</label>
</div>
<div class="PRG">
    <input type="checkbox" checked="checked" name="e5" id="e5" />
    <label for="e5">Prague</label>
</div>
<div class="SOF">
    <input type="checkbox" checked="checked" name="e6" id="e6" />
    <label for="e6">Sofia</label>
</div>
<div class="VNO">
    <input type="checkbox" checked="checked" name="e7" id="e7" />
    <label for="e7">Vilnius</label>
</div>
<div class="WAW">
    <input type="checkbox" checked="checked" name="e8" id="e8" />
    <label for="e8">Warsaw</label>
</div>
</div>
<div><BR><BR><BR></div>
<DIV id='calendar'></div>
</div>
<div id='route'>
<p id=rhead>Search by days and price:</p>
<form name=routeForm method=post action=getroute.php>
<select name=company id=company>
  <option value="wizz">WizzAir</option>
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
