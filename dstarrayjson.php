
<?php
include 'db_con_string.php';
$db = pg_pconnect($conn_string);

$query="select string_agg(cast(a.id as text), ','), airport, max(destination)  from destinations f join companies a on f.company=a.name group by airport";
$result = pg_query($db, $query);

pg_close();


$json=array();
while ($row = pg_fetch_row($result)) {
 $cur_elem=array('airport' => $row[1], 'companies' => "[$row[0]]", 'destination' => $row[2]);
 array_push($json, $cur_elem);
}
echo json_encode($json);
?>
