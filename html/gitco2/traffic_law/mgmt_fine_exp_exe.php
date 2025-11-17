<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

$Id= CheckValue('Id','n');
$P= CheckValue('P','s');
$Note= CheckValue('Note','s');
$ReasonId = CheckValue('ReasonId','n');
$n_Duplicate=CheckValue('duplicate','n');
$TrespasserId=CheckValue('TrespasserId','n');
$ProtocolId = CheckValue('ProtocolId','n');
$n_AddNotificationFee = CheckValue('AddNotificationFee','n');
$n_AddResearchFee = CheckValue('AddResearchFee','n');
$ArchiveDate = DateInDB(CheckValue('ArchiveDate','s'));
$CountryId = CheckValue('CountryId','s');
$n_Violation= CheckValue('Search_Violation','n');
$n_Status= CheckValue('Search_Status','n');
$s_TypePlate = CheckValue('TypePlate','s');
$NotificationDate = date("Y-m-d");
$ReceiveDate = CheckValue('ReceiveDate','s');

$PreviousStatusTypeId = CheckValue('PreviousStatusTypeId','n');
$PreviousNote = StringOutDB(CheckValue('PreviousNote','s'));
$FineChiefControllerId = CheckValue('FineChiefControllerId','n');
$UIFineChiefControllerId = CheckValue('UIFineChiefControllerId','n');

//In caso di rinotifica per messo blocca la procedura se qualcun altro stà creando atti, in modo che 
//non inserisca la parte di dati del nuovo atto prima di essere bloccata quando entra nella stampa verbale
if($n_Duplicate==4){
    $rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
        if ($r_Locked['Locked'] == 1) {
            $_SESSION['POST'] = $_POST;
            $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
            header("location: mgmt_fine_exp.php".$str_Parameters.'&Id='.$Id);
            DIE;
        }
    }
}

//RADIO 6
function rinotifica_tnf($rs){
    $error="";
    $Id= CheckValue('Id','n');
    $TrespasserBId=CheckValue('TrespasserId','n');
    $ReceiveDate = CheckValue('ReceiveDate','s');
    $n_AddNotificationFee = CheckValue('AddNotificationFee','n');
    $n_AddResearchFee = CheckValue('AddResearchFee','n');
    
    $aFine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>14,'settype'=>'int'),
        array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
    );
    
    
    //Tipo di rinotifica 10 - annullato
    $aFineTrespasserA = array(
        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>2,'settype'=>'int'),
        array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>10,'settype'=>'int'),
    );
    
    $aFineTrespasserB = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserBId, 'settype'=>'int'),
        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
        array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>$ReceiveDate),
        
    );
    
    $rs_Fine = $rs->Select('Fine',"Id=$Id");
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
    $rs->Start_Transaction();
    
    if(mysqli_num_rows($rs_Fine) > 0){
        $rs_FineTrespasserA = $rs->Select('FineTrespasser',"FineId=$Id");
        $r_FineTrespasserA = mysqli_fetch_array($rs_FineTrespasserA);
        $rs_FineTrespasserB = $rs->Select('FineTrespasser',"FineId=$Id AND TrespasserId=$TrespasserBId");
        $rs_TrespasserB = $rs->Select('Trespasser',"Id=$TrespasserBId");
        
        if(mysqli_num_rows($rs_FineTrespasserA) > 0 && mysqli_num_rows($rs_TrespasserB) > 0){
            //RIMETTI A POSTO QUESTI 2 IF!!!! TOGLI I "!"
            if (!mysqli_num_rows($rs_FineTrespasserB) > 0){
                if (mysqli_num_rows($rs_FineTrespasserA) == 1 && $r_FineTrespasserA['TrespasserTypeId'] == 1){
                    $rs_FineCommunication = $rs->Select('FineCommunication', "FineId=$Id");
                    $r_FineCommunication = mysqli_fetch_array($rs_FineCommunication);
                    $rs_FineNotification = $rs->Select('FineNotification',"FineId=$Id");
                    $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
                    $rs_SentDate = $rs->SelectQuery("SELECT Id, COALESCE(SendDate,NotificationDate) AS SentDate FROM FineHistory WHERE FineId=$Id AND NotificationTypeId=2");
                    $r_SentDate = mysqli_fetch_array($rs_SentDate);
                    $rs_NotificationDate = $rs->SelectQuery("SELECT Id, NotificationDate FROM FineHistory WHERE FineId=$Id AND NotificationTypeId=6");
                    $r_NotificationDate = mysqli_fetch_array($rs_NotificationDate);
                    $rs_FineDocumentation = $rs->Select("FineDocumentation", "FineId=".$Id." AND (DocumentationTypeId=10 OR DocumentationTypeId=11 OR DocumentationTypeId=12)");
                    
                    $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
                    
                    //print_r("FineId:$Id<br /><br />");
                    
                    //TRESP_A
                    if(empty($r_FineTrespasserA['FineCreateDate'])){
                        //echo "FineCreateDate è nulla<br />";
                        $aFineTrespasserA[] = array('field'=>'FineCreateDate','selector'=>'value','type'=>'date','value'=>$r_Fine['FineDate']);
                        
                    }
                    if(empty($r_FineTrespasserA['FineSendDate'])){
                        //echo "FineSendDate è nulla<br />";
                        if(mysqli_num_rows($rs_SentDate) > 0){
                            $aFineTrespasserA[] = array('field'=>'FineSendDate','selector'=>'value','type'=>'date','value'=>$r_SentDate['SentDate']);
                        }      
                    }
                    if(empty($r_FineTrespasserA['FineNotificationDate'])){
                        //echo "FineNotificationDate è nulla<br />";
                        //Valorizzo $r_NotificationDate da FineHistory se il record esiste
                        if(mysqli_num_rows($rs_NotificationDate) > 0){
                            $notificationDate = $r_NotificationDate['NotificationDate'];
                        }   
                        //Sovrascrivo $r_NotificationDate con FineNotification se il record esiste
                        if (!empty($r_FineNotification['NotificationDate']))
                        {
                            $notificationDate = $r_FineNotification['NotificationDate'];
                            //echo "Ho valorizzato da FineNotification<br />";
                        }
                        //Metto $r_NotificationDate['FineNotification'] nell'array per l'update se sono riuscito a valorizzarlo
                        if (!empty($notificationDate))
                            $aFineTrespasserA[] = array('field'=>'FineNotificationDate','selector'=>'value','type'=>'date','value'=>$notificationDate);
                    }
                    if(empty($r_FineTrespasserA['ReceiveDate'])){
                        //echo "ReceiveDate è nulla<br />";
                        if(mysqli_num_rows($rs_FineCommunication) > 0){
                            $aFineTrespasserA[] = array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>$r_FineCommunication['CommunicationDate']);
                        }
                    }
                    
                    //echo '<pre> TRESP_A: ',print_r($aFineTrespasserA),'</pre>';
                    
                    //TRESP_B
                    $OwnerAdditionalFee = 0.00;
                    $rs_Customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
                    $r_Customer = mysqli_fetch_array($rs_Customer);
                    $LumpSum = $r_Customer['LumpSum'];
                    $rs_CustomerAdditionalFee = $rs->SelectQuery("SELECT CustomerFee AS CustomerAdditionalFee FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
                    $r_CustomerAdditionalFee = mysqli_fetch_array($rs_CustomerAdditionalFee);
                    $CustomerAdditionalFee = $r_CustomerAdditionalFee['CustomerAdditionalFee'];
                    if($n_AddNotificationFee){
                        $rs_Fee = $rs->SelectQuery("SELECT (NotificationFee + CanFee + CadFee + NotifierFee + OtherFee) AS AddNotificationFee1,NotificationFee AS AddNotificationFee0 FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
                        $r_Fee = mysqli_fetch_array($rs_Fee);
                        $OwnerAdditionalFee += $r_Fee['AddNotificationFee'.$LumpSum]-$CustomerAdditionalFee;
                        
                    }
                    if($n_AddResearchFee){
                        $rs_Fee = $rs->SelectQuery("SELECT (ResearchFee) AS AddResearchFee FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
                        $r_Fee = mysqli_fetch_array($rs_Fee);
                        $OwnerAdditionalFee += $r_Fee['AddResearchFee'];
                    }
                    
                    $aFineTrespasserB[] = array('field'=>'OwnerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$OwnerAdditionalFee,'settype'=>'flt');
                    $aFineTrespasserB[] = array('field'=>'CustomerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$CustomerAdditionalFee,'settype'=>'flt');
                    
                    //echo '<pre> TRESP_B: ',print_r($aFineTrespasserB),'</pre>';
                    
                    //QUERIES
                    $rs->Update('Fine',$aFine, 'Id='.$Id);
                    $rs->Update('FineTrespasser',$aFineTrespasserA, 'FineId='.$Id);
                    $rs->Insert('FineTrespasser',$aFineTrespasserB);
                    
                    if (mysqli_num_rows($rs_SentDate) > 0)
                        //echo "FineHistory: Cancellazione per SentDate prevista<br />";
                        $rs->Delete("FineHistory", "Id=" .$r_NotificationDate['Id']);
                    if (mysqli_num_rows($rs_NotificationDate) > 0)
                        //echo "FineHistory: Cancellazione per NotificationDate prevista<br />";
                        $rs->Delete('FineHistory', "Id=" .$r_SentDate['Id']);
                    if (mysqli_num_rows($rs_FineNotification) > 0)
                        //echo "Cancellazione per FineNotification prevista<br />";
                        $rs->Delete('FineNotification', "FineId=" .$Id);
                        
                    if (is_dir($str_Folder."/".$_SESSION['cityid']."/".$Id)) {
                        while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
                           $Documentation = $r_FineDocumentation['Documentation'];
                           $DocumentationId = $r_FineDocumentation['Id'];
                           if(unlink($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$Documentation))
                               $rs->Delete('FineDocumentation',"Id = $DocumentationId");
                       }
                    }
                    if (count(scandir($str_Folder."/".$_SESSION['cityid']."/".$Id)) == 2) {
                        rmdir($str_Folder."/".$_SESSION['cityid']."/".$Id);
                    }
                    
//                     while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
//                         print_r($r_FineDocumentation);
//                         echo "<br />";
//                     }
                } else $error = "Il verbale è già stato notificato a più di un trasgressore. Verificare il tipo di verbale e i trasgressori associati.";
            } else $error = "Il trasgressore non firmatario è già stato assegnato a questo verbale: ".$Id;
        } else $error = "Trasgressore o non firmatario non trovati per id: ".$Id;
    } else $error = "Verbale non trovato per id: ".$Id;
    
    $rs->End_Transaction();
    
    return $error;
}

if($n_Duplicate==6){
    $error = rinotifica_tnf($rs);
    if (!empty($error)) 
        $_SESSION['Fine_exp']['Error'] = $error;
    else 
        $_SESSION['Fine_exp']['Success'] = "Azione eseguita con successo";
    header("location: mgmt_fine_exp.php" . $str_GET_Parameter . '&P=mgmt_fine.php' . '&Id=' . $Id);
    exit;
}

trigger_error("Archiviazione di tipo ".$n_Duplicate." dell'atto Id ".$Id);

if($n_Duplicate==2 || $n_Duplicate==4 || $n_Duplicate==7){
    $StatusTypeId = 33;
}else $StatusTypeId = ($n_Duplicate==3) ? 36 : 35;

$rs->Start_Transaction();

$aFine = array(
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
    array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$FineChiefControllerId),
    array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$UIFineChiefControllerId)
);
//aggiornamento dello stato del verbale originale
$rs->Update('Fine',$aFine, 'Id='.$Id);
trigger_error("Archiviazione di tipo ".$n_Duplicate." dell'atto Id ".$Id." Nuovo stato: ".$StatusTypeId, E_USER_WARNING);

if($n_Duplicate!=2){
    $a_FineArchive = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
        array('field'=>'ReasonId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'ArchiveDate','selector'=>'value','type'=>'date','value'=>$ArchiveDate),
        array('field'=>'Note','selector'=>'field','type'=>'str'),
        array('field'=>'PreviousStatusTypeId','selector'=>'value','type'=>'int','value'=>$PreviousStatusTypeId,'settype'=>'int'),
        array('field'=>'PreviousNote','selector'=>'value','type'=>'str','value'=>$PreviousNote),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    $rs->Insert('FineArchive',$a_FineArchive);
}
if($n_Duplicate>0){
    $VehiclePlate = strtoupper(CheckValue('VehiclePlate','s'));
    $VehicleCountry = CheckValue('VehicleCountry','s');
    $CountryId = CheckValue('CountryId','s');
    $VehicleTypeId = CheckValue('VehicleTypeId','n');
    $VehicleBrand = CheckValue('VehicleBrand','s');
    $VehicleModel = CheckValue('VehicleModel','s');
    $VehicleColor = CheckValue('VehicleColor','s');
    $Note = "Violazione duplicata: ID ".$Id;
    $rs_Fine = $rs->Select('Fine',"Id=$Id");
    $r_Fine = mysqli_fetch_array($rs_Fine);
    $Code = $r_Fine['Code'];
    $FineDate = DateTime::createFromFormat("Y-m-d", $r_Fine['FineDate']);
    $FineTime = $r_Fine['FineTime'];
    $FineTypeId = $r_Fine['FineTypeId'];
    $ControllerId = $r_Fine['ControllerId'];
    $ControllerDate = $r_Fine['ControllerDate'];
    $ControllerTime = $r_Fine['ControllerTime'];
    $Locality = $r_Fine['Locality'];
    $StreetTypeId = $r_Fine['StreetTypeId'];
    $Address = $r_Fine['Address'];
    $VehicleMass = $r_Fine['VehicleMass'];
    $KindCreateDate = $r_Fine['KindCreateDate'];
    $KindSendDate = $r_Fine['KindSendDate'];
    $IuvCode = $r_Fine['IuvCode'];
    $DepartmentId = 0;
    $PreviousId = ($r_Fine['PreviousId']==0) ? $Id : $r_Fine['PreviousId'];
    $ProtocolYear = $r_Fine['ProtocolYear'];
    $GpsLat = $r_Fine['GpsLat'];
    $GpsLong = $r_Fine['GpsLong'];

    //se l'ente prevede la gestione del PagoPa resettiamo gli Iuv nella rinotifica
    // in modo da farli elaborare per il preinserimento generato dalla rinotifica
    if ($r_Fine['CountryId']=='Z000'){
        $PagoPA1 = $r_Customer['PagoPAPayment'] == 1 ? null : $r_Fine['PagoPA1'];
        $PagoPA2 = $r_Customer['PagoPAPayment'] == 1 ? null : $r_Fine['PagoPA2'];
    } else {
        $PagoPA1 = $r_Customer['PagoPAPaymentForeign'] == 1 ? null : $r_Fine['PagoPA1'];
        $PagoPA2 = $r_Customer['PagoPAPaymentForeign'] == 1 ? null : $r_Fine['PagoPA2'];
    }

    $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
    $str_FolderDuplicate = ($CountryId=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
    //nota se il verbale è contratto deve essere rinotificato come contratto
    // se è contratto va copiato anche il documento da una cartella all'altra
    $a_Fine = array(
        array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>1),
        array('field'=>'FineDate','selector'=>'value','type'=>'date','value'=>$FineDate->format("Y-m-d")),
        array('field'=>'FineTime','selector'=>'value','type'=>'str','value'=>$FineTime),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId, 'settype'=>'int'),
        array('field'=>'ControllerDate','selector'=>'value','type'=>'date','value'=>$ControllerDate),
        array('field'=>'ControllerTime','selector'=>'value','type'=>'str','value'=>$ControllerTime),
        array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
        array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>$StreetTypeId, 'settype'=>'int'),
        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
        array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$DepartmentId,'settype'=>'int'),
        array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$VehicleTypeId,'settype'=>'int'),
        array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
        array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$VehicleCountry),
        array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId),
        array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$VehicleBrand),
        array('field'=>'VehicleModel','selector'=>'value','type'=>'str','value'=>$VehicleModel),
        array('field'=>'VehicleColor','selector'=>'value','type'=>'str','value'=>$VehicleColor),
        array('field'=>'PreviousId','selector'=>'value','type'=>'int','value'=>$PreviousId,'settype'=>'int'),
        array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$VehicleMass,'settype'=>'flt'),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
        array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$Note),
        array('field'=>'KindCreateDate','selector'=>'value','type'=>'date','value'=>$KindCreateDate),
        array('field'=>'KindSendDate','selector'=>'value','type'=>'date','value'=>$KindSendDate),
        array('field'=>'IuvCode','selector'=>'value','type'=>'str','value'=>$IuvCode),
        array('field'=>'GpsLat','selector'=>'value','type'=>'str','value'=>$GpsLat),
        array('field'=>'GpsLong','selector'=>'value','type'=>'str','value'=>$GpsLong),
        array('field'=>'PagoPA1','selector'=>'value','type'=>'str','value'=>$PagoPA1),
        array('field'=>'PagoPA2','selector'=>'value','type'=>'str','value'=>$PagoPA2),
        array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$FineChiefControllerId),
        array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$UIFineChiefControllerId)
    );

    //se il verbale è contratto lo risalvo come verbale contratto
    if($FineTypeId==4){
        $a_Fine[] =  array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>$FineTypeId, 'settype'=>'int');
    }

    //per i record di rinotifica che devono conservare il protocollo, l'anno d'esercizio deve essere uguale a quello dell'atto di partenza
    //in modo che conservi cronologico e anno. Per gli altri può essere preso dall'anno di esercizio in sessione
    //NOTA: per gli atti che hanno un cronologico nuovo è stato cambiato il comportamento con Bug 3490:
    /*Ciao,
    
    ti confermo che per tutti i tipi di rinotifiche fa fede la data di infrazione e non la data di identificazione.
    Questo vale sia per le infrazioni italiane che per l'estero.
    Sono solo i 126 bis che possono fare riferimento a verbali di un anno ma finire poi nell'anno successivo in quanto la data di elaborazione diventa anche la data di infrazione.
    
    Laura*/
    if($n_Duplicate==2 || $n_Duplicate==4 || $n_Duplicate==7){
        $a_Fine[] =  array('field'=>'ProtocolIdAssigned','selector'=>'value','type'=>'int','value'=>$ProtocolId, 'settype'=>'int');
        $a_Fine[] = array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$ProtocolYear);
    }
    else {
        $a_Fine[] = array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$FineDate->format("Y"));
    }
    $FineId = $rs->Insert('Fine',$a_Fine);
    
    $rs_FineArticle = $rs->Select('FineArticle',"FineId=$Id");
    $r_FineArticle = mysqli_fetch_array($rs_FineArticle);
    $ArticleId = $r_FineArticle['ArticleId'];
    $ViolationTypeId = $r_FineArticle['ViolationTypeId'];
    $Fee = $r_FineArticle['Fee'];
    $MaxFee   = $r_FineArticle['MaxFee'];
    $DetectorId = $r_FineArticle['DetectorId'];
    $SpeedLimit = $r_FineArticle['SpeedLimit'];
    $SpeedControl = $r_FineArticle['SpeedControl'];
    $SpeedTimeAverage = $r_FineArticle['SpeedTimeAverage'];
    $Speed = $r_FineArticle['Speed'];
    $ReasonId = $r_FineArticle['ReasonId'];

    $TimeTLightFirst = $r_FineArticle['TimeTLightFirst'];
    $TimeTLightSecond = $r_FineArticle['TimeTLightSecond'];
    $RoadSideDistance = $r_FineArticle['RoadSideDistance'];
    $ArticleNumber = $r_FineArticle['ArticleNumber'];
    $TrespasserId1_180 = $r_FineArticle['TrespasserId1_180'];
    $DayNumber_180 = $r_FineArticle['DayNumber_180'];
    $ExpirationDate = $r_FineArticle['ExpirationDate'];


    $a_FineArticle = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
        array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
        array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId,'settype'=>'int'),
        array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
        array('field'=>'DetectorId','selector'=>'value','type'=>'int','value'=>$DetectorId,'settype'=>'int'),
        array('field'=>'SpeedLimit','selector'=>'value','type'=>'flt','value'=>$SpeedLimit,'settype'=>'flt'),
        array('field'=>'SpeedControl','selector'=>'value','type'=>'flt','value'=>$SpeedControl,'settype'=>'flt'),
        array('field'=>'SpeedTimeAverage','selector'=>'value','type'=>'flt','value'=>$SpeedTimeAverage,'settype'=>'flt'),
        array('field'=>'Speed','selector'=>'value','type'=>'flt','value'=>$Speed,'settype'=>'flt'),
        array('field'=>'TimeTLightFirst','selector'=>'value','type'=>'int','value'=>$TimeTLightFirst,'settype'=>'int'),
        array('field'=>'TimeTLightSecond','selector'=>'value','type'=>'int','value'=>$TimeTLightSecond,'settype'=>'int'),
        array('field'=>'RoadSideDistance','selector'=>'value','type'=>'int','value'=>$RoadSideDistance,'settype'=>'int'),
        array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$ArticleNumber,'settype'=>'int'),
        array('field'=>'TrespasserId1_180','selector'=>'value','type'=>'int','value'=>$TrespasserId1_180,'settype'=>'int'),
        array('field'=>'DayNumber_180','selector'=>'value','type'=>'int','value'=>$DayNumber_180,'settype'=>'int'),
        array('field'=>'ExpirationDate','selector'=>'value','type'=>'date','value'=>$ExpirationDate,'settype'=>'date'),
    );
    $rs->Insert('FineArticle',$a_FineArticle);

    $rs_FineOwner = $rs->Select('FineOwner',"FineId=$Id");
    if(mysqli_num_rows($rs_FineOwner)>0){
        $rs->SelectQuery("
            INSERT INTO FineOwner (FineId,ArticleDescriptionIta,ArticleDescriptionEng,ArticleDescriptionGer,ArticleDescriptionSpa,
ArticleDescriptionFre,ReasonDescriptionIta,ReasonDescriptionEng,ReasonDescriptionGer,ReasonDescriptionSpa,ReasonDescriptionFre,
AdditionalDescriptionIta,AdditionalDescriptionEng,AdditionalDescriptionGer,AdditionalDescriptionSpa,AdditionalDescriptionFre,
DeclarationDescriptionIta,DeclarationDescriptionEng,DeclarationDescriptionGer,DeclarationDescriptionSpa,DeclarationDescriptionFre,
DamageDescriptionIta,DamageDescriptionEng,DamageDescriptionGer,DamageDescriptionSpa,DamageDescriptionFre,
RemovalDescriptionIta,RemovalDescriptionEng,RemovalDescriptionGer,RemovalDescriptionSpa,RemovalDescriptionFre,
NoteDescriptionIta,NoteDescriptionEng,NoteDescriptionGer,NoteDescriptionSpa,NoteDescriptionFre,
ArticleDescriptionRom,ArticleDescriptionPor,ArticleDescriptionPol,ArticleDescriptionHol,ArticleDescriptionAlb,
ArticleDescriptionDen,ReasonDescriptionRom,ReasonDescriptionPor,ReasonDescriptionPol,ReasonDescriptionHol,ReasonDescriptionAlb,
ReasonDescriptionDen,AdditionalDescriptionRom,AdditionalDescriptionPor,AdditionalDescriptionPol,AdditionalDescriptionHol,
AdditionalDescriptionAlb,AdditionalDescriptionDen,DeclarationDescriptionRom,DeclarationDescriptionPor,DeclarationDescriptionPol,DeclarationDescriptionHol,
DeclarationDescriptionAlb,DeclarationDescriptionDen,DamageDescriptionRom,DamageDescriptionPor,DamageDescriptionPol,
DamageDescriptionHol,DamageDescriptionAlb,DamageDescriptionDen,RemovalDescriptionRom,RemovalDescriptionPor,RemovalDescriptionPol,
RemovalDescriptionHol,RemovalDescriptionAlb,RemovalDescriptionDen,NoteDescriptionRom,NoteDescriptionPor,NoteDescriptionPol,
NoteDescriptionHol,NoteDescriptionAlb,NoteDescriptionDen
    )
    SELECT
    ". $FineId .", 
    ArticleDescriptionIta,
    ArticleDescriptionEng,
    ArticleDescriptionGer,
    ArticleDescriptionSpa,
    ArticleDescriptionFre,
    ReasonDescriptionIta,
    ReasonDescriptionEng,
    ReasonDescriptionGer,
    ReasonDescriptionSpa,
    ReasonDescriptionFre,
    AdditionalDescriptionIta,
    AdditionalDescriptionEng,
    AdditionalDescriptionGer,
    AdditionalDescriptionSpa,
    AdditionalDescriptionFre,
    DeclarationDescriptionIta,
    DeclarationDescriptionEng,
    DeclarationDescriptionGer,
    DeclarationDescriptionSpa,
    DeclarationDescriptionFre,
    DamageDescriptionIta,
    DamageDescriptionEng,
    DamageDescriptionGer,
    DamageDescriptionSpa,
    DamageDescriptionFre,
    RemovalDescriptionIta,
    RemovalDescriptionEng,
    RemovalDescriptionGer,
    RemovalDescriptionSpa,
    RemovalDescriptionFre,
    NoteDescriptionIta,
    NoteDescriptionEng,
    NoteDescriptionGer,
    NoteDescriptionSpa,
    NoteDescriptionFre,
    ArticleDescriptionRom,
    ArticleDescriptionPor,
    ArticleDescriptionPol,
    ArticleDescriptionHol,
    ArticleDescriptionAlb,
    ArticleDescriptionDen,
    ReasonDescriptionRom,
    ReasonDescriptionPor,
    ReasonDescriptionPol,
    ReasonDescriptionHol,
    ReasonDescriptionAlb,
    ReasonDescriptionDen,
    AdditionalDescriptionRom,
    AdditionalDescriptionPor,
    AdditionalDescriptionPol,
    AdditionalDescriptionHol,
    AdditionalDescriptionAlb,
    AdditionalDescriptionDen,
    DeclarationDescriptionRom,
    DeclarationDescriptionPor,
    DeclarationDescriptionPol,
    DeclarationDescriptionHol,
    DeclarationDescriptionAlb,
    DeclarationDescriptionDen,
    DamageDescriptionRom,
    DamageDescriptionPor,
    DamageDescriptionPol,
    DamageDescriptionHol,
    DamageDescriptionAlb,
    DamageDescriptionDen,
    RemovalDescriptionRom,
    RemovalDescriptionPor,
    RemovalDescriptionPol,
    RemovalDescriptionHol,
    RemovalDescriptionAlb,
    RemovalDescriptionDen,
    NoteDescriptionRom,
    NoteDescriptionPor,
    NoteDescriptionPol,
    NoteDescriptionHol,
    NoteDescriptionAlb,
    NoteDescriptionDen       
    
    FROM FineOwner
    WHERE FineId=".$Id.";
        ");
    }

if($ArticleNumber>1){

    $rs->SelectQuery("
        INSERT INTO FineAdditionalArticle (FineId,ArticleId,CityId,Fee,MaxFee,ExpirationDate,ArticleOrder,
        ArticleDescriptionIta,ArticleDescriptionEng,ArticleDescriptionGer,ArticleDescriptionSpa,
        ArticleDescriptionFre,ArticleDescriptionRom,ArticleDescriptionPor,ArticleDescriptionPol,
        ArticleDescriptionHol,ArticleDescriptionAlb,ArticleDescriptionDen
        ) 
        SELECT 
        ".$FineId.",
        ArticleId,
        CityId,
        Fee,
        MaxFee,
        ExpirationDate,
        ArticleOrder,
        ArticleDescriptionIta,
        ArticleDescriptionEng,
        ArticleDescriptionGer,
        ArticleDescriptionSpa,
        ArticleDescriptionFre,
        ArticleDescriptionRom,
        ArticleDescriptionPor,
        ArticleDescriptionPol,
        ArticleDescriptionHol,
        ArticleDescriptionAlb,
        ArticleDescriptionDen
        
        FROM FineAdditionalArticle 
        WHERE FineId=".$Id.";
    
    ");


}

    $rs_FineAdditionalController = $rs->Select('FineAdditionalController',"FineId=$Id");
    if(mysqli_num_rows($rs_FineAdditionalController)>0){
        $rs->SelectQuery("
            INSERT INTO FineAdditionalController (FineId,ControllerId) 
            SELECT 
            ".$FineId.",
            ControllerId
            
            FROM FineAdditionalController 
            WHERE FineId=".$Id.";
        ");
    }


    
    if($TrespasserId>0){
        $TrespasserTypeId = 1;
        if($n_Duplicate>1){
            $rs_trespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id." AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
            $r_trespasser = mysqli_fetch_array($rs_trespasser);
            
            //rinotifica a stesso trasgressore o tramite messo
            if($n_Duplicate==2 || $n_Duplicate==4){
                $TrespasserId = $r_trespasser['TrespasserId'];
                //se era noleggio a lungo termine rinotifica alla ditta di noleggio
                if($r_trespasser['TrespasserTypeId']==11){
                    $rs_TmpTrespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id." AND TrespasserTypeId=10");
                    $r_TmpTrespasser = mysqli_fetch_array($rs_TmpTrespasser);

                    $RentId = $r_TmpTrespasser['TrespasserId'];
                    $TrespasserTypeId = 10;
                    $a_FineTrespasser = array(
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$RentId, 'settype'=>'int'),
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                        array('field'=>'AssociatedOnImport','selector'=>'value','type'=>'int','value'=>$r_TmpTrespasser['AssociatedOnImport'],'settype'=>'int'),
                    );
                    
                    //tramite messo metto il tipo di notifica sul trasgressore di tipo messo (tipo 2)
                    if($n_Duplicate==4) {
                        $a_FineTrespasser[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int');
                    }
                    
                    $rs->Insert('FineTrespasser',$a_FineTrespasser);
                    $TrespasserTypeId = 11;

                }

            }
            //"3" Rinotifica per leasing/noleggio a lungo termine
            if($n_Duplicate==3){
                $RentId = $r_trespasser['TrespasserId']; //trasgressore di tipo obbligato viene riportato al tipo Noleggio/Leasing
                $TrespasserTypeId = 10;
                $a_FineTrespasser = array(
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$RentId, 'settype'=>'int'),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                    array('field'=>'AssociatedOnImport','selector'=>'value','type'=>'int','value'=>$r_trespasser['AssociatedOnImport'],'settype'=>'int'),
                );
                $rs->Insert('FineTrespasser',$a_FineTrespasser);
                $TrespasserTypeId = 11;
            }
            
            //"7" Rinotifica aggiungendo l'obbligato in solido (noleggio a breve termine)
            if($n_Duplicate==7){
                $RentId = $r_trespasser['TrespasserId']; //trasgressore di tipo obbligato viene riportato al tipo Noleggio/Leasing
                $rs_FineHistory = $rs->Select('FineHistory', "NotificationTypeId=6 AND TrespasserId=$RentId AND FineId=$FineId");
                $r_FineHistory = $rs->getArrayLine($rs_FineHistory);
                
                $TrespasserTypeId = 2;
                $a_FineTrespasser = array(
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$RentId, 'settype'=>'int'),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                    //TODO (Bug 1673) se non c'è la DeliveryDate della figura originale potremmo provare a metterci la ReceiveDate (data identificazione) della seconda figura
                    array('field'=>'FineCreateDate','selector'=>'value','type'=>'str','value'=>$r_FineHistory['DeliveryDate'] ?? null, 'nullable' => true),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                    array('field'=>'AssociatedOnImport','selector'=>'value','type'=>'int','value'=>$r_trespasser['AssociatedOnImport'],'settype'=>'int'),
                );
                $rs->Insert('FineTrespasser',$a_FineTrespasser);
                $TrespasserTypeId = 3;
            }
        }
        $OwnerAdditionalFee = 0.00;

        $LumpSum = $r_Customer['LumpSum'];
        $rs_CustomerAdditionalFee = $rs->SelectQuery("SELECT CustomerFee AS CustomerAdditionalFee FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
        $r_CustomerAdditionalFee = mysqli_fetch_array($rs_CustomerAdditionalFee);
        $CustomerAdditionalFee = $r_CustomerAdditionalFee['CustomerAdditionalFee'];
        if($n_AddNotificationFee){
            $rs_Fee = $rs->SelectQuery("SELECT (NotificationFee + CanFee + CadFee + NotifierFee + OtherFee) AS AddNotificationFee1,NotificationFee AS AddNotificationFee0 FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
            $r_Fee = mysqli_fetch_array($rs_Fee);
            $OwnerAdditionalFee += $r_Fee['AddNotificationFee'.$LumpSum]-$CustomerAdditionalFee;

        }
        if($n_AddResearchFee){
            $rs_Fee = $rs->SelectQuery("SELECT (ResearchFee) AS AddResearchFee FROM FineHistory WHERE FineId=".$Id." AND NotificationTypeId=6");
            $r_Fee = mysqli_fetch_array($rs_Fee);
            $OwnerAdditionalFee += $r_Fee['AddResearchFee'];
        }
        $VehiclePlate = CheckValue('VehiclePlate','s');
        $StatusTypeId = 10;
        //31/03/2022 Davide: è possibile che qui si verifichi un errore sql perchè $TrespasserId non viene valorizzata per radio come "Rinotifica stesso trasgressore" a causa
        //del risultato della riga 530 nel caso il trasgressore sia a 2 figure (Tipo 2 e 3) perchè la condizione fa in modo che trovi solo i tipi 1 o 11
        $trespassers = $rs->Select('Trespasser',"Id=".$TrespasserId);
        $trespasser = mysqli_fetch_array($trespassers);
        $LanguageId = CheckValue('LanguageId','n');
        if($LanguageId==0){
            if($trespasser['CountryId']=='Z133'){
                $ZoneId = substr($VehiclePlate,0,2);
                $zones = $rs->Select('CountryZone',"Id='".$ZoneId."' AND CountryId='Z133'");
                $zone = mysqli_fetch_array($zones);
                $LanguageId = $zone['LanguageId'];
            }
        }
        if($LanguageId>0){
            $a_Trespasser = array(
                array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
            );
            $rs->Update('Trespasser',$a_Trespasser, 'Id='.$TrespasserId);
        }
        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
            array('field'=>'OwnerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$OwnerAdditionalFee,'settype'=>'flt'),
            array('field'=>'CustomerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$CustomerAdditionalFee,'settype'=>'flt'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
        );
        //17/12/2020 la rinotifica allo stesso trasgressore non prevedeva l'indicazione della data di identificazione
        if($n_Duplicate==3 || $n_Duplicate==1 || $n_Duplicate==4 || $n_Duplicate==2 || $n_Duplicate==7){
            $a_FineTrespasser[] = array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>DateInDB($ReceiveDate));
        }
        
        //tramite messo metto il tipo di notifica sul trasgressore di tipo messo (tipo 2)
        if($n_Duplicate==4) {
            $a_FineTrespasser[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int');
        }
        
        $rs->Insert('FineTrespasser',$a_FineTrespasser); //il trasgressore indicato nella pagina di partenza è lo stesso già salvato con tipo 10

        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
        );

        if($n_Duplicate==3 && $CountryId=='Z000' && $trespasser['CountryId']!='Z000'){
            $str_FolderDuplicate = FOREIGN_VIOLATION;
            $CountryId = 'Z00Z';
            $VehicleCountry = 'Italia Noleggi';
            $a_Fine[] = array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$VehicleCountry);
            $a_Fine[] = array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId);
        }
    }
    
    $a_Fine[] = array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$FineChiefControllerId);
    $a_Fine[] = array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$UIFineChiefControllerId);

    //aggiorna lo stato del nuovo atto a preinserimento in presenza di trasgressore
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);

    $DocumentationTypeId = 1;
    //se il verbale era contratto al momento della rinotifica
    // copio il pdf della scansione originale nelle cartelle
    if($FineTypeId==4){
        $rs_FineDocumentation = $rs->Select('FineDocumentation',"FineId=$Id AND DocumentationTypeId=2 and Documentation not in (select distinct Documentation from FineHistory where FineId=$Id)");
        $DocumentationTypeId = 2;
        $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
        $str_FolderDuplicate = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
    } else {
    $rs_FineDocumentation = $rs->Select('FineDocumentation',"FineId=$Id AND DocumentationTypeId=1");
    }
    while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
        $Documentation = $r_FineDocumentation['Documentation'];
        $a_FineDocumentation = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId),
        );
        $rs->Insert('FineDocumentation',$a_FineDocumentation);
        if (!is_dir($str_FolderDuplicate."/".$_SESSION['cityid'])) {
            mkdir($str_FolderDuplicate."/".$_SESSION['cityid'], 0777);
       
        }
        if (!is_dir($str_FolderDuplicate."/".$_SESSION['cityid']."/".$FineId)) {
            mkdir($str_FolderDuplicate."/".$_SESSION['cityid']."/".$FineId, 0777);
        }
        copy($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$Documentation, $str_FolderDuplicate."/".$_SESSION['cityid']."/".$FineId."/".$Documentation);
    }
}



$rs->End_Transaction();

if($n_Duplicate==4){
    include(INC."/header.php");

    require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

    $CreationDate = date('d/m/Y');

    //Determina se usare la procedura dinamica o statica per stampare la rinotifica per messo
    $ProcedurePage = 'frm_createdynamic_fine_exe.php';

    echo '
        <form name="f_print" id="f_print" action="'.$ProcedurePage.$str_Parameters.'" method="post">
        	<input type="hidden" name="P" value="mgmt_fine.php" />

            <input type="hidden" name="CreationDate" id="CreationDate" value="'.$CreationDate.'"  />
            <input type="hidden" name="ChiefControllerId" id="ChiefControllerId" value="'.$UIFineChiefControllerId.'"  />
            <input type="hidden" name="PrintDestinationFold" value="1"/>
            <input type="hidden" value=1 name="ultimate" id="ultimate" />
            <input type="hidden" name="checkbox[]" value="' . $FineId . '" />
            <input type="hidden" value=4 name="Duplicate" id="Duplicate" />
          
        </form>
    
    <script>
    	$(document).ready(function () {
            $("#f_print").submit();
	    });
    
    </script>
    
    
    ';



}else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    header("location: ".$P.$str_Parameters.'&Id='.$Id);
}
