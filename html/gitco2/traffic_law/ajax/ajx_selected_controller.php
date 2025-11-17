<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$exist = "";
$controller_id = $_POST['id'];
$article_id = $_POST['article'];
$finedate = $_POST['FineDate'];
$newdate = explode("/",$finedate);
$finaldate = $newdate[2]."-".$newdate[1]."-".$newdate[0];
$acce_number = $_POST['acce_number'];
//echo $finaldate;
if ($article_id!="") {
    if ($acce_number == 1) {
        $cont_type = $rs->SelectQuery("SELECT ControllerTypeId FROM `Controller` where Id = $controller_id");

        while ($row = mysqli_fetch_array($cont_type)) {
            $type = $row['ControllerTypeId'];
        }
        $cont_article = $rs->SelectQuery("SELECT * FROM `ArticleController` where Article = $article_id");
        $num_rows_article = mysqli_num_rows($cont_article);

        if ($type == 2) {
            if ($num_rows_article == 0) $exist = "NOTOK";
            else $exist = "OK";
        } else if ($type == 1) {
            $getfields = $rs->SelectQuery("SELECT FromDate,ToDate FROM Controller WHERE Id = $controller_id");
            $row = mysqli_fetch_array($getfields);
            $fromdate = $row['FromDate'];
            $toDate = $row['ToDate'];
            if ($fromdate != null && $toDate != null) {
                $chk_data = $rs->SelectQuery("SELECT * FROM `Controller` WHERE Id = $controller_id and '$finaldate' BETWEEN FromDate AND ToDate");
                if (mysqli_num_rows($chk_data) == 0) {
                    $exist = "NO";
                } else {
                    $exist = "OK";
                }
            } else {
                $exist = "OK";
            }

        }

    } else if ($acce_number > 1 && $acce_number <= 5) {
        $getfields = $rs->SelectQuery("SELECT FromDate,ToDate FROM Controller WHERE Id = $controller_id");
        $row = mysqli_fetch_array($getfields);
        $fromdate = $row['FromDate'];
        $toDate = $row['ToDate'];
        if ($fromdate != null && $toDate != null) {
            $chk_data = $rs->SelectQuery("SELECT * FROM `Controller` WHERE Id = $controller_id and '$finaldate' BETWEEN FromDate AND ToDate");
            if (mysqli_num_rows($chk_data) == 0) {
                $exist = "NO";
            } else {
                $exist = "OK";
            }
        } else {
            $exist = "OK";
        }
    }
}else{
    if ($acce_number == 1) {

            $getfields = $rs->SelectQuery("SELECT FromDate,ToDate FROM Controller WHERE Id = $controller_id");
            $row = mysqli_fetch_array($getfields);
            $fromdate = $row['FromDate'];
            $toDate = $row['ToDate'];
            if ($fromdate != null && $toDate != null) {
                $chk_data = $rs->SelectQuery("SELECT * FROM `Controller` WHERE Id = $controller_id and '$finaldate' BETWEEN FromDate AND ToDate");
                if (mysqli_num_rows($chk_data) == 0) $exist = "NO";
                    else $exist = "OK";
            } else {
                $exist = "OK";
            }


    } else  {
        $getfields = $rs->SelectQuery("SELECT FromDate,ToDate,Id FROM Controller WHERE Id = $controller_id");
        $row = mysqli_fetch_array($getfields);
        $fromdate = $row['FromDate'];
        $toDate = $row['ToDate'];
        if ($fromdate != null && $toDate != null) {
            $chk_data = $rs->SelectQuery("SELECT * FROM `Controller` WHERE Id = $controller_id and '$finaldate' BETWEEN FromDate AND ToDate");
            if (mysqli_num_rows($chk_data) == 0) {
                $exist = "NO";
            } else {
                $exist = "OK";
            }
        } else {
            $exist = "OK";
        }
    }
}
echo $exist;
