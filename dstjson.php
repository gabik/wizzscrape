
<?php
$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

if (array_key_exists('company', $_POST)) {
  $where_cmp = " where company='".$_POST['company']."'";
} else {
  $where_cmp = "";
}

$query="select * from destinations".$where_cmp;
$result = pg_query($db, $query);

pg_close();


$json=array();
$i=1;
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('id' => $i, 'company' => $row[0], 'destination' => $row[2], 'airport' => $row[1] );
 array_push($json, $cur_elem);
 $i+=1;
}
echo json_encode($json);
?>
