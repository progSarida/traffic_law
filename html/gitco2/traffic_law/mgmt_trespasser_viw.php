<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

if (isset($_GET['message'])){
    $answer = $_GET['message'];
    echo "<div class='alert alert-warning message'>$answer</div>";
    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 4000);
    </script>
    <?php
}
if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    echo "<div class='alert alert-success'>$answer</div>";
    ?>
    <?php
}

$BackPage = strtok($str_BackPage, '?');

$Search_VatCode = CheckValue('Search_VatCode','s');
$Search_TrespCode = CheckValue('Search_TrespCode','s');
$Search_Province = CheckValue('Search_Province','s');
$Search_TaxCode = CheckValue('Search_TaxCode','s');
$Search_CityTitle = CheckValue('Search_CityTitle','s');
$str_GET_Parameter .= "&Search_VatCode=$Search_VatCode&Search_TrespCode=$Search_TrespCode&Search_Province=$Search_Province&Search_TaxCode=$Search_TaxCode&Search_CityTitle=$Search_CityTitle";

$aUserButton = array();
$UserPages = $rs->Select(MAIN_DB.".V_UserPage", "MainMenuId=".MENU_ID." AND UserId=".$_SESSION['userid']." AND LinkPage='mgmt_trespasser.php';");
while($UserPage = mysqli_fetch_array($UserPages)){
    $aUserButton[] = $UserPage['Title'];
}

$str_CurrentPage = curPageName();
$str_CurrentPage .="?1";
$FormPage = $str_CurrentPage;

$str_BackPage= CheckValue('P','s');
$str_GETLink ="?1&P=".$str_BackPage;
$rs= new CLS_DB();
//$rs->SetCharset('utf8');
$query = "";
$TrespasserId= CheckValue('Id','n');

$str_TrespasserHistory = "";
$str_ForwardingHistory = "";
$str_DomicileHistory = "";
$str_DwellingHistory = "";

$trespasser_rows = $rs->Select('Trespasser',"Id=".$TrespasserId, "Id");
$trespasser_row = mysqli_fetch_array($trespasser_rows);
$getNotificationDate = $rs->Select('FineTrespasser',"TrespasserId=".$TrespasserId);
$finenotificationdate = mysqli_fetch_array($getNotificationDate)['FineNotificationDate'];
$strTrespasser = DivTrespasserViewNEW($trespasser_row, "Anagrafica",$finenotificationdate);


$all_ids = array();
if(isset($_GET['query'])){
    $query = $_GET['query'];
    $ids = $rs->SelectQuery($_GET['query']);
    while ($id = mysqli_fetch_array($ids)) $all_ids [] = $id['Id'];
}
$key = array_search($TrespasserId, $all_ids);
if(isset($all_ids[$key+1])) $str_Next = '<a href="'.$str_CurrentPage.'&Id='.$all_ids[$key+1].'&P='.$str_BackPage.'&query='.$_GET['query'].'"><i class="glyphicon glyphicon-arrow-right" style="font-size:3.8rem;color:#fff; float: right"></i></a>';
else $str_Next='';
if(isset($all_ids[$key-1])) $str_Previous = '<a href="' . $str_CurrentPage. '&Id=' . $all_ids[$key-1] .'&P='.$str_BackPage. '&query='.$_GET['query'].'"><i class="glyphicon glyphicon-arrow-left" style="font-size:3.8rem;color:#fff"></i></a>';
else $str_Previous = '';

$DeathDateWarning = false;
$str_DeathWarning = "";


if($trespasser_row['DeathDate']!=""){
    $rs_FineTrespasser = $rs->Select('V_FineTrespasser',"TrespasserId=".$TrespasserId);
    while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
        if ($trespasser_row['DeathDate'] < $r_FineTrespasser['FineDate']) $DeathDateWarning = true;
    }
    
    if($DeathDateWarning) $str_DeathWarning =
    "$('#div_message_page').addClass('alert alert-warning')
 $('#div_message_page').text('Attenzione: la data di decesso è antecedente rispetto alla data di infrazione di almeno uno o più verbali associati')";
}


//STORICO TRASGRESSORE
$rs_TrespasserHistory = $rs->SelectQuery('SELECT T.*, C.Title CountryTitle FROM TrespasserHistory T JOIN Country C ON T.CountryId=C.Id  WHERE T.TrespasserId='.$TrespasserId.' ORDER BY Id DESC');
$RowNumber = mysqli_num_rows($rs_TrespasserHistory);

if ($RowNumber==0){
    $str_TrespasserHistory .= '
        <div class="col-sm-12 BoxRow">
            <div class="col-sm-12 BoxRowLabel">
                Nessun modifica effettuata
            </div>
        </div>';
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
            </div>';
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


// id, codice,  data, ora, targa
$str_out .='
    	<div class="row-fluid">
    	    <input type="hidden" name="Genre" id="Genre" value="">
    	    <input type=hidden name="TrespasserId" value="'.$trespasser_row['Id'].'">
    	    <div class="col-sm-12 BoxRowCaption" style="height: 4.5rem;">
        	    <div class="col-sm-1">'.$str_Previous.'</div>
                <div class="col-sm-1" style="float: right">'.$str_Next.'</div>
            </div>
			<div class="col-sm-12" >
                <div class="col-sm-7">
                    '.$strTrespasser.'
                    <div class="clean_row HSpace16"></div>
                    <div class="clean_row HSpace16"></div>
                </div>
                <div class="col-sm-5">
                    <div class="BoxRowTitle" style="text-align:center">
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
        </div>';

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
    else{
        
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

$str_out.= $str_Fine.'
    <div class="table_label_H HSpace4 col-sm-12" style="height:8rem;">
        <button type="button" class="btn btn-default" id="back" style="margin-top:2rem;">Indietro</button>
    </div>';

echo $str_out;
?>
<script type="text/javascript">

	$(document).ready(function () {

		<?= $str_DeathWarning; ?>

	    //Nasconde o mostra gli storici in base al tab
	    $("a[data-toggle=\'tab\']").on("shown.bs.tab", function (){
	        if ($(this).parent().attr("id") == "tab_Subject"){
	            $("#TrespasserHistory").show();
	            $("#ForwardingHistory, #DomicileHistory, #DwellingHistory").hide();
	        }
	        if ($(this).parent().attr("id") == "tab_Forwarding"){
	            $("#ForwardingHistory").show();
	            $("#TrespasserHistory, #DomicileHistory, #DwellingHistory").hide();
	        }
	        if ($(this).parent().attr("id") == "tab_Domicile"){
	            $("#DomicileHistory").show();
	            $("#TrespasserHistory, #ForwardingHistory, #DwellingHistory").hide();
	        }
	        if ($(this).parent().attr("id") == "tab_Dwelling"){
	            $("#DwellingHistory").show();
	            $("#TrespasserHistory, #ForwardingHistory, #DomicileHistory").hide();
	        }
	    });

	    //Cambia i caret in base al collapse negli storici dei contatti
	    $('.collapse').on("show.bs.collapse", function(){
	    	var id = $(this).attr("id");
	    	$(".caret-toggle[data-target='#" + id +"']").toggleClass('fa-angle-up fa-angle-down');
	    });

	    $('.collapse').on("hide.bs.collapse", function(){
	    	var id = $(this).attr("id");
	    	$(".caret-toggle[data-target='#" + id +"']").toggleClass('fa-angle-up fa-angle-down');
	    });

	});

    $('#back').click(function(){
        window.location="<?= $BackPage.$str_GET_Parameter.'&Filter=1' ?>"
    });

</script>
<?php
include(INC."/footer.php");
