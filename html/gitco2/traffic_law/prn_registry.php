<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/initialization.php");
require_once(PGFN . "/fn_prn_registry.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//Quando si entra la prima volta nella pagina
//Imposta il tasto solleciti emessi "solo ultimi emessi" di default ad 1
//Imposta il tasto Avvisi bonari pagamento agevolato di default ad 1
if($btn_search!=1){
    $Search_IsLastEmitted = 1;
    $Search_HasKindFine = 1;
    $Search_CurrentYear = 1;
}

//Parametri ente
// $rs_ProcessingDataNational = $rs->Select('ProcessingDataPaymentNational',"CityId = '".$_SESSION['cityid']."'");
// $rs_ProcessingDataForeign = $rs->Select('ProcessingDataPaymentForeign',"CityId = '".$_SESSION['cityid']."'");

// $r_ProcessingDataNational = mysqli_fetch_array($rs_ProcessingDataNational);
// $r_ProcessingDataForeign = mysqli_fetch_array($rs_ProcessingDataForeign);


$str_DetectorOptions = '<option></option>';
$rs_Detector = $rs->SelectQuery("SELECT D.TitleIta, D.Id FROM Detector D JOIN DetectorType DT on D.DetectorTypeId = DT.Id WHERE DT.ViolationTypeId =".$Search_Violation." AND D.CityId='".$_SESSION['cityid']."'");

while ($r_Detector = mysqli_fetch_array($rs_Detector)){
    $str_DetectorOptions .= '<option'.($r_Detector['Id'] == $Search_Detector ? ' selected' : '').' value='.$r_Detector['Id'].'>'.StringOutDB($r_Detector['TitleIta']).'</option>';
}

$str_ActFilter ='
    <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;" id="TypeViolationLabel">
        Tipo atto:
    </div>
    <div class="col-sm-2 BoxRowCaption" id="TypeViolationDivSelect">
        <select name="Search_TypeViolation" class="form-control fineTypeFilter" id="TypeViolationSelect">
            <option value="0"></option>
            <option value="1" id="TypeViolation_1" '.$a_ChkTypeViolation[1].'>Preavvisi</option>
            <option value="2" id="TypeViolation_2" '.$a_ChkTypeViolation[2].'>Verbali</option>
            <option value="3" id="TypeViolation_3" '.$a_ChkTypeViolation[3].'>Solleciti emessi</option>
        </select>
    </div>
    <div class="col-sm-1 BoxRowCaption text-center" id="LastEmittedReminderDiv">
            <input type="checkbox" id="LastEmittedReminderInput" value="1" name="Search_IsLastEmitted" ' . ChkCheckButton($Search_IsLastEmitted) . '>
            <span style="vertical-align: text-bottom;"> Solo ultimi emessi</span>
    </div>
    <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
                Tipo notifica/invio:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <select name="Search_TypeNotification" class="form-control notificationFilter">
                    <option value="0"></option>
                    <option value="5" '.$a_ChkTypeNotification[5].'>Inviati via posta</option>
                    <option value="6" '.$a_ChkTypeNotification[6].'>Inviati via pec, messo o altro</option>
                    <option value="1" '.$a_ChkTypeNotification[1].'>Notificati via posta</option>
                    <option value="4" '.$a_ChkTypeNotification[4].'>Notificati pec, messo o altro</option>
                    <option value="2" '.$a_ChkTypeNotification[2].'>Ancora da notificare</option>
                    <option value="3" '.$a_ChkTypeNotification[3].'>Non notificati</option>
                </select>
            </div>
    <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
        Pagamento:
    </div>
    <div class="col-sm-1 BoxRowCaption">
        <select name="Search_TypePayment" class="form-control" id="Search_TypePayment">
            <option value="0"></option>
            <option value="2" '.$a_ChkTypePayment[2].'>Pagato</option>
            <option value="3" '.$a_ChkTypePayment[3].'>Non pagato</option>
            <option value="4" '.$a_ChkTypePayment[4].'>Parziale</option>
        </select>
    </div>
';



//FIXME Il filtro sul ruolo non ha logiche collegate. A quanto pare è sempre stato così
$str_TypeRule ='
    <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
        Ruolo:
    </div>
    <div class="col-sm-1 BoxRowCaption">
        <select name="Search_HasInjunction" class="form-control">
            <option value="0"></option>
            <option value="1" '.$a_ChkHasInjunction[1].'>Iscritti</option>
            <option value="2" '.$a_ChkHasInjunction[2].'>Non iscritti</option>
        </select>
    </div>
';


$str_TypeArchive ='
    <div class="col-sm-1 BoxRowLabel tipiArchiviati" style="padding-right:1rem;display:none;">
        Tipologie archiviati:
    </div>
    <div class="col-sm-1 BoxRowCaption tipiArchiviati">
        <select name="Search_TypeArchive" class="form-control">
            <option value="0">Tutte</option>
            <option value="35" '.$a_ChkTypeArchive[35].'>Archiv. da ente</option>
            <option value="36" '.$a_ChkTypeArchive[36].'>Archiv. per noleggio</option>
            <option value="37" '.$a_ChkTypeArchive[37].'>Archiv. d\'ufficio</option>
        </select>
    </div>
';

$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

$str_Locality = '
    <div class="col-sm-2 BoxRowCaption">
    </div>
';
if($n_Code>0) {
    $str_Locality = '
                <div class="col-sm-1 BoxRowLabel">
                    Comune
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <select class="form-control" name="Search_Locality">
                    <option></option>
';

    while ($r_Row = mysqli_fetch_array($rs_Row)) {

        $str_Locality .= '<option value="' . $r_Row['Id'].'"';

        if($Search_Locality==$r_Row['Id']) $str_Locality .=' SELECTED ';


        $str_Locality .= '>' . $r_Row['Title'] . '</option>';

    }

    $str_Locality .= '        </select>
                    </div>';

}

if($_SESSION['userlevel']>0){
    $str_UserLevelFilter = '                            
        <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
              <input class="form-control frm_field_date" name="PrintDate" type="text" value="'.$d_PrintDate.'">
        </div>
       ';

} else {
    $str_UserLevelFilter = '
        <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="PrintDate" type="text" value="'.$d_PrintDate.'" readonly>
        </div>                        
';
}

$str_out .='

<form id="f_search" action="prn_registry.php" method="get">
<input type="hidden" name="btn_search" value="1">
<input type="hidden" id="Action" name="Action" value="">

<div class="row-fluid">        
    <div class="col-sm-12" >
        <div class="col-sm-11" style="height:16.0rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Accertamento:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_FromFineDate" type="text" value="'.$Search_FromFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_ToFineDate" type="text" value="'.$Search_ToFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="padding-right:1rem;">
                Genere:
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="Search_Genre" class="form-control">
                    <option value="0">Tutti</option>
                    <option value="1" '.$str_CheckGenre1.'>Persona fisica</option>
                    <option value="2" '.$str_CheckGenre2.'>Persona giuridica</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="'.$Search_Trespasser.'">
            </div>  
            '.$str_UserLevelFilter.'
            <div class="clean_row HSpace4"></div>      
            <div class="col-sm-1 BoxRowLabel">
                Notifica:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input disabled data-toggle="tooltip" data-placement="right" data-html="true" data-container="body" title="Compilare i filtri \'Tipo\' (Verbali) e \'Notifica\' (Notificati via posta oppure Inviati via pec messo o altro) per sbloccare" class="tooltip-r form-control frm_field_date notificationDateFilter" name="Search_FromNotificationDate" type="text" value="'.$Search_FromNotificationDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input disabled data-toggle="tooltip" data-placement="right" data-html="true" data-container="body" title="Compilare i filtri \'Tipo\' (Verbali) e \'Notifica\' (Notificati via posta oppure Inviati via pec messo o altro) per sbloccare" class="tooltip-r form-control frm_field_date notificationDateFilter" name="Search_ToNotificationDate" type="text" value="'.$Search_ToNotificationDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Luogo infrazione:
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.CreateSelectQuery("SELECT DISTINCT Address FROM Fine WHERE CityId='".$_SESSION['cityid']."' AND ProtocolYear=". $_SESSION['year'] ." ORDER BY ADDRESS ","Search_Address","Address","Address",$Search_Address,false) .'
            </div>                    
            '.$str_Locality.'                        
                                      
            <div class="clean_row HSpace4"></div>                 
 
            <div class="col-sm-1 BoxRowLabel">
                Postalizzazione:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_date" type="text" value="'. $Search_FromSendDate. '" id="Search_FromSendDate" name="Search_FromSendDate">
			</div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_date" type="text" value="'. $Search_ToSendDate. '" id="Search_ToSendDate" name="Search_ToSendDate">
		    </div> 
            <div class="col-sm-1 BoxRowLabel">
                Articolo:
            </div>
            <div class="col-sm-6 BoxRowCaption">
                '.CreateSelectQuery("SELECT DISTINCT A.Id ArticleId, A.Article,A.Letter,A.Paragraph, CONCAT(A.Article,' ',A.Letter,' ',A.Paragraph) ArticleTitle FROM Fine F JOIN FineArticle FA ON F.Id=FA.FineId JOIN Article A ON FA.ArticleId=A.Id WHERE F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=". $_SESSION['year'] ." ORDER BY A.Article,A.Letter,A.Paragraph ","Search_ArticleId","ArticleId","ArticleTitle",$Search_ArticleId,false) .'
            </div> 
              
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Archiviazione:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_date" type="text" value="'. $Search_FromArchiveDate. '" id="Search_FromArchiveDate" name="Search_FromArchiveDate" title="Selezionare archiviati Solo loro per sbloccare">
			</div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_date" type="text" value="'. $Search_ToArchiveDate. '" id="Search_ToArchiveDate" name="Search_ToArchiveDate" title="Selezionare archiviati Solo loro per sbloccare">
		    </div>
            <div class="col-sm-3 BoxRowCaption" style="padding:0">
                <div class="col-sm-4 BoxRowLabel">
                    Archiviati:
                </div>
                <div class="col-sm-8 BoxRowCaption">
                    <input type="radio" name="FineArchive" value="0" id="FineArchive_0" class="FineArchive" ' . $str_ChkFineArchive0 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Escludi</span> 
                    <input type="radio" name="FineArchive" value="1" id="FineArchive_1" class="FineArchive" ' . $str_ChkFineArchive1 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" name="FineArchive" value="2" id="FineArchive_2" class="FineArchive" ' . $str_ChkFineArchive2 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
            </div>
            '.$str_TypeArchive.'
            <div class="col-sm-4 BoxRowCaption sceltaRicorsi" style="padding:0">        		               
                <div class="col-sm-3 BoxRowLabel">
                    Ricorsi:
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    <input type="radio" name="FineDispute" value="0" ' . $str_ChkFineDispute0 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Escludi</span> 
                    <input type="radio" name="FineDispute" value="1" ' . $str_ChkFineDispute1 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" name="FineDispute" value="2" ' . $str_ChkFineDispute2 . ' style="top:0;"><span style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Cronologico:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId">
			</div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>   
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId">
		    </div> 
            <div class="col-sm-1 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select class="form-control" name="Search_Detector" id="Search_Detector">
                    '.$str_DetectorOptions.'
                </select>
            </div>
            <div class="col-sm-3 BoxRowCaption"></div>
             		               
            <div class="clean_row HSpace4"></div>'; 
            

            //Bug3153 Il filtro sulla nazionalità del trasgressore dev'essere visibile solo agli utenti con permessi superiori a quelli del comune
            if($_SESSION['userlevel'] > 2){
            $str_out .=
            '<div class="col-sm-1 BoxRowLabel">
                Nazionalità trasgressore
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(NAZIONALE => "Italiano", STRANIERO => "Estero"), true, "Search_NationalityId", "Search_NationalityId", $Search_NationalityId, false) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Fascia oraria
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(FASCIA_DIURNA=>"Diurna",FASCIA_NOTTURNA=>"Notturna"), true, "Search_DayTimeSlot", "Search_DayTimeSlot",$Search_DayTimeSlot,false).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false) .'
            </div>';
            }
            else{
            $str_out .=
            '<div class="col-sm-1 BoxRowLabel">
                Fascia oraria
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.CreateArraySelect(array(FASCIA_DIURNA=>"Diurna",FASCIA_NOTTURNA=>"Notturna"), true, "Search_DayTimeSlot", "Search_DayTimeSlot",$Search_DayTimeSlot,false).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false) .'
            </div>';
            }
            
            $str_out .=
            '               		               
            <div class="col-sm-1 BoxRowCaption text-center" id="ForeignFineNotPayed">
                <input type="checkbox" value="1" name="Search_ForeignFineNotPayed" id="Search_ForeignFineNotPayed" ' . ChkCheckButton($Search_ForeignFineNotPayed) . '>
                <span style="vertical-align: text-bottom; font-size:9px;"> Solo verb. esteri non pagati</span>
            </div>
            <div class="col-sm-1 BoxRowCaption text-center" id="HasPEC">
                <input type="checkbox" value="1" name="Search_HasPEC" id="$Search_HasPEC" ' . ChkCheckButton($Search_HasPEC) . '>
                <span style="vertical-align: text-bottom;"> Solo verb. PEC</span>
            </div>
            <div class="col-sm-1 BoxRowCaption text-center" id="Renotified">
                <input type="checkbox" value="1" name="Search_NotificationStatus" id="Search_NotificationStatus" ' . ChkCheckButton($Search_NotificationStatus) . '>
                <span style="vertical-align: text-bottom;"> Solo rinotifiche</span>
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="1" name="Search_HasKindSendDate" id="Search_HasKindSendDate" ' . ChkCheckButton($Search_HasKindSendDate) . '>
                <span style="vertical-align: text-bottom;"> Includi inviti in AG</span>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="checkbox" value="1" name="Search_HasKindFine" id="Search_HasKindFine" ' . ChkCheckButton($Search_HasKindFine) . '>
                <span style="vertical-align: text-bottom; font-size:9px;"> Includi avvisi bonari pag. agevolato</span>
            </div>
		    <div class="clean_row HSpace4"></div>  
		            
            '.$str_ActFilter.'
            '.$str_TypeRule.'
            
            <div class="col-sm-1 BoxRowCaption text-center" id="CurrentYearDiv">
                <input type="checkbox" value="1" name="Search_CurrentYear" ' . ChkCheckButton($Search_CurrentYear) . '>
                <span style="vertical-align: text-bottom;"> Anno corrente</span>
            </div>
            <div class="col-sm-1 BoxRowCaption"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:16.0rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="height:33%;font-size:2.2rem;padding:0;margin:0;width:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
            <button type="submit" data-action="Pdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning col-sm-4" id="printPdf" name="printPdf" style="height:33%;font-size:2.2rem;padding:0;;width:100%">
            	<i class="fa fa-file-pdf-o"></i>
            </button>
            <button type="submit" data-action="Excel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success col-sm-4" id="printExcel" name="printExcel" style="height:33%;font-size:2.2rem;padding:0;;width:100%">
            	<i class="fa fa-file-excel-o"></i>
            </button>
        </div>
    </div>
</div>
</form>';

$str_out .='        
    	<div class="row-fluid">
        	<div class="col-sm-12">
                <div class="clean_row HSpace4"></div>
				<div class="table_label_H col-sm-2">Protocollo</div>
				<div class="table_label_H col-sm-3">Riferimento</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-2">Targa</div>
                <div class="table_label_H col-sm-3">Articolo</div>

                <div class="clean_row HSpace4"></div>

                <div class="table_label_H col-sm-5">Località</div>
                <div class="table_label_H col-sm-2">Pagamento</div>
                <div class="table_label_H col-sm-5">Trasgressore</div>

				<div class="clean_row HSpace4"></div>';


if($btn_search==1){

    $str_Query = creaQuery();
        
    //echo $str_Query;
    $rs_Registry = $rs->SelectQuery($str_Query);
	$RowNumber = mysqli_num_rows($rs_Registry);
	
	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
	} else {



        $rs_Result = $rs->Select('Result', "1=1");
        while ($r_Result = mysqli_fetch_array($rs_Result)){
            $a_Result[$r_Result['Id']] = $r_Result['Title'];
        }
        $a_GradeType = array("","I","II","III");



        $n_CountFine = 0;
        $n_CountNotification = 0;
        $n_CountPayment = 0;

        $f_TotalPayment = 0.00;

        $f_TotFee      = 0.00;

        //Variabili per il conteggio dei solleciti
        $ReminderCount = 0; //Contatore solleciti per stesso FineId
        $LastReminderFineId = 0; //Conserva il FineId dell'ultimo verbale con sollecito
        $MaxReminderId = 0; //Conserva il ReminderId dell'ultimo sollecito
        $counter = 1;
        
		while ($r_Registry = mysqli_fetch_array($rs_Registry)) {
		    
            //Controlla se il verbale ha almeno un sollecito associato
            $hasReminderFlow = isset($r_Registry['ReminderId']);
            
            //Se ha almeno un sollecito ed il FineId attuale è diverso da quello appena precedente azzero il conteggio dei solleciti legati al FineId in corso
            if($hasReminderFlow && ($r_Registry['FineId'] != $LastReminderFineId)):
                $ReminderCount = 1;
                $LastReminderFineId = $r_Registry['FineId']; //Segno il FineId attuale come id con sollecito corrente
                //Cerco il ReminderId massimo legato al FineId corrente in modo da non fare cumulo dei pagamenti e dei dovuti di tutti i solleciti legati allo stesso verbale
                $rs_Reminders = $rs->SelectQuery("SELECT MAX(Id) AS ReminderId FROM FineReminder WHERE FineId = ".$r_Registry['FineId']." AND FlowDate IS NOT NULL ORDER BY Id, FineId");
                $r_Reminders = mysqli_fetch_array($rs_Reminders);
                $MaxReminderId = $r_Reminders['ReminderId'];
            //Se ha almeno un sollecito e se il FineId attuale è uguale a quello processato in precedenza incremento il conteggio dei solleciti perchè multipli
            elseif($hasReminderFlow && ($r_Registry['FineId'] == $LastReminderFineId)):
                $ReminderCount++;
            endif;

            //Controlla se il sollecito attuale è l'ultimo disponibile per il verbale
            $isLastReminderAvailable = ($hasReminderFlow && ($r_Registry['ReminderId'] == $MaxReminderId));
            
            //Definisce se un verbale sia o meno da mostrare
            $isFineShowable = (($n_TypeViolation != 3 && !$hasReminderFlow) || ($n_TypeViolation != 3 && $hasReminderFlow && $isLastReminderAvailable));
            
            //Definisce se il sollecito sia o meno da mostrare
            $isReminderShowable = ($n_TypeViolation == 3 && $Search_IsLastEmitted && $hasReminderFlow && ($r_Registry['ReminderId'] == $MaxReminderId)) || ($n_TypeViolation == 3 && !$Search_IsLastEmitted && $hasReminderFlow);
            
            //echo "<br>Cron: ".$r_Registry['ProtocolId']." Prog. n°: ".$ReminderCount." FineId corrente: ".$LastReminderFineId." Massimo ReminderId: ".$MaxReminderId;
                
            $str_Article = "Art. ";
            $rs_article = $rs->Select('Article',"Id=".$r_Registry['ArticleId']);
            $r_article = mysqli_fetch_array($rs_article);

            $str_Article .= $r_article['Article'].' '.str_replace("0","",$r_article['Paragraph']).' '.$r_article['Letter'];
            
            $AdditionalArticleAmount = 0;
            
            if($r_Registry['ArticleNumber']>1){

                $rs_FineAdditionalArticle= $rs->SelectQuery(
                    "
                SELECT    
                    FAA.Fee,
                    FAA.MaxFee,
                    
                    A.Article,
                    A.Paragraph,
                    A.Letter
                    FROM FineAdditionalArticle FAA JOIN Article A ON FAA.ArticleId=A.Id
                    

                    WHERE
                    FAA.FineId=".$r_Registry['FineId']
                );
                //Articoli addizionali solo se selezione Nulla, Preavvisi, Verbali
                if($n_TypeViolation < 3)
                    while ($r_FineAdditionalArticle = mysqli_fetch_array($rs_FineAdditionalArticle)){
                        $str_Article .= " + Art. ";
    
    
                        $str_Article .= $r_FineAdditionalArticle['Article'].' '.str_replace("0","",$r_FineAdditionalArticle['Paragraph']).' '.$r_FineAdditionalArticle['Letter'];
                        $AdditionalArticleAmount += $r_FineAdditionalArticle['Fee'];
                    }
            }

            $rs_Locality = $rs->Select(MAIN_DB.".City","Id='".$r_Registry['Locality']."'");
            $r_Locality = mysqli_fetch_array($rs_Locality);

            $str_Locality = $r_Locality['Title'] .' - '.$r_Registry['Address'];
            
            //In caso di verbale collegato a più solleciti, prende la riga associata al sollecito più recente
            if($n_TypeViolation < 3 && !$isFineShowable)
                    continue;
            if($n_TypeViolation == 3 && !$isReminderShowable)
                    continue;
                
            $AmountPayed = 0;
            
            $str_Payment = '';
            
            $nextReminderExistence = true; //Controlla se esistono successivi solleciti. E' inizializzata a true perchè le logiche devono essere scatenate in caso il sollecito in questione sia l'ultimo
            
            //In caso di selezione Nulla, Preavvisi, Verbali
            if($n_TypeViolation < 3){
                //******************************PARTE PAGAMENTI E DOVUTI*********************************
                $NotificationDate = isset($r_Registry['NotificationDate']) ? $r_Registry['NotificationDate'] : $r_Registry['FineDate'];
                //PAYMENT
                $rs_Payment = $rs->SelectQuery('SELECT MIN(PaymentDate) AS PaymentDate, SUM(Amount) AS Amount FROM FinePayment WHERE FineId=' . $r_Registry['FineId']);
                $r_Payment = mysqli_fetch_array($rs_Payment);
                
                //Controlla l'ammontare del pagato
                if (isset($r_Payment['Amount']) && $r_Payment['Amount'] > 0){
                    $AmountPayed = number_format($r_Payment['Amount'], 2, '.', '');
                }
                
                //Pagamenti dovuti/////////////////////////////////////////////////////////////////////////////////////
                $n_Interval = 0;
                if (isset($r_Payment['PaymentDate'])){
                    $PaymentDate = $r_Payment['PaymentDate'];
                    $n_Interval = date_diff(date_create($NotificationDate), date_create($PaymentDate));
                } else {
                    $n_Interval = date_diff(date_create($NotificationDate), date_create(DateInDB($d_PrintDate)));
                }
                $n_Interval = $n_Interval->format('%a');
                //ADDITIONAL ARTICLE
                $rs_AdditionalArticle=null;
                if ($r_Registry['ArticleNumber'] > 1) {
                    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_Registry['FineId'], "ArticleOrder");
                }
                //ARTICLE TARIFF
                $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Registry['ArticleId'] . " AND Year=" . $r_Registry['ProtocolYear']);
                $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
                //var_dump($r_Customer);
                $a_OwnedPayment = calcolaImportiSulVerbale($r_Registry,$rs_AdditionalArticle,$r_ArticleTariff,$r_Customer);
                $AmountFee = number_format($a_OwnedPayment['AdditionalFee'], 2, '.', '');
                if ($n_Interval <= FINE_DAY_LIMIT_REDUCTION){
                    $AmountOwned = number_format($a_OwnedPayment['ReducedPartial'], 2, '.', '');
                    $AmountWithoutFee = number_format(($a_OwnedPayment['ReducedPartial'] - $AmountFee), 2, '.', '');
                } else if ($n_Interval <= FINE_DAY_LIMIT){
                    $AmountOwned = number_format($a_OwnedPayment['ReducedTotal'], 2, '.', '');
                    $AmountWithoutFee = number_format(($a_OwnedPayment['ReducedTotal'] - $AmountFee), 2, '.', '');
                } else {
                    $AmountOwned = number_format($a_OwnedPayment['Total'], 2, '.', '');
                    $AmountWithoutFee = number_format(($a_OwnedPayment['Total'] - $AmountFee), 2, '.', '');
                }
                //*******************************************************************
                
                if ($r_Payment['Amount'] > 0) {
                    $str_Payment = 'Pagato: '. DateOutDB($r_Payment['PaymentDate'])." - € ".NumberDisplay($AmountPayed);
                }
                else{
                    $str_Payment = 'Pagato: € 0';
                }
                $str_Payment .= ' Dovuto: € '.NumberDisplay($AmountOwned);
            }
            //in caso di selezione Solleciti emessi
            elseif($n_TypeViolation == 3){
                //Controllo se c'è un sollecito successivo
                $rs_NextReminder = $rs->SelectQuery("SELECT Id AS ReminderId, FlowDate FROM FineReminder WHERE FineId = ".$r_Registry['FineId']." AND FlowDate IS NOT NULL AND Id > ".$r_Registry['ReminderId']." ORDER BY Id LIMIT 1");
                $r_NextReminder = mysqli_fetch_array($rs_NextReminder);
                $rs_Payment;
                //Prendo tutti i pagamenti
                if(mysqli_num_rows($rs_NextReminder) == 0):
                    $rs_Payment = $rs->SelectQuery('SELECT MIN(PaymentDate) AS PaymentDate, SUM(Amount) AS Amount FROM FinePayment WHERE FineId=' . $r_Registry['FineId']);
                //Prendo solo i pagamenti fino alla data di emissione del sollecito successivo
                else:
                    $rs_Payment = $rs->SelectQuery('SELECT MIN(PaymentDate) AS PaymentDate, SUM(Amount) AS Amount FROM FinePayment WHERE FineId=' . $r_Registry['FineId']." AND PaymentDate < '".$r_NextReminder['FlowDate']."'");
                endif;
                
                $AmountOwned = $r_Registry['TotalAmount'];
                
                $r_Payment = mysqli_fetch_array($rs_Payment);
                if ($r_Payment['Amount'] > 0) {
                    $AmountPayed = $r_Payment['Amount'];
                    $str_Payment = 'Pagato: '. DateOutDB($r_Payment['PaymentDate'])." - € ".NumberDisplay($AmountPayed);
                    
                    if(mysqli_num_rows($rs_NextReminder) == 0)
                        $nextReminderExistence = false;
                }
                else{
                    $str_Payment = 'Pagato: € 0';
                }
                $str_Payment .= ' Dovuto: € '.NumberDisplay($r_Registry['TotalAmount']);
            }
            
            if(!$Search_ForeignFineNotPayed){
                if($n_TypePayment == 2 && $AmountOwned > $AmountPayed)
                    continue;
                elseif($n_TypePayment == 3 && $AmountPayed != 0)
                    continue;
                elseif(($n_TypePayment == 4 && ($AmountOwned <= $AmountPayed)) || ($n_TypePayment == 4 && $AmountPayed == 0))
                    continue;
                }
                    
            $n_CountFine++;
            //Nulla, Preavvisi e verbali
            if($isFineShowable):
                $f_TotFee += $r_Registry['Fee']+$AdditionalArticleAmount;
            //Solleciti emessi
            elseif(($n_TypeViolation == 3) && ($r_Registry['ReminderId'] == $MaxReminderId)):
                $f_TotFee += $r_Registry['TotalAmount'];
            endif;
            
            if($AmountPayed > 0)
                $n_CountPayment++;
            
            if($n_TypeViolation < 3){
                $f_TotalPayment += $AmountPayed;
                }
            elseif($n_TypeViolation == 3){
                if(!$nextReminderExistence)
                    $f_TotalPayment += $r_Payment['Amount'];
                }
                
            $str_ReminderNumber = "";
            //Per stampare il numero di sollecito
            if($n_TypeViolation == 3 && $Search_IsLastEmitted){
                if($hasReminderFlow)
                    $str_ReminderNumber = " - Solleciti generati ed emessi: ".$ReminderCount;
            }
            //Stampa della riga
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $r_Registry['ProtocolId'].' / '.$r_Registry['ProtocolYear'].$str_ReminderNumber.'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-3">' . 'Ref: '.$r_Registry['Code'] .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-1">' . DateOutDB($r_Registry['FineDate']) .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-1">' . TimeOutDB($r_Registry['FineTime']) .'</div>';
			$str_out.= '<div class="table_caption_H font_small col-sm-2">' . $r_Registry['VehiclePlate'] .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-3">' . $str_Article .'</div>';

			$str_out.= '<div class="clean_row HSpace4"></div>';


            $str_out.= '<div class="table_caption_H font_small col-sm-5">' . $str_Locality .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-2">' . $str_Payment .'</div>';
            $str_out.= '<div class="table_caption_H font_small col-sm-5">' . '('.$r_Registry['TrespasserId']. ') '.$r_Registry['CompanyName'] .' '.$r_Registry['Surname'] .' '.$r_Registry['Name'] .'</div>';


            $str_out.= '<div class="clean_row HSpace16"></div>';
		}
		//Titoli dei totali Tutti, Preavvisi, Verbali
		if($n_TypeViolation < 3){
            $str_out.= '
                <div class="table_label_H col-sm-12 center">Riepilogo generale</div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="BoxRowLabel col-sm-2">Totale verbali</div>
                <div class="BoxRowCaption col-sm-4">' . $n_CountFine . '</div>
                <div class="BoxRowLabel col-sm-2">Totale Violazioni (minimo edittale)</div>
                <div class="BoxRowCaption col-sm-4">' . NumberDisplay($f_TotFee) .'</div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="BoxRowLabel col-sm-2">Verbali pagati</div>
                <div class="BoxRowCaption col-sm-4">' . $n_CountPayment .'</div>
                <div class="BoxRowLabel col-sm-2">Totale pagamenti</div>
                <div class="BoxRowCaption col-sm-4">' . NumberDisplay($f_TotalPayment) .'</div>
            ';
		}
		//Titoli dei totali Solleciti emessi
		elseif($n_TypeViolation == 3){
		    $str_out.= '
                <div class="table_label_H col-sm-12 center">Riepilogo generale</div>
		        
                <div class="clean_row HSpace4"></div>
		        
                <div class="BoxRowLabel col-sm-2">Totale solleciti emessi</div>
                <div class="BoxRowCaption col-sm-4">' . $n_CountFine .'</div>
                <div class="BoxRowLabel col-sm-2">Totale dovuto (solleciti)</div>
                <div class="BoxRowCaption col-sm-4">' . NumberDisplay($f_TotFee) .'</div>
                    
                <div class="clean_row HSpace4"></div>
                    
                <div class="BoxRowLabel col-sm-2">Solleciti pagati</div>
                <div class="BoxRowCaption col-sm-4">' . $n_CountPayment .'</div>
                <div class="BoxRowLabel col-sm-2">Totale pagamenti</div>
                <div class="BoxRowCaption col-sm-4">' . NumberDisplay($f_TotalPayment) .'</div>
            ';
		}

	}
} else {
    $str_out.= '
        <div class="table_caption_H col-sm-12 text-center">
            Inserire criteri di ricerca
        </div>';
}

	$str_out.= '<div>
</div>';


	echo $str_out;
?>
<script type="text/javascript">

    $(document).ready(function () {
    
		controllaBloccoInputDateNotifica(); //Controllo se è impostata la combinazione di filtri necessaria perchè il filtro sulle date di notifica funzioni
		
        $('#search').click(function () {
            $("#printExcel, #printPdf, #search").prop('disabled', true);
            $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
            $('#f_search').submit();
        });

        $("#printExcel, #printPdf").on('click',function(e){
            $('#f_search').attr('action', 'prn_registry_exe.php');
            $('#Action').val($(this).data('action'));
            $('#search, #printPdf, #printExcel').prop('disabled', true);
            $(this).html('<i class="fas fa-circle-notch fa-spin"></i>');
            $('#f_search').submit();
        });

        $('#Search_Violation').on('change', function(){
            var ViolationTypeId = $(this).val();

            $.ajax({
                url: 'ajax/ajx_get_detectorByViolationTypeId.php',
                type: 'POST',
                dataType: 'json',
                data: {ViolationTypeId:ViolationTypeId},
                success: function (data) {
                	$('#Search_Detector').html(data.Options);
                },
                error: function (result) {
                    console.log(result);
                    alert("error: " + result.responseText);
                }
            });
        });
        
        //Sblocca le input delle date di notifica in caso vengano impostati i filtri "Tipo" (Verbali/Solleciti emessi) e "Notifica" (Notificati)
        $('.fineTypeFilter, .notificationFilter').on('change',controllaBloccoInputDateNotifica);
        
        //Controlla se è impostata la combinazione dei filtri corretta e, nel caso, blocca/sblocca il filtro sulle date di notifica
        function controllaBloccoInputDateNotifica(){
        	if($('.fineTypeFilter').val() == 2 || $('.fineTypeFilter').val() == 0){
        		if($('.notificationFilter').val() == 1 || $('.notificationFilter').val() == 4){
        			$('.notificationDateFilter').removeAttr('disabled');
        			}
    			else{
    				$('.notificationDateFilter').attr('disabled',true);
    				$('.notificationDateFilter').val("");
    				}
        	}
        	else{
    			$('.notificationDateFilter').attr('disabled',true);
    			$('.notificationDateFilter').val("");
    			}	
        }

		//Controlla la selezione del filtro sul tipo di atto
        $("#TypeViolationSelect").on("change",function(){
            var numScelta = $("#TypeViolationSelect").val();
            
            //Controlla il filtro "ultimi emessi"
            if(numScelta != 3){								//Altri tipi
            	$("#TypeViolationDivSelect").attr("class","col-sm-2 BoxRowCaption");
            	$("#CurrentYearDiv").attr("class","col-sm-1 BoxRowCaption text-center");
            	$("#LastEmittedReminderDiv").css("display","none");
            	$("#LastEmittedReminderInput").prop("disabled",true);
            	$(".notificationFilter").prop("disabled",false);
            }
            else{											//Solleciti emessi
            	$("#TypeViolationDivSelect").attr("class","col-sm-1 BoxRowCaption");
            	$("#CurrentYearDiv").attr("class","col-sm-1 BoxRowCaption text-center");
            	$("#LastEmittedReminderDiv").css("display","");
            	$("#LastEmittedReminderInput").prop("disabled",false);
            	$(".notificationFilter").prop("disabled",true);
            	$(".notificationFilter").val(0);
            }
            
            //Controlla la spunta sul filtro degli avvisi bonari per pagamento agevolato
            if(numScelta == 1 || numScelta == 3) //Preavvisi e Solleciti emessi
            	{
            	$('#Search_HasKindFine').attr("disabled",true);
            	$('#Search_HasKindFine').attr("checked",false);
            	}
        	else{								 //Selezione nulla e Verbali
        		$('#Search_HasKindFine').attr("disabled",false);
        		}
            }).change();
    });
    
    //Disattiva la tendina dei pagamenti al click sulla checkbox degli esteri non pagati
    $('#Search_ForeignFineNotPayed').on("change",function(){
    	if(this.checked){
    		$('#Search_TypePayment').attr("disabled", true);
    		$('#Search_TypePayment').val("");
    		$('#Search_NationalityId').attr("disabled", true);
    		$('#Search_NationalityId').val("");
    		}
		else{
			$('#Search_TypePayment').attr("disabled", false);
			$('#Search_NationalityId').attr("disabled", false);
			}
    }).change();
    
    //Disattiva la checkbox degli esteri non pagati in caso venga selezionata la nazionalità italiana
    $('#Search_NationalityId').on("change", function(){
    	if(this.value == 0 || this.value == 2){
    		$('#Search_ForeignFineNotPayed').attr("disabled", false);
    		}
		else if(this.value == 1){
    		$('#Search_ForeignFineNotPayed').attr("disabled", true);
    		$('#Search_ForeignFineNotPayed').val("");
    		}
    }).change();
    
    //Condizioni per la selezione dei verbali archiviati
    $('.FineArchive').on("change", function(){
        if($('#FineArchive_0').is(':checked')) //Escludi o Includi
        	{
        	$('#Search_FromArchiveDate').attr("disabled", true);	//Abilito i campi data
    		$('#Search_FromArchiveDate').val("");
    		$('#Search_ToArchiveDate').attr("disabled", true);
    		$('#Search_ToArchiveDate').val("");
    		$('#TypeViolation_3').attr("disabled", false); //Abilito la selezione del tipo di violazione
        	$('.tipiArchiviati').css("display","none");
        	$('.sceltaRicorsi').attr("class","col-sm-4 BoxRowCaption sceltaRicorsi");
        	}
        else if($('#FineArchive_1').is(':checked'))
        	{
        	$('#Search_FromArchiveDate').attr("disabled", true);	//Abilito i campi data
    		$('#Search_FromArchiveDate').val("");
    		$('#Search_ToArchiveDate').attr("disabled", true);
    		$('#Search_ToArchiveDate').val("");
    		$('#TypeViolation_3').attr("disabled", false); //Abilito la selezione del tipo di violazione
        	$('.tipiArchiviati').css("display","");
        	$('.sceltaRicorsi').attr("class","col-sm-2 BoxRowCaption sceltaRicorsi");
        	}
    	else if($('#FineArchive_2').is(':checked'))	//Solo loro
    		{
    		$('#Search_FromArchiveDate').attr("disabled", false);	//Disabilito i campi data
    		$('#Search_ToArchiveDate').attr("disabled", false);
    		$('#TypeViolation_3').attr("disabled", true);		//Disabilito la selezione del tipo di violazione
    		if($('#TypeViolation_3').is(':checked')){			//Se fosse selezionato "Solleciti emessi"
    			$('#TypeViolationSelect').val("").change();		//Imposto la selezione nulla
    			}				
    		$('.tipiArchiviati').css("display","");
    		$('.sceltaRicorsi').attr("class","col-sm-2 BoxRowCaption sceltaRicorsi");
    		}
	}).change();
    
</script>
<?php
include(INC."/footer.php");
