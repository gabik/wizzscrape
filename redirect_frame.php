<?php

if ($_GET['company'] == "easyjet") {
 $start=date_format(date_create($_GET['start']), 'd/m/Y');
 $end=date_format(date_create($_GET['end']), 'd/m/Y');
 $url = '"http://www.easyjet.com/links.mvc?dep=TLV&dest=' . $_GET['DST'] .'&dd='. $start .'&rd='. $end .'&apax=1&pid=www.easyjet.com&cpax=0&ipax=0&lang=EN&isOneWay=off&searchFrom=SearchPod|/en/"';
} else if ($_GET['company'] == "wizz") {
 $url = '"http://www.wizzair.com"';
}

?>

<HTML><HEAD><TITLE>Redirecting - 2fly.cheap</TITLE>
<script src='lib/jquery.min.js'></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

<script>
function url_redirect(options){
 var $form = $("<form />");
 $form.attr("action",options.url);
 $form.attr("method",options.method);
 for (var data in options.data)
  $form.append('<input type="hidden" name="'+data+'" value="'+options.data[data]+'" />');
                  
 $("body").append($form);
 $form.submit();
};

var $t=0;
var $flag=1;
$(document).ready(function() {
    var progress = setInterval(function() {
    var $bar = $('.progress-bar');

    if ($flag==1){
     if ($t==80) {
      $(function(){ url_redirect({url: <?php echo $url; ?>, method: "post", data: {
/*      $(function(){ url_redirect({url: "http://wizzair.com/en-GB/Search", method: "post", data: {
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$OriginStation":"TLV",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DestinationStation":"OTP",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$DepartureDate":"30/09/2014",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$ReturnDate":"02/10/2014",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountADT":"1",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountCHD":"0",
"ControlGroupRibbonAnonHomeView$AvailabilitySearchInputRibbonAnonHomeView$PaxCountINFANT":"0" */
} }); })
      $flag=0;
     } else {
      $bar.width($bar.width()+80);
      $t=$t+1;
     }
    }
}, 100);

});
</script>

</HEAD<BODY>
<div class=row>
 <div class="jumbotron">
  <div class=container>
   <div class="col-md-10">
    Redirecting..<BR>
    Please use the upper bar for instructions to find the specific flight you searched for.
<div class="progress progress-striped active">
    <div class="progress-bar progress-bar-danger six-sec-ease-in-out" role="progressbar" </div>
</div>
   </div>
  </div>
 </div>
</div>
</BODY></HTML>
