<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
$flag = false;
if (isset($_POST['update'])){
    $id = $_POST['FineId'];
    $targa = $_POST['targa'];
    $ora = $_POST['ora'];
    $data = $_POST['today_date'];
    $test = explode("/", $data);
    $newdate = $test[2] . "-" . $test[1] . "-" . $test[0];
    $array = array();
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_POST['article' . $i]) and $_POST['article' . $i] != "") {
            $array[] = $_POST['article' . $i];
        }
    }
    if (sizeof($array) == 1){
        $get = $rs->SelectQuery("select Fine.Id,FineArticle.FineId,FineArticle.ArticleId FROM Fine LEFT JOIN FineArticle on Fine.Id = FineArticle.FineId WHERE Fine.Id = $id");
        $articleId = mysqli_fetch_array($get)['ArticleId'];
        if ($articleId == $array[0]){
            echo "OK";
        }else{
            $rs_Article = $rs->SelectQuery("SELECT Fine.FineDate,Fine.FineTime,Fine.VehiclePlate,FineArticle.ArticleId
              FROM Fine
              JOIN FineArticle on Fine.Id = FineArticle.FineId
              WHERE Fine.FineDate ='$newdate'
              AND Fine.FineTime = '$ora'
              AND FineArticle.ArticleId = $array[0]
              AND Fine.VehiclePlate = '$targa'");
            if (mysqli_num_rows($rs_Article) == 0) {
                echo "OK";
            }else{
                echo "NOK";
            }
        }
    }else{
        $flag = false;
        array_shift($array);

        foreach ($array as $value) {
            $getmany = $rs->SelectQuery("select Fine.Id,FineAdditionalArticle.FineId,FineAdditionalArticle.ArticleId 
            FROM Fine LEFT JOIN FineAdditionalArticle on Fine.Id = FineAdditionalArticle.FineId WHERE Fine.Id = $id");
            $articlesId = mysqli_fetch_array($getmany)['ArticleId'];
            if ($articlesId==$value){
                $flag = true;
            }else {
                $rs_Article_many = $rs->SelectQuery("SELECT Fine.FineDate,Fine.FineTime,Fine.VehiclePlate,FineAdditionalArticle.ArticleId
                          FROM Fine
                          JOIN FineAdditionalArticle on Fine.Id = FineAdditionalArticle.FineId
                          WHERE Fine.FineDate ='$newdate'
                          AND Fine.FineTime = '$ora'
                          AND FineAdditionalArticle.ArticleId = $value
                          AND Fine.VehiclePlate = '$targa'");
                if (mysqli_num_rows($rs_Article_many) == 0) {
                    $flag = true;
                }else{
                    $flag = false;
                }
            }
        }
        if ($flag){
            echo "OK";
        }else{
            echo "NOK";
        }
    }


}else {
    $ora = $_POST['ora'];
    $data = $_POST['today_date'];
    $test = explode("/", $data);
    $newdate = $test[2] . "-" . $test[1] . "-" . $test[0];
    $array = array();
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_POST['article' . $i]) and $_POST['article' . $i] != "") {
            $array[] = $_POST['article' . $i];
        }
    }

    $targa = $_POST['targa'];

    $rs_Article = $rs->SelectQuery("SELECT Fine.FineDate,Fine.FineTime,Fine.VehiclePlate,FineArticle.ArticleId
      from Fine
      JOIN FineArticle on Fine.Id = FineArticle.FineId
      WHERE Fine.FineDate ='$newdate'
      AND Fine.FineTime = '$ora'
      AND FineArticle.ArticleId = $array[0]
      AND Fine.VehiclePlate = '$targa'
    ");

    if (mysqli_num_rows($rs_Article) == 0) {
        $flag = true;
        if (sizeof($array) > 1) {
            array_shift($array);

            foreach ($array as $value) {

                $rs_Article_many = $rs->SelectQuery("SELECT Fine.FineDate,Fine.FineTime,Fine.VehiclePlate,FineAdditionalArticle.ArticleId
                  from Fine
                  JOIN FineAdditionalArticle on Fine.Id = FineAdditionalArticle.FineId
                  WHERE Fine.FineDate ='$newdate'
                  AND Fine.FineTime = '$ora'
                  AND FineAdditionalArticle.ArticleId = $value
                  AND Fine.VehiclePlate = '$targa'");
                if (mysqli_num_rows($rs_Article_many) == 0) {
                    $flag = true;
                }else{
                    $flag = false;
                }
            }
        }

    } else {
        $flag = false;
    }
    if($flag) echo "OK";
    else echo "NOK";
}






