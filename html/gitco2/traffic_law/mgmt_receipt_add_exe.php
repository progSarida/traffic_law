<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$year = $_SESSION['year'];
$before_year = $year-1;
$city_id = $_SESSION['cityid'];
if (isset($_POST['first_number'])) {
    if (!isset($_POST['multi_accertatore']))
        $controller_id = $_POST['controller'];
    else $controller_id = 0;
    $prefix1 = $_POST['prefix_1'];
    $prefix2 = $_POST['prefix_2'];
    if ($prefix1 != '' && $prefix2 != '' && $prefix1 == $prefix2) $prefix = $prefix1;
    else $prefix = null;

    $and_query = $prefix != null? "AND Preffix ='$prefix'":null;
    $select = $_POST['select'];
    if ($select ==1 or $select == 3){
        $and_tipo = 'AND TipoAtto IN (1,3)';
    }else{
        $and_tipo = 'AND TipoAtto = 2';
    }
    $startnumber = (int)$_POST['first_number'];
    $endnumber = (int)$_POST['second_number'];
    $numero_blocco = $_POST['numero_bloco'];

    $ErrorVar = "&prefix_1=" . $prefix1 . "&prefix_2=" . $prefix2 . "&first_number=".$startnumber . "&second_number=" . $endnumber. "&numero_bloco=" . $numero_blocco;
    $check = $rs->SelectQuery("SELECT * FROM Receipt where CityId = '".$city_id."' $and_query AND Session_Year = '$year' $and_tipo");
    
    if (mysqli_num_rows($check)>0){

        while ($row = mysqli_fetch_array($check)){
            if (($startnumber <= (int)$row['EndNumber'] && (int)$row['StartNumber'] <= $endnumber) && $prefix ==$row['Preffix'] && $numero_blocco ==$row['Numero_blocco']){
                header("Location: mgmt_receipt_add.php?answer=L'intervallo di numeri definito si sovrappone a quello di un bollettario già esistente per questo blocco/lettera".$ErrorVar);
                DIE;
            }
        }

    }elseif (mysqli_num_rows($check)==0){
        $check_before = $rs->SelectQuery("SELECT * FROM Receipt where CityId = '".$city_id."' $and_query AND Session_Year='$before_year'");
        if (mysqli_num_rows($check_before)>0){

            $previousBollettario = mysqli_fetch_array($check_before);

            $previousBollettarioPreffix = $previousBollettario['Preffix'];
            $previousBollettarioStart = $previousBollettario['StartNumber'];
            $previousBollettarioEnd = $previousBollettario['Scaricato'] ==0? $previousBollettario['EndNumber']:$previousBollettario['NumPage'];
//select COUNT(Id) FROM Fine WHERE Fine.Code BETWEEN 'N100' AND 'N150'

            $results = 0;

            $pageBeforeYear = $rs->SelectQuery("select Code from Fine where CityId = '$city_id' and YEAR(FineDate)= $before_year");
            while ($row = mysqli_fetch_array($pageBeforeYear)){

                $newCode = explode("/",$row['Code']);

                if (is_numeric($newCode[0])){
                    $Code = (int)$newCode[0];
                    if ($Code >= $previousBollettarioStart && $Code <= $previousBollettarioEnd ){
                        $results ++;
                    }
                }else{
                    $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $newCode[0]);
                    $prefix = $parts[0];
                    $Code = (int)$parts[1];
                    if ($Code >= $previousBollettarioStart && $Code <= $previousBollettarioEnd && $previousBollettarioPreffix == $prefix){
                        $results ++;
                    }
                }

            }

            if ($previousBollettario['Scaricato'] == 0){
                $totalBolletarioPages = $previousBollettario['EndNumber']-$previousBollettario['StartNumber'] + 1;
                if ($results < $totalBolletarioPages){
                    header("Location: mgmt_receipt_add.php?answer=Questo blocco è in uso!".$ErrorVar);
                    DIE;
                }
            }else{
                $totalBolletarioPages = $previousBollettario['NumPage']-$previousBollettario['StartNumber'] + 1;
                if ($results < $totalBolletarioPages){
                    header("Location: mgmt_receipt_add.php?answer=Questo blocco è in uso!".$ErrorVar);
                    DIE;
                }
            }

        }

    }
    $date = date("Y-m-d");
    $aBoletario = array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $city_id),
        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controller_id, 'settype' => 'int'),
        array('field' => 'TipoAtto', 'selector' => 'value', 'type' => 'int', 'value' => $select, 'settype' => 'int'),
        array('field' => 'Preffix', 'selector' => 'value', 'type' => 'str', 'value' => $prefix),
        array('field' => 'StartNumber', 'selector' => 'value', 'type' => 'int', 'value' => $startnumber, 'settype' => 'int'),
        array('field' => 'EndNumber', 'selector' => 'value', 'type' => 'int', 'value' => $endnumber, 'settype' => 'int'),
        array('field' => 'Scaricato', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'NumPage', 'selector' => 'value', 'type' => 'int', 'value' => '', 'settype' => 'int'),
        array('field' => 'Date', 'selector' => 'value', 'type' => 'date', 'value' => $date),
        array('field' => 'Session_Year', 'selector' => 'value', 'type' => 'str', 'value' => $year),
        array('field' => 'Numero_blocco', 'selector' => 'value', 'type' => 'str', 'value' => $numero_blocco),

    );
    $insert = $rs->Insert('Receipt', $aBoletario);
    if ($insert)
    header("Location: mgmt_receipt.php?answer=Inserito con successo");
    else header("Location: mgmt_receipt.php?answer=ERRORE: contattare l'amministratore del server");
}else if (isset($_POST['first_number_edit'])){

    $prefix1 = $_POST['prefix_1'];
    $prefix2 = $_POST['prefix_2'];
    if ($prefix1 != '' && $prefix2 != '' && $prefix1 == $prefix2) {
        $prefix = $prefix1;
    } else {
        $prefix = null;
    }
    $boletario_id = (int)$_POST['boletario_id'];
    //var_dump($boletario_id);die;
    $startnumber = $_POST['first_number_edit'];
    $endnumber = $_POST['second_number'];
    $select = $_POST['select'];
    if ($select ==1 or $select == 3){
        $and_tipo = 'AND TipoAtto IN (1,3)';
    }else{
        $and_tipo = 'AND TipoAtto = 2';
    }
    if (isset($_POST['nr_page'])){
        $nr_page = $_POST['nr_page'];
    }else{
        $nr_page = null;
    }
    $and_query = $prefix != null? "AND Preffix ='$prefix'":null;
    $date = date("Y-m-d");
    if (isset($_POST['scarica'])){

        $scaricato = $_POST['scarica'];
    }else{
        $scaricato = 0;
    }
    if (!isset($_POST['multi_accertatore'])) $controller_id = $_POST['controller'];
        else $controller_id = 0;

    $check = $rs->SelectQuery("SELECT * FROM Receipt where CityId = '$city_id' $and_query AND Session_Year = '$year' and Id !=$boletario_id $and_tipo");

    if (mysqli_num_rows($check)>0){


        while ($row = mysqli_fetch_array($check)){
            if (($startnumber <= (int)$row['EndNumber'] && (int)$row['StartNumber'] <= $endnumber) && $prefix ==$row['Preffix']){
                header("Location: mgmt_receipt_upd.php$str_GET_Parameter&boletario=$boletario_id&answer=L'intervallo di numeri definito si sovrappone a quello di un bollettario già esistente per questo blocco/lettera".$ErrorVar);
                DIE;
            }
        }

    }elseif (mysqli_num_rows($check)==0){
        $check_before = $rs->SelectQuery("SELECT * FROM Receipt where CityId = '".$city_id."' $and_query AND Session_Year='$before_year' $and_tipo");
        if (mysqli_num_rows($check_before)>0){

            $previousBollettario = mysqli_fetch_array($check_before);

            $previousBollettarioPreffix = $previousBollettario['Preffix'];
            $previousBollettarioStart = $previousBollettario['StartNumber'];
            $previousBollettarioEnd = $previousBollettario['Scaricato'] ==0? $previousBollettario['EndNumber']:$previousBollettario['NumPage'];
//select COUNT(Id) FROM Fine WHERE Fine.Code BETWEEN 'N100' AND 'N150'

            $results = 0;

            $pageBeforeYear = $rs->SelectQuery("select Code from Fine where CityId = '$city_id' and YEAR(FineDate)= $before_year");
            while ($row = mysqli_fetch_array($pageBeforeYear)){

                $newCode = explode("/",$row['Code']);

                if (is_numeric($newCode[0])){
                    $Code = (int)$newCode[0];
                    if ($Code >= $previousBollettarioStart && $Code <= $previousBollettarioEnd ){
                        $results ++;
                    }
                }else{
                    $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $newCode[0]);
                    $prefix = $parts[0];
                    $Code = (int)$parts[1];
                    if ($Code >= $previousBollettarioStart && $Code <= $previousBollettarioEnd && $previousBollettarioPreffix == $prefix){
                        $results ++;
                    }
                }

            }


            if ($previousBollettario['Scaricato'] == 0){
                $totalBolletarioPages = $previousBollettario['EndNumber']-$previousBollettario['StartNumber'] + 1;
                if ($results < $totalBolletarioPages){
                    header("Location: mgmt_receipt_add.php?answer=Questo blocco è in uso!".$ErrorVar);
                    DIE;
                }
            }else{
                $totalBolletarioPages = $previousBollettario['NumPage']-$previousBollettario['StartNumber'] + 1;
                if ($results < $totalBolletarioPages){
                    header("Location: mgmt_receipt_add.php?answer=Questo blocco è in uso!".$ErrorVar);
                    DIE;
                }
            }

        }

    }
    $aBoletario = array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $city_id),
        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controller_id, 'settype' => 'int'),
        array('field' => 'TipoAtto', 'selector' => 'value', 'type' => 'int', 'value' => $select, 'settype' => 'int'),
        array('field' => 'Preffix', 'selector' => 'value', 'type' => 'str', 'value' => $prefix),
        array('field' => 'StartNumber', 'selector' => 'value', 'type' => 'int', 'value' => $startnumber, 'settype' => 'int'),
        array('field' => 'EndNumber', 'selector' => 'value', 'type' => 'int', 'value' => $endnumber, 'settype' => 'int'),
        array('field' => 'Scaricato', 'selector' => 'value', 'type' => 'int', 'value' => $scaricato, 'settype' => 'int'),
        array('field' => 'NumPage', 'selector' => 'value', 'type' => 'int', 'value' => $nr_page, 'settype' => 'int'),
        array('field' => 'Date', 'selector' => 'value', 'type' => 'date', 'value' => $date),

    );
    $rs->Update('Receipt', $aBoletario,'Id='.$boletario_id);
    header("Location: mgmt_receipt.php?answer=Aggiornato con successo");
}
