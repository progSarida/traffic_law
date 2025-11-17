<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$aField = $_POST['aField'];
$table = $_POST['table'];
$qtype = $_POST['qtype'];

// check request
if(isset($_POST['id']) && isset($_POST['id']) != "")
{
    // get user id
    $Id = $_POST['id'];

    // delete User
	$rs->Delete($table,"id=".$id);

    }
}
?>