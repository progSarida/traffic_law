<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id= CheckValue('Id','n');


$rs->Delete('Mail', 'Id='.$Id);


header("location: ".$str_BackPage);



