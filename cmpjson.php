
<?php
$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";
$db = pg_pconnect($conn_string);

$query="select * from companies";
$result = pg_query($db, $query);

pg_close();


$json=array();
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('id' => $row[0], 'name' => $row[1] );
 array_push($json, $cur_elem);
}
echo json_encode($json);
?>
