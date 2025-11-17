<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

if($_POST) {
    $str_Password = addslashes(CheckValue('old_P','s'));

    $rs_Login = $rs->Select(MAIN_DB.".User", "Id='".$_SESSION['userid']."' and Password='".md5($str_Password)."'");


    $n_Password = mysqli_num_rows($rs_Login);



    echo json_encode(
        array(
            "Password" => $n_Password,
        )
    );



}






