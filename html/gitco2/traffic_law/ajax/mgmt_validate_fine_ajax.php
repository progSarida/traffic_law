<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$strCode = "";

$rs= new CLS_DB();
$prefix = null;
$First_Code = $_POST['Code'];
$FineType = $_POST['FineTypeId'];
if ($FineType ==1 or $FineType ==3 or $FineType == 4 or $FineType ==5){
    $where = 'AND FineTypeId IN (1,3,4,5)';
    $and_tipo = 'AND TipoAtto IN (1,3)';
}else if ($FineType == 2){
    $where = 'AND FineTypeId = 2';
    $and_tipo = 'AND TipoAtto = 2';
}

$Controller = $_POST['Controller'];
$year = $_SESSION['year'];
$old_year = $year -1;
$before_year = (string)$old_year;
$CityId = $_SESSION['cityid'];

if (isset($_POST['update'])){

//    $newCode = explode("/",$First_Code);
//   var_dump($newCode);
    if (is_numeric($First_Code)){
        $Code = (int)$First_Code;

    }else{
        $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $First_Code);
        $prefix = $parts[0];

        if (strpos($prefix, '/') !== false) {

            if ($_POST['Eludi_Controlli'] == 'false'){

                $count_recipt_check =$rs->SelectQuery("SELECT * FROM `Receipt` WHERE `CityId` = '$CityId' $and_tipo");
                if ( mysqli_num_rows($count_recipt_check) == 0){
                    $strCode = "OK";
                    echo $strCode;die;
                }else{
                    $strCode = "NOB";
                    echo $strCode;die;
                }
            }else{
                $strCode = "OK";
                echo $strCode;die;
            }

        }
        $Code = (int)$parts[1];
    }

}else{
    if (is_numeric($First_Code)){
        $Code = (int)$First_Code;
    }else{
        $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $First_Code);
        $prefix = $parts[0];
        if (strpos($prefix, '/') !== false) {
            if ($_POST['Eludi_Controlli'] == 'false'){
                $count_recipt_check =$rs->SelectQuery("SELECT * FROM `Receipt` WHERE `CityId` = '$CityId' $and_tipo");
                if ( mysqli_num_rows($count_recipt_check) == 0){
                    $strCode = "OK";
                    echo $strCode;die;
                }else{
                    $strCode = "NOB";
                    echo $strCode;die;
                }

            }else{
                $strCode = "OK";
                echo $strCode;die;
            }

        }
        $Code = (int)$parts[1];
    }
}

$count_recipt =$rs->SelectQuery("SELECT * FROM `Receipt` WHERE `CityId` = '$CityId' $and_tipo");
$nr_boletario = mysqli_num_rows($count_recipt);
$query = $prefix != null ? "and Preffix='$prefix'" :"and Preffix=NULL";
if ($nr_boletario ==0){
    $strCode = "OK";
}else {
    if ($_POST['Eludi_Controlli'] == 'false') {
        $first_range = $rs->SelectQuery("SELECT * FROM `Receipt` where CityId ='$CityId' and StartNumber <= $Code and EndNumber >=$Code $query AND Session_Year=$year $and_tipo");
        $row = mysqli_fetch_array($first_range);
        $start_number = $row['StartNumber'];
        $end_number = $row['Scaricato'] == 0 ? $row['EndNumber'] : $row['NumPage'];
        $controller_db = $row['ControllerId'];
        if (mysqli_num_rows($first_range) > 0) {
            if ($Code >= $start_number and $Code <= $end_number) {
                if ($controller_db != 0) {
                    if ($Controller != $controller_db) $strCode = "NE";
                    else $strCode = "OK";
                } else $strCode = "OK";
            } else {
                $strCode = "NOB";
            }
        } else {

            $second_range = $rs->SelectQuery("SELECT * FROM `Receipt` where CityId ='$CityId' and StartNumber <= $Code and EndNumber >=$Code $query AND Session_Year=$before_year $and_tipo");


            $previousBollettario = mysqli_fetch_array($second_range);

            if (mysqli_num_rows($second_range) > 0) {

                $previousBollettarioPreffix = $previousBollettario['Preffix'];
                $previousBollettarioStart = $previousBollettario['StartNumber'];
                $previousBollettarioEnd = $previousBollettario['Scaricato'] == 0 ? $previousBollettario['EndNumber'] : $previousBollettario['NumPage'];
                $controller_db = $previousBollettario['ControllerId'];

                $pageBeforeYear = $rs->SelectQuery("select Code from Fine where CityId = '$CityId' and YEAR(FineDate)= $before_year $where");
                $results = 0;
                while ($row = mysqli_fetch_array($pageBeforeYear)) {
                    $newCode = explode("/", $row['Code']);

                    if (is_numeric($newCode[0])) {
                        $testCode = (int)$newCode[0];

                        if ($testCode >= $previousBollettarioStart && $testCode <= $previousBollettarioEnd) {
                            $results++;
                        }
                    } else {
                        $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $newCode[0]);
                        $prefix = $parts[0];
                        $testCode = (int)$parts[1];

                        if ($testCode >= $previousBollettarioStart && $testCode <= $previousBollettarioEnd && $previousBollettarioPreffix == $prefix) {
                            $results++;
                        }
                    }
                }


                if ($Code >= $previousBollettarioStart && $Code <= $previousBollettarioEnd) {

                    if ($results < ($previousBollettarioEnd - $previousBollettarioStart + 1)) {

                        if ($controller_db != 0) {
                            if ($Controller != $controller_db) $strCode = "NE";
                            else {

                                $actuaCode = $First_Code . "/" . $before_year;
                                $existFinePreviousYear = $rs->SelectQuery("select Code from Fine where CityId = '$CityId' and YEAR(FineDate)= $before_year and Code = '$actuaCode' $where");
                                if (mysqli_num_rows($existFinePreviousYear) > 0) {
                                    $strCode = "GE";
                                } else {
                                    $strCode = "OK";
                                }
                            }
                        } else $strCode = "OK";
                    } else {
                        $strCode = "OK";
                    }

                } else {
                    $strCode = "NOB";
                }

            } else {
                $strCode = "NOB";
            }
        }
    }else{
        $strCode = "OK";
    }

}

echo $strCode;