<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$rs_city = $rs->Select("sarida.City","Title LIKE '%".$_GET['q']."%'","Title ASC");

$json = [];
while($row = mysqli_fetch_array($rs_city)){
    $json[] = ['id'=>$row['Id'], 'text'=>$row['Title']];
}

echo json_encode($json);