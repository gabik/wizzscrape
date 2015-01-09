
<?php
include 'db_con_string.php';
$db = pg_pconnect($conn_string);

$query="select * from companies";
$result = pg_query($db, $query);

pg_close();


$json=array();
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('id' => $row[0], 'name' => $row[1], 'url' => $row[2] , 'display_name' => $row[5]);
 array_push($json, $cur_elem);
}
echo json_encode($json);
?>
