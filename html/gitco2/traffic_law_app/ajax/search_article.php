<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$ArticleId= CheckValue('ArticleId','n');


$rs_ArticleApp = $rs->Select('ArticleApp',"Id=". $ArticleId);
$str_Description = mysqli_fetch_array($rs_ArticleApp)['Description'];


echo json_encode(
    array(
        "Description" => $str_Description,
    )
);

