<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

if($_POST) {

    $rs= new CLS_DB();

    $Country = CheckValue('Country', 's');
    $Province = CheckValue('Province', 's');

    $a_ProvCity = array();
    if(isset($_POST['province_shorttitle'])){
        $shortitle = $_POST['province_shorttitle'];
        $provinceid = $rs->SelectQuery("SELECT Id from sarida.Province WHERE ShortTitle='$shortitle'");
        $provinceid = mysqli_fetch_array($provinceid);
        $Province = $provinceid['Id'];
        //var_dump($Province);
    }

    if($Country!=""){
        $rs_Province = $rs->Select(MAIN_DB.".City","1=1","Title");
        while($r_Province = mysqli_fetch_array($rs_Province)){
            $a_ProvCity[$r_Province['Id']] = array(
                "Title"     => $r_Province['Title'],
            );
        }
    } else if($Province!="") {
        $rs_City = $rs->Select(MAIN_DB.".City","ProvinceId='".$Province."'","Title");
        while($r_City = mysqli_fetch_array($rs_City)){
            $a_ProvCity[$r_City['Id']] = array(
                "Title"     => $r_City['Title'],
            );
        }
    }

    echo json_encode(
        array(
            "selectValues" => $a_ProvCity,
        )
    );

}
