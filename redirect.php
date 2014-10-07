<?php
$get_vars="";
foreach($_GET as $key => $value) { $get_vars=$get_vars.$key."=".$value."&"; }
$start=date_format(date_create($_GET['start']), 'd/m/Y');
$end=date_format(date_create($_GET['end']), 'd/m/Y');

switch ($_GET['company']) {
 case 'wizz':
  $instructions="
   Select the following fields:<BR>
   Leaving From: Tel Aviv (TLV)<BR>
   Going To: ".$_GET['destination']." (".$_GET['DST'].")<BR>
   Departure Date: ".$start." <BR>
   Return Date: ".$end."
  ";
  break;
}
?>

<HTML><HEAD><TITLE>Redirecting - 2fly.cheap</TITLE>
<script src='lib/jquery.min.js'></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<style>
body {
    width: 100%;
    height: 100%;
}

#bar {
    height: 100px;
    background: #ddd;
}

iframe {
    box-sizing: border-box;
    width: 100%;
    height: calc(100% - 100px);
}
.row {margin: 0 20px; }
</style>
</HEAD<BODY>
 <div id="bar">
  <div class=row>
   <div class="col-lg-2">
    <h1><a href=2fly.cheap>2Fly.Cheap</a></H1>
   </div>
   <div class="col-lg-2">
    <?php echo $instructions; ?>
   </div>
  </div>
 </div>
</div>
<iframe src="redirect_frame.php?<?php echo $get_vars; ?>" frameborder="0"></iframe>
</BODY></HTML>
