<?php
include('_path.php');

include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$user = addslashes($_REQUEST['usr']);
$pass = addslashes($_REQUEST['psw']);


$rs_Login = $rs->Select(MAIN_DB. ".User", "UserName='".$user."' and Password='".md5($pass)."'");

if(mysqli_num_rows($rs_Login)>0) {

    $r_Login = mysqli_fetch_array($rs_Login);



    $_SESSION['Message']			            = "";
    $_SESSION['Documentation'] 		            = "";
    $_SESSION['username'] 			            = $r_Login['UserName'];
    $_SESSION['usercity'] = $_SESSION['cityid'] = $r_Login['CityId'];
    $_SESSION['userid'] 			            = $r_Login['Id'];
    $_SESSION['usertype'] 			            = $r_Login['UserType'];
    $_SESSION['controllerid'] 		            = $r_Login['ControllerId'];
    $_SESSION['userlevel'] 			            = $r_Login['UserLevel'];
    $_SESSION['UserMenuType'] 		            = $r_Login['UserMenuType'];



    header("location: panel.php");


} else {
        echo "<script>alert('Nome utente o Password errati.'); history.back();</script>";

}
