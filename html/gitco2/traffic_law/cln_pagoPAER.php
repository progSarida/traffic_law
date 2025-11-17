<?php
include("_path.php");
include_once(INC."/parameter.php");
include(INC."/function.php");

require_once (CLS."/cls_db.php");
include(CLS . "/pagopaER/PagoPAERService.php");

$PagoPAERService=NEW PagoPAERService("https:/multe.ovunque-si.it/PagoPAERService",new CLS_DB());


