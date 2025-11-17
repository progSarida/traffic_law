<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

    $strPlate = "OK";
    $ShowReceipt = false;
    $AlternateReceipt = false;
    $ReceiptNumber = "";
    $ReceiptPrefix = "";
    $StartNumber = "";
    $EndNumber = "";
    $Message = "";
    $EludiControlli = "false";
    $ForeignPlate = "false";
    $a_Receipt = array();
    $a_ReceiptResult = array();
    $test="";

    $rs= new CLS_DB();
    
    $Code = $_POST['Code'];
    $CityId = $_SESSION['cityid'];
    $FineType = $_POST['FineTypeId'];
    $ActType = $_POST['ActType'];
    $ControllerId = $_POST['ControllerId'];
    $EludiControlli = $_POST['EludiControlli'];
    $ForeignPlate = $_POST['ForeignPlate'];
    
    if ($FineType ==1 or $FineType ==3 or $FineType == 4 or $FineType ==5){
        $where = 'AND FineTypeId IN (1,3,4,5)';
    }else if ($FineType == 2){
        $where = 'AND FineTypeId = 2';
    }
    $newCode = null;

    if ($ControllerId != "" && $ActType != ""){
        if ($EludiControlli == "false"){ // && $ForeignPlate == "false")
            
            $tipoAttoWhere = $ActType!= null ? " AND TipoAtto=".$ActType : "";
            $controllerIdWhere = $ControllerId!= null 
                ? " AND (ControllerId=".$ControllerId. " OR ControllerId=0)" 
                : " AND ControllerId=0" ;
            $rs_Receipt = $rs->Select("Receipt", "CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year'].$tipoAttoWhere.$controllerIdWhere );
            
            //$rs_Receipt = $rs->Select("Receipt", "TipoAtto='".$ActType."' AND ControllerId=".$ControllerId." AND CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year']);
            
            if (mysqli_num_rows($rs_Receipt) > 0){
                
                while($r_Receipt = mysqli_fetch_array($rs_Receipt)){
                    if ($r_Receipt['ControllerId'] == $ControllerId){
                        $a_ReceiptResult = $r_Receipt;
                        break;
                    } else if ($r_Receipt['ControllerId'] == 0){
                        $a_ReceiptResult = $r_Receipt;
                    }
                }
                
                //TEST
                mysqli_data_seek($rs_Receipt, 0);
                while($r_Receipt = mysqli_fetch_array($rs_Receipt)){
                    $test .= '<option number="'.$r_Receipt['Numero_blocco'].'" prefix="'.$r_Receipt['Preffix'].'" from="'.$r_Receipt['StartNumber'].'" to="'.$r_Receipt['EndNumber'].'">'.$r_Receipt['Numero_blocco'].'</option>';
                }
                
                if ($ForeignPlate == "false") $ShowReceipt = true;
                $ReceiptNumber = $a_ReceiptResult['Numero_blocco'];
                $ReceiptPrefix = $a_ReceiptResult['Preffix'];
                $StartNumber = $a_ReceiptResult['StartNumber'];
                $EndNumber = $a_ReceiptResult['EndNumber'];
                
//                 if ($ForeignPlate == "false") {
//                     if(isset($ReceiptPrefix) && $ReceiptPrefix != null ){
//                         if (strpos($Code, $ReceiptPrefix) === false) {
//                             $Message = "Il Riferimento non contiene la lettera prevista dal bollettario";
//                             $strPlate = "NO";
//                         } 
//                         else $strPlate = "OK";
//                     }
//                 } else $strPlate = "OK";
                
                if ($ForeignPlate == "false") {
                    if ($Code < $StartNumber || $Code > $EndNumber){
                        $Message = "Fuori limite!";
                        $strPlate = "NO";
                    } else $strPlate = "OK";
                } else $strPlate = "OK";
                
            } else if (empty($a_ReceiptResult)){
                $controllerIdWhere = $ControllerId!= null
                ? " AND (ControllerId !=".$ControllerId. " AND ControllerId != 0)"
                : " AND ControllerId != 0" ;
                $rs_Receipt = $rs->Select("Receipt", "CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year'].$tipoAttoWhere.$controllerIdWhere );
                
                if (mysqli_num_rows($rs_Receipt) > 0){
                    while($r_Receipt = mysqli_fetch_array($rs_Receipt)){
                        if ($Code <= $r_Receipt['EndNumber'] && $Code >= $r_Receipt['StartNumber']){
                            $AlternateReceipt = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    if ($strPlate == "OK") {
        if (strpos($Code, '/') !== false) {
    
            $newCode = str_replace("/","",$Code)."/".$_SESSION['year'];
            $check = $newCode != ""? "OR Code ='$newCode'":null;
            $checkedcode = $Code.'/'.$_SESSION['year'];
            $checked_code_without_change = $Code.'/'.$_SESSION['year'];
            $Codes = $rs->Select('Fine',"CityId='".$CityId."' AND Code='".$newCode."' $where","Id DESC");
            if (mysqli_num_rows($Codes)==0){
                $Codes1 = $rs->Select('Fine',"CityId='".$CityId."' AND Code='".$checkedcode."' $where","Id DESC");
                if (mysqli_num_rows($Codes1)==0){
                    $strPlate = "OK";
                }else{
                    $status = mysqli_fetch_array($Codes1)['StatusTypeId'];
                    $FineId = mysqli_fetch_array($Codes1)['Id'];
                    if ($status>=35) $strPlate = "OK";
                    else {
                        $strPlate = "NO";
                        $Message = "Già presente!";
                    }
                }
            }else{
                $status = mysqli_fetch_array($Codes)['StatusTypeId'];
                $FineId = mysqli_fetch_array($Codes)['Id'];
                if ($status>=35) $strPlate = "OK";
                else {
                    $strPlate = "NO";
                    $Message = "Già presente!";
                }
            }
    
    
    
        }else{
    
            if (is_numeric($Code)) {
                $checkedcode = $Code.'/'.$_SESSION['year'];
                $check = null;
                $Codes = $rs->Select('Fine',"CityId='".$CityId."' AND Code='".$checkedcode."' $where","Id DESC");
                if (mysqli_num_rows($Codes)==0){
                    $strPlate = "OK";
                }else{
                    $status = mysqli_fetch_array($Codes)['StatusTypeId'];
                    $FineId = mysqli_fetch_array($Codes)['Id'];
                    if ($status>=35) $strPlate = "OK";
                    else {
                        $Message = "Già presente!";
                        $strPlate = "NO";
                    }
    
                }
    
            }else{
    
                $checkedcode = $Code."/".$_SESSION['year'];
                $Codes = $rs->Select('Fine',"CityId='".$CityId."' AND Code='".$checkedcode."' $where","Id DESC");
                if (mysqli_num_rows($Codes)==0){
                    $strPlate = "OK";
                }else{
                    $status = mysqli_fetch_array($Codes)['StatusTypeId'];
                    $FineId = mysqli_fetch_array($Codes)['Id'];
                    if ($status>=35) $strPlate = "OK";
                    else {
                        $Message = "Già presente!";
                        $strPlate = "NO";
                    }
    
                }
            }
        }
    }

    echo json_encode(
        array(
            "Eludi" => $EludiControlli,
            "ForeignPlate" => $ForeignPlate,
            "Result" => $strPlate,
            "ShowReceipt" => $ShowReceipt,
            "ReceiptNumber" => $ReceiptNumber,
            "Prefix" => $ReceiptPrefix,
            "StartNumber" => $StartNumber,
            "EndNumber" => $EndNumber,
            "Message" => $Message,
            "Receipt" => $a_Receipt,
            "AlternateReceipt" => $AlternateReceipt,
            "Blocks" => $test,
        )
    );
