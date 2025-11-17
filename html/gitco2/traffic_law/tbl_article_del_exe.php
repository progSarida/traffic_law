<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$Id= CheckValue('Id','n');


$rs->SetCharset('utf8');



$rs->Delete('Article',"Id=".$Id);




$rs->Delete('ArticleTariff', "ArticleId=".$Id." AND Year=".$_SESSION['year']);







header("location: tbl_article.php");