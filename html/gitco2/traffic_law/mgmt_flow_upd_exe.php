<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id= CheckValue('Id','n');
$P= CheckValue('P','s');
$Filters = CheckValue('Filters','s');

$str_UploadDate     = DateInDB(CheckValue('UploadDate','s'));
$str_ProcessingDate = DateInDB(CheckValue('ProcessingDate','s'));
$str_PaymentDate    = DateInDB(CheckValue('PaymentDate','s'));
$str_PaymentBank    = CheckValue('PaymentBank','s');
$str_SendDate       = DateInDB(CheckValue('SendDate','s'));
$str_ShippingOffice = CheckValue('ShippingOffice','s');
$str_note           = CheckValue('Note','s');
$printNumberCheck   = CheckValue('PrintNumber','n');

$a_update = array(
    "UploadDate"=>array("s",$str_UploadDate),
    "ProcessingDate"=>array("s",$str_ProcessingDate),
    "PaymentDate"=>array("s",$str_PaymentDate),
    "PaymentBank"=>array("s",$str_PaymentBank),
    "SendDate"=>array("s",$str_SendDate),
    "ShippingOffice"=>array("s",$str_ShippingOffice),
    "Note"=>array("s",$str_note)
);

if($str_SendDate!=null){
    $rs_Flow = $rs->Select('Flow', "Id=$Id");
    $r_Flow = $rs->getArrayLine($rs_Flow);
    
    $str_FileName = CheckValue('FileName','s');
    $int_PrinterId = CheckValue('PrinterId','s');
    $str_CreationDate = DateInDB(CheckValue('CreationDate','s'));

    $printercharges = $rs->Select('PrinterCharge', "PrinterId=".$int_PrinterId." AND FromDate<='".$str_SendDate."' AND ToDate IS NULL");
    $RowNumber = mysqli_num_rows($printercharges);

    if($RowNumber==0){
        $printercharges = $rs->Select('PrinterCharge', "PrinterId=".$int_PrinterId." AND '".$str_SendDate."' BETWEEN FromDate AND ToDate");
        $printercharge = mysqli_fetch_array($printercharges);
    }
    else
        $printercharge = mysqli_fetch_array($printercharges);

    if($r_Flow['PrintTypeId']==3){
        //14/03/2023
        //rimuoviamo l'assegnazione di KindSendDate sugli avvisi bonari perché non ha mai funzionato correttamente
        // e mettendola ora confonderemmo gli avvisi con gli inviti in AG.
//         $aUpdate = array(
//             array('field' => 'KindSendDate', 'selector' => 'value', 'type' => 'date', 'value' => $str_SendDate, 'settype' => 'date'),
//         );
//         $rs->Update('Fine', $aUpdate, "KindCreateDate='" . $str_CreationDate. "' AND CityId='".$_SESSION['cityid']."'");

        //Anche l'assegnazione di queste spese sarebbe fallita
        $a_update['Zone0Postage'] = array("d",$printercharge['OtherCost']);
        $a_update['Zone1Postage'] = array("d",$printercharge['OtherCost']);
        $a_update['Zone2Postage'] = array("d",$printercharge['OtherCost']);
        $a_update['Zone3Postage'] = array("d",$printercharge['OtherCost']);
        $a_update['PrintCost'] = array("d",$printercharge['OtherKindCost']);

    } else {

        $a_update['Zone0Postage'] = array("d", $printercharge['Zone0']);
        $a_update['Zone1Postage'] = array("d", $printercharge['Zone1']);
        $a_update['Zone2Postage'] = array("d", $printercharge['Zone2']);
        $a_update['Zone3Postage'] = array("d", $printercharge['Zone3']);
        $a_update['PrintCost'] = array("d", $printercharge['OtherCost']);

    }
    
    //Se il flusso è un avviso bonario, imposta StatusTypeId=9 per ogni verbale appartenente ad esso
    if($r_Flow['PrintTypeId'] == 3){
        trigger_error("aggiorno il flusso di avviso bonario: "."SELECT FineId FROM FineHistory WHERE Documentation='".$str_FileName."' AND NotificationTypeId=30", E_USER_WARNING);
        $rs_FineHistory = $rs->SelectQuery("SELECT FineId FROM FineHistory WHERE Documentation='".$str_FileName."' AND NotificationTypeId=30");
        if (mysqli_num_rows($rs_FineHistory) > 0){
            
            $aFineUpdate = array(
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 9),
            );
            
            while ($r_FineHistory = mysqli_fetch_assoc($rs_FineHistory)){
                $rs->Update('Fine', $aFineUpdate, "Id=".$r_FineHistory['FineId']. " AND StatusTypeId=8 ");
            }
        }
    }
    //Altrimenti se il flusso è una lettera invio bonario, imposta la KindSendDate per ogni verbale appartenente ad esso
    //01/03/2022 su richiesta di Ivana togliamo questa funzionalità in favore di una pagina da cui lei selezionerà i preinserimenti cui metter la data
    /*else if($r_Flow['PrintTypeId'] == 8){
        $rs_FineHistory = $rs->SelectQuery("SELECT FineId FROM FineHistory WHERE Documentation='".$str_FileName."' AND NotificationTypeId=31");
        if (mysqli_num_rows($rs_FineHistory) > 0){
            
            $aFineUpdate = array(
                array('field' => 'KindSendDate', 'selector' => 'value', 'type' => 'str', 'value' => $str_SendDate),
            );
            
            while ($r_FineHistory = mysqli_fetch_assoc($rs_FineHistory)){
                $rs->Update('Fine', $aFineUpdate, "Id=".$r_FineHistory['FineId']);
            }
        }
    }*/
    //////////////////////////////////////////////////////////////////////////////////////////////////
    
    $aUpdate = array(
        array('field' => 'SendDate', 'selector' => 'field', 'type' => 'date', 'settype' => 'date'),
    );

    $rs->Update('FineHistory', $aUpdate, "Documentation='" . $str_FileName. "'");


    $int_Zone0Number = CheckValue('Zone0Number','n');
    $int_Zone1Number = CheckValue('Zone1Number','n');
    $int_Zone2Number = CheckValue('Zone2Number','n');
    $int_Zone3Number = CheckValue('Zone3Number','n');
    $int_RecordsNumber = CheckValue('PrintNumber','n');

    $flt_Zone0Postage = CheckValue('Zone0Postage','f');
    $flt_Zone1Postage = CheckValue('Zone1Postage','f');
    $flt_Zone2Postage = CheckValue('Zone2Postage','f');
    $flt_Zone3Postage = CheckValue('Zone3Postage','f');
    $flt_PrintCost = CheckValue('PrintCost','f');

    $int_printInvoiceId = CheckValue('PrintInvoiceId','n');
    $int_postageInvoiceId = CheckValue('PostageInvoiceId','n');

    $a_update['PrintInvoiceId'] = array("i",$int_printInvoiceId);
    $a_update['PostageInvoiceId'] = array("i",$int_postageInvoiceId);
    if($printNumberCheck>0){
        $a_update['Zone0Number'] = array("i",$int_Zone0Number);
        $a_update['Zone1Number'] = array("i",$int_Zone1Number);
        $a_update['Zone2Number'] = array("i",$int_Zone2Number);
        $a_update['Zone3Number'] = array("i",$int_Zone3Number);
        $a_update['RecordsNumber'] = array("i",$int_RecordsNumber);
    }

    if($flt_Zone0Postage>0)
        $a_update['Zone0Postage'] = array("d",$flt_Zone0Postage);
    if($flt_Zone1Postage>0)
        $a_update['Zone1Postage'] = array("d",$flt_Zone1Postage);
    if($flt_Zone2Postage>0)
        $a_update['Zone2Postage'] = array("d",$flt_Zone2Postage);
    if($flt_Zone3Postage>0)
        $a_update['Zone3Postage'] = array("d",$flt_Zone3Postage);
    if($flt_PrintCost>0)
        $a_update['PrintCost'] = array("d",$flt_PrintCost);

}

$filter = "WHERE Id=".$Id;
$rs->bindUpdateArray("Flow",$a_update,$filter);

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

header("location: ".impostaParametriUrl(array('Filter' => 1), "mgmt_flow.php".$Filters));