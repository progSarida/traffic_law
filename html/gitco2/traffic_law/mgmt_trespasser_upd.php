<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$BackPage = strtok($str_BackPage, '?');

$Search_VatCode = CheckValue('Search_VatCode','s');
$Search_TrespCode = CheckValue('Search_TrespCode','s');
$Search_Province = CheckValue('Search_Province','s');
$Search_TaxCode = CheckValue('Search_TaxCode','s');
$Search_CityTitle = CheckValue('Search_CityTitle','s');
$str_GET_Parameter .= "&Search_VatCode=$Search_VatCode&Search_TrespCode=$Search_TrespCode&Search_Province=$Search_Province&Search_TaxCode=$Search_TaxCode&Search_CityTitle=$Search_CityTitle";

$TrespasserId= CheckValue('Id','n');



$str_TrespasserHistory = "";
$str_ForwardingHistory = "";
$str_DomicileHistory = "";
$str_DwellingHistory = "";

$rs_Trespasser = $rs->Select('Trespasser',"Id=".$TrespasserId, "Id");
$r_Trespasser = mysqli_fetch_array($rs_Trespasser);

$rs_Language = $rs->Select('Language',"Id=".$r_Trespasser['LanguageId']);
$r_Language = mysqli_fetch_array($rs_Language);

$DeathDateWarning = false;
$str_DeathWarning = "";

if($r_Trespasser['DeathDate']!=""){
    $rs_FineTrespasser = $rs->Select('V_FineTrespasser',"TrespasserId=".$TrespasserId);
    while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
        if ($r_Trespasser['DeathDate'] < $r_FineTrespasser['FineDate']) $DeathDateWarning = true;
    }
    
    if($DeathDateWarning) $str_DeathWarning =
    "$('#div_message_page').addClass('alert alert-warning')
 $('#div_message_page').text('Attenzione: la data di decesso è antecedente rispetto alla data di infrazione di almeno uno o più verbali associati')";
}

$strTrespasser = DivTrespasserUpdateNEW($r_Trespasser, "ANAGRAFICA");

$a_Zone = array(
    1 => "Europa e aree mediterranee",
    2 => "Altri stati non compresi",
    3 => "Oceania",
);


//STORICO TRASGRESSORE
$rs_TrespasserHistory = $rs->SelectQuery('SELECT T.*, C.Title CountryTitle FROM TrespasserHistory T JOIN Country C ON T.CountryId=C.Id  WHERE T.TrespasserId='.$TrespasserId.' ORDER BY Id DESC');
$RowNumber = mysqli_num_rows($rs_TrespasserHistory);

if ($RowNumber==0){
    $str_TrespasserHistory .= '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowLabel">
                Nessua modifica effettuata
            </div>
        </div>
    ';
} else {
    while($r_TrespasserHistory = mysqli_fetch_array($rs_TrespasserHistory)){
        $str_TrespasserHistory .= '
            <div class="col-sm-12">
                <div class="col-sm-7 BoxRowLabel">
                    Data: '.DateOutDB($r_TrespasserHistory['VersionDate']).'
                </div>
                <div class="col-sm-5 BoxRowLabel">
                    Utente: '.utf8_encode($r_TrespasserHistory['UserId']).'
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    '.utf8_encode($r_TrespasserHistory['CompanyName']) . ' ' . utf8_encode($r_TrespasserHistory['Surname']) . ' ' . utf8_encode($r_TrespasserHistory['Name']).'
                </div>
                <div class="col-sm-7 BoxRowCaption">
                    '.StringOutDB($r_TrespasserHistory['Address']." ".$r_TrespasserHistory['StreetNumber']." ".$r_TrespasserHistory['Ladder']." ".$r_TrespasserHistory['Indoor']." ".$r_TrespasserHistory['Plan']).'
                </div>
                <div class="col-sm-5 BoxRowCaption">
                    '.StringOutDB($r_TrespasserHistory['City']).' '.$r_TrespasserHistory['Province'].' '.StringOutDB($r_TrespasserHistory['CountryTitle']).'
                </div>
                <div class="col-sm-7 BoxRowCaption">
                    '.$r_TrespasserHistory['PEC'].'
                </div>
                <div class="col-sm-5 BoxRowCaption">
                    '.$r_TrespasserHistory['Phone'].'
                </div>
            </div>
                        
    ';
    }
}

//STORICO RECAPITI
$rs_ForwardingContacts = $rs->Select('TrespasserContact', "TrespasserId=$TrespasserId AND ContactTypeId=1 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)");
$n_Forwardings = mysqli_num_rows($rs_ForwardingContacts);

if ($n_Forwardings==0){
    $str_ForwardingHistory .= '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowLabel">
                Nessua modifica effettuata
            </div>
        </div>';
} else {
    $str_ForwardingHistory .= '<div class="panel" style="border:none;">';
    
    while($r_ForwardingContacts = mysqli_fetch_array($rs_ForwardingContacts)){
        $str_ForwardingHistory .= '
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRowLabel" style="background-color: #294A9C;">
                    Identificativo: '.$r_ForwardingContacts['Id'].'
                </div>
                <div class="col-sm-1 BoxRowLabel text-center" style="background-color:#294A9C;padding-top:5px;">
                    <i class="fas fa-angle-down caret-toggle" id="heading'.$r_ForwardingContacts['Id'].'" data-toggle="collapse" data-target="#collapse'.$r_ForwardingContacts['Id'].'" aria-expanded="false" aria-controls="collapse'.$r_ForwardingContacts['Id'].'" style="cursor:pointer;"></i>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>';
        
        $str_ForwardingHistory .= '<div class="collapse col-sm-12" id="collapse'.$r_ForwardingContacts['Id'].'" aria-labelledby="heading'.$r_ForwardingContacts['Id'].'" data-parent="#ForwardingHistory">';
        
        $rs_ForwardingHistory = $rs->SelectQuery('SELECT T.*, C.Title CountryTitle FROM TrespasserContactHistory T JOIN Country C ON T.CountryId=C.Id  WHERE T.TrespasserContactId='.$r_ForwardingContacts['Id'].' ORDER BY VersionDate DESC');
        $n_ForwardingsHistory = mysqli_num_rows($rs_ForwardingHistory);
        
        if ($n_ForwardingsHistory==0){
            $str_ForwardingHistory .= '
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel">
                        Nessua modifica effettuata
                    </div>
                </div>';
        } else {
            while($r_ForwardingHistory = mysqli_fetch_array($rs_ForwardingHistory)){
                $str_ForwardingHistory .= '
                    <div class="col-sm-12">
                        <div class="col-sm-7 BoxRowLabel">
                            Data: '.DateTimeOutDB($r_ForwardingHistory['VersionDate']).'
                        </div>
                        <div class="col-sm-5 BoxRowLabel">
                            Utente: '.$r_ForwardingHistory['UserId'].'
                        </div>
                        <div class="col-sm-12 BoxRowCaption">
                            Nominativo: '.$r_ForwardingHistory['Nominative'].'
                        </div>
                        <div class="col-sm-7 BoxRowCaption">
                            '.StringOutDB($r_ForwardingHistory['Address']." ".$r_ForwardingHistory['StreetNumber']." ".$r_ForwardingHistory['Ladder']." ".$r_ForwardingHistory['Indoor']." ".$r_ForwardingHistory['Plan']).'
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            '.StringOutDB($r_ForwardingHistory['City']).' '.$r_ForwardingHistory['Province'].' '.$r_ForwardingHistory['CountryTitle'].'
                        </div>
                        <div class="col-sm-12 BoxRowCaption">
                            '.StringOutDB($r_ForwardingHistory['PEC']).'
                        </div>
                    </div>';
            }
        }
        $str_ForwardingHistory .= '</div>';
    }
    $str_ForwardingHistory .= '</div>';
}

//STORICO DOMICILI
$rs_DomicileContacts = $rs->Select('TrespasserContact', "TrespasserId=$TrespasserId AND ContactTypeId=2 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)");
$n_Domiciles = mysqli_num_rows($rs_DomicileContacts);

if ($n_Domiciles==0){
    $str_DomicileHistory .= '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowLabel">
                Nessua modifica effettuata
            </div>
        </div>';
} else {
    $str_DomicileHistory .= '<div class="panel" style="border:none;">';
    
    while($r_DomicileContacts = mysqli_fetch_array($rs_DomicileContacts)){
        $str_DomicileHistory .= '
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRowLabel" style="background-color: #294A9C;">
                    Identificativo: '.$r_DomicileContacts['Id'].'
                </div>
                <div class="col-sm-1 BoxRowLabel text-center" style="background-color:#294A9C;padding-top:5px;">
                    <i class="fas fa-angle-down caret-toggle" id="heading'.$r_DomicileContacts['Id'].'" data-toggle="collapse" data-target="#collapse'.$r_DomicileContacts['Id'].'" aria-expanded="false" aria-controls="collapse'.$r_DomicileContacts['Id'].'" style="cursor:pointer;"></i>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>';
        
        $str_DomicileHistory .= '<div class="collapse col-sm-12" id="collapse'.$r_DomicileContacts['Id'].'" aria-labelledby="heading'.$r_DomicileContacts['Id'].'" data-parent="#DomicileHistory">';
        
        $rs_DomicileHistory = $rs->SelectQuery('SELECT T.*, C.Title CountryTitle FROM TrespasserContactHistory T JOIN Country C ON T.CountryId=C.Id  WHERE T.TrespasserContactId='.$r_DomicileContacts['Id'].' ORDER BY VersionDate DESC');
        $n_DomicilesHistory = mysqli_num_rows($rs_DomicileHistory);
        
        if ($n_DomicilesHistory==0){
            $str_DomicileHistory .= '
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel">
                        Nessua modifica effettuata
                    </div>
                </div>';
        } else {
            while($r_DomicileHistory = mysqli_fetch_array($rs_DomicileHistory)){
                $str_DomicileHistory .= '
                    <div class="col-sm-12">
                        <div class="col-sm-7 BoxRowLabel">
                            Data: '.DateTimeOutDB($r_DomicileHistory['VersionDate']).'
                        </div>
                        <div class="col-sm-5 BoxRowLabel">
                            Utente: '.$r_DomicileHistory['UserId'].'
                        </div>
                        <div class="col-sm-7 BoxRowCaption">
                            '.StringOutDB($r_DomicileHistory['Address']." ".$r_DomicileHistory['StreetNumber']." ".$r_DomicileHistory['Ladder']." ".$r_DomicileHistory['Indoor']." ".$r_DomicileHistory['Plan']).'
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            '.StringOutDB($r_DomicileHistory['City']).' '.$r_DomicileHistory['Province'].' '.$r_DomicileHistory['CountryTitle'].'
                        </div>
                        <div class="col-sm-12 BoxRowCaption">
                            '.StringOutDB($r_DomicileHistory['PEC']).'
                        </div>
                    </div>';
            }
        }
        $str_DomicileHistory .= '</div>';
    }
    $str_DomicileHistory .= '</div>';
}

//STORICO DIMORE
$rs_DwellingContacts = $rs->Select('TrespasserContact', "TrespasserId=$TrespasserId AND ContactTypeId=3 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)");
$n_Dwellings = mysqli_num_rows($rs_DwellingContacts);

if ($n_Dwellings==0){
    $str_DwellingHistory .= '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowLabel">
                Nessua modifica effettuata
            </div>
        </div>';
} else {
    $str_DwellingHistory .= '<div class="panel" style="border:none;">';
    
    while($r_DwellingContacts = mysqli_fetch_array($rs_DwellingContacts)){
        $str_DwellingHistory .= '
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRowLabel" style="background-color: #294A9C;">
                    Identificativo: '.$r_DwellingContacts['Id'].'
                </div>
                <div class="col-sm-1 BoxRowLabel text-center" style="background-color:#294A9C;padding-top:5px;">
                    <i class="fas fa-angle-down caret-toggle" id="heading'.$r_DwellingContacts['Id'].'" data-toggle="collapse" data-target="#collapse'.$r_DwellingContacts['Id'].'" aria-expanded="false" aria-controls="collapse'.$r_DwellingContacts['Id'].'" style="cursor:pointer;"></i>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>';
        
        $str_DwellingHistory .= '<div class="collapse col-sm-12" id="collapse'.$r_DwellingContacts['Id'].'" aria-labelledby="heading'.$r_DwellingContacts['Id'].'" data-parent="#DwellingHistory">';
        
        $rs_DwellingHistory = $rs->SelectQuery('SELECT T.*, C.Title CountryTitle FROM TrespasserContactHistory T JOIN Country C ON T.CountryId=C.Id  WHERE T.TrespasserContactId='.$r_DwellingContacts['Id'].' ORDER BY VersionDate DESC');
        $n_DwellingsHistory = mysqli_num_rows($rs_DwellingHistory);
        
        if ($n_DwellingsHistory==0){
            $str_DwellingHistory .= '
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel">
                        Nessua modifica effettuata
                    </div>
                </div>';
        } else {
            while($r_DwellingHistory = mysqli_fetch_array($rs_DwellingHistory)){
                $str_DwellingHistory .= '
                    <div class="col-sm-12">
                        <div class="col-sm-7 BoxRowLabel">
                            Data: '.DateTimeOutDB($r_DwellingHistory['VersionDate']).'
                        </div>
                        <div class="col-sm-5 BoxRowLabel">
                            Utente: '.$r_DwellingHistory['UserId'].'
                        </div>
                        <div class="col-sm-7 BoxRowCaption">
                            '.StringOutDB($r_DwellingHistory['Address']." ".$r_DwellingHistory['StreetNumber']." ".$r_DwellingHistory['Ladder']." ".$r_DwellingHistory['Indoor']." ".$r_DwellingHistory['Plan']).'
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            '.StringOutDB($r_DwellingHistory['City']).' '.$r_DwellingHistory['Province'].' '.$r_DwellingHistory['CountryTitle'].'
                        </div>
                        <div class="col-sm-12 BoxRowCaption">
                            '.StringOutDB($r_DwellingHistory['PEC']).'
                        </div>
                    </div>';
            }
        }
        $str_DwellingHistory .= '</div>';
    }
    $str_DwellingHistory .= '</div>';
}


//Verbali trasgressore//////////////////////////////////////////////////////////////////////////////////////////////////////////////

function verbaliTrasgressore($aUserButton,$r_Customer,$table_row,$buttons){
    global $str_GET_Parameter;
    global $BackPage;
    global $rs;

    $a_StatusTypeId = array();
    $a_StatusTypeId[35] = "#A94442";
    $a_StatusTypeId[36] = "#23448E";
    $a_StatusTypeId[37] = "#A94442";
    
    $a_Euro = array();
    $a_Euro[28] = "DDD728";
    $a_Euro[30] = "3C763D";
    
    
    $rs_Result = $rs->Select('Result', "1=1");
    while ($r_Result = mysqli_fetch_array($rs_Result)){
        $a_Result[$r_Result['Id']] = $r_Result['Title'];
    }
    
    $a_GradeType = array("","I","II","III");
    
    $a_DisputeStatusId = array("","#DDD728","#3C763D","#A94442");
    
    $ExternalProtocol = ($table_row['ExternalProtocol']>0)? $table_row['ExternalProtocol'].'/'.$table_row['ExternalYear'] : "";
    $rs_Row = $rs->Select('V_FineHistory',"Id=".$table_row['FineId']." AND NotificationTypeId=6");
    $r_Row = mysqli_fetch_array($rs_Row);
    $str_PreviousId     = "";
    $str_Archive        = "";
    $str_ProtocolId     = "";
    if($table_row['PreviousId']>0){
        $rs_Previous = $rs->Select('Fine',"Id=".$table_row['PreviousId']);
        $r_Previous = mysqli_fetch_array($rs_Previous);
        $str_PreviousId = '
            <a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Previous['Id'].'&ReminderPage=1">
                <i data-toggle="tooltip" data-container="body" data-placement="right" title="Verbale collegato Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'].'" class="tooltip-r fa fa-file-text" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
            </a>
            ';
    }
    
    $str_126Bis = '';
    $rs_126Bis = $rs->Select('V_FineArticle', "PreviousId=".$table_row['FineId']. " AND Id1=126");
    
    if(mysqli_num_rows($rs_126Bis)>0){
        $r_126Bis = mysqli_fetch_array($rs_126Bis);
        $str_126Bis = '
            <a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_126Bis['Id'].'&ReminderPage=1">
                <i data-toggle="tooltip" data-container="body" data-placement="right" title="126 BIS creato in data '. DateOutDB($r_126Bis['FineDate']).' Cron '.$r_126Bis['ProtocolId'].'/'.$r_126Bis['ProtocolYear'].'" class="tooltip-r fa fa-paperclip" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
            </a>
            ';
        
    }
    
    $str_Style      = (isset($a_StatusTypeId[$table_row['StatusTypeId']])) ? ' style="color:'.$a_StatusTypeId[$table_row['StatusTypeId']].';"' : '';
    $str_CssEuro    = (isset($a_Euro[$table_row['StatusTypeId']])) ? $a_Euro[$table_row['StatusTypeId']] : '000';
    
    
    if($table_row['StatusTypeId']==35 || $table_row['StatusTypeId']==37){
        $rs_Archive = $rs->SelectQuery("
                SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                WHERE FA.FineId=".$table_row['FineId']);
        $r_Archive = mysqli_fetch_array($rs_Archive);
        
        $str_Archive = '<i data-toggle="tooltip" data-container="body" data-placement="right" title="Verbale archiviato in data '. DateOutDB($r_Archive['ArchiveDate']).' '.$r_Archive['ReasonTitle'].' '.$r_Archive['Note'].'" class="tooltip-r fa fa-info-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
        
        
    }else if($table_row['StatusTypeId']==36){
        $rs_Previous = $rs->Select('Fine',"PreviousId=".$table_row['FineId']);
        $r_Previous = mysqli_fetch_array($rs_Previous);
        
        $str_PreviousId = '<i data-toggle="tooltip" data-container="body" data-placement="right" title="Verbale noleggio ristampato con Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'].'" class="tooltip-r fa fa-file-text" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
        
    }else if($table_row['StatusTypeId']==33){
        $str_ProtocolId = '<i data-toggle="tooltip" data-container="body" data-placement="right" title="Verbale rinotificato" class="tooltip-r fa fa-exchange" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
    }
    
    $str_Fine.= '
    <div class="tableRow">
			<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $table_row['FineId'] .' '.$str_PreviousId.$str_Archive.$str_126Bis.$str_ProtocolId.'</div>
			<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'].'</div>
		    <div class="table_caption_H col-sm-1"'.$str_Style.'>' . utf8_encode($table_row['Code']).'</div>
        	<div class="table_caption_H col-sm-1"'.$str_Style.'>' . DateOutDB($table_row['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $table_row['FineTime'] .'</div>
        	<div class="table_caption_H col-sm-3"'.$str_Style.'>' . StringOutDB($table_row['VehiclePlate']) .'</div>
			';
    //<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $ExternalProtocol .'</div>';
    
    $Status = '';
    if($r_Customer['ExternalRegistration']==1) {
        $Status .= ($table_row['ExternalProtocol']>0) ? '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale protocollato in data '. DateOutDB($r_Row['ExternalDate']).'" class="tooltip-r fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>' : '<i class="fa fa-book opaque" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
    }
    if($table_row['StatusTypeId']>14) {
        $Status .= (!is_null($r_Row['FlowDate'])) ? '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Flusso creato in data ' . DateOutDB($r_Row['FlowDate']) . '" class="tooltip-r fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>' : '<i class="fa fa-sort-amount-desc opaque" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
        $Status .= (!is_null($r_Row['PrintDate'])) ? '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Flusso stampato in data ' . DateOutDB($r_Row['PrintDate']) . '" class="tooltip-r fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>' : '<i class="fa fa-print opaque" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
    } else if($table_row['FineTypeId']==2) {
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Preavviso" class="tooltip-r fa fa-file-text" style="margin-top:0.2rem;margin-left:3.9rem;font-size:1.7rem;"></i>' ;
    } else if($table_row['StatusTypeId']==3) {
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale" class="tooltip-r fa fa-file-text" style="margin-top:0.2rem;margin-left:3.9rem;font-size:1.7rem;"></i>' ;
    } else {
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale creato digitalmente" class="tooltip-r fa fa fa-laptop" style="margin-top:0.2rem;margin-left:3.5rem;font-size:1.8rem;"></i>' ;
    }
    
    $Status .= (! is_null($r_Row['SendDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale inviato in data '. DateOutDB($r_Row['SendDate']).'"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
    
    if (! is_null($r_Row['ResultId'])) {
        if (! is_null($r_Row['DeliveryDate'])) {
            $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale notificato in data '. DateOutDB($r_Row['DeliveryDate']).'" class="tooltip-r fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:green;"></i>';
            $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
        }else{
            $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="'.$a_Result[$r_Row['ResultId']].'" class="tooltip-r fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:red;"></i>';
            $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
        }
        
    } else {
        if($_SESSION['usertype']>50) {
            $Status .= '
                <a href="mgmt_notification_add.php'.$str_GET_Parameter.'&FineId='.$table_row['FineId'].'">
                    <i data-toggle="tooltip" data-container="body" data-placement="left" title="Importa notifica" class="tooltip-r fa fa-envelope-square opaque" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;"></i>
                </a>';
        }else{
            $Status .= '
                <i class="fa fa-envelope-square opaque" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
        }
        $str_DeliveryStatus = '&nbsp;';
    }
    
    $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['FineId']);
    if(mysqli_num_rows($rs_Row)>0){
        $r_Row = mysqli_fetch_array($rs_Row);
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'" id="'.$r_Row['Id'].'" class="tooltip-r fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#'.$str_CssEuro.'" name="'.$str_CssEuro.'"></i>';
    } else if($table_row['StatusTypeId']==27 && $_SESSION['userlevel']>=7) 
        $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
    else {
        
        if($_SESSION['usertype']>50) {
            $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Cerca pagamento" class="fa fa-eur src_payment opaque" fineid="'.$table_row['FineId'].'" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;"></i>';
        }else{
            $Status .= '<i class="fa fa-eur opaque" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#'.$str_CssEuro.'" name="'.$str_CssEuro.'"></i>';
        }
    }
    
    $rs_Row = $rs->Select('V_FineDispute',"FineId=".$table_row['FineId']." ORDER BY GradeTypeId DESC");
    if(mysqli_num_rows($rs_Row)>0){
        $r_Row = mysqli_fetch_array($rs_Row);
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="'.$a_GradeType[$r_Row['GradeTypeId']].' Grado - '.$r_Row['OfficeTitle'].' '. $r_Row['OfficeCity'].' Depositato in data '. DateOutDB($r_Row['DateFile']) .'" class="fa fa-gavel tooltip-r" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:'.$a_DisputeStatusId[$r_Row['DisputeStatusId']].'"></i>';
        
    } else $Status .= '<i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
    
    $rs_Row = $rs->Select('FineCommunication',"FineId=".$table_row['FineId']);
    if(mysqli_num_rows($rs_Row)>0){
        $r_Row = mysqli_fetch_array($rs_Row);
        $Status .= '<i data-toggle="tooltip" data-container="body" data-placement="left" title="Comunicazione presentata in data '.DateOutDB($r_Row['CommunicationDate']).'" id="'.$r_Row['FineId'].'" class="fa fa-address-card tooltip-r" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>';
    }else $Status .= '<i class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
    
    
    $str_Fine.='
        <div class="table_caption_H col-sm-3">' . $Status .'</div>
    	<div class="table_caption_button col-sm-1">
    		'.( $buttons == 1 || $buttons == 3 ? ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'&ReminderPage=1"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza Verbale" class="tooltip-r glyphicon glyphicon-eye-open fa-fw" style="margin-top:0.3rem;"></span></a>') : ''). '
            '.( $buttons == 2 || $buttons == 3 ? ChkButton($aUserButton, 'viw','<a href="mgmt_communication_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza Com. Art. 126" class="tooltip-r fa fa-address-card fa-fw" style="margin-top:0.3rem;"></span></a>') : ''). '
    	</div>
    </div>
	<div class="clean_row HSpace4"></div>';
    
    return $str_Fine;
}

$aUserButton = array();
$UserPages = $rs->Select(MAIN_DB.".V_UserPage", "MainMenuId=".MENU_ID." AND UserId=".$_SESSION['userid']." AND LinkPage='mgmt_trespasser.php';");
while($UserPage = mysqli_fetch_array($UserPages)){
    $aUserButton[] = $UserPage['Title'];
}

$where = "TrespasserId='$TrespasserId'";
//Se l'utente ha un valore di permessi <=50 oppure la pagina di provenienza è Anagrafica Ente
if($_SESSION['usertype']<=50 || $BackPage == 'mgmt_trespasser_city.php'){
    $where.=" AND CityId='".$_SESSION['cityid']."' ";
}

$str_Fine ='
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                VERBALI
            </div>
            <div class="clean_row HSpace4"></div>
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Ref</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-3">Targa</div>
				<div class="table_label_H col-sm-3">Stato pratica</div>
        		<div class="table_add_button col-sm-1 right">
    
			</div>
			<div class="clean_row HSpace4"></div>';

$a_table_rows = array();

$fine_table_rows = $rs->Select('V_TrespasserRelatedFines',$where, 'ProtocolId');
$comm_table_rows = $rs->Select('V_TrespasserRelatedCommunications',$where, 'ProtocolId');

while ($table_row = mysqli_fetch_assoc($fine_table_rows)){
    $a_table_rows[$table_row['FineId']] = array('Row' => $table_row, 'Buttons' => 1);
}

while ($table_row = mysqli_fetch_assoc($comm_table_rows)){
    if(array_key_exists($table_row['FineId'], $a_table_rows)){
        $a_table_rows[$table_row['FineId']]['Buttons'] = 3;
    } else {
        $a_table_rows[$table_row['FineId']] = array('Row' => $table_row, 'Buttons' => 2);
    }
}

if (count($a_table_rows) <= 0) {
    $str_Fine .= '
        <div class="table_caption_H col-sm-12" style="text-align: center">
        Nessun record presente
        </div>
        ';
} else {
    foreach ($a_table_rows as $table_row) {
        $str_Fine .= verbaliTrasgressore($aUserButton, $r_Customer, $table_row['Row'], $table_row['Buttons']);
    }
}

//"Filter" è un parametro usato da diverse pagine per capire che è stata effettuata la ricerca
$str_out .='
    
<div class="row-fluid">
    <form name="f_trespasser" id="f_trespasser" action="mgmt_trespasser_upd_exe.php'.$str_GET_Parameter.'&BackPage='.$BackPage.'" method="post" autocomplete="off">
    <input type="hidden" id="Filter" value="1" name="Filter">
    <input type="hidden" id="TrespasserId" value="'.$TrespasserId.'" name="TrespasserId">
    <div class="col-sm-12">
        <div class="col-sm-7">
            '.$strTrespasser.'
            <div id="DIV_AdditionalData">
                <div class="col-sm-12">
                	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                		<strong>LINGUA</strong>
                	</div>
                	<div class="col-sm-2 BoxRowLabel">
                		<span class="col-sm-10">Forza cambio lingua</span>
                		<input class="col-sm-2" name="ForceLanguage" type="checkbox"'.($r_Trespasser['CountryId'] == "Z000" ? " disabled" : "").'>
					</div>
                    <div class="col-sm-3 BoxRowCaption">
                        <div id="select_Language">
                            ' . CreateSelectExtended('Language', '1=1', 'Id', 'LanguageSelect', 'LanguageSelect', 'Id', 'Title', $r_Trespasser['LanguageId'], true, true) . '
                            <input type="hidden" value="'.$r_Trespasser['LanguageId'].'" name="LanguageId" id="LanguageId">
                        </div>
                    </div>
                    <div id="ZoneIdDiv">
                        <div class="col-sm-2 BoxRowLabel">
                            Zona
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <span id="span_ZoneId">'.$r_Trespasser['ZoneId']." - ".$a_Zone[$r_Trespasser['ZoneId']].'</span>
                            <input value="'.$r_Trespasser['ZoneId'].'" type="hidden" class="form-control" id="ZoneId" name="ZoneId">
                        </div>
                    </div>
                </div>
            </div>
    
            <div hidden class="alert alert-warning" id="erroriva"></div>
        </div>
    
        <div class="col-sm-5">
    
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                STORICO
            </div>
            <div class="clean_row HSpace4"></div>
            <div id="TrespasserHistory">
                <div class="table_label_H col-sm-12">
                    <strong>Soggetto</strong>
                </div>
                <div class="clean_row HSpace4"></div>
                '.$str_TrespasserHistory.'
            </div>
            <div id="ForwardingHistory" style="display:none">
                <div class="table_label_H col-sm-12">
                    <strong>Recapiti</strong>
                </div>
                <div class="clean_row HSpace4"></div>
                '.$str_ForwardingHistory.'
            </div>
            <div id="DomicileHistory" style="display:none">
                <div class="table_label_H col-sm-12">
                    <strong>Domicili</strong>
                </div>
                <div class="clean_row HSpace4"></div>
                '.$str_DomicileHistory.'
            </div>
            <div id="DwellingHistory" style="display:none">
                <div class="table_label_H col-sm-12">
                    <strong>Dimore</strong>
                </div>
                <div class="clean_row HSpace4"></div>
                '.$str_DwellingHistory.'
            </div>
                
        </div>
    </div>
    '.$str_Fine.'
    <div class="table_label_H HSpace4 col-sm-12" style="height:8rem;">
        <button type="submit" class="btn btn-success" id="btn_Save" style="margin-top:2rem;"><i class="fa fa-save fa-fw"></i> Salva</button>
        <button type="button" class="btn btn-default" id="back" style="margin-top:2rem;">Indietro</button>
    </div>
    </form>
                
';

echo $str_out;

require(INC . "/module/mod_zip.php");
require(INC . "/module/mod_foreignzip.php");
include(INC . "/module/mod_foreigncity_add.php");

?>

<script src="<?= LIB ?>/codicefiscalejs/dist/codice.fiscale.js"></script>

<script type="text/javascript">
    //NUOVO ANAGRAFE
    var validPIVA = true;
    var validCF = true;
    //
    
function checkUniqueTrespasser(){
	var Typology = $('input[name="Typology"]:checked').val();
	var Genre = $('input[name="Genre"]:checked').val();
	var TrespasserId = $("#TrespasserId").val();
	var Name = "";
	var Surname = "";
	var CompanyName = "";
	var VatCode = "";
	var TaxCode = "";
	var BornDate = "";
	var BornCountry = "";
	var BornCity = "";

	if (Typology == "D"){
		var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
		VatCode = $("#VatCode").val();
		CompanyName = $("#CompanyName").val();
		if (isIndividualCompany){
			Name = $("#Name").val();
			Surname = $("#Surname").val();
		}
	} else {
		TaxCode = $("#TaxCode").val();
		Name = $("#Name").val();
		Surname = $("#Surname").val();
		BornDate = $("#BornDate").val();
		BornCountry = $("#BornCountry option:selected").val();
		//Italia, Austria e Germania hanno combo per selezione città, gli altri stati un input libero
		if (BornCountry == 'Z000' || BornCountry == 'Z102' || BornCountry == 'Z112')
			BornCity = $("#BornCitySelect option:selected").text();
		else
			BornCity = $("BornCityInput").val();
	}
	
    return $.ajax({
        url: 'ajax/checkUniqueTrespasser.php',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {TrespasserId:TrespasserId, Typology:Typology, VatCode:VatCode, TaxCode:TaxCode, Name:Name, Surname:Surname, CompanyName:CompanyName, Genre:Genre, BornDate:BornDate, BornCountry:BornCountry, BornCity:BornCity},
        error: function (data) {
            alert("error");
            console.log(data);
        }
    });
}

//NOTA: è stata modificata la libreria CodiceFiscaleJS (lib/codicefiscalejs/dist/codice.fiscale.js) in modo da restituire il codice catastale
//e selezionare lo stato/città in base ad esso, invece che la nomenclatura, tenere monitorato
function fillDecodedCFFields(cfData){
  $("input[name=Genre][value="+cfData.gender+"]").prop('checked', true);
  $("#BornDate").val(new Date(cfData.birthday).toLocaleDateString('it-IT', {year: "numeric",month: "2-digit",day: "2-digit"}));
  if (cfData.birthplaceProvincia == 'EE'){
  	//$('#BornCountry option[data-uppertitle="' + cfData.birthplace + '"]').prop("selected", true).change();
		$('#BornCountry').val(cfData.birthplaceId).change();
  } else {
      $("#BornCountry").val('Z000').change();
      $('#BornCitySelect').val(cfData.birthplaceId);
      //$('#BornCitySelect option[data-uppertitle="' + cfData.birthplace + '"]').prop("selected", true);
  }
}

$(document).ready(function () {


    $('#back').click(function(){
        window.location="<?= $BackPage.$str_GET_Parameter.'&Filter=1' ?>"
    });

    <?= $str_DeathWarning; ?>

	var submitted = false;

    setTimeout(function(){
        //Converte le combo delle città straniere visibili in select2 (TENERE D'OCCHIO)
        $("#Forwarding").find('[id^=Forwarding_ForeignCitySelect]:not([style*="display:none"])').select2();
        $("#Domicile").find('[id^=Domicile_ForeignCitySelect]:not([style*="display:none"])').select2();
        $("#Dwelling").find('[id^=Dwelling_ForeignCitySelect]:not([style*="display:none"])').select2();
        $('#ForeignCitySelect:visible, #ForeignBornCitySelect:visible').select2();
  	}, 500);
	
    $('#f_trespasser').bootstrapValidator({
        live: 'disabled',
        fields: {
            frm_field_required: {
                selector: '.frm_field_required',
                validators: {
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            },

            frm_field_numeric: {
                selector: '.frm_field_numeric',
                validators: {
                    numeric: {
                        message: 'Numero'
                    }
                }
            },

            frm_field_date:{
                selector: '.frm_field_date',
                validators: {
                    date: {
                        format: 'DD/MM/YYYY',
                        message: 'Data non valida'
                    }

                }

            },

            VatCode: {
                validators: {
                    regexp: {
                        regexp: '^[0-9]{11}$',
                        message: 'P.IVA non valida'
                    }
                }

            },

            TaxCode: {
            	validators: {
                    regexp: {
                        regexp: '^[a-zA-Z0-9]{16}$',
                        message: 'C.F non valido'
                    }
                }

            },

        }
    }).on('success.form.bv', function(event){
    	event.preventDefault();

    	var validateform = true;
    	var VatCode = $('#VatCode').val()
    	var TaxCode = $('#TaxCode').val()

    	if (!$("#tab_Subject").hasClass("active")){
        	$("#tab_Subject a[data-toggle='tab']").click();
            setTimeout(function(){
          	  $("#btn_Save").click();
            }, 100);
    	}

    	validateform = $("#f_trespasser").data('bootstrapValidator').isValid();

    	if (validateform && !submitted){
    	    $.when(checkUniqueTrespasser()).done(function(data){
    		    if (data.Exists == "Not Exists"){
    		    	submitted = true;
    		    	//DA RIVEDERE
    		    	$("#Forwarding :input").prop('disabled', false);
    		    	$("#Domicile :input").prop('disabled', false);
    		    	$("#Dwelling :input").prop('disabled', false);
    		    	//
    		    	
    		    	$('#f_trespasser').off('submit').submit();
    		    }
    		    /*
                if (data.Exists == "Exists"){
                	if (confirm(data.Message + "\nProcedere comunque con un nuovo inserimento con questi dati?\nNOTA: recapiti,domicili e dimore scaduti non verranno inclusi")){
                    	$('#f_trespasser').attr('action', 'mgmt_trespasser_add_exe.php<?= $str_GET_Parameter ?>');
                    	$('#f_trespasser').off('submit').submit();
                	} else return false;
                }
                */
                
                 if (data.Exists == "Exists"){
                	if (confirm(data.Message + "\nProcedere comunque con l'aggiornamento dei dati del trasgressore in modifica?")){
        		    	submitted = true;
        		    	$('#f_trespasser').off('submit').submit();
                	} else return false;
                }
                
                if (data.Exists == "Error"){
                    alert("Qualcosa è andato storto");
                    console.log(data);
                }
            });
    	}

    });

    //Nasconde o mostra gli storici in base al tab
    $("a[data-toggle=\'tab\']").on("shown.bs.tab", function (){

    	var id = $(this).parent().attr("id");
        
        if (id == "tab_Subject"){
            $("#TrespasserHistory").show();
            $("#ForwardingHistory, #DomicileHistory, #DwellingHistory").hide();
        }
        if (id == "tab_Forwarding"){
            $("#ForwardingHistory").show();
            $("#TrespasserHistory, #DomicileHistory, #DwellingHistory").hide();
        }
        if (id == "tab_Domicile"){
            $("#DomicileHistory").show();
            $("#TrespasserHistory, #ForwardingHistory, #DwellingHistory").hide();
        }
        if (id == "tab_Dwelling"){
            $("#DwellingHistory").show();
            $("#TrespasserHistory, #ForwardingHistory, #DomicileHistory").hide();
        }
        
    });

//     $('[id^="Forwarding_Cds"], [id^="Domicile_Cds"], [id^="Dwelling_Cds"]').change(function (e){
// 		var id = $(this).attr('id').slice(0, -1);
// 		var nid = id.slice(id.length - 1);
// 		console.log(id);
//         console.log($('[id^="ForwardingFields"]').length);

//         if (!this.checked){
//             if (!confirm('Really uncheck this one ?')){
//             	this.checked=!this.checked
//             }
//         }
//     });

    //Cambia i caret in base al collapse negli storici dei contatti
    $('.collapse').on("show.bs.collapse", function(){
    	var id = $(this).attr("id");
    	$(".caret-toggle[data-target='#" + id +"']").toggleClass('fa-angle-up fa-angle-down');
    });

    $('.collapse').on("hide.bs.collapse", function(){
    	var id = $(this).attr("id");
    	$(".caret-toggle[data-target='#" + id +"']").toggleClass('fa-angle-up fa-angle-down');
    });

//DATI SOGGETTO

	//Pulsante per dedurre i dati di nascita tramite C.F (usa libreria codice.fiscale.js)
	$('#DisassembleTaxCode').on('click', function(){
		var cf = $('#TaxCode').val().toUpperCase();
        if(CodiceFiscale.check(cf)){
            var cfData = CodiceFiscale.computeInverse(cf);
        	fillDecodedCFFields(cfData);
        } else alert('Codice fiscale non valido.');
	});
	
	//Nasconde o mostra dati addizionali in base al tab
    $("a[data-toggle=\'tab\']").on("shown.bs.tab", function (){
        if ($(this).parent().attr("id") == "tab_Subject")
        	$("#DIV_AdditionalData").show();
        else $("#DIV_AdditionalData").hide();
    });

	//Cambio Ditta/Persona
	$('input[type=radio][name=Typology]').change(function() {
		var value = $('input[name="Typology"]:checked').val();
		var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
		var isIndividualPerson = $("#PersonLegalFormId").val() == 23 || $("#PersonLegalFormId").val() == 24;

		if (value=="P"){
			$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate, #DIV_LicenseData").show();
			$("#CompanyData, #DIV_VatCode, #DIV_CompanyTaxCode").hide();
			$("#CompanyLegalFormId").addClass('hidden');
			$("#PersonLegalFormId").removeClass('hidden');
			$("#LegalFormLabel").text("Impresa Individuale");
			$("#LABEL_Residence").text("DATI RESIDENZA");
			if(isIndividualPerson) 
				$("#DIV_VatCode").show(); 
			else 
				$("#DIV_VatCode").hide();
		} else if (value=="D"){
			$("#DIV_TaxCode, #DIV_BornData, #DIV_DeathDate, #DIV_LicenseData").hide();
			$("#CompanyData, #DIV_VatCode, #DIV_CompanyTaxCode").show();
			$("#CompanyLegalFormId").removeClass('hidden');
			$("#PersonLegalFormId").addClass('hidden');
			$("#LegalFormLabel").text("Forma Giuridica");
			if(isIndividualCompany) {
				$("#LABEL_Residence").text("DATI RESIDENZA");
				$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").show();
				$("#DIV_CompanyTaxCode").hide();
			} else {
				$("#LABEL_Residence").text("DATI SEDE");
				$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").hide();
				$("#DIV_CompanyTaxCode").show();
			}
		}

	});

	//Azioni ditta forma giuridica se "impresa individuale" è selezionata
    $('#CompanyLegalFormId').change( function(){
    	var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
        if (isIndividualCompany){
        	$("#LABEL_Residence").text("DATI RESIDENZA");
        	$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").show();
        	$("#DIV_CompanyTaxCode").hide();
        } else {
        	$("#LABEL_Residence").text("DATI SEDE");
            $("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").hide();
            $("#DIV_CompanyTaxCode").show();
        }
    });

	//Azioni persona forma giuridica se "lavoratore autonomo" o "libero professionista" è selezionata
    $('#PersonLegalFormId').change( function(){
    	var isIndividualPerson = $("#PersonLegalFormId").val() == 23 || $("#PersonLegalFormId").val() == 24;
        if (isIndividualPerson){
        	$("#DIV_VatCode").show();
        } else $("#DIV_VatCode").hide();
    });

	//Cambia la nazione in Italia in dati patente se la nazione di residenza è Italia
    $("#TrespasserCountryId").change(function () {
        if ($(this).val() == "Z000") 
            $("#LicenseCountryId").val("Z000");
    });

  	//Se la nazioni di residenza o nascita sono Italiane, mostra selezione provincia, se Austria o Germania mostra selezione città straniere, altrimenti campo di testo libero
    $("#BornCountry, #TrespasserCountryId, #DocumentCountryId2").change(function () {
        if ($(this).attr("id")=="BornCountry"){
            if ($(this).val() == "Z000"){
            	$("#BornCityInput").hide();
            	$("#ForeignBornCitySelect").hide();
            	$('#ForeignBornCitySelect').next(".select2-container").hide();
            	$("#BornCitySelect").show();
            	$("#FBornCityAdd").hide();
            	$("#FBornCityAdd").attr('fieldid', '').attr('country', '');
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#BornCityInput").hide();
            	$("#ForeignBornCitySelect").show();
            	$('#ForeignBornCitySelect').select2();
            	$("#BornCitySelect").hide();
            	$("#FBornCityAdd").show();
            	$("#FBornCityAdd").attr('fieldid', 'BornCountry').attr('country', $(this).val());
            } else {
            	$("#BornCityInput").show();
            	$("#ForeignBornCitySelect").hide();
            	$('#ForeignBornCitySelect').next(".select2-container").hide();
            	$("#BornCitySelect").hide();
            	$("#FBornCityAdd").show();
            	$("#FBornCityAdd").attr('fieldid', 'BornCityInput').attr('country', $(this).val());
            }
        }
        if ($(this).attr("id")=="TrespasserCountryId"){
            if ($(this).val() == "Z000"){
            	$("#CityInput").hide();
            	$("#ForeignCitySelect, #DIV_Land").hide();
            	$('#ForeignCitySelect').next(".select2-container").hide();
            	$("#CitySelect, #DIV_Province").show();
            	$("#FCityAdd").hide();
            	$("#FCityAdd").attr('fieldid', '').attr('country', '');
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#CityInput").hide();
            	$("#span_Land").html("");
            	$("#ForeignCitySelect, #DIV_Land").show();
            	$('#ForeignCitySelect').select2();
            	$("#CitySelect, #DIV_Province").hide();
            	$("#FCityAdd").show();
            	$("#FCityAdd").attr('fieldid', 'TrespasserCountryId').attr('country', $(this).val());
            } else {
            	$("#CityInput").show();
            	$("#ForeignCitySelect, #DIV_Land").hide();
            	$('#ForeignCitySelect').next(".select2-container").hide();
            	$("#CitySelect, #DIV_Province").hide();
            	$("#FCityAdd").show();
            	$("#FCityAdd").attr('fieldid', 'CityInput').attr('country', $(this).val());
            }
        }
        if ($(this).attr("id")=="DocumentCountryId2"){
            if ($(this).val() == "Z000"){
            	$("#DocumentOfficeInput").hide();
            	$("#ForeignDocumentOfficeSelect").hide();
            	$("#DocumentOfficeSelect").show();
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#DocumentOfficeInput").hide();
            	$("#ForeignDocumentOfficeSelect").show();
            	$("#DocumentOfficeSelect").hide();
            } else {
            	$("#DocumentOfficeInput").show();
            	$("#ForeignDocumentOfficeSelect").hide();
            	$("#DocumentOfficeSelect").hide();
            }
        }

		if ($(this).val() == "Z102" || $(this).val() == "Z112"){
	        var CountryId = $(this).val();
	        var ElementId = $(this).attr("id");
            
	        $.ajax({
	            url: 'ajax/ajx_get_foreignCities.php',
	            type: 'POST',
	            dataType: 'json',
	            data: {CountryId:CountryId},
	            success: function (data) {
		            console.log(data);
	            	if (ElementId=="TrespasserCountryId")
	            		$('#ForeignCitySelect').html(data.Options);
	            	if (ElementId=="BornCountry")
	            		$('#ForeignBornCitySelect').html(data.Options);
	            	if (ElementId=="DocumentCountryId2")
	            		$('#ForeignDocumentOfficeSelect').html(data.Options);
	            },
	            error: function (result) {
	                console.log(result);
	                alert("error: " + result.responseText);
	            }
	        });
		}

    });

	//Prende il ZoneId e LanguageId
    $("#TrespasserCountryId").change(function () {
        var CountryId = $(this).val();
        
        if (CountryId != "Z000"){
        	$('input[name="ForceLanguage"]').prop("disabled", false);
        }
    	else {
    		$('input[name="ForceLanguage"]').prop("checked", false);
    		$('input[name="ForceLanguage"]').prop("disabled", true);
    		$('input[name="ForceLanguage"]').change();
    	}
            
        $.ajax({
            url: 'ajax/ajx_get_zoneId.php',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {CountryId:CountryId},
            success: function (data) {
            	$("#span_ZoneId").html(data.ZoneId + " - " + data.ZoneDescription);
            	//$("#span_Language").html(data.Language);
            	$("#ZoneId").val(data.ZoneId);
            	$("#LanguageSelect").val(data.LanguageId);
            	$("#LanguageId").val(data.LanguageId);
            },
        });
    });

	//Scrive il LanguageId nel campo nascosto a ogni cambio lingua
    $("#LanguageSelect").change(function () {
    	$("#LanguageId").val($(this).val());
    });

	//Genera il CF
    $("#BornCountry, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #TrespasserCountryId").on('blur change', function(){

        var Surname = $('#Surname').val();
        var Name = $('#Name').val();
        var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
        var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

        var BornDate = $('#BornDate').val();
        var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();
        //console.log(Surname, Name, Sex, BornDate, BornCitySelect);

        if (!ForcedTaxCode) {
            if (Surname && Name && Sex && BornDate && BornCitySelect) {
                var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);
    
                if (TaxCode.length == 16){
                    $('#TaxCode').val(TaxCode);
                    $('#span_TaxCode').html(TaxCode);
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-success');
                } else {
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                }
    
            }
        }
    });

    //Forza il CF
    $('#ForcedTaxCode').change(function () {
    	var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

    	if (ForcedTaxCode){
            var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
            
    		$('#TaxCode').show();
    		$('#span_TaxCode').hide();
    		$('#ForcedTaxCode').val(Sex);
    	} else {
            var Surname = $('#Surname').val();
            var Name = $('#Name').val();
            var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
            var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");
            var BornDate = $('#BornDate').val();
            var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();

            if (Surname && Name && Sex && BornDate && BornCitySelect) {
                var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);

                if (TaxCode.length == 16){
                    $('#TaxCode').val(TaxCode);
                    $('#span_TaxCode').html(TaxCode);
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-success');
                } else {
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                }

            }
    		$('#TaxCode').hide();
    		$('#span_TaxCode').show();
    		$('#ForcedTaxCode').val('');
    	}
    });

    //Valorizza nel caso venga cambiato il genere forzato se "Forza C.f" è selezionato
    $('#sexM, #sexF').change(function () {
    	var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
    	var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

    	if (ForcedTaxCode)
    		$('#ForcedTaxCode').val(Sex);
    });
	
	//Genera il cap
    $("#CityInput, #ForeignCitySelect, #BornCitySelect, #CitySelect, #AddressT, #StreetNumber").change(function () {

        var str_FieldNaneId = $(this).attr("id");
        var Type = "";

        if(str_FieldNaneId=="BornCitySelect") $('#BornCity').val($("#"+str_FieldNaneId+" option:selected" ).text());
        else {
            var CityId="";
            if($("#CitySelect").is(':visible')){
                $('#City').val($("#"+str_FieldNaneId+" option:selected" ).text());
                CityId =  $('#CitySelect').val();
            } else if ($("#CityInput").is(':visible')) {
                CityId =  $('#CityInput').val();
            } else if ($("#ForeignCitySelect").is(':visible')) {
                CityId =  $('#ForeignCitySelect').val();
                Type = "Foreign";
            }

            var Address = $('#AddressT').val();
            var CountryId = $('#TrespasserCountryId').val();
            var StreetNumber = $('#StreetNumber').val();

            if(CityId!=""){

                $.ajax({
                    url: 'ajax/ajx_src_zip.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId:CityId, Address:Address, Type:Type},
                    success: function (data) {
                        if (CountryId == "Z102" || CountryId == "Z112"){
                        	$("#span_Land").html(data.LandTitle);
                        	$("#LandId").val(data.LandId);
                        }
                        if(data.ZIP !="" ){
                            $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#ZIP").val(data.ZIP);
                        } else $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                    },
                    error: function (data) {
                    	console.log(data);
                    }
                });

            } else {
            	$("#ZIP").val('').removeClass('txt-success txt-warning txt-danger');
            	$("#LandId").val('');
            	$("#span_Land").html('');
            }
        }

        if(str_FieldNaneId=="CitySelect") {
            var CityId = $(this).val();
            $.ajax({
                url: 'ajax/ajx_src_prov_shortTitle.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {CityId:CityId},
                success: function (data) {
                    $("#span_Province").html(data.Province);
                },
                error: function (data) {
                	console.log(data);
                }
            });
        }
    });

    //Rimuove bordo rosso se cap compilato
    $("#ZIP").change(function () {
    	$("#ZIP").removeClass('txt-danger');
    });

    //Abilita il campo lingua
    $('input[name="ForceLanguage"]').change(function () {
        if ($(this).is(':checked'))
        	$("#LanguageSelect").prop("disabled", false);
        else
        	$("#LanguageSelect").prop("disabled", true);
    });

    //Controlla il numero patente
    $("[name='LicenseNumber'], #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #BornCityInput").change(function () {
    	var Genre = $('input[name="Genre"]:checked').val();
    	var TrespasserId = $("#TrespasserId").val();
    	var Name = "";
    	var Surname = "";
    	var BornDate = "";
    	var BornCity = "";
    	var BornCountry = "";
    	var LicenseNumber = "";

		Name = $("#Name").val();
		Surname = $("#Surname").val();
		BornDate = $("#BornDate").val();
		LicenseNumber = $("[name='LicenseNumber']").val();
		BornCountry = $("#BornCountry option:selected").val();
		
		//Italia, Austria e Germania hanno combo per selezione citt�, gli altri stati un input libero
		if (BornCountry == 'Z000' || BornCountry == 'Z102' || BornCountry == 'Z112')
			BornCity = $("#BornCitySelect option:selected").text();
		else
			BornCity = $("BornCityInput").val();

		if (LicenseNumber != ""){
	        $.ajax({
	            url: 'ajax/checkLicenseNumber.php',
	            type: 'POST',
	            dataType: 'json',
	            cache: false,
	            data: {TrespasserId:TrespasserId, Name:Name, Surname:Surname, Genre:Genre, BornDate:BornDate, BornCity:BornCity, LicenseNumber:LicenseNumber},
	            success: function (data) {
	            	if (data.Exists == "Exists"){
	                	alert(data.Message);
	            	}
	            },
	            error: function (data) {
	                console.log(data);
	                alert("error: " + data.responseText);
	            }
	        });
		}
    });

    //Controlla lunghezza totale indirizzo (per stampe)
    $("#AddressT, #StreetNumber, #Ladder, #Indoor, #Plan").change(function () {
        var Address = 
            ($("#AddressT").val().length > 0 ? $("#AddressT").val() : "") +
    		($("#StreetNumber").val().length > 0 ? " " + $("#StreetNumber").val() : "") +
			($("#Ladder").val().length > 0 ? " " + $("#Ladder").val() : "") +
			($("#Indoor").val().length > 0 ? " " + $("#Indoor").val() : "") +
			($("#Plan").val().length > 0 ? " " + $("#Plan").val() : "");
        if (Address.length > 46) 
            alert("Attenzione: l'indirizzo potrebbe risultare troncato su eventuali stampe in quanto non deve eccedere 46 caratteri (spazi compresi). Spazi contati: " + Address.length + " \n\n" + Address);
    });

//INTERAZIONI MODALE STRADARIO

	//Stili frecce frazionamento
    $(document).on("mouseenter", ".glyphicon-road, .glyphicon-plus-sign, .fa-id-card", function(){
        $(this).css("color","#2684b1");
        $(this).css("cursor","pointer");
    }).on("mouseleave",".glyphicon-road, .glyphicon-plus-sign, .fa-id-card",  function(){
        $(this).css("color","#fff");
        $(this).css("cursor","");
    });

    //Popola Lo stradario con la città di residenza in base all'icona cliccata
	$(document).on("click", "#RoadIcon, [id^=Forwarding_RoadIcon], [id^=Domicile_RoadIcon], [id^=Dwelling_RoadIcon]", function () {
		var id = $(this).attr('id');
		var nid = id.slice(id.length - 1);
		
		if (id.includes('Forwarding')){
			if ($("#Forwarding_CountryId" + nid).val() == "Z000"){
				if($("#Forwarding_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Forwarding_CitySelect" + nid).val());
			        $('#city_title').html($( "#Forwarding_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Forwarding_CountryId" + nid).val() == "Z102" || $("#Forwarding_CountryId" + nid).val() == "Z112"){
				if($("#Forwarding_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Forwarding_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Forwarding_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else if (id.includes('Domicile')){
			if ($("#Domicile_CountryId" + nid).val() == "Z000"){
				if($("#Domicile_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Domicile_CitySelect" + nid).val());
			        $('#city_title').html($( "#Domicile_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Domicile_CountryId" + nid).val() == "Z102" || $("#Domicile_CountryId" + nid).val() == "Z112"){
				if($("#Domicile_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Domicile_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Domicile_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else if (id.includes('Dwelling')){
			if ($("#Dwelling_CountryId" + nid).val() == "Z000"){
				if($("#Dwelling_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Dwelling_CitySelect" + nid).val());
			        $('#city_title').html($( "#Dwelling_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Dwelling_CountryId" + nid).val() == "Z102" || $("#Dwelling_CountryId" + nid).val() == "Z112"){
				if($("#Dwelling_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Dwelling_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Dwelling_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else {
			if ($("#TrespasserCountryId").val() == "Z000"){
				if($("#CitySelect").val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#CitySelect").val());
			        $('#city_title').html($( "#CitySelect option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#TrespasserCountryId").val() == "Z102" || $("#TrespasserCountryId").val() == "Z112"){
				if($("#ForeignCitySelect").val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#ForeignCitySelect").val());
			        $('#foreign_city_title').html($( "#ForeignCitySelect option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
		}
	});

//INTEREZIONI MODALE CITTà STRANIERE
$(document).on("click", ".add_fcity", function(){
	$('#mod_foreigncity_add').modal('show');
	$('#mod_foreigncity_add').attr('fieldid', $(this).attr('fieldid'));
	$('#fcity_CountryId').val($(this).attr('country'));
	$('#fcity_CountryId').change();
});

$( "#f_foreigncity_add" ).on( "submitted", function( event, data ) {
    var FieldId = "#" + $('#mod_foreigncity_add').attr('fieldid')
	$("#f_foreigncity_add").trigger("reset");
	$("#fcity_LandId").html('<option></option>');
	$("#fcity_LandId").prop("disabled", true);
	
	if ($(FieldId).is("select")){
		$(FieldId).change();
	} else {
		$(FieldId).val(data.CityTitle).change();
	}
});

    
//DATI CONTATTI
        
    //RECAPITI
        var nF = parseInt($("#ForwardingNumber").val());
        var saved_nF = parseInt($("#ForwardingNumber").val());

        //if (nF > 1) $("#forwardingUp").show();
        
        $("#Forwarding").on("change", '[id^="Forwarding_Cds"]', function (e){
    		var id = $(this).attr('id');
    		var nid = id.slice(id.length - 1);

    		if (nid > 1){
                if ($('#Forwarding_Cds' + (nid-1)).is(":checked") && $('#Forwarding_ValidUntil' + (nid-1)).val() == ""){
                    if (this.checked){
                    	this.checked=!this.checked
                    	alert ("Compilare la fine validità del precedente recapito per poter impostare l'attuale come C.d.S");
                    }
                }
    		}
        });

        $("#Forwarding").on("change", '[id^="Forwarding_ValidUntil"]', function (e){
        	var id = $(this).attr('id');
        	var nid = id.slice(id.length - 1);
        	
            if ($(this).val() == "" && $("#Forwarding_Cds" + (parseInt(nid)+1)).is(":checked")){
            	alert ("Il recapito successivo è impostato come C.d.S, è necessario che la fine validità sia compilata");
            	$(this).val($(this).data('val'));
            }
        });

        $("#Forwarding").on("focusin", '[id^="Forwarding_ValidUntil"]', function (e){
        	//console.log("Saving value " + $(this).val());
            $(this).data('val', $(this).val());
        });
        
        $("#forwardingDown").click(function () {
            var emptyValidUntil = false;
            var emptyFields = false;
            
//             $('input[name^="Forwarding_ValidUntil"]').each( function() {
//             	if (this.value == "") emptyValidUntil = true;
//     	    });

            if(nF > 1){
                if ($('#Forwarding_Cds' + nF).is(":checked") && $('#Forwarding_ValidUntil' + (nF-1)).val() == ""){
                	emptyValidUntil = true;
                }
            }

            $('[name^="Forwarding_Address"], [name^="Forwarding_CitySelect"]:visible, [name^="Forwarding_CityInput"]:visible').each( function() {
            	if (this.value == "") emptyFields = true;
            });
            

            if (!emptyFields){
        	    if (!emptyValidUntil){
        	    	nF ++;
                    var clone = $("#ForwardingFields1").clone();
                    
                    clone.find("#deleteForwarding1").remove();
                    clone.attr("id","ForwardingFields" + nF);
                    clone.find("[id]").each(function( index ) {
                        this.id = this.id.slice(0,-1) + nF;
                    });
                    clone.find('input:checkbox').each(function( index ) {
                        var name = $(this).attr('name').replace("[0]", "["+(nF-1)+"]");
                        $(this).attr('name', name);
                    });
                    //Attributi della select2 clonata da rimuovere per poterla rigenerare
                    clone.find("#Forwarding_ForeignCitySelect" + nF).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                    
                    $("#Forwarding").append(clone);
    
                    $("#ForwardingFields" + nF + " :input").prop('disabled', false);
                    $("#ForwardingFields" + nF + " input:checkbox").prop('checked', false);
                    $("#ForwardingFields" + nF + " .forwarding_number").html("Recapito n. " + nF);
                    $("#ForwardingFields" + nF + " .forwarding_id").html("");
                    $("#ForwardingFields" + nF + " input").val("");
                    $("#ForwardingFields" + nF + " select").val("");
                    $("#ForwardingFields" + nF + " #Forwarding_CountryId" + nF).val("Z000");
                    $("#ForwardingFields" + nF + " textarea").val("");
                    $("#ForwardingNumber").val(nF);
                    $("#Forwarding_CityInput" + nF).hide();
                    $("#Forwarding_ForeignCitySelect" + nF).hide();
                    //Rimuove la select2 clonata
                    $('#Forwarding_ForeignCitySelect' + nF).next(".select2-container").remove();
                    $("#Forwarding_CitySelect" + nF).show();
                    $("#Forwarding_DIV_Province" + nF).show();
                    $("#Forwarding_span_Province" + nF).html("");
                    $("#Forwarding_FCityAdd" + nF).hide();
                    $("#forwardingUp").show();
                } else alert("Compilare la fine validità dei recapiti già inseriti per poterne inserire uno nuovo");
            } else alert("Compilare i campi richiesti per inserire un nuovo recapito");
            
        });

        $("#forwardingUp").click(function () {
            $("#ForwardingFields" + nF).remove();
            nF--;
            $("#ForwardingNumber").val(nF);
            if (nF > saved_nF) $("#forwardingUp").show();
            else $("#forwardingUp").hide();
        });
    //
        
    //DOMICILI
        var nDo = parseInt($("#DomicileNumber").val());
        var saved_nDo = parseInt($("#DomicileNumber").val());

        //if (nDo > 1) $("#DomicileUp").show();
        
        $("#Domicile").on("change", '[id^="Domicile_Cds"]', function (e){
    		var id = $(this).attr('id');
    		var nid = id.slice(id.length - 1);

    		if (nid > 1){
                if ($('#Domicile_Cds' + (nid-1)).is(":checked") && $('#Domicile_ValidUntil' + (nid-1)).val() == ""){
                    if (this.checked){
                    	this.checked=!this.checked
                    	alert ("Compilare la fine validità del precedente domicilio per poter impostare l'attuale come C.d.S");
                    }
                }
    		}
        });

        $("#Domicile").on("change", '[id^="Domicile_ValidUntil"]', function (e){
        	var id = $(this).attr('id');
        	var nid = id.slice(id.length - 1);
        	
            if ($(this).val() == "" && $("#Domicile_Cds" + (parseInt(nid)+1)).is(":checked")){
            	alert ("Il domicilio successivo è impostato come C.d.S, è necessario che la fine validità sia compilata");
            	$(this).val($(this).data('val'));
            }
        });

        $("#Domicile").on("focusin", '[id^="Domicile_ValidUntil"]', function (e){
        	//console.log("Saving value " + $(this).val());
            $(this).data('val', $(this).val());
        });
        
        $("#domicileDown").click(function () {
            var emptyValidUntil = false;
            var emptyFields = false;
            
//             $('input[name^="Domicile_ValidUntil"]').each( function() {
//             	if (this.value == "") emptyValidUntil = true;
//     	    });

            if(nDo > 1){
                if ($('#Domicile_Cds' + nDo).is(":checked") && $('#Domicile_ValidUntil' + (nDo-1)).val() == ""){
                	emptyValidUntil = true;
                }
            }

            $('[name^="Domicile_Address"], [name^="Domicile_CitySelect"]:visible, [name^="Domicile_CityInput"]:visible').each( function() {
            	if (this.value == "") emptyFields = true;
            });

            if (!emptyFields){
        	    if (!emptyValidUntil){
                	nDo ++;
                    var clone = $("#DomicileFields1").clone()
                    
                    clone.find("#deleteDomicile1").remove();
                    clone.attr("id","DomicileFields" + nDo);
                    clone.find("[id]").each(function( index ) {
                        this.id = this.id.slice(0,-1) + nDo;
                    });
                    clone.find('input:checkbox').each(function( index ) {
                        var name = $(this).attr('name').replace("[0]", "["+(nDo-1)+"]");
                        $(this).attr('name', name);
                    });
                    //Attributi della select2 clonata da rimuovere per poterla rigenerare
                    clone.find("#Domicile_ForeignCitySelect" + nDo).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                    
                    $("#Domicile").append(clone);
                    
                    $("#DomicileFields" + nDo + " :input").prop('disabled', false);
                    $("#DomicileFields" + nDo + " input:checkbox").prop('checked', false);
                    $("#DomicileFields" + nDo + " .domicile_number").html("Domicilio n. " + nDo);
                    $("#DomicileFields" + nDo + " .domicile_id").html("");
                    $("#DomicileFields" + nDo + " input").val("");
                    $("#DomicileFields" + nDo + " select").val("");
                    $("#DomicileFields" + nDo + " #Domicile_CountryId" + nDo).val("Z000");
                    $("#DomicileFields" + nDo + " textarea").val("");
                    $("#DomicileNumber").val(nDo);
                    $("#Domicile_CityInput" + nDo).hide();
                    $("#Domicile_ForeignCitySelect" + nDo).hide();
                    //Rimuove la select2 clonata
                    $('#Domicile_ForeignCitySelect' + nDo).next(".select2-container").remove();
                    $("#Domicile_CitySelect" + nDo).show();
                    $("#Domicile_DIV_Province" + nDo).show();
                    $("#Domicile_span_Province" + nDo).html("");
                    $("#Domicile_FCityAdd" + nDo).hide();
                    $("#domicileUp").show();
        	    } else alert("Compilare la fine validità dei domicili già inseriti per poterne inserire uno nuovo");
            } else alert("Compilare i campi richiesti per inserire un nuovo domicilio");
            
        });

        $("#domicileUp").click(function () {
            $("#DomicileFields" + nDo).remove();
            nDo--;
            $("#DomicileNumber").val(nDo);
            if (nDo > saved_nDo) $("#domicileUp").show();
            else $("#domicileUp").hide();
        });
    //
        
    //DIMORE
        var nDw = parseInt($("#DwellingNumber").val());
        var saved_nDw = parseInt($("#DwellingNumber").val());

        //if (nDw > 1) $("#DwellingUp").show();
        
        $("#Dwelling").on("change", '[id^="Dwelling_Cds"]', function (e){
    		var id = $(this).attr('id');
    		var nid = id.slice(id.length - 1);

    		if (nid > 1){
                if ($('#Dwelling_Cds' + (nid-1)).is(":checked") && $('#Dwelling_ValidUntil' + (nid-1)).val() == ""){
                    if (this.checked){
                    	this.checked=!this.checked
                    	alert ("Compilare la fine validità della precedente dimora per poter impostare l'attuale come C.d.S");
                    }
                }
    		}
        });

        $("#Dwelling").on("change", '[id^="Dwelling_ValidUntil"]', function (e){
        	var id = $(this).attr('id');
        	var nid = id.slice(id.length - 1);
        	
            if ($(this).val() == "" && $("#Dwelling_Cds" + (parseInt(nid)+1)).is(":checked")){
            	alert ("La dimora successiva è impostata come C.d.S, è necessario che la fine validità sia compilata");
            	$(this).val($(this).data('val'));
            }
        });

        $("#Dwelling").on("focusin", '[id^="Dwelling_ValidUntil"]', function (e){
        	//console.log("Saving value " + $(this).val());
            $(this).data('val', $(this).val());
        });
        
        $("#dwellingDown").click(function () {
            var emptyValidUntil = false;
            var emptyFields = false;
            
//             $('input[name^="Dwelling_ValidUntil"]').each( function() {
//             	if (this.value == "") emptyValidUntil = true;
//     	    });

            if(nDw > 1){
                if ($('#Domicile_Cds' + nDw).is(":checked") && $('#Domicile_ValidUntil' + (nDw-1)).val() == ""){
                	emptyValidUntil = true;
                }
            }

            $('[name^="Dwelling_Address"], [name^="Dwelling_CitySelect"]:visible, [name^="Dwelling_CityInput"]:visible').each( function() {
            	if (this.value == "") emptyFields = true;
            });

            if (!emptyFields){
        	    if (!emptyValidUntil){
                    nDw ++;
                    var clone = $("#DwellingFields1").clone()
                    
                    clone.find("#deleteDwelling1").remove();
                    clone.attr("id","DwellingFields" + nDw);
                    clone.find("[id]").each(function( index ) {
                        this.id = this.id.slice(0,-1) + nDw;
                    });
                    clone.find('input:checkbox').each(function( index ) {
                        var name = $(this).attr('name').replace("[0]", "["+(nDw-1)+"]");
                        $(this).attr('name', name);
                    });
                    //Attributi della select2 clonata da rimuovere per poterla rigenerare
                    clone.find("#Dwelling_ForeignCitySelect" + nDw).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                    
                    $("#Dwelling").append(clone);
    
                    $("#DwellingFields" + nDw + " :input").prop('disabled', false);
                    $("#DwellingFields" + nDw + " input:checkbox").prop('checked', false);
                    $("#DwellingFields" + nDw + " .dwelling_number").html("Dimora n. " + nDw);
                    $("#DwellingFields" + nDw + " .dwelling_id").html("");
                    $("#DwellingFields" + nDw + " input").val("");
                    $("#DwellingFields" + nDw + " select").val("");
                    $("#DwellingFields" + nDw + " #Dwelling_CountryId" + nDw).val("Z000");
                    $("#DwellingFields" + nDw + " textarea").val("");
                    $("#DwellingNumber").val(nDw);
                    $("#Dwelling_CityInput" + nDw).hide();
                    $("#Dwelling_ForeignCitySelect" + nDw).hide();
                    //Rimuove la select2 clonata
                    $('#Dwelling_ForeignCitySelect' + nDw).next(".select2-container").remove();
                    $("#Dwelling_CitySelect" + nDw).show();
                    $("#Dwelling_DIV_Province" + nDw).show();
                    $("#Dwelling_span_Province" + nDw).html("");
                    $("#Dwelling_FCityAdd" + nDw).hide();
                    $("#dwellingUp").show();
        	    } else alert("Compilare la fine validità delle dimore già inserite per poterne inserire una nuova");
            } else alert("Compilare i campi richiesti per inserire una nuova dimora");
        });

        $("#dwellingUp").click(function () {
            $("#DwellingFields" + nDw).remove();
            nDw--;
            $("#DwellingNumber").val(nDw);
            if (nDw > saved_nDw) $("#dwellingUp").show();
            else $("#dwellingUp").hide();
        });
    //

        //CONTROLLO NAZIONE PER CITTà
        $("#Forwarding, #Domicile, #Dwelling").on("change", "[id^=Forwarding_CountryId], [id^=Domicile_CountryId], [id^=Dwelling_CountryId]", function(){
            var id = $(this).attr("id");
    
            if (id.includes('Forwarding_CountryId')){
            	id = id.replace('Forwarding_CountryId','');
            	if ($(this).val() == "Z000"){
            		$("#Forwarding_CitySelect" + id).show();
            		$("#Forwarding_ForeignCitySelect" + id).hide();
            		$('#Forwarding_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Forwarding_CityInput" + id).hide();
            		$("#Forwarding_DIV_Province" + id).show();
            		$("#Forwarding_FCityAdd" + id).hide();
            		$("#Forwarding_FCityAdd" + id).attr('fieldid', '').attr('country', '');
            	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            		$("#Forwarding_CitySelect" + id).hide();
            		$("#Forwarding_ForeignCitySelect" + id).show();
            		$("#Forwarding_ForeignCitySelect" + id).select2();
            		$("#Forwarding_CityInput" + id).hide();
            		$("#Forwarding_DIV_Province" + id).hide();
            		$("#Forwarding_FCityAdd" + id).show();
            		$("#Forwarding_FCityAdd" + id).attr('fieldid', 'Forwarding_CountryId' + id).attr('country', $(this).val());
                } else {
            		$("#Forwarding_CitySelect" + id).hide();
            		$("#Forwarding_ForeignCitySelect" + id).hide();
            		$('#Forwarding_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Forwarding_CityInput" + id).show();
            		$("#Forwarding_DIV_Province" + id).hide();
            		$("#Forwarding_FCityAdd" + id).show();
            		$("#Forwarding_FCityAdd" + id).attr('fieldid', 'Forwarding_CityInput' + id).attr('country', $(this).val());
            	}
            } else if (id.includes('Domicile_CountryId')) {
            	id = id.replace('Domicile_CountryId','');
            	if ($(this).val() == "Z000"){
            		$("#Domicile_CitySelect" + id).show();
            		$("#Domicile_ForeignCitySelect" + id).hide();
            		$('#Domicile_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Domicile_CityInput" + id).hide();
            		$("#Domicile_DIV_Province" + id).show();
            		$("#Domicile_FCityAdd" + id).hide();
            		$("#Domicile_FCityAdd" + id).attr('fieldid', '').attr('country', '');
            	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            		$("#Domicile_CitySelect" + id).hide();
            		$("#Domicile_ForeignCitySelect" + id).show();
            		$("#Domicile_ForeignCitySelect" + id).select2();
            		$("#Domicile_CityInput" + id).hide();
            		$("#Domicile_DIV_Province" + id).hide();
            		$("#Domicile_FCityAdd" + id).show();
            		$("#Domicile_FCityAdd" + id).attr('fieldid', 'Domicile_CountryId' + id).attr('country', $(this).val());
                } else {
            		$("#Domicile_CitySelect" + id).hide();
            		$("#Domicile_ForeignCitySelect" + id).hide();
            		$('#Domicile_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Domicile_CityInput" + id).show();
            		$("#Domicile_DIV_Province" + id).hide();
            		$("#Domicile_FCityAdd" + id).show();
            		$("#Domicile_FCityAdd" + id).attr('fieldid', 'Domicile_CityInput' + id).attr('country', $(this).val());
            	}
            } else if (id.includes('Dwelling_CountryId')) {
            	id = id.replace('Dwelling_CountryId','');
            	if ($(this).val() == "Z000"){
            		$("#Dwelling_CitySelect" + id).show();
            		$("#Dwelling_ForeignCitySelect" + id).hide();
            		$('#Dwelling_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Dwelling_CityInput" + id).hide();
            		$("#Dwelling_DIV_Province" + id).show();
            		$("#Dwelling_FCityAdd" + id).hide();
            		$("#Dwelling_FCityAdd" + id).attr('fieldid', '').attr('country', '');
            	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            		$("#Dwelling_CitySelect" + id).hide();
            		$("#Dwelling_ForeignCitySelect" + id).show();
            		$("#Dwelling_ForeignCitySelect" + id).select2();
            		$("#Dwelling_CityInput" + id).hide();
            		$("#Dwelling_DIV_Province" + id).hide();
            		$("#Dwelling_FCityAdd" + id).show();
            		$("#Dwelling_FCityAdd" + id).attr('fieldid', 'Dwelling_CountryId' + id).attr('country', $(this).val());
                } else {
            		$("#Dwelling_CitySelect" + id).hide();
            		$("#Dwelling_ForeignCitySelect" + id).hide();
            		$('#Dwelling_ForeignCitySelect' + id).next(".select2-container").hide();
            		$("#Dwelling_CityInput" + id).show();
            		$("#Dwelling_DIV_Province" + id).hide();
            		$("#Dwelling_FCityAdd" + id).show();
            		$("#Dwelling_FCityAdd" + id).attr('fieldid', 'Dwelling_CityInput' + id).attr('country', $(this).val());
            	}
            }
    
    		if ($(this).val() == "Z102" || $(this).val() == "Z112"){
    	        var CountryId = $(this).val();
    	        var id = $(this).attr("id");
                
    	        $.ajax({
    	            url: 'ajax/ajx_get_foreignCities.php',
    	            type: 'POST',
    	            dataType: 'json',
    	            data: {CountryId:CountryId},
    	            success: function (data) {
    		            console.log(data);
    	            	if (id.includes('Forwarding_CountryId')){
    	            		id = id.replace('Forwarding_CountryId','');
    	            		$('#Forwarding_ForeignCitySelect' + id).html(data.Options);
    	            	}
    	            	if (id.includes('Domicile_CountryId')){
    	            		id = id.replace('Domicile_CountryId','');
    	            		$('#Domicile_ForeignCitySelect' + id).html(data.Options);
    	            	}
    	            	if (id.includes('Dwelling_CountryId')){
    	            		id = id.replace('Dwelling_CountryId','');
    	            		$('#Dwelling_ForeignCitySelect' + id).html(data.Options);
    	            	}
    	            },
    	            error: function (result) {
    	                console.log(result);
    	                alert("error: " + result.responseText);
    	            }
    	        });
    		}
        });

        //Controlla lunghezza totale indirizzo (per stampe)
        $("#Forwarding, #Domicile, #Dwelling").on("change", 
        	    "[id^=Forwarding_Address], [id^=Forwarding_StreetNumber], [id^=Forwarding_Ladder], [id^=Forwarding_Indoor], [id^=Forwarding_Plan], " +
        	    "[id^=Domicile_Address], [id^=Domicile_StreetNumber], [id^=Domicile_Ladder], [id^=Domicile_Indoor], [id^=Domicile_Plan]," +
        	    "[id^=Dwelling_Address], [id^=Dwelling_StreetNumber], [id^=Dwelling_Ladder], [id^=Dwelling_Indoor], [id^=Dwelling_Plan]", function () {
        	var id = $(this).attr("id");
            var n = id[id.length -1];
            var element = "";

            if (id.includes("Forwarding"))
                element = "#Forwarding";
            else if (id.includes("Domicile"))
                element = "#Domicile";
            else if(id.includes("Dwelling"))
        		element = "#Dwelling";

            var Address = 
                ($(element + "_Address" + n).val().length > 0 ? $(element + "_Address" + n).val() : "") +
        		($(element + "_StreetNumber" + n).val().length > 0 ? " " + $(element + "_StreetNumber" + n).val() : "") +
    			($(element + "_Ladder" + n).val().length > 0 ? " " + $(element + "_Ladder" + n).val() : "") +
    			($(element + "_Indoor" + n).val().length > 0 ? " " + $(element + "_Indoor" + n).val() : "") +
    			($(element + "_Plan" + n).val().length > 0 ? " " + $(element + "_Plan" + n).val() : "");
            if (Address.length > 46) 
            alert("Attenzione: l'indirizzo potrebbe risultare troncato su eventuali stampe in quanto non deve eccedere 46 caratteri (spazi compresi). Spazi contati: " + Address.length + " \n\n" + Address);
        });

        //ZIP RECAPITI
        $("#Forwarding").on("change", "[id^=Forwarding_Address], [id^=Forwarding_CityInput], [id^=Forwarding_CitySelect], [id^=Forwarding_ForeignCitySelect], [id^=Forwarding_StreetNumber]", function () {

            var id = $(this).attr("id");
            var n = id[id.length -1];
            var CityId="";
            var Type = "";
            console.log(id);
            console.log(id);

            if($("#Forwarding_CitySelect" + n).is(':visible')){
                CityId =  $('#Forwarding_CitySelect' + n).val();
            } else if ($("#Forwarding_CityInput" + n).is(':visible')) {
                CityId =  $('#Forwarding_CityInput' + n).val();
            } else if ($("#Forwarding_ForeignCitySelect" + n).is(':visible')) {
                CityId =  $('#Forwarding_ForeignCitySelect' + n).val();
                Type = "Foreign";
            }

            var Address = $('#Forwarding_Address' + n).val();
            var CountryId = $('#Forwarding_CountryId' + n).val();
            var StreetNumber = $('#Forwarding_StreetNumber' + n).val();

            if(CityId!=""){

                $.ajax({
                    url: 'ajax/ajx_src_zip.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                    success: function (data) {
                        if (CountryId == "Z102" || CountryId == "Z112"){
                        	$("#Forwarding_LandId" + n).val(data.LandId);
                        }
                        if(data.ZIP !="" ){
                            $("#Forwarding_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#Forwarding_ZIP" + n).val(data.ZIP);
                        } else $("#Forwarding_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                    }
                });

            } else {
            	$("#Forwarding_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
            	$("#Forwarding_LandId" + n).val('');
            }

            if(id.includes("Forwarding_CitySelect")) {
                $.ajax({
                    url: 'ajax/ajx_src_prov_shortTitle.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {CityId:CityId},
                    success: function (data) {
                        $("#Forwarding_span_Province" +n).html(data.Province);
                    },
                    error: function (data) {
                    	console.log(data);
                    }
                });
            }
        });

        //ZIP DOMICILI
        $("#Domicile").on("change", "[id^=Domicile_Address], [id^=Domicile_CityInput], [id^=Domicile_CitySelect], [id^=Domicile_ForeignCitySelect], [id^=Domicile_StreetNumber]", function () {

            var id = $(this).attr("id");
            var n = id[id.length -1];
            var CityId="";
            var Type = "";

            if($("#Domicile_CitySelect" + n).is(':visible')){
                CityId =  $('#Domicile_CitySelect' + n).val();
            } else if ($("#Domicile_CityInput" + n).is(':visible')) {
                CityId =  $('#Domicile_CityInput' + n).val();
            } else if ($("#Domicile_ForeignCitySelect" + n).is(':visible')) {
                CityId =  $('#Domicile_ForeignCitySelect' + n).val();
                Type = "Foreign";
            }

            var Address = $('#Domicile_Address' + n).val();
            var CountryId = $('#Domicile_CountryId' + n).val();
            var StreetNumber = $('#Domicile_StreetNumber' + n).val();

            if(CityId!=""){

                $.ajax({
                    url: 'ajax/ajx_src_zip.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                    success: function (data) {
                        if (CountryId == "Z102" || CountryId == "Z112"){
                        	$("#Domicile_LandId" + n).val(data.LandId);
                        }
                        if(data.ZIP !="" ){
                            $("#Domicile_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#Domicile_ZIP" + n).val(data.ZIP);
                        } else $("#Domicile_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                    }
                });

            } else {
            	$("#Domicile_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
            	$("#Domicile_LandId" + n).val('');
            }

            if(id.includes("Domicile_CitySelect")) {
                $.ajax({
                    url: 'ajax/ajx_src_prov_shortTitle.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {CityId:CityId},
                    success: function (data) {
                        $("#Domicile_span_Province" +n).html(data.Province);
                    },
                    error: function (data) {
                    	console.log(data);
                    }
                });
            }
        });

        //ZIP DIMORE
        $("#Dwelling").on("change", "[id^=Dwelling_Address], [id^=Dwelling_CityInput], [id^=Dwelling_CitySelect], [id^=Dwelling_ForeignCitySelect], [id^=Dwelling_StreetNumber]", function () {

            var id = $(this).attr("id");
            var n = id[id.length -1];
            var CityId="";
            var Type = "";
            
            if($("#Dwelling_CitySelect" + n).is(':visible')){
                CityId =  $('#Dwelling_CitySelect' + n).val();
            } else if ($("#Dwelling_CityInput" + n).is(':visible')) {
                CityId =  $('#Dwelling_CityInput' + n).val();
            } else if ($("#Dwelling_ForeignCitySelect" + n).is(':visible')) {
                CityId =  $('#Dwelling_ForeignCitySelect' + n).val();
                Type = "Foreign";
            }

            var Address = $('#Dwelling_Address' + n).val();
            var CountryId = $('#Dwelling_CountryId' + n).val();
            var StreetNumber = $('#Dwelling_StreetNumber' + n).val();

            if(CityId!=""){

                $.ajax({
                    url: 'ajax/ajx_src_zip.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                    success: function (data) {
                        if (CountryId == "Z102" || CountryId == "Z112"){
                        	$("#Dwelling_LandId" + n).val(data.LandId);
                        }
                        if(data.ZIP !="" ){
                            $("#Dwelling_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#Dwelling_ZIP" + n).val(data.ZIP);
                        } else $("#Dwelling_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                    }
                });

            } else {
            	$("#Dwelling_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
            	$("#Dwelling_LandId" + n).val('');
            }

            if(id.includes("Dwelling_CitySelect")) {
                $.ajax({
                    url: 'ajax/ajx_src_prov_shortTitle.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {CityId:CityId},
                    success: function (data) {
                        $("#Dwelling_span_Province" +n).html(data.Province);
                    },
                    error: function (data) {
                    	console.log(data);
                    }
                });
            }
        });

        //Cancellazione
        $("[id^=deleteForwarding], [id^=deleteDomicile], [id^=deleteDwelling]").click(function () {
            var ContactType = "";
            var ContactId = "";
            var TrespasserId = $('#TrespasserId').val();
            var genre = $('#Genre').val();
        	var id = $(this).attr("id");
    		var n = id[id.length -1];
        	ContactId = $(this).attr("contactid");
        	
        	if (id.includes("deleteForwarding")) {
            	ContactType = "Forwarding";
        	} else if (id.includes("deleteDomicile")) {
            	ContactType = "Domicile";
        	} else if (id.includes("deleteDwelling")){
            	ContactType = "Dwelling";
        	}
        	console.log(ContactType, n, ContactId);

            if(ContactType!=""){
            	if (confirm('Sei sicuro di voler cancellare questo contatto?')) {
            		$.ajax({
                        url: 'ajax/ajx_del_trespasserContact.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {ContactId:ContactId, ContactType:ContactType, TrespasserId:TrespasserId},
                        success: function (data) {
                            console.log(data);
    						if (ContactType == "Forwarding"){
    				            $("#ForwardingFields" + n + " input").val("");
    				            $("#ForwardingFields" + n + " select").val("");
    				            $("#ForwardingFields" + n + " #Forwarding_CountryId" + n).val("Z000");
    				            $("#ForwardingFields" + n + " textarea").val("");
    				            $("#ForwardingFields" + n + " :input").prop('disabled', false);
    				            $("#ForwardingFields" + n + " .forwarding_number").html("Recapito n. " + n);
    				            $("#Forwarding_CityInput" + n).hide();
    				            $("#Forwarding_CitySelect" + n).show();
    				            $("#deleteForwarding" + n).remove();

    				            if (data.NoForwardings) $("#tab_Forwarding a").removeClass("alert-success");
    						}
    						
    						if (ContactType == "Domicile"){
    				            $("#DomicileFields" + n + " input").val("");
    				            $("#DomicileFields" + n + " select").val("");
    				            $("#DomicileFields" + n + " #Domicile_CountryId" + n).val("Z000");
    				            $("#DomicileFields" + n + " textarea").val("");
    				            $("#DomicileFields" + n + " :input").prop('disabled', false);
    				            $("#DomicileFields" + n + " .domicile_number").html("Domicilio n. " + n);
    				            $("#Domicile_CityInput" + n).hide();
    				            $("#Domicile_CitySelect" + n).show();
    				            $("#deleteDomicile" + n).remove();

    				            if (data.NoDomiciles) $("#tab_Domicile a").removeClass("alert-success");
    						}

    						if (ContactType == "Dwelling"){
    				            $("#DwellingFields" + n + " input").val("");
    				            $("#DwellingFields" + n + " select").val("");
    				            $("#DwellingFields" + n + " #Dwelling_CountryId" + n).val("Z000");
    				            $("#DwellingFields" + n + " textarea").val("");
    				            $("#DwellingFields" + n + " :input").prop('disabled', false);
    				            $("#DwellingFields" + n + " .dwelling_number").html("Dimora n. " + n);
    				            $("#Dwelling_CityInput" + n).hide();
    				            $("#Dwelling_CitySelect" + n).show();
    				            $("#deleteDwelling" + n).remove();

    				            if (data.NoDwellings) $("#tab_Dwelling a").removeClass("alert-success");
    						}

    						if(genre != "D") $("#tab_Trespasser a[data-toggle='tab']").click();
    						else $("#tab_Company a[data-toggle='tab']").click();
        						
                        },
                        error: function (data) {
                            alert("error");
                            console.log(data);
                        }
                    });
            	} else return false;
            }
        });

        $(".fa-caret-down, .fa-caret-up, .fa-times").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });

    });

	$(".tableRow").mouseover(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
  	});

</script>
<?php
include(INC."/footer.php");
