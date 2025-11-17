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
    $a_Receipt = null;
    $a_ReceiptResult = array();
    //$a_ReceiptMixedResult = array();

    $rs= new CLS_DB();
    $Code = trim(CheckValue('Code', 's'));
    $InputPrefix = trim(CheckValue('InputPrefix', 's'));
    $InputBlockNumber = trim(CheckValue('InputBlockNumber', 's'));
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
                    $a_ReceiptResult[] = $r_Receipt;
                    /*if ($r_Receipt['ControllerId'] == $ControllerId){
                        $a_ReceiptResult[] = $r_Receipt;
                    } else if ($r_Receipt['ControllerId'] == 0){
                        $a_ReceiptMixedResult[] = $r_Receipt;
                    }*/
                }
                               
                if ($ForeignPlate == "false") $ShowReceipt = true;
                //in base agli input inseriti cerco il bollettario nel primo o secondo array
                if (!empty($a_ReceiptResult)) {
                    $fiteredReceiptNumberCode = array();
                    $fiteredReceiptPrefix = array();
                    $fiteredReceiptNumberBlock = array();
                    
                    //print_r($a_ReceiptResult);
                    if (Count($a_ReceiptResult) > 1) {
                        $fiteredReceiptNumberCode = array_filter($a_ReceiptResult, function ($item) use ($Code)  {
                            return $item['StartNumber'] <= $Code && $item['EndNumber'] >= $Code;
                        });
                        //print_r($fiteredReceiptNumberCode);
                        if (Count($fiteredReceiptNumberCode) > 1 ) {
                            $fiteredReceiptPrefix = array_filter($fiteredReceiptNumberCode, function ($item) use ($InputPrefix){
                                return isset($InputPrefix) ? $item['Preffix'] == $InputPrefix : true;
                            });
                            //echo "lettera: ".$InputPrefix."\n";
                            //print_r($fiteredReceiptPrefix);
                            if (Count($fiteredReceiptPrefix) > 1) {
                                $fiteredReceiptNumberBlock = array_filter($fiteredReceiptPrefix, function ($item) use ($InputBlockNumber) {
                                    return isset($InputBlockNumber) ? $item['Numero_blocco'] == $InputBlockNumber : true;
                                });
                               //     print_r($fiteredReceiptNumberBlock);
                                if (Count($fiteredReceiptNumberBlock)>= 1) {
                                    reset($fiteredReceiptNumberBlock);
                                    $firstKey = key($fiteredReceiptNumberBlock);
                                    //echo "chiave blocco: ".$firstKey;
                                    $a_Receipt = $fiteredReceiptNumberBlock[$firstKey];
                                }
                                //print_r($a_Receipt);
                            } else {
                                if (Count($fiteredReceiptPrefix) === 1){
                                    reset($fiteredReceiptPrefix);
                                    $firstKey = key($fiteredReceiptPrefix);
                                    //echo "chiave lettera: ".$firstKey;
                                    $a_Receipt = $fiteredReceiptPrefix[$firstKey];
                                }
                               // print_r($a_Receipt);
                            }
                        } else {
                            if (Count($fiteredReceiptNumberCode) === 1 ) {
                                reset($fiteredReceiptNumberCode);
                                $firstKey = key($fiteredReceiptNumberCode);
                                //echo "chiave bolletta: ".$firstKey;
                                $a_Receipt = $fiteredReceiptNumberCode[$firstKey];
                            }
                            
                            //print_r($a_Receipt);
                        }
                    } else {
                        if (Count($a_ReceiptResult) === 1 ) {
                            reset($a_ReceiptResult);
                            $firstKey = key($a_ReceiptResult);
                            //echo "chiave: ".$firstKey;
                            $a_Receipt = $a_ReceiptResult[$firstKey];
                        }
                       // print_r($a_Receipt);
                    }
                }
                
                if (!empty($a_Receipt)){
                    //print_r($a_Receipt);
                    $ReceiptNumber = $a_Receipt['Numero_blocco'];
                    $ReceiptPrefix = $a_Receipt['Preffix'];
                    $StartNumber = $a_Receipt['StartNumber'];
                    $EndNumber = $a_Receipt['EndNumber'];
                }
                
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
                
            } /*else */
            //se non abbiamo trovato ricevute per l'accertatore e i dati inseriti
            // guardiamo se ce ne può essere almeno una per un accertatore differente
            if (empty($a_ReceiptResult) || empty($a_Receipt)) { // && empty($a_ReceiptMixedResult)){
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
                    $r_Codes1 = mysqli_fetch_assoc($Codes1);
                    $status = $r_Codes1['StatusTypeId'];
                    $FineId = $r_Codes1['Id'];
                    if ($status>=35) $strPlate = "OK";
                    else {
                        $strPlate = "NO";
                        $Message = "Già presente!";
                    }
                }
            }else{
                $r_Codes = mysqli_fetch_assoc($Codes);
                $status = $r_Codes['StatusTypeId'];
                $FineId = $r_Codes['Id'];
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
                    $r_Codes = mysqli_fetch_assoc($Codes);
                    $status = $r_Codes['StatusTypeId'];
                    $FineId = $r_Codes['Id'];
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
                    $r_Codes = mysqli_fetch_assoc($Codes);
                    $status = $r_Codes['StatusTypeId'];
                    $FineId = $r_Codes['Id'];
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
        )
    );
