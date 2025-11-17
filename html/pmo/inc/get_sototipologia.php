<?php

include("document_functions.php");
checkLogin();
$tipologia_id = $_POST['tipologia_id'];
$query = "SELECT * FROM sottoTipologia WHERE tipologia_id = '$tipologia_id'";
$rs= mysqli_query($conn, $query);
$output = array();
while($row = mysqli_fetch_array($rs)){
$output[]= $row;
}
echo json_encode(array("result"=>$output));
?>
