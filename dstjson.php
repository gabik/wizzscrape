
<?php
include 'db_con_string.php';
$db = pg_pconnect($conn_string);

if (array_key_exists('company', $_POST)) {
  $where_cmp = " where company='".$_POST['company']."'";
} else {
  $where_cmp = "";
}

$query="select distinct destination, airport, country , country_he , destination_he from destinations".$where_cmp;
$result = pg_query($db, $query);

pg_close();


$json=array();
$i=1;
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('id' => $i, 'destination' => $row[0], 'airport' => $row[1], 'country' => $row[2], 'country_he' => $row[3], 'destination_he' => $row[4] );
 array_push($json, $cur_elem);
 $i+=1;
}
echo json_encode($json);
?>
