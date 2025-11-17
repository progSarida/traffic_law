<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS . "/cls_file.php");
require_once(INC."/function.php");
require_once(INC."/function_postalCharge.php");
require_once(INC."/initialization.php");
ini_set('display_errors',1);
ini_set('max_execution_time', 0);

$a_City = array();
$a_NotificationCount = array();
$chkTolerance = 0;

include(CLS."/cls_notification.php");

$PrinterId = CheckValue('PrinterId','s');
$str_CurrentPage.="&PrinterId=".$PrinterId;

$cls_not = new cls_notification($PrinterId, null, false);

$ImportFile = CheckValue('ImportFile','s');
$P = CheckValue('P','s');

$queryResult = "SELECT PR.*, R.Title FROM Result R JOIN PrinterResult PR ON PR.ResultId = R.Id ";
$queryResult.= "WHERE PR.PrinterId=" . $PrinterId." AND R.Disabled=0";     ;
$rs_Results = $rs->getResults($rs->ExecuteQuery($queryResult));
if(count($rs_Results)==0){
    echo "LISTA NOTIFICHE STAMPATORE NON TROVATA";
    die;
}
$a_results = array();
foreach ($rs_Results as $key=>$a_result){
    $a_results[$a_result['Code']] = $a_result;
}

$cls_file = new cls_file();
$a_csv = $cls_file->getArrayFromCsv($cls_not->a_path['toImport'].$ImportFile);
$a_header = array(
    "stampatore", "numero_raccomandata", "numero_ricevuta_ritorno", "cc",
    "nazione", "tipo_stampa", "id_applicativo", "id_tipo_documento", "id_documento",
    "cronologico", "anno", "destinatario", "indirizzo", "cap", "localita",
    "cod_mancato_recapito", "mancato_recapito", "data_mancato_recapito",
    "cod_notifica", "notifica", "data_notifica",
    "data_spedizione", "data_log", "scatola", "lotto", "posizione",
    "img_unica", "img_fronte", "img_retro"
);

$rs->Start_Transaction();
$rs->Begin_Transaction();
$cont = 0;
foreach ($a_csv as $row) {
    if($cont==0){
        $a_missingHeader = $cls_file->checkFileHeaderInArray(array_keys($a_csv[0]), $a_header);
        if($a_missingHeader!=null){
            echo "INTESTAZIONE NON CONFORME AL MODELLO <br><br>Le seguenti colonne non sono state trovate: ";
            foreach ($a_missingHeader as $header)
                echo $header."<br>";
            die;
        }
    }

    $cont++;

    $r_Fine = null;
    if($row['id_documento']>0 && $row['cc']!=""){
        $queryFine = "SELECT F.* FROM Fine F ";
        $queryFine.= "WHERE F.Id=" . $row['id_documento'] . " AND F.CityId='" . $row['cc'] . "' ";
        if($row['cronologico']>0)
            $queryFine.= "AND F.ProtocolId=" . $row['cronologico'] ." ";
        if($row['anno']>0)
            $queryFine.= "AND F.ProtocolYear=" . $row['anno'] ." ";        ;

            echo $queryFine."<br><br>";

        $r_Fine = $rs->getArrayLine($rs->ExecuteQuery($queryFine));

    }



    if ($row['id_documento']=="" || $row['cc']=="" || is_null($r_Fine)) {
        // todo Verbale non presente

    }else{
        if (! in_array($row['cc'], $a_City)) {
            $a_City[] = $row['cc'];
            $a_NotificationCount[$row['cc']] = 0;
        }
        $a_NotificationCount[$row['cc']]++;

        $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id']. " AND NotificationTypeId=6");
        $r_FineHistory = mysqli_fetch_array($rs_FineHistory);

        if(isset($r_FineHistory['SendDate'])){
            // todo Data invio non coincidente
            if($r_FineHistory['SendDate']!=DateInDB($row['data_spedizione'])){
                // Controllo data se Anno spedizione < dell'anno cronologico sostituisco con data spedizione in FineHistory
                if((int)substr($row['data_spedizione'],-4)<(int)$row['anno'])
                    $row['data_spedizione'] = DateOutDB($r_FineHistory['SendDate']);
            }
        }
        if(!isset($r_FineHistory['DeliveryDate']) || is_null($r_FineHistory['DeliveryDate'])){
            if($row['cod_notifica']!="")
                $ResultId = $a_results[$row['cod_notifica']]['ResultId'];
            else if($row['cod_mancato_recapito']!="")
                $ResultId = $a_results[$row['cod_mancato_recapito']]['ResultId'];

            $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $r_Fine['Id']);
            $r_Tariff = mysqli_fetch_array($rs_Tariff);
            
            $rs_Article = $rs->Select('V_FineArticle', "Id=" . $FineId);
            $r_Article = mysqli_fetch_array($rs_Article);

            //LICENCE POINT
            $LicensePointProcedure = null;
            if(isset($r_Tariff['LicensePoint']))
                $LicensePointProcedure = $r_Tariff['LicensePoint'];
            $rs_TMP_PaymentProcedure = $rs->Select('TMP_LicensePointProcedure', "FineId=" .$r_Fine['Id']);
            if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                $LicensePointProcedure = 0;
                $rs->Delete('TMP_LicensePointProcedure', "FineId=".$r_Fine['Id']);
            }

            //PRESENTATION DOCUMENT
            $PresentationDocumentProcedure = null;
            if(isset($r_Tariff['PresentationDocument']))
                $PresentationDocumentProcedure = $r_Tariff['PresentationDocument'];
            $rs_TMP_PaymentProcedure = $rs->Select('TMP_PresentationDocumentProcedure', "FineId=" .$r_Fine['Id']);
            if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                $PresentationDocumentProcedure = 0;
                $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$r_Fine['Id']);
            }
            
            //devo però controllare anche se è un 193/2 o 80/14
            //(`F`.`KindCreateDate` is not null and ((`A`.`Article`=193 AND `A`.`Paragraph`='2') OR (`A`.`Article`=80 AND `A`.`Paragraph`='14')))
            if ($PresentationDocumentProcedure == null || $PresentationDocumentProcedure == 0) {
                if (isset($r_Fine['KindCreateDate']) && isset($r_Fine['KindSendDate']) 
                    && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
                    
                    $PresentationDocumentProcedure = 1;
                    $rs_TMP_PaymentProcedure = $rs->Select('TMP_PresentationDocumentProcedure', "FineId=" .$FineId);
                    if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                        $PresentationDocumentProcedure = 0;
                        $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$FineId);
                    }
                }
            }

            //126BIS
            $BisProcedure = null;
            if(isset($r_Tariff['126Bis']))
                $BisProcedure = $r_Tariff['126Bis'];
            $rs_TMP_PaymentProcedure = $rs->Select('TMP_126BisProcedure', "FineId=" .$r_Fine['Id']);
            if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                $BisProcedure = 0;
                $rs->Delete('TMP_126BisProcedure', "FineId=".$r_Fine['Id']);
            }

            $HabitualProcedure = null;
            if(isset($r_Tariff['Habitual']))
                $HabitualProcedure = $r_Tariff['Habitual'];
            $SuspensionLicenseProcedure = null;
            if(isset($r_Tariff['SuspensionLicense']))
                $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
            $LossLicenseProcedure = null;
            if(isset($r_Tariff['LossLicense']))
                $LossLicenseProcedure = $r_Tariff['LossLicense'];

            $a_FineNotification = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_Fine['Id'],'settype'=>'int'),
                array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>DateInDB($row['data_spedizione'])),
                array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>DateInDB($row['data_log'])),
                array('field'=>'Box','selector'=>'value','type'=>'str','value'=>$row['scatola']),
                array('field'=>'Lot','selector'=>'value','type'=>'str','value'=>$row['lotto']),
                array('field'=>'Position','selector'=>'value','type'=>'str','value'=>$row['posizione']),
                array('field'=>'ReceiptNumber','selector'=>'value','type'=>'str','value'=>$row['numero_ricevuta_ritorno']),
                array('field'=>'LetterNumber','selector'=>'value','type'=>'str','value'=>$row['numero_raccomandata']),
                array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$PresentationDocumentProcedure,'settype'=>'int'),
                array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$LicensePointProcedure,'settype'=>'int'),
                array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$HabitualProcedure,'settype'=>'int'),
                array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$SuspensionLicenseProcedure,'settype'=>'int'),
                array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$LossLicenseProcedure,'settype'=>'int'),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
            );

            $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure', "FineId=" .$r_Fine['Id']);
            if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                array_push($a_FineNotification, array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'));
                $rs->Delete('TMP_PaymentProcedure', "FineId=".$r_Fine['Id']);
            }

            $a_FineHistory = array(
                array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
            );

            if($ResultId<10 || $ResultId == 22){
                $StatusTypeId = 25;
                array_push($a_FineNotification, array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>DateInDB($row['data_notifica'])));
                array_push($a_FineHistory,array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>DateInDB($row['data_notifica'])));

                if($ResultId>=2 && $ResultId<=4){
                    $r_CadCanFee=getPostalCharge($_SESSION['cityid'],DateInDB($row['data_notifica']));

                    if($ResultId==2 OR $ResultId==4){
                        $CadFee = $r_CadCanFee['CadFee'];

                        array_push($a_FineHistory,array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$CadFee,'settype'=>'flt'));
                    }
                    if($ResultId==3){
                        $CanFee = $r_CadCanFee['CanFee'];

                        array_push($a_FineHistory,array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$CanFee,'settype'=>'flt'));
                    }
                }
            }else{
                $StatusTypeId = 23;
            }

            if($r_Fine['StatusTypeId']==20){
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
                );
                $rs->Update('Fine',$a_Fine, "Id=".$r_Fine['Id']);
            }

            $rs->Insert('FineNotification',$a_FineNotification);
            $rs->Update('FineHistory',$a_FineHistory, "FineId=".$r_Fine['Id']." AND NotificationTypeId=6");

            $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;

            if($row['img_unica']!=""){
                $a_images[0]['name'] = $row['img_unica'];
                $a_images[0]['path'] = $cls_not->a_path['toImport'].$row['img_unica'];
                $a_images[0]['documentationType'] = 82;
            }
            else{
                $a_images[0]['name'] = $row['img_fronte'];
                $a_images[0]['path'] = $cls_not->a_path['toImport'].$row['img_fronte'];
                $a_images[0]['documentationType'] = 10;

                $a_images[1]['name'] = $row['img_retro'];
                $a_images[1]['path'] = $cls_not->a_path['toImport'].$row['img_retro'];
                $a_images[1]['documentationType'] = 11;
            }

            if(!file_exists($str_Folder."/".$row['cc']."/".$r_Fine['Id']."/"))
                mkdir($str_Folder."/".$row['cc']."/".$r_Fine['Id']."/");
            foreach($a_images as $key=>$a_img){
                if (file_exists($a_img['path'])) {
                    $a_FineDocumentation = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_Fine['Id'],'settype'=>'int'),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$a_img['name']),
                        array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$a_img['documentationType'],'settype'=>'int'),
                    );
                    $rs->Insert('FineDocumentation',$a_FineDocumentation);

                    $newFile = $str_Folder."/".$row['cc']."/".$r_Fine['Id']."/".$a_img['name'];
                    copy($a_img['path'], $newFile);


                    if (file_exists($newFile)){
                        unlink($a_img['path']);
                    }
                    else{
                        echo "Problemi con la creazione del documento: ".$a_img['name'];
                        die;
                    }
                }
            }
        }
        else
        {
            //todo chk_LogDate
        }
    }
}

$rs->End_Transaction();
unlink($cls_not->a_path['toImport'].$ImportFile);

for($i=0; $i<count($a_City);$i++){

    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$a_City[$i]."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono state importate n. ".$a_NotificationCount[$a_City[$i]]." notifiche.";

        $a_Mail = array(
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail',$a_Mail);
        $rs->End_Transaction();
    }
}

header("location: ".$P."?PrinterId=".$PrinterId);
