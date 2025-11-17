<?php
include_once("_path.php");
include_once(INC."/parameter.php");
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

$ReminderOperation = isset($ReminderOperation) ? $ReminderOperation : REMINDER_CREATE_DOC;

$Search_Status = CheckValue("Search_Status", "s");
$Search_FromFineDate = CheckValue("Search_FromFineDate", "s");
$Search_ToFineDate = CheckValue("Search_ToFineDate", "s");
$Search_FromNotificationDate = CheckValue("Search_FromNotificationDate", "s");
$Search_ToNotificationDate = CheckValue("Search_ToNotificationDate", "s");
$Search_FromProtocolId = CheckValue("Search_FromProtocolId", "s");
$Search_ToProtocolId = CheckValue("Search_ToProtocolId", "s");
$Search_FromReminderId = CheckValue("Search_FromReminderId", "s");
$Search_ToReminderId = CheckValue("Search_ToReminderId", "s");
$Search_ElaborationDate = CheckValue("Search_ElaborationDate", "s");
$CheckDispute = CheckValue("CheckDispute", "n");
$PrintOrderBy = CheckValue("PrintOrderBy", "s") != "" ? CheckValue("PrintOrderBy", "s") : 1;
$PrintType = CheckValue("PrintType", "s") != "" ? CheckValue("PrintType", "s") : 1;
$PrintDestinationFold  = CheckValue('PrintDestinationFold','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinterReminder'] : $r_Customer['ForeignPrinterReminder']) : CheckValue('PrintDestinationFold','n');
$ControllerId     = CheckValue('ControllerId','s');

$CurrentDate = date('d/m/Y');

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
    $str_SelectCountryBank = "<select class='form-control' name='CountryBankId' id='CountryBankId'><option></option></select>";
    
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['NationalPrinterReminder'] > 0 ? "Id IN(1,7,{$r_Customer['NationalPrinterReminder']})" : "Id IN(1,7)";
}

$str_out .= '
<script>
SARIDA.mostraCaricamento("Caricamento in corso...");
</script>';

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12" >
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="TypePlate" class="form-control" id="TypePlate">
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
            <div class="col-sm-2 BoxRowLabel">
                Presenza ricorso
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input value="1" type="checkbox" id="CheckDispute" name="CheckDispute"'.($CheckDispute == 1 ? "checked" : "").'/>
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
                $str_Sql = "SELECT DISTINCT DATE(ProcessingPaymentDateTime) ProcessingPaymentDateTime FROM Fine WHERE ProcessingPaymentDateTime IS NOT NULL AND ReminderDate IS NOT NULL AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." ORDER BY DATE(ProcessingPaymentDateTime) DESC";
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
                Ordina stampa per
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="1" style="position: initial;vertical-align: top;"'.($PrintOrderBy == 1 ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Cronologico</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="2" style="position: initial;vertical-align: top;"'.($PrintOrderBy == 2 ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Data verbale</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="3" style="position: initial;vertical-align: top;"'.($PrintOrderBy == 3 ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Data di notifica</label>
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="4" style="position: initial;vertical-align: top;"'.($PrintOrderBy == 4 ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Trasgressore</label>
            </div>

        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height: 9.1rem;">
        	<button type="button" id="search" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
    </div>    	
</div>
<div class="clean_row HSpace4"></div>
';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_createdynamic_reminder_exe.php'.$Parameters.'" method="post">
        	<input type="hidden" name="Operation" value="'.$ReminderOperation.'">
            <div class="col-sm-12">
                <div class="table_label_H col-sm-1">Seleziona <input type="checkbox" id="checkAll" checked /></div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-2">Riferimento</div>
                <div class="table_label_H col-sm-1">Data notifica</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Nazione</div>
                <div class="table_label_H col-sm-1">Stato pagamento</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';
if($s_TypePlate==""){
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Scegliere nazionalità
		</div>';
} else {


    $str_where = "1=1";
    $str_where .= " AND FR.Documentation IS NULL AND F.ReminderDate IS NOT NULL AND F.ProtocolId>0 AND (F.StatusTypeId=27 OR F.StatusTypeId=28) AND F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year'];

    $str_where .= $str_WhereCountry;

    if($str_WhereCountry == ""){
        if ($s_TypePlate == "N") {
            $str_where .= " AND COALESCE(T.CountryId,F.CountryId) = 'Z000'";
        } else {
            $str_where .= " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'";
        }
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
    if ($Search_FromReminderId != "") {
        $str_where .= " AND FR.Id>=".$Search_FromReminderId;
    }
    if ($Search_ToReminderId != "") {
        $str_where .= " AND FR.Id<=".$Search_ToReminderId;
    }
    if ($Search_ElaborationDate != "") {
        $str_where .= " AND F.ProcessingPaymentDateTime='".DateInDB($Search_ElaborationDate)."'";
    }
        
    if ($CheckDispute != 0){
        $str_where .= " AND DisputeStatusId IN(".RICORSO_INAMMISSIBILE.",".RICORSO_RESPINTO.")";
    }
    
    if ($PrintOrderBy != ""){
        switch ($PrintOrderBy){
            case 1:
                $strOrder = "F.ProtocolId ASC";
                break;
            case 2:
                $strOrder = "F.FineDate ASC";
                break;
            case 3:
                $strOrder = "FN.NotificationDate ASC";
                break;
            case 4:
                $strOrder = "COALESCE(NULLIF(T.CompanyName, ''), T.Surname) ASC";
                break;
            default:
                $strOrder = "F.ProtocolId ASC";
        }
    }
	
    $table_rows = $rs->SelectQuery(
        "SELECT
            FR.Id AS FineReminderId,
            FR.PrintDate,
            FR.Documentation,
        
            F.Id,
            F.Code,
            F.ControllerId,
            F.ProtocolId,
            F.ProtocolYear,
            F.FineDate,
            F.FineTime,
            F.VehiclePlate,
            F.VehicleCountry,
            F.VehicleTypeId,
            F.StatusTypeId,
            F.ProcessingPaymentDateTime,
            F.CountryId AS FineCountry,
        
            FN.NotificationDate,
            FD.DisputeStatusId,
        
            T.CompanyName,
            T.Surname,
            T.Name,
            T.CountryId AS TrespasserCountry
        
            FROM FineReminder FR
            JOIN Fine F ON FR.FineId = F.Id AND F.ReminderDate = FR.PrintDate
            JOIN FineNotification FN ON FR.FineId = FN.FineId
            JOIN Trespasser T ON FR.TrespasserId = T.Id
            LEFT JOIN FineDispute FD ON F.Id = FD.FineId
            WHERE $str_where ORDER BY $strOrder LIMIT $RecordLimit"
        );
    
	$RowNumber = mysqli_num_rows($table_rows);
    $n_Row = 1;
	$str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			    Nessun record presente
		    </div>';
	} else {
		while ($table_row = mysqli_fetch_array($table_rows)) {
            $str_CssController = "";

            if($table_row['ControllerId']=="") $str_CssController = 'background-color:rgba(107,155,29,0.76);';
            
            $str_out .= '<div class="tableRow">';
			$str_out.= '
            <div class="col-sm-1" style="text-align:center;padding:0">
    			<div class="table_caption_button col-sm-6" style="text-align:center;">
                    <input type="checkbox" name="checkbox[]" value="' . $table_row['FineReminderId'] . '" checked />
				</div>
    			<div class="table_caption_H col-sm-6" style="text-align:center;'.$str_CssController.'">
    				'. $n_Row++ .'
				</div>
			</div>
			<div class="table_caption_H col-sm-1">' . $table_row['FineReminderId'] .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] .'</div>
			<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>
            <div class="table_caption_H col-sm-1">' . DateOutDB($table_row['NotificationDate']) .'</div>
			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>';

			$str_out.= '
			<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
			<div class="table_caption_H col-sm-1">' . $table_row['VehicleCountry'] .'</div>';

			$Status = '';
			
            $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['Id']);
            if(mysqli_num_rows($rs_Row)>0){
                $r_Row = mysqli_fetch_array($rs_Row);
                $Status .= '<i id="'.$r_Row['Id'].'" data-toggle="tooltip" data-placement="top" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#DDD728" name="DDD728"></i></span>';
            }else $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
            
            $str_out .= '<div class="table_caption_H col-sm-1 text-center">' . $Status . '</div>';
	
            $str_out.= '
			<div class="table_caption_button col-sm-1">
			    '. ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php?Id=' . $table_row['Id'] . '&ReminderPage=1"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>').'
			</div>
			<div class="clean_row HSpace4"></div>';
			
			$str_out .= '</div>';

		}

        $str_out.='
            <div class="col-sm-12 BoxRowFilterButton" style="height:auto;padding:0;">
                <div class="col-sm-3 BoxRowLabel">
                    Data creazione stampa
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="'.$CurrentDate.'">
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

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-3 BoxRowLabel">
                    Opzioni di stampa
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="radio" name="PrintType" value="1" style="position: initial;vertical-align: top;"'.($PrintType == 1 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Entrambi</label>
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="radio" name="PrintType" value="2" style="position: initial;vertical-align: top;"'.($PrintType == 2 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Solo bollettino</label>
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="radio" name="PrintType" value="3" style="position: initial;vertical-align: top;"'.($PrintType == 3 ? ' checked=""' : '').'>
                    <label style="line-height:2;vertical-align: top;"> Solo documento</label>
                </div>
            </div>

            <div class="clean_row HSpace4"></div>';


        $strButtonsCreate =
            '
            <input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1.2rem;" value="Anteprima di stampa" />
    		<div><input type="checkbox" value=1 name="ultimate" id="ultimate"><span style="color:#fff;"> Definitiva</span></div>';


        $str_out.= '
		<div class="col-sm-12 table_label_H"  style="height:8rem;text-align:center;">
            '.ChkButton($aUserButton, 'act',$strButtonsCreate).'  
    		<img src="'.IMG.'/progress.gif" style="display: none;" id="Progress"/>
		</div>
		</form>
	</div>';



	}

}


echo $str_out;
?>

<script type="text/javascript">

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
}

	$(document).ready(function () {
		SARIDA.nascondiCaricamento();

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
            var CheckDispute = $('#CheckDispute').is(":checked") ? 1 : 0;
            var PrintOrderBy = $("input[name='PrintOrderBy']:checked").val();
            var CountryBankId = $( "#CountryBankId" ).val();
            var Search_Status = $( "#Search_Status" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
            var Search_FromNotificationDate = $( "#Search_FromNotificationDate" ).val();
            var Search_ToNotificationDate = $( "#Search_ToNotificationDate" ).val();
            var Search_FromProtocolId = $( "#Search_FromProtocolId" ).val();
            var Search_ToProtocolId = $( "#Search_ToProtocolId" ).val();
            var Search_FromReminderId = $( "#Search_FromReminderId" ).val();
            var Search_ToReminderId = $( "#Search_ToReminderId" ).val();
            var Search_ElaborationDate = $( "#Search_ElaborationDate" ).val();
        
            if(TypePlate!=""){
            	SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
            	$(window.location).attr('href', "?PageTitle="+$.urlParam('PageTitle')+
                    	"&TypePlate="+TypePlate+
                    	"&RecordLimit="+RecordLimit+
            			"&Search_Status="+Search_Status+
                        "&Search_FromFineDate="+Search_FromFineDate+
                        "&Search_ToFineDate="+Search_ToFineDate+
                        "&Search_FromNotificationDate="+Search_FromNotificationDate+
                        "&Search_ToNotificationDate="+Search_ToNotificationDate+
                        "&Search_FromProtocolId="+Search_FromProtocolId+
                        "&Search_ToProtocolId="+Search_ToProtocolId+
                        "&Search_FromReminderId="+Search_FromReminderId+
                        "&Search_ToReminderId="+Search_ToReminderId+
                        "&Search_ElaborationDate="+Search_ElaborationDate+
                        "&CheckDispute="+CheckDispute+
                        "&CountryBankId="+CountryBankId+
                    	"&PrintOrderBy="+PrintOrderBy);
            }
        });
        
        
        
        $('#ultimate').click(function(){
        	if($('#ultimate').is(":checked")) {
        		$('#sub_Button').val('Stampa defitiva');
        		$('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
        	}else{
        		$('#sub_Button').val('Anteprima di stampa');
        		$('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
        	}
        });
        
        
        
        $('#sub_Button').click(function() {
        	if($('#ultimate').is(":checked")) {
        		var c = confirm("Si stanno per creare i solleciti in maniera definitiva. Continuare?");
        		if(c){
        			SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
        			$('#sub_Button').hide();
        			$('#Progress').show();
        			$('#ultimate').hide();
        			$('#f_print').submit();
        		}
        		else return false;
        	}else{
        		SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
        		$('#f_print').submit();
        	}
        });
        
        
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
                },
        
        
                CreationDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},
        
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
        
                    }
        
                },

                ProcessingDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},
        
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
        
                    }
        
                },
            }
        });
	});
</script>
<?php
include(INC."/footer.php");
