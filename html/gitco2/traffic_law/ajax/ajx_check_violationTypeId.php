<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";

$Id = $_POST['Id'];

$rs_ViolationType = $rs->Select('ViolationType', "Id=$Id");

if(mysqli_num_rows($rs_ViolationType)>0)
    $result = "NO";
else
    $result = "OK";

echo json_encode(
    array(
        "Result" => $result
    )
);
