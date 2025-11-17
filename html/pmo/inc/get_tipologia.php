<?php
include("document_functions.php");
checkLogin();

$query = "SELECT * FROM Tipologia";
$rs= mysqli_query($conn, $query);
$output = array();
while($row = mysqli_fetch_array($rs)){
$output[]= Array('value' => $row['Id'], 'text' => $row['Description_Tipologia']);
}
//var_dump($output);die;
echo json_encode($output);
?>