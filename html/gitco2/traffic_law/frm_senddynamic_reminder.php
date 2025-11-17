<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$PrinterId = CheckValue('PrinterId','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinterReminder'] : $r_Customer['ForeignPrinterReminder']) : CheckValue('PrinterId','n');
$PageTitle   = "?PageTitle=".CheckValue('PageTitle','s');
$Search_Status = CheckValue("Search_Status", "s");
$Search_Violation = CheckValue("Search_Violation", "s");
$Search_FromFineDate = CheckValue("Search_FromFineDate", "s");
$Search_ToFineDate = CheckValue("Search_ToFineDate", "s");
$Search_FromNotificationDate = CheckValue("Search_FromNotificationDate", "s");
$Search_ToNotificationDate = CheckValue("Search_ToNotificationDate", "s");
$Search_FromProtocolId = CheckValue("Search_FromProtocolId", "s");
$Search_ToProtocolId = CheckValue("Search_ToProtocolId", "s");
$Search_FromReminderId = CheckValue("Search_FromReminderId", "s");
$Search_ToReminderId = CheckValue("Search_ToReminderId", "s");
$Search_ElaborationDate = CheckValue("Search_ElaborationDate", "s");
$Search_FromAddressChangeDate = CheckValue("Search_FromAddressChangeDate", "s") ?? "";
$Search_ToAddressChangeDate = CheckValue("Search_ToAddressChangeDate", "s") ?? "";
$CheckOnlyLast = CheckValue("CheckOnlyLast", "s") != "" ? CheckValue("CheckOnlyLast", "s") : 1;
$CheckDispute = CheckValue("CheckDispute", "n");
$OperationFilter = CheckValue("OperationFilter", "s");
$PrintType = CheckValue("PrintType", "s") != "" ? CheckValue("PrintType", "s") : 1;
$PrintDestinationFold  = CheckValue('PrintDestinationFold','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinterReminder'] : $r_Customer['ForeignPrinterReminder']) : CheckValue('PrintDestinationFold','n');
$CreationDate       = CheckValue('CreationDate','s');
$ProcessingDate     = CheckValue('ProcessingDate','s');
$ControllerId     = CheckValue('ControllerId','s');

$FirstSearch = CheckValue("FirstSearch", "s");

//Controlla la data, se è il 29/02, controlla se l'anno è bisestile. In caso negativa la trasforma in 28/02
aggiustaBisestile($Search_FromFineDate);
aggiustaBisestile($Search_ToFineDate);
aggiustaBisestile($Search_FromNotificationDate);
aggiustaBisestile($Search_ToNotificationDate);

$str_WhereControllers = "CityId='".$_SESSION['cityid']."' AND ('".date('Y-m-d')."' >= FromDate OR FromDate IS NULL) AND ('".date('Y-m-d')."' <= ToDate OR ToDate IS NULL) AND Disabled=0 AND ChiefController=1";

$str_WhereCountry = '';
if ($s_TypePlate == "F"){
    $CountryBankId = CheckValue('CountryBankId','s');
    
    $str_Sql = "SELECT CB.CountryId CountryId, C.Title
        FROM CountryBank CB JOIN Country C ON CB.CountryId=C.Id
        WHERE CB.CityId='". $_SESSION['cityid'] ."' ORDER BY C.Title
    ";
    
    $str_SelectCountryBank = CreateSelectQuery($str_Sql,"CountryBankId","CountryId","Title",$CountryBankId,false);
    $str_SelectCountryBank = str_replace('name="CountryBankId"','class= "form-control" name="CountryBankId"',$str_SelectCountryBank);
    
    
    if($CountryBankId!=''){
        $str_WhereCountry .= " AND COALESCE(T.CountryId,F.CountryId) = '". $CountryBankId ."'";
    } else {
        $rs_CountryBank = $rs->SelectQuery($str_Sql);
        if(mysqli_num_rows($rs_CountryBank)>0){
            while($r_CountryBank= mysqli_fetch_array($rs_CountryBank)){
                $str_WhereCountry .= " AND COALESCE(T.CountryId,F.CountryId) != '". $r_CountryBank['CountryId'] ."'";
            }
            
        }
    }
    
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['ForeignPrinterReminder'] > 0 ? "Id IN(1,7,{$r_Customer['ForeignPrinterReminder']})" : "Id IN(1,7)";
} else {
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['NationalPrinterReminder'] > 0 ? "Id IN(1,7,{$r_Customer['NationalPrinterReminder']})" : "Id IN(1,7)";

    $str_SelectCountryBank = "<select class='form-control' name='CountryBankId' id='CountryBankId'><option></option></select>";
}

$str_out .= '
<script>
SARIDA.mostraCaricamento("Caricamento in corso...");
</script>';

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                    <ul style="list-style-position: inside;">
                        <li>
                            La lista presente in questa pagina riguarda quei verbali che hanno subito elaborazione e poi stampa definitiva.
                        </li>
                    </ul>
            	</li>
                <li>
                    Legenda degli stati di elaborazione definitiva.
                    <ul style="list-style-position: inside;">
                        <li>
                            <b>Omessi:</b> Verbali senza alcun pagamento. Il sollecito viene creato.
                        </li>
                        <li>
                            <b>Parziali:</b> Verbali con almeno un pagamento la cui somma non colma l\'intero dovuto. E\' composto anche di tardivi, cioè di pagamenti completi effettuati dopo lo scadere del relativo scaglione. Il sollecito viene creato.
                        </li>
                        <li>
                            <b>Sospesi:</b> Verbali attivi che sono in attesa di un iter che li sblocchi (Es.: ricorso in corso). Vengono saltati dall\'elaborazione.
                        </li>
                        <li>
                            <b>Chiusi:</b> Verbali i cui pagamenti coprono per intero somma dovuta. Vengono contrassegnati come pagati ed escono dal giro dei solleciti.
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="col-sm-11">
            <input type="hidden" name="FirstSearch" id="FirstSearch" value="'.$FirstSearch.'">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Numero record</div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(5,10,20,50,100,200), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,"") .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Stato pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="Search_Status" id="Search_Status">
                    <option></option>
                    <option value="27" ' .$a_Search_Status[27] .'>Non pagato</option>
                    <option value="28" ' .$a_Search_Status[28] .'>Pagato parziale</option>
                </select>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                C/C Paese
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. $str_SelectCountryBank .'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Da data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromNotificationDate.'" name="Search_FromNotificationDate" id="Search_FromNotificationDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToNotificationDate.'" name="Search_ToNotificationDate" id="Search_ToNotificationDate">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Da Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="'.$Search_FromProtocolId.'" name="Search_FromProtocolId" id="Search_FromProtocolId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="'.$Search_ToProtocolId.'" name="Search_ToProtocolId" id="Search_ToProtocolId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Da sollecito
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="'.$Search_FromReminderId.'" name="Search_FromReminderId" id="Search_FromReminderId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A sollecito
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="'.$Search_ToReminderId.'" name="Search_ToReminderId" id="Search_ToReminderId">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Data elaborazione solleciti
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" id="Search_ElaborationDate" name="Search_ElaborationDate">
                    <option></option>';
                $str_Sql = "SELECT DISTINCT DATE(F.ProcessingPaymentDateTime) ProcessingPaymentDateTime, FR.Documentation Documentation, F.Id FROM Fine F JOIN FineReminder FR ON F.Id = FR.FineId WHERE F.ProcessingPaymentDateTime IS NOT NULL AND F.ReminderDate IS NOT NULL AND F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year']." AND FR.Documentation IS NOT NULL ORDER BY DATE(F.ProcessingPaymentDateTime) DESC";
                $rs_ProcessingDate = $rs->SelectQuery($str_Sql);
                if (mysqli_num_rows($rs_ProcessingDate) > 0){
                    while($r_ProcessingDate= mysqli_fetch_array($rs_ProcessingDate)){
                        $dateOut = DateOutDB($r_ProcessingDate['ProcessingPaymentDateTime']);
                        $str_out.='<option'.($dateOut == $Search_ElaborationDate ? " selected" : "").' value="'.$dateOut.'">'.$dateOut.'</option>';
                    }
                }
$str_out.='
                </select>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Solo ultimi creati
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" id="CheckOnlyLast" name="CheckOnlyLast"'.($CheckOnlyLast == 1 ? "checked" : "").'/>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Presenza ricorso
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input value="1" type="checkbox" id="CheckDispute" name="CheckDispute"'.($CheckDispute == 1 ? "checked" : "").'/>
            </div>
            <div class="col-sm-3 BoxRowCaption"></div>

            <div class="clean_row HSpace4"></div>';


    $str_out.='
            <div class="col-sm-12" id="residencyDateIntervalBlock" style="padding:0px">
                <div class="col-sm-2 BoxRowLabel">
                    <div class="col-sm-8 BoxRowLabel" style="text-align:left;padding-left:0px;">
                        Data ultimo cambio residenza
                    </div>
                    <div class="col-sm-4 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                        Da
                    </div>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" name="Search_FromAddressChangeDate" id="Search_FromAddressChangeDate" type="text" value="'.$Search_FromAddressChangeDate.'">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                    A
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" name="Search_ToAddressChangeDate" id="Search_ToAddressChangeDate" type="text" value="'.$Search_ToAddressChangeDate.'">
                </div>
                <div class="col-sm-7 BoxRowCaption"></div>
    
                <div class="clean_row HSpace4"></div>
            </div>
';

$str_out.=' 
            <div class="col-sm-1 BoxRowLabel">
                Operazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="OperationFilter" value="create" id="radioCrea" style="position: initial;vertical-align: top;"'.($OperationFilter == "create" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Crea nuovi solleciti</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="OperationFilter" value="flow" id="radioInvia" style="position: initial;vertical-align: top;"'.($OperationFilter == "flow" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Invia flusso</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="OperationFilter" value="update" id="radioAggiorna" style="position: initial;vertical-align: top;"'.($OperationFilter == "update" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Aggiorna solleciti</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="OperationFilter" value="delete" id="radioElimina" style="position: initial;vertical-align: top;"'.($OperationFilter == "delete" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Elimina solleciti</label>
            </div>
            <div class="col-sm-3 BoxRowCaption"></div>

        </div>
        <div id="searchBox" class="col-sm-1 BoxRowFilterButton" style="height: 13.7rem;">
        	<button type="button" id="search" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';

if($OperationFilter != 'delete')
    $str_out .='        
        	<div class="row-fluid">
        	<form name="f_print" id="f_print" action="frm_senddynamic_reminder_exe.php'.$Parameters.'" method="post">
            	<div class="col-sm-12">
     	   	    	<div class="table_label_H col-sm-1">Seleziona <input type="checkbox" id="checkAll" checked /></div>
     	   	    	<div class="table_label_H col-sm-1">Id</div>
    				<div class="table_label_H col-sm-1">Cron</div>
    				<div class="table_label_H col-sm-1">Data sollecito</div>
    				<div class="table_label_H col-sm-2">Violazione</div>
    				<div class="table_label_H col-sm-1">Targa</div>
                    <div class="table_label_H col-sm-1">Data notifica</div>
    				<div class="table_label_H col-sm-1">Data</div>
    				<div class="table_label_H col-sm-1">Ora</div>
                    <div class="table_label_H col-sm-1">Stato pratica</div>
    				<div class="table_add_button col-sm-1">&nbsp;</div>
    				
    
    				<div class="clean_row HSpace4"></div>';
else
    $str_out .='
        	<div class="row-fluid">
        	<form name="f_print" id="f_print" action="frm_senddynamic_reminder_exe.php'.$Parameters.'" method="post">
            	<div class="col-sm-12">
     	   	    	<div class="table_label_H col-sm-1">Seleziona <input type="checkbox" id="checkAll" checked /></div>
     	   	    	<div class="table_label_H col-sm-1">Id</div>
    				<div class="table_label_H col-sm-1">Cron</div>
    				<div class="table_label_H col-sm-1">Data sollecito</div>
    				<div class="table_label_H col-sm-1">Violazione</div>
    				<div class="table_label_H col-sm-1">Targa</div>
                    <div class="table_label_H col-sm-1">Data notifica</div>
    				<div class="table_label_H col-sm-1">Data e Ora</div>
                    <div class="table_label_H col-sm-1">Data Stampa</div>
                    <div class="table_label_H col-sm-1">Data Flusso</div>
                    <div class="table_label_H col-sm-1">Stato pratica</div>
    				<div class="table_add_button col-sm-1">&nbsp;</div>
        	    
        	    
    				<div class="clean_row HSpace4"></div>';
    
if($s_TypePlate==""){
    $str_out.=
        '<div class="table_caption_H col-sm-12">
			Scegliere la nazionalità e l\'operazione che si vuole eseguire sui dati selezionati
		</div>';
} else {

    $str_where = "1=1 ";
    $str_where .= " AND (FR.Documentation IS NOT NULL AND FR.Documentation!='') ".($OperationFilter =="delete" ? "" : "AND ReminderDate IS NOT NULL")." AND ProtocolId>0 AND (StatusTypeId=27 OR StatusTypeId=28) AND F.CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
    $operationType = "";
    switch($OperationFilter):
        case "delete":
        case "update":
        case "flow": $operationType = 'AND FlowDate IS NULL'; break;
        case "create": $operationType = 'AND FlowDate IS NOT NULL'; break;
    endswitch;    
    
    $str_where .= " AND FR.Id IN (SELECT Id FROM FineReminder WHERE CityId='".$_SESSION['cityid']."'".$operationType.")";
    $str_where .= $str_WhereCountry;
    $strOrder = "ProtocolId";
    

    if ($s_TypePlate == "N") {
        $str_where .= " AND COALESCE(T.CountryId,F.CountryId) = 'Z000'";
        //$str_Where .= " AND CountryId='Z000'";
    } else {
        $str_where .= " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'";
        //$str_Where .= " AND CountryId!='Z000'";
    }
    if ($Search_Status != "") {
        $str_where .= " AND F.StatusTypeId=$Search_Status";
    }
    if ($Search_FromFineDate != "") {
        $str_where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($Search_FromNotificationDate != "") {
        $str_where .= " AND FN.NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
    }
    if ($Search_ToNotificationDate != "") {
        $str_where .= " AND FN.NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";
    }
    if ($Search_FromProtocolId != "") {
        $str_where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $str_where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
    }
    if ($Search_Violation != "") {
        $str_where .= " AND FA.ViolationTypeId=".$Search_Violation;
    }
    if ($Search_ToProtocolId != "") {
        $str_where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
    }
    if ($Search_FromReminderId != "") {
        $str_where .= " AND FR.Id>=".$Search_FromReminderId;
    }
    if ($Search_ToReminderId != "") {
        $str_where .= " AND FR.Id<=".$Search_ToReminderId;
    }
    if ($Search_ElaborationDate != "") {
        $str_where .= " AND F.ProcessingPaymentDateTime='".DateInDB($Search_ElaborationDate)."'";
    }
    if($OperationFilter == 'create')
        $CheckOnlyLast = 1;
    //La creazione deve sempre mostrare l'ultimo sollecito disponibile
    if ($CheckOnlyLast == 1)
        $str_checkJoin = "AND F.ReminderDate = FR.PrintDate";
    else
        $str_checkJoin = "";
    
    if ($CheckDispute != 0){
        $str_where .= " AND DisputeStatusId IN(".RICORSO_INAMMISSIBILE.",".RICORSO_RESPINTO.")";
    }
    
    //Colonne da selezionare nella query principale
    $str_SelectColumns = '
                FR.Id AS FineReminderId,
                FR.PrintDate,
                FR.FlowDate,
        
                F.Id,
                F.ProtocolId,
                F.ProtocolYear,
                F.FineDate,
                F.FineTime,
                F.VehiclePlate,
                F.VehicleTypeId,
                F.StatusTypeId,
                F.ProcessingPaymentDateTime,
                F.CountryId AS FineCountry,

                FA.ViolationTypeId,
                FN.NotificationDate,
                FD.DisputeStatusId,        

                T.Id AS TrespasserId,
                T.CompanyName,
                T.Surname,
                T.Name,
                T.CountryId AS TrespasserCountry,

                VT.Title ViolationTitle
                ';
    
    //Join per la data di cambio indirizzo
    $str_TrespasserHistoryJoin = '';
    
    if($Search_FromAddressChangeDate != '' || $Search_ToAddressChangeDate != ''){
        
        //Colonne da aggiungere al recupero dalla history
        $str_SelectColumns .= ", 
                               TH.UpdateDataSourceDate, 
                               TH.UpdateDataSourceTime
                              ";
        
        //Imposto le condizioni sulle date di modifica dati di residenza
        $str_FromAddress = $Search_FromAddressChangeDate != '' ? "AND TH.UpdateDataSourceDate >= '".DateInDB($Search_FromAddressChangeDate)."'" : "";
        $str_ToAddress = $Search_ToAddressChangeDate != '' ? "AND TH.UpdateDataSourceDate <= '".DateInDB($Search_ToAddressChangeDate)."'" : "";
        
        //Condizione della TrespasserHistory che prende i solleciti in base alla data di ultimo cambio dati di residenza
        $str_TrespasserHistoryJoin = " 
                               JOIN 
                                TrespasserHistory TH ON T.Id = TH.TrespasserId 
                                AND TH.Id = (SELECT MAX(Id) FROM TrespasserHistory AS TH2 WHERE TH2.TrespasserId = T.Id AND (TH2.Address != T.Address OR TH2.CountryId != T.CountryId OR TH2.City != T.City OR TH2.Province != T.Province OR TH2.StreetNumber != T.StreetNumber OR TH2.ZIP != T.ZIP OR TH2.Ladder != T.Ladder OR TH2.Indoor != T.Indoor OR TH2.Plan != T.Plan OR TH2.PEC != T.PEC)) 
                                 $str_FromAddress $str_ToAddress
                               ";
    }
    
    $strOrder = "ProtocolId";
    
    //Recupero solo l'ultimo sollecito esistente
    if($OperationFilter == "create")
        $str_where .= " AND FR.Id = (SELECT MAX(Id) FROM FineReminder FR2 WHERE FR2.FineId = F.Id)";
    
    //Per escludere gli inviti in AG
    $str_where .= " AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FineId FROM FineHistory WHERE NotificationTypeId = 30))) ";
        
    //$table_rows = $rs->Select('V_FineReminder', $str_Where, $strOrder);
    $table_rows = $rs->SelectQuery(
        "SELECT
                $str_SelectColumns
                FROM FineReminder FR
                JOIN Fine F ON FR.FineId = F.Id $str_checkJoin
                JOIN Trespasser T ON FR.TrespasserId = T.Id
                JOIN FineArticle FA ON F.Id = FA.FineId
                JOIN FineNotification FN ON FR.FineId = FN.FineId
                JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
                LEFT JOIN FineDispute FD ON F.Id = FD.FineId
                $str_TrespasserHistoryJoin
                WHERE $str_where ORDER BY $strOrder LIMIT $RecordLimit"
        );
    
    $RowNumber = mysqli_num_rows($table_rows);

    $str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';
    
    if ($RowNumber == 0) {
        $str_out .=
            '<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
    } else {
        $n_Row = 1;
        while ($table_row = mysqli_fetch_array($table_rows)) {
            
            $str_out .= '<div class="tableRow">';

            $str_out.= '
            <div class="col-sm-1" style="text-align:center;padding:0">
    			<div class="table_caption_button col-sm-6" style="text-align:center;">
                    <input type="checkbox" name="checkbox[]" value="'.($OperationFilter != "delete" ? $table_row['Id'] : $table_row['FineReminderId']).'" checked />
				</div>
    			<div class="table_caption_H col-sm-6" style="text-align:center;">
    				'. $n_Row++ .'
				</div>
			</div>';
            if($OperationFilter != 'delete'){
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['FineReminderId'] . '</div>';
    
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'] . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['PrintDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-2">' . $table_row['ViolationTitle'] . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] . '<i class="' . $aVehicleTypeId[$table_row['VehicleTypeId']] . '" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
                
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['NotificationDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) . '</div>';
    
                $Status = '';
                
                $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['Id']);
                if(mysqli_num_rows($rs_Row)>0){
                    $r_Row = mysqli_fetch_array($rs_Row);
                    $Status .= '<i id="'.$r_Row['Id'].'" data-toggle="tooltip" data-placement="top" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#DDD728" name="DDD728"></i></span>';
                }else $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
                
                $str_out .= '<div class="table_caption_H col-sm-1 text-center">' . $Status . '</div>';
    
                $str_out .= '<div class="table_caption_button col-sm-1">';
                $str_out .=  ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php?Id=' . $table_row['Id'] . '&ReminderPage=1"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>');
                $str_out .= '&nbsp;';
    //            $str_out .= ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_prn.php?Id=' . $table_row['Id'] . '&P=' . $FormPage . '"><span class="fa fa-print" id="' . $table_row['Id'] . '"></span></a>');
    //            $str_out .= '&nbsp;';
                $str_out .= '</div>
    			            <div class="clean_row HSpace4"></div>';
                
                $str_out .= '</div>';
            }
            else{
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['FineReminderId'] . '</div>';
                
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'] . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['PrintDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ViolationTitle'] . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] . '<i class="' . $aVehicleTypeId[$table_row['VehicleTypeId']] . '" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
                
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['NotificationDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .' - '. TimeOutDB($table_row['FineTime']) .'</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['PrintDate']) . '</div>';
                $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FlowDate']) . '</div>';
                
                $Status = '';
                
                $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['Id']);
                if(mysqli_num_rows($rs_Row)>0){
                    $r_Row = mysqli_fetch_array($rs_Row);
                    $Status .= '<i id="'.$r_Row['Id'].'" data-toggle="tooltip" data-placement="top" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#DDD728" name="DDD728"></i></span>';
                }else $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
                
                $str_out .= '<div class="table_caption_H col-sm-1 text-center">' . $Status . '</div>';
                
                $str_out .= '<div class="table_caption_button col-sm-1">';
                $str_out .=  ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php?Id=' . $table_row['Id'] . '&ReminderPage=1"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>');
                $str_out .= '&nbsp;';
                $str_out .= '</div>
    			            <div class="clean_row HSpace4"></div>';
                
                $str_out .= '</div>';
            }

        }
        
        $str_out .= '<div class="col-sm-12 BoxRowFilterButton" style="height:auto;padding:0;">';
        
        if ($OperationFilter == "flow"){
            $str_out .= '
                <div class="col-sm-3 BoxRowLabel">
                    Stampatore
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.CreateSelect('Printer', $str_WherePrinter, 'Id', 'PrinterId', 'Id', 'Name', $PrinterId, false, null, 'frm_field_required').'
                </div>';
        } else if($OperationFilter == "create" || $OperationFilter == "update"){
            $str_out .= '
                <div class="col-sm-3 BoxRowLabel">
                    Data creazione stampa
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="'.($CreationDate != '' ? $CreationDate : date('d/m/Y')).'">
                </div>
                        
                <div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel">
                    Data rielaborazione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" class="form-control frm_field_date frm_field_required" name="ProcessingDate" id="ProcessingDate" value="'.($ProcessingDate != '' ? $ProcessingDate : date('d/m/Y')).'">
                </div>
                        
                <div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel" style="height:7rem;">
                    Destinazione stampa
                </div>
                <div class="col-sm-3 BoxRowCaption" style="height:7rem;">
                    '.CreateSelect('Printer', $str_WherePrinter, 'Id', 'PrintDestinationFold', 'Id', 'Name', $PrintDestinationFold, false, null, 'frm_field_required').'
                </div>
    			<div class="table_caption_H col-sm-6 alert-warning pull-right" style="height:auto;min-height:7rem;line-height:1.5rem;">
                    <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>
                    <ul style="margin:0;padding-left:3.5rem;">
                        <li>In base a quanto scelto verranno incluse nelle stampe, se valorizzate, le informazioni sulla restituzione del piego come definite nelle configurazioni. Lo stampatore predefinito può essere scelto e modificato nella pagina Ente > Gestione Ente > Stampatori predefiniti. Le informazioni del piego possono essere inserite/modificate in Ente > Gestione Ente > Posta nella scheda dello stampatore interessato.</li>
                    </ul>
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-3 BoxRowLabel">
                    Firmatario
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WhereControllers ORDER BY CAST(Code AS UNSIGNED)","ControllerId","Id","Name",$ControllerId,false,"","frm_field_required") .'
                </div>
    			<div class="table_caption_H col-sm-6 alert-warning pull-right" style="line-height:1.5rem;">
                    <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>
                    <ul style="margin:0;padding-left:3.5rem;">
                        <li>NOTA: in caso di operazione "Aggiorna solleciti", il firmatario scelto verrà sovrascritto al posto di quello specificato al momento della prima stampa.</li>
                    </ul>
                </div>

                <div class="clean_row HSpace4"></div>
                    
                <div class="col-sm-3 BoxRowLabel">
                    Opzioni di stampa
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" name="PrintType" value="1" style="position: initial;vertical-align: top;"'.($PrintType == 1 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Entrambi</label>
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" name="PrintType" value="2" style="position: initial;vertical-align: top;"'.($PrintType == 2 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Solo bollettino</label>
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" name="PrintType" value="3" style="position: initial;vertical-align: top;"'.($PrintType == 3 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Solo documento</label>
                </div>
                <div class="col-sm-3 BoxRowCaption"></div>';
        }
                    
        $str_out .= '</div>';
                    
        if ($OperationFilter != "delete"){
            $strButtons = '
                <input type="submit" id="sub_Button" class="btn btn-success" style="width:16rem;" value="Anteprima" />
                <input type="checkbox" value="1" name="ultimate" id="ultimate" style="margin-left:5rem;"> Definitivo';
        } else {
            $strButtons = '<input type="submit" id="sub_Button" class="btn btn-danger" style="width:16rem;" value="Elimina" />';
        }
                    
        $str_out .= '
                <div class="clean_row HSpace4"></div>
                            
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div style="padding-top:2rem;">
                        <input type="hidden" name="Operation" value="'.$OperationFilter.'">
            	    	' . ChkButton($aUserButton, 'act', $strButtons) . '
        	    	</div>
                </div>';
    }
}
$str_out .= '</form>
	</div>';

echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {
		SARIDA.nascondiCaricamento();

        $(".glyphicon-search").hover(function(){
            if ($( "#TypePlate" ).val() != ""){
                $(this).css("color","#2684b1");
                $(this).css("cursor","pointer");
            }
        },function(){
        	if ($( "#TypePlate" ).val() != ""){
                $(this).css("color","#fff");
                $(this).css("cursor","");
        	}
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
        });

        $('#search').click(function(){
            var TypePlate = $( "#TypePlate" ).val();
            var RecordLimit = $('#RecordLimit').val();
            var CheckOnlyLast = $('#CheckOnlyLast').is(":checked") ? 1 : 0;
            var CheckDispute = $('#CheckDispute').is(":checked") ? 1 : 0;
            var OperationFilter = $("input[name='OperationFilter']:checked").val();
            var Search_Status = $( "#Search_Status" ).val();
            var Search_Violation = $( "#Search_Violation" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
            var Search_FromNotificationDate = $( "#Search_FromNotificationDate" ).val();
            var Search_ToNotificationDate = $( "#Search_ToNotificationDate" ).val();
            var Search_FromProtocolId = $( "#Search_FromProtocolId" ).val();
            var Search_ToProtocolId = $( "#Search_ToProtocolId" ).val();
            var Search_FromReminderId = $( "#Search_FromReminderId" ).val();
            var Search_ToReminderId = $( "#Search_ToReminderId" ).val();
            var Search_ElaborationDate = $( "#Search_ElaborationDate" ).val();
            var Search_FromAddressChangeDate = replaceUndefined($( "#Search_FromAddressChangeDate" ).val());
            var Search_ToAddressChangeDate = replaceUndefined($( "#Search_ToAddressChangeDate" ).val());
            if(TypePlate!="" && OperationFilter!=null){
            	SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
            	$(window.location).attr('href', "frm_senddynamic_reminder.php<?= $PageTitle ?>&TypePlate="+TypePlate+
            			"&Search_Status="+Search_Status+
            			"&RecordLimit="+RecordLimit+
            			"&Search_Violation="+Search_Violation+
                        "&Search_FromFineDate="+Search_FromFineDate+
                        "&Search_ToFineDate="+Search_ToFineDate+
                        "&Search_FromNotificationDate="+Search_FromNotificationDate+
                        "&Search_ToNotificationDate="+Search_ToNotificationDate+
                        "&Search_FromProtocolId="+Search_FromProtocolId+
                        "&Search_ToProtocolId="+Search_ToProtocolId+
                        "&Search_FromReminderId="+Search_FromReminderId+
                        "&Search_ToReminderId="+Search_ToReminderId+
                        "&Search_ElaborationDate="+Search_ElaborationDate+
                        "&Search_FromAddressChangeDate="+Search_FromAddressChangeDate+
                        "&Search_ToAddressChangeDate="+Search_ToAddressChangeDate+
                    	"&CheckOnlyLast="+CheckOnlyLast+
                    	"&CheckDispute="+CheckDispute+
                    	"&OperationFilter="+OperationFilter+
                    	"&FirstSearch="+1);
            }
        });

        $("input[name='OperationFilter']").change(function(){
            if($( "#FirstSearch" ).val() != ""){
                var TypePlate = $( "#TypePlate" ).val();
            	var RecordLimit = $('#RecordLimit').val();
                var CheckOnlyLast = $('#CheckOnlyLast').is(":checked") ? 1 : 0;
                var CheckDispute = $('#CheckDispute').is(":checked") ? 1 : 0;
                var OperationFilter = $("input[name='OperationFilter']:checked").val();
                var Search_Status = $( "#Search_Status" ).val();
                var Search_Violation = $( "#Search_Violation" ).val();
                var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
                var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
                var Search_FromNotificationDate = $( "#Search_FromNotificationDate" ).val();
                var Search_ToNotificationDate = $( "#Search_ToNotificationDate" ).val();
                var Search_FromProtocolId = $( "#Search_FromProtocolId" ).val();
                var Search_ToProtocolId = $( "#Search_ToProtocolId" ).val();
                var Search_FromReminderId = $( "#Search_FromReminderId" ).val();
                var Search_ToReminderId = $( "#Search_ToReminderId" ).val();
                var Search_ElaborationDate = $( "#Search_ElaborationDate" ).val();
                var Search_FromAddressChangeDate = replaceUndefined($( "#Search_FromAddressChangeDate" ).val());
            	var Search_ToAddressChangeDate = replaceUndefined($( "#Search_ToAddressChangeDate" ).val());
            	SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
            	$(window.location).attr('href', "frm_senddynamic_reminder.php<?= $PageTitle ?>&TypePlate="+TypePlate+
            			"&Search_Status="+Search_Status+
            			"&RecordLimit="+RecordLimit+
            			"&Search_Violation="+Search_Violation+
                        "&Search_FromFineDate="+Search_FromFineDate+
                        "&Search_ToFineDate="+Search_ToFineDate+
                        "&Search_FromNotificationDate="+Search_FromNotificationDate+
                        "&Search_ToNotificationDate="+Search_ToNotificationDate+
                        "&Search_FromProtocolId="+Search_FromProtocolId+
                        "&Search_ToProtocolId="+Search_ToProtocolId+
                        "&Search_FromReminderId="+Search_FromReminderId+
                        "&Search_ToReminderId="+Search_ToReminderId+
                        "&Search_ElaborationDate="+Search_ElaborationDate+
                        "&Search_FromAddressChangeDate="+Search_FromAddressChangeDate+
                        "&Search_ToAddressChangeDate="+Search_ToAddressChangeDate+
                    	"&CheckOnlyLast="+CheckOnlyLast+
                    	"&CheckDispute="+CheckDispute+
                    	"&OperationFilter="+OperationFilter+
                    	"&FirstSearch="+1);
            }
        });


        $('#ultimate').click(function(){
            if($('#ultimate').is(":checked")) {
                $('#sub_Button').val('Definitiva');
                $('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#sub_Button').val('Anteprima');
                $('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
            }
        });

//         $('#f_print').submit(function(e) {
//             e.preventDefault();
//             console.log("submit");
//         });


        $('#f_print').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(){
            var value = $("#sub_Button").val();
            if (value == "Elimina"){
        		if(confirm("Si stanno per eliminare i solleciti selezionati. Continuare?")){
        			if(confirm("Si stà per eseguire l'operazione in maniera definitiva. Continuare?")){
        				SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
            			$('#sub_Button').hide();
            			$('#Progress').show();
            			$('#ultimate').hide();
        			} else return false;
        		} else return false;
            } else if (value == "Definitiva"){
            	if($('#ultimate').is(":checked")) {
            		if(confirm("Si stà per eseguire l'operazione in maniera definitiva. Continuare?")){
            			SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
            			$('#sub_Button').hide();
            			$('#Progress').show();
            			$('#ultimate').hide();
            		} else return false;
            	}
            } else SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
        });
		
		//Controlla la visibilità della parte delle date di cambio residenza in base al menu selezionato, in fase di caricamento
		if($("#radioCrea").is(":checked")){
			//Disabilita la scelta del filtro "solo ultimi emessi" per la creazione di nuovi solleciti perchè devono essere visibili obbligatoriamente solo gli ultimi emessi
			$("#CheckOnlyLast").attr("disabled",true);
			$("#residencyDateIntervalBlock").show();
			//Modifica l'altezza del box di ricerca perchè con i flussi viene aggiunto il filtro sulle date di variazione anagrafica
			$("#searchBox").css("height","13.7rem");
			}
		if($("#radioInvia").is(":checked")){
			$("#residencyDateIntervalBlock").show();
			$("#searchBox").css("height","13.7rem");
			}
		if($("#radioAggiorna").is(":checked")){
			$("#residencyDateIntervalBlock").hide();
			$("#searchBox").css("height","11.4rem");
			}
		if($("#radioElimina").is(":checked")){
			$("#residencyDateIntervalBlock").hide();
			$("#searchBox").css("height","11.4rem");
			};
		
			
		//Controlla la visibilità delle input di inserimento data cambio residenza in base al click sul sotto menu
		$("#radioCrea").on("click",function(){$("#CheckOnlyLast").attr("disabled",true);$("#searchBox").css("height","13.7rem");$("#residencyDateIntervalBlock").show();})
		$("#radioInvia").on("click",function(){$("#searchBox").css("height","13.7rem");$("#residencyDateIntervalBlock").show();})
		$("#radioAggiorna").on("click",function(){$("#searchBox").css("height","11.4rem");$("#residencyDateIntervalBlock").hide();$("#Search_FromAddressChangeDate").val("");$("#Search_ToAddressChangeDate").val("");})
		$("#radioElimina").on("click",function(){$("#searchBox").css("height","11.4rem");$("#residencyDateIntervalBlock").hide();$("#Search_FromAddressChangeDate").val("");$("#Search_ToAddressChangeDate").val("");})
			
	});
	
	function replaceUndefined(item){
		if(typeof item == 'undefined')
			return '';
		else
			return item;
	}
</script>
<?php
include(INC."/footer.php");
