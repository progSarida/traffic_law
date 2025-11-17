<?php
include ("_path.php");
include (INC . "/parameter.php");

include (CLS . "/cls_db.php");
require_once(CLS . "/cls_view.php");

include (INC . "/function.php");
include (INC . "/header.php");

require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$Search_Id = CheckValue('Search_Id', 's');
$PageTitle = CheckValue('PageTitle','s');

$a_StatusTypeId = array();
$a_StatusTypeId[34] = "#800080";
$a_StatusTypeId[35] = "#A94442";
$a_StatusTypeId[36] = "#23448E";
$a_StatusTypeId[37] = "#A94442";
$a_StatusTypeId[8] = "#928703";
$a_StatusTypeId[9] = "#3C763D";

$FineIcon = '';

$a_Euro = array();
$a_Euro[27] = "A94442"; // ROSSO
$a_Euro[28] = "FFF952"; // GIALLO
$a_Euro[30] = "3C763D"; // VERDE

$str_Union = CreateSelectCustomerUnion($Search_Locality);

$chh_FindFilter = trim($str_Where);

if ($chh_FindFilter == "1=1"){
    $Search_CurrentYear = 1;
}

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_Where .= " AND CityId='" . $_SESSION['cityid'] . "'";
$strOrder = "ProtocolYear DESC, ProtocolId DESC";

if ($Search_Id != '') {
    $str_Where .= " AND FineId =" . $Search_Id;
}
if ($Search_CurrentYear > 0) {
    $str_Where .= " AND ProtocolYear =" . $_SESSION['year'];
}
if ($Search_Type > 0) {
    $str_Where .= " AND FineTypeId = 4";
}
if ($Search_FromNotificationDate != '') {
    $str_Having .= " AND NotificationDate >= '" . DateInDB($Search_FromNotificationDate) . "'";
}
if ($Search_ToNotificationDate != '') {
    $str_Having .= " AND NotificationDate <= '" . DateInDB($Search_ToNotificationDate) . "'";
}
if ($Search_Iuv != '') {
    $str_Where .= " AND '$Search_Iuv' in (PagoPA1,PagoPA2)";
}
if ($Search_PrefectCommunication == 2) {
    $str_Where .= " AND PrefectCommSendDate IS NOT NULL";
} else if ($Search_PrefectCommunication == 1){
    //TODO trovare un modo di filtrare i verbali per i quali è possibile creare la comunicazione al prefetto
    //ovvero se negli articoli dei verbali è abilitata la sospsensione della patente
    //oppure se è abilitata la recidiva, la quale implica dover cercare se il trasgressore del verbale ha commesso la stessa violazione
    //in un altro verbale nel biennio
}

if ($Search_PaymentRate == 1)
    $PaymentRate_Checked = 'checked';
    else
        $PaymentRate_Checked = '';
        
        $str_Search_ViolationDisabled = "";
        if ($s_TypePlate != "F") {
            $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
        }
        if ($Search_HasKindSendDate > 0) {
            $str_Where .= " AND KindSendDate IS NOT NULL AND StatusTypeId != 34";
        }
        //cerchiamo gli avvisi bonari in base al fatto che abbiano il record di storico di creazione e non in base allo stato
        // in modo da poterli trovare anche quando tramutati in verbali
        if ($Search_IsKindFine > 0) {
            $str_Where .= " AND FineId IN (SELECT FH30.FineId FROM FineHistory FH30 where FH30.NotificationTypeId = 30)";
        }
        if ($Search_VehicleType > 0) {
            $str_Where .= " AND VehicleTypeId = $Search_VehicleType";
        }
        
        
        //Solo per elenco verbali
        //if($Search_TrespasserFullNameSearch!="")  $str_Having .= " AND (TrespasserFullName LIKE '%".addslashes($Search_TrespasserFullNameSearch)."%')";
        if($Search_StatusExtended>0){
            $a_Search_StatusExtended[$Search_StatusExtended] = " SELECTED ";
            if($Search_StatusExtended<2300) {
                $str_Where .= " AND StatusTypeId=".$Search_StatusExtended;
            } else {
                //<option value="2300" ' . $a_Search_StatusExtended[23] . '>Non notificato AG</option>
                //<option value="2302" ' . $a_Search_StatusExtended[23] . '>Non notificato MESSO</option>
                //<option value="2323" ' . $a_Search_StatusExtended[23] . '>Non notificato PEC</option>
                switch ($Search_StatusExtended) {
                    case 2323:
                        //cerco stato non notificato con resultId = 23 - Mancata notifica PEC
                        $str_Where .= " AND StatusTypeId=23 AND ResultId=23 ";
                        $str_Having .= " AND FineNotificationType not like '%2%'";
                        break;
                    case 2324:
                        //cerco stato 25 (notificato) con resultId = 22 (Notifica via pec) - Notificato PEC
                        $str_Where .= " AND StatusTypeId=25 AND ResultId=22 ";
                        break;
                    case 2302:
                        //cerco stato non notificato con tipo di notifica sul FineTrespasser con NotificationTypeId = 2 (via Messo)
                        $str_Where .= " AND StatusTypeId=23 AND ResultId<>23 ";
                        $str_Having .= " AND FineNotificationType like '%2%' ";
                        break;
                    case 2303:
                        //cerco stato notificato con tipo di notifica sul FineTrespasser con NotificationTypeId = 2 (via Messo)
                        $str_Where .= " AND StatusTypeId=25 AND ResultId=6 ";
                        $str_Having .= " AND FineNotificationType like '%2%' ";
                        break;
                    case 2304:
                        //cerco stato notificato con tipo di notifica sul FineTrespasser con NotificationTypeId = 2 (via Messo)
                        $str_Having .= " AND FineNotificationType like '%2%' ";
                        break;
                    case 2300:
                        //cerco stato non notificato con resultId <> 23 - Mancata notifica PEC e FineTrespasser con NotificationTypeId <> 2 (via AG)
                        $str_Where .= " AND StatusTypeId=23 AND ResultId<>23 ";
                        $str_Having .= " AND FineNotificationType not like '%2%' ";
                        break;
                    case 2301:
                        //cerco stato notificato con resultId 1,2,3,4,5 - Notifiche positive NotificationTypeId <> 2 (via AG) - Notificato AG
                        $str_Where .= " AND StatusTypeId=25 AND ResultId IN(1,2,3,4,5) ";
                        $str_Having .= " AND FineNotificationType not like '%2%' ";
                        break;
                    case 2333:
                        //cerco le righe che hanno avuto un mancato recapito più volte nelle versioni precedenti o collegate
                        // le versioni precedenti o collegate sono quelle che hanno lo stesso previousId della corrente con FineId diverso
                        // i mancati recapiti sono quelli che hanno un FineHistory di tipo 6 con resultId negativo
                        // quindi si fa una sotto query tra i Fine con FineId <> dal corrente e FineHistory 6 con resultId in un elenco di risultati negativi (o non positivi)
                        // e si mette che il verbale corrente ha PreviousId > 0 AND PreviousId in (sotto query)
                        $str_Where .= " AND F.PreviousId>0 AND F.ProtocolId IN(
                            	SELECT FMulti.ProtocolId FROM (
                            		SELECT F2.ProtocolId,COUNT(F2.Id)
                            		FROM Fine F2
                            		JOIN FineHistory FH6 ON F2.Id = FH6.FineId AND FH6.NotificationTypeId = 6
                            		WHERE F2.CityId='D711' AND F2.Code=Code AND F2.ProtocolId=ProtocolId AND ((FH6.ResultId > 9 and FH6.ResultId < 21) or FH6.ResultId=23)
                            		GROUP BY F2.ProtocolId HAVING COUNT(F2.Id) >= 2)
                            	AS FMulti)";
                        $str_Having .= ""; //perché non mettiamo condizioni sul tipo di notifica fatta
                        break;
                    case 3000:
                        // rendo i record che hanno almeno un pagamento associato per l'ente
                        $str_Where .= " AND F.Id in (SELECT FP.FineId FROM FinePayment FP where FP.CityId='" . $_SESSION['cityid'] . "' AND FP.FineId>0) ";
                        $str_Having .= "";
                        break;
                }
            }
        }
        
        $rs_Result = $rs->Select('Result', "1=1");
        while ($r_Result = mysqli_fetch_array($rs_Result)) {
            $a_Result[$r_Result['Id']] = $r_Result['Title'];
        }
        $a_GradeType = array(
            "",
            "I",
            "II",
            "III"
        );
        
        $a_DisputeStatusId = array(
            "",
            "#FFF952",
            "#3C763D",
            "#A94442"
        );
        
        $b_ShowInfo = $_SESSION['usertype'] >= 51 ? true : false;
        
        // Per resettare il paginatore alla prima pagina quando si effettua una nuova ricerca con un filtro
        $page = CheckValue("page", "n");
        if ($page != "") {
            $str_CurrentPage = str_replace("page=" . $page, "page=1", $str_CurrentPage);
        }
        
        $str_out .= '
            
    <div class="col-sm-12">
        <div id="DIV_SrcPayment" style="display:none; position:absolute;top:30%;left:20%; z-index: 900">
            <input type="hidden" name="FineId" id="FineId">
            <div class="col-sm-12">
                <div class="col-sm-12 table_label_H" style="text-align:center">
                    Ricerca pagamento
                </div>
                <span class="fa fa-times-circle close_window" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>
            </div>
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    Cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input name="Payment_Protocol" id="Payment_Protocol" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Ref
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Code" id="Payment_Code" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nome
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Name" id="Payment_Name" type="text" style="width:15rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Targa
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Plate" id="Payment_Plate" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="text-align: center">
                    <i class="fa fa-search-plus" style="margin-top:0.3rem;font-size:1.6rem;"></i>
                </div>
            
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:20rem;">
                    <div id="payment_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
                </div>
            </div>
        </div>
    </div>
            
<div class="row-fluid">
    <form id="f_Search" action="mgmt_fine.php" method="post">
    <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
    <div class="col-sm-12" >
        <div class="col-sm-11" style="border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ProtocolId" type="text" value="' . $Search_ProtocolId . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" value="' . $Search_Plate . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Prot/Ref
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="' . $Search_Ref . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . CreateSelect("ViolationType", "1=1", "Id", "Search_Violation", "Id", "Title", $Search_Violation, false) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo di veicolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . CreateSelect("VehicleType", "1=1", "Id", "Search_VehicleType", "Id", "TitleIta", $Search_VehicleType, false) . '
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"' . $s_SelPlateN . '>Nazionali</option>
                    <option value="F"' . $s_SelPlateF . '>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' AND F.CityId='{$_SESSION['cityid']}' ORDER BY C.Title", "Search_Country", "CountryId", "Title", $Search_Country, false) . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Stato pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <select class="form-control" name="Search_StatusExtended" id="Search_StatusExtended">
                    <option value="0"></option>
                    <option value="20" ' . ($a_Search_StatusExtended[20] ?? '') . '>In attesa di notifica</option>
                    <option value="2304" ' . ($a_Search_StatusExtended[2304] ?? '') . '>Inviato MESSO</option>
                    <option value="23" ' . ($a_Search_StatusExtended[23] ?? '') . '>Non notificato (tutti)</option>
                    <option value="2300" ' . ($a_Search_StatusExtended[2300] ?? '') . '>Non notificato AG</option>
                    <option value="2301" ' . ($a_Search_StatusExtended[2301] ?? '') . '>Notificato AG</option>
                    <option value="2302" ' . ($a_Search_StatusExtended[2302] ?? '') . '>Non notificato MESSO</option>
                    <option value="2323" ' . ($a_Search_StatusExtended[2323] ?? '') . '>Non notificato PEC</option>
                    <option value="2324" ' . ($a_Search_StatusExtended[2324] ?? '') . '>Notificato PEC</option>
                    <option value="2333" ' . ($a_Search_StatusExtended[2333] ?? '') . '>Non notificato più volte</option>
                    <option value="25" ' . ($a_Search_StatusExtended[25] ?? '') . '>Notificato</option>
                    <option value="2303" ' . ($a_Search_StatusExtended[2303] ?? '') . '>Notificato MESSO</option>
                    <option value="30" ' . ($a_Search_StatusExtended[30] ?? '') . '>Pagato (chiuso da elabora solleciti)</option>
                    <option value="3000" ' . ($a_Search_StatusExtended[3000] ?? '') . '>Pagato (ha almeno un pagamento)</option>
                    <option value="35" ' . ($a_Search_StatusExtended[35] ?? '') . '>Archiviato</option>
                    <option value="36" ' . ($a_Search_StatusExtended[36] ?? '') . '>Rinotificato</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_FromFineDate . '" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_ToFineDate . '" name="Search_ToFineDate">
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' .$RuleTypeTitle. '
            </div>
            <div class="col-sm-2 BoxRowLabel font_small">
                Comunicazione prefetto
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(1 => 'Non trasmesse', 2 => 'Trasmesse'), true, 'Search_PrefectCommunication', 'Search_PrefectCommunication', $Search_PrefectCommunication).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $str_Union . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data not.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_FromNotificationDate . '" name="Search_FromNotificationDate"">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data not.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_ToNotificationDate . '" name="Search_ToNotificationDate">
            </div>
                    
            <div class="clean_row HSpace4"></div>';
        
        if($b_ShowInfo)
            $str_out .='
            <div class="col-sm-1 BoxRowLabel">
                ID
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_Id" type="text" value="' . $Search_Id . '">
            </div>';
            
            $str_out .='
                
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_TrespasserFullNameSearch" type="text" value="' . $Search_TrespasserFullNameSearch . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Iuv
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Iuv" type="text" value="' . $Search_Iuv . '">
            </div>
            <div class="'.($b_ShowInfo ? 'col-sm-4 ' : 'col-sm-6 ').'BoxRowLabel">
            </div>
                
            <div class="clean_row HSpace4"></div>
                
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo avvisi bonari
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="1" id="Search_IsKindFine" name="Search_IsKindFine" ' . ChkCheckButton($Search_IsKindFine > 0 ? 1 : 0) . '>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo inviti in AG
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="4" id="Search_HasKindSendDate" name="Search_HasKindSendDate" ' . ChkCheckButton($Search_HasKindSendDate > 0 ? 1 : 0) . '>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo verbali contratto
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="4" id="Search_Type" name="Search_Type" ' . ChkCheckButton($Search_Type > 0 ? 1 : 0) . '>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Notifiche da validare
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" name="Search_ValidatedAddress" id="Search_ValidatedAddress" value="1" ' . ChkCheckButton($Search_ValidatedAddress > 0 ? 1 : 0) . '>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rateizzazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="hidden" name="Search_PaymentRate" value="0">
                <input type="checkbox" name="Search_PaymentRate" id="PaymentRate" value="1" ' . $PaymentRate_Checked . '>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            </div>
            <div class="col-sm-1 BoxRowCaption font_small">
                <input type="checkbox" value="1" name="Search_CurrentYear" ' . ChkCheckButton($Search_CurrentYear) . '>
                <span style="vertical-align: text-bottom;"> Anno corrente</span>
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="" class="tooltip-r btn btn-primary" id="search" style="margin-top:0;width:100%; height:11.4rem;" data-original-title="Cerca"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';
            
            $str_out .= '
    	<div class="row-fluid">
        	<div class="col-sm-12">
                '.($b_ShowInfo ? '<div class="table_label_H col-sm-1" style="width:7%;">ID</div>' : '').'
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Ref</div>
				<div class="table_label_H col-sm-2">Dati atto</div>
				<div class="table_label_H col-sm-2">Proprietario / Obbligato</div>
				<div class="table_label_H col-sm-2">Trasgressore</div>
				<div class="table_label_H col-sm-3"'.($b_ShowInfo ? ' style="width:18%"' : '').'>Stato pratica</div>
        			<div class="table_add_button col-sm-1 right">
				</div>
				<div class="clean_row HSpace4"></div>';
            
            if ($chh_FindFilter == "1=1") {
                $str_out .= '
        <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
        Inserire criteri ricerca
        </div>
        ';
            } else {
                $mgmtFine = new CLS_VIEW(MGMT_FINETRESPASSER);
                $limit = $pagelimit . ',' . PAGE_NUMBER;
                $query = $mgmtFine->generateSelect($str_Where, $str_Having);
                $RowNumber = mysqli_num_rows($rs->selectQuery($query));
                
                $query = $mgmtFine->generateSelect($str_Where, $str_Having, $strOrder, $limit);
                $rs_Fine = $rs->selectQuery($query);
                
                if ($RowNumber == 0) {
                    $str_out .= '
		<div class="table_caption_H col-sm-12" style="text-align: center">
		Nessun record presente
		</div>
		';
                }
                else {
                    while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                        
                        $a_TrespasserTypeId     = array();
                        $a_TrespasserId         = array();
                        $a_PEC                  = array();
                        $a_TrespasserFullName   = array();
                        $a_FineNotificationDate = array();
                        
                        // $ExternalProtocol = ($r_Fine['ExternalProtocol']>0)? $r_Fine['ExternalProtocol'].'/'.$r_Fine['ExternalYear'] : "";
                        $query = "select
			    f.Id AS FineId,
			    f.Code AS Code,
			    fh.FlowDate AS FlowDate,
			    fh.PrintDate AS PrintDate,
			    fh.SendDate AS SendDate,
			    fh.ResultId AS ResultId,
			    fh.DeliveryDate AS DeliveryDate,
			    fh.NotificationTypeId AS NotificationTypeId,
			    fp.Id AS PaymentId,
			    fp.PaymentDate AS PaymentDate,
			    fc.ReducedPoint AS ReducedPoint,
			    fc.ReducedDate AS ReducedDate,
			    fc.CommunicationDate AS CommunicationDate,
			    fd.Documentation AS Documentation,
			    fd.DocumentationTypeId,
			    d.Id AS DisputeId,
			    d.GradeTypeId AS GradeTypeId,
			    d.DateFile AS DateFile,
			    d.OfficeCity AS OfficeCity,
			    d.OfficeId AS OfficeId,
			    o.TitleIta AS OfficeTitle,
			    fdi.DisputeStatusId AS DisputeStatusId
			from
			    (((((((Fine f
			left join FineHistory fh on
			    ((f.Id = fh.FineId)))
			left join FinePayment fp on
			    ((fp.FineId = f.Id)))
			left join FineCommunication fc on
			    (((fc.FineId = f.Id)
			    and ((fc.TrespasserTypeId = 1)
			    or (fc.TrespasserTypeId = 3)))))
			left join FineDocumentation fd on
			    (((fd.FineId = f.Id)
			    and (fd.DocumentationTypeId = 2))))
			left join FineDispute fdi on
			    ((fh.FineId = fdi.FineId)))
			left join Dispute d on
			    ((fdi.DisputeId = d.Id)))
			left join Office o on
			    ((o.Id = d.OfficeId)))
			where
			    ((fh.NotificationTypeId = 6)
			    or (fh.NotificationTypeId = 30 and f.StatusTypeId in(8,9,30,32))
			    or (fh.NotificationTypeId = 1 and f.StatusTypeId in(13))
			    or isnull(fh.NotificationTypeId)) and f.Id = {$r_Fine['FineId']}";
                        
                        $rs_FineHistoryTrespasser = $rs->selectQuery($query);
                        $hasHistory = $r_FineHistoryTrespasser = mysqli_fetch_array($rs_FineHistoryTrespasser);
                        $a_paymentRate = $rs->getArrayLine($rs->Select('PaymentRate', "FineId=" . $r_Fine['FineId']));
                        $notificationTypeId = null;
                        $flowDate = null;
                        $sendDate = null;
                        $printDate = null;
                        $resultId = null;
                        $paymentDate = null;
                        $deliveryDate = null;
                        $paymentId = null;
                        $gradeTypeId = null;
                        $officeCity = null;
                        $officeTitle = null;
                        $reducedPoint = null;
                        $reducedDate = null;
                        $communicationDate = null;
                        $documentation = null;
                        $fhtfineId = null;
                        if ($hasHistory) {
                            if (key_exists('NotificationTypeId', $r_FineHistoryTrespasser))
                                $notificationTypeId = $r_FineHistoryTrespasser['NotificationTypeId'];
                                if (key_exists('FlowDate', $r_FineHistoryTrespasser))
                                    $flowDate = $r_FineHistoryTrespasser['FlowDate'];
                                    if (key_exists('SendDate', $r_FineHistoryTrespasser))
                                        $sendDate = $r_FineHistoryTrespasser['SendDate'];
                                        if (key_exists('PrintDate', $r_FineHistoryTrespasser))
                                            $printDate = $r_FineHistoryTrespasser['PrintDate'];
                                            if (key_exists('ResultId', $r_FineHistoryTrespasser))
                                                $resultId = $r_FineHistoryTrespasser['ResultId'];
                                                if (key_exists('PaymentDate', $r_FineHistoryTrespasser))
                                                    $paymentDate = $r_FineHistoryTrespasser['PaymentDate'];
                                                    if (key_exists('DeliveryDate', $r_FineHistoryTrespasser))
                                                        $deliveryDate = $r_FineHistoryTrespasser['DeliveryDate'];
                                                        if (key_exists('PaymentId', $r_FineHistoryTrespasser))
                                                            $paymentId = $r_FineHistoryTrespasser['PaymentId'];
                                                            if (key_exists('GradeTypeId', $r_FineHistoryTrespasser))
                                                                $gradeTypeId = $r_FineHistoryTrespasser['GradeTypeId'];
                                                                if (key_exists('OfficeTitle', $r_FineHistoryTrespasser))
                                                                    $officeTitle = $r_FineHistoryTrespasser['OfficeTitle'];
                                                                    
                                                                    if (key_exists('ReducedPoint', $r_FineHistoryTrespasser))
                                                                        $reducedPoint = $r_FineHistoryTrespasser['ReducedPoint'];
                                                                        if (key_exists('ReducedDate', $r_FineHistoryTrespasser))
                                                                            $reducedDate = $r_FineHistoryTrespasser['ReducedDate'];
                                                                            if (key_exists('CommunicationDate', $r_FineHistoryTrespasser))
                                                                                $communicationDate = $r_FineHistoryTrespasser['CommunicationDate'];
                                                                                if (key_exists('Documentation', $r_FineHistoryTrespasser))
                                                                                    $documentation = $r_FineHistoryTrespasser['Documentation'];
                                                                                    if (key_exists('FineId', $r_FineHistoryTrespasser))
                                                                                        $fhtfineId = $r_FineHistoryTrespasser['FineId'];
                                                                                        if (key_exists('OfficeCity', $r_FineHistoryTrespasser))
                                                                                            $officeCity = $r_FineHistoryTrespasser['OfficeCity'];
                        }
                        //***INIZIO ICONE PROTOCOL***
                        $str_PreviousId = "";
                        $str_Archive = "";
                        $str_ProtocolId = "";
                        $str_Kind = "";
                        $str_Injunction = "";
                        
                        if ($r_Fine['StatusTypeId'] == 35 || $r_Fine['StatusTypeId'] == 34 || $r_Fine['StatusTypeId'] == 37)
                        {
                            $rs_Archive = $rs->SelectQuery("
                	SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                	FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                	WHERE FA.FineId=" . $r_Fine['FineId']);
                            $r_Archive = mysqli_fetch_array($rs_Archive);
                        }
                        if ($r_Fine['PreviousId'] > 0)
                        {
                            $rs_Previous = $rs->Select('Fine', "Id=" . $r_Fine['PreviousId']);
                            $r_Previous = mysqli_fetch_array($rs_Previous);
                            
                            if($r_Previous['StatusTypeId'] == 34){
                                $str_PreviousId = '
                        <a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Previous['Id'] . '">
                            <span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale collegato Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'] . ' ' . $r_Archive['Note'] . '">
                                <i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i>
                            </span>
                        </a>';
                            } else {
                                $str_PreviousId = '
	                <a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Previous['Id'] . '">
	                    <span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale collegato Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'] . '">
	                        <i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i>
	                    </span>
	                </a>';
                            }
                        }
                        
                        //Controlla se il verbale ha generato 126bis e nel caso gli associa un'icona nella prima colonna
                        $query126 = "SELECT Id, Code, PreviousId FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND PreviousId = ".$r_Fine['FineId']." AND Code LIKE '".$r_Fine['FineId']."BIS/%'";
                        $rs_query126 = $rs->selectQuery($query126);
                        $r_query126 = mysqli_fetch_assoc($rs_query126);
                        if(mysqli_num_rows($rs_query126)>0)
                            $str_PreviousId .= '
                            <a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_query126['Id'] . '">
	    			<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Ha generato un 126bis con id '.$r_query126['Id'].'">
                    			<i class="fa fa-envelope" style="margin-top:0.4rem;font-size:1.3rem;"></i>
                		</span>
                	    </a>';
                            
                            if ($r_Fine['StatusTypeId'] == 35 || $r_Fine['StatusTypeId'] == 37)
                            {
                                $str_Archive = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale archiviato in data ' . DateOutDB($r_Archive['ArchiveDate']) . ' ' . $r_Archive['ReasonTitle'] . ' ' . $r_Archive['Note'] . '"><i class="fa fa-info-circle" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
                            }
                            else if ($r_Fine['StatusTypeId'] == 34)
                            {
                                $str_Archive = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Invito in AG chiuso in data '.DateOutDB($r_Archive['ArchiveDate']).'"><i class="fas fa-mail-bulk" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
                            }
                            else if ($r_Fine['StatusTypeId'] == 36)
                            {
                                //Cerco il verbale successivo a quello con la rinotifica
                                $rs_PreviousFwd = $rs->Select('Fine', " ProtocolYear in (" . $_SESSION['year']. ", ". ($_SESSION['year']+1). ", ". ($_SESSION['year']-1). ") AND CityId='" . $_SESSION['cityid'] . "'" . " AND Note=" . "'Violazione duplicata: ID {$r_Fine['FineId']}'");	//FIXME Soluzione temporanea, trovare un modo diretto per ricavare il verbale successivo (magari con una tabella in cui salvare la relazione dell'ID duplicato per non toccare quella del previousId che punta al primo della catena, tipo logical-Key di eport mentre qui serve il parent-referred di eport)
                                $r_PreviousFwd = mysqli_fetch_array($rs_PreviousFwd);
                                $str_PreviousId = '<a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_PreviousFwd['Id'] . '"><span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale noleggio ristampato con Cron ' . $r_PreviousFwd['ProtocolId'] . '/' . $r_PreviousFwd['ProtocolYear'] . ' (ID: '.$r_PreviousFwd['Id'].')"><i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i></span></a>';
                            }
                            else if ($r_Fine['StatusTypeId'] == 33)
                            {
                                $rs_PreviousFwd = $rs->Select('Fine', " ProtocolYear in (" . $_SESSION['year']. ", ". ($_SESSION['year']+1). ", ". ($_SESSION['year']-1). ") AND CityId='" . $_SESSION['cityid'] . "'" . " AND Note=" . "'Violazione duplicata: ID {$r_Fine['FineId']}'");
                                $r_PreviousFwd = mysqli_fetch_array($rs_PreviousFwd);
                                $str_ProtocolId = '<a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_PreviousFwd['Id'] . '"><span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale rinotificato (ID: '.$r_PreviousFwd['Id'].')"><i class="fa fa-exchange" style="margin-top:0.4rem;font-size:1.3rem;"></i></span></a>';
                            }
                            else if ($r_Fine['StatusTypeId'] == 8)
                            {
                                $str_Kind = '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario' . (! is_null($notificationTypeId) && ($notificationTypeId == 30) && ! is_null($flowDate) ? (' - creato in data ' . DateOutDB($flowDate)) : '') . '"><i class="fas fa-wallet" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
                            }
                            else if ($r_Fine['StatusTypeId'] == 9)
                            {
                                $str_Kind = '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario' . (! is_null($notificationTypeId) && ($notificationTypeId == 30) && ! is_null($sendDate) ? ' - inviato in data ' . DateOutDB($sendDate) : '') . '"><i class="fas fa-wallet" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
                            }
                            
                            $rs_FineInjunction = $rs->Select("FineInjunction","FineId =".$r_Fine['FineId']);
                            $r_FineInjunction = $rs->getArrayLine($rs_FineInjunction);
                            
                            if(mysqli_num_rows($rs_FineInjunction) > 0)
                            {
                                $str_Injunction = '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Ruolo creato in data '.DateOutDB($r_FineInjunction['RegDate']).'"> <i class="fa fa-fast-forward" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
                            }
                            
                            //***FINE ICONE PROTOCOL***
                            if (strpos($r_Fine['TrespasserId'], "|") === false) {
                                $a_TrespasserId[$r_Fine['TrespasserTypeId']] = $r_Fine['TrespasserId'];
                                $a_PEC[$r_Fine['TrespasserTypeId']]                   = $r_Fine['PEC'];
                                $a_TrespasserFullName[$r_Fine['TrespasserTypeId']] = $r_Fine['TrespasserFullName'];
                                $a_FineNotificationDate[$r_Fine['TrespasserTypeId']] = $r_Fine['FineNotificationDate'];
                            }
                            else
                            {
                                $a_Tmp_TrespasserTypeId = explode("|", $r_Fine['TrespasserTypeId']);
                                $a_Tmp_TrespasserId = explode("|", $r_Fine['TrespasserId']);
                                $a_Tmp_PEC                  = explode("|", $r_Fine['PEC']);
                                $a_Tmp_TrespasserFullName = explode("|", $r_Fine['TrespasserFullName']);
                                $a_Tmp_FineNotificationDate = explode("|", $r_Fine['FineNotificationDate']);
                                for ($i = 0; $i < count($a_Tmp_TrespasserId); $i ++) {
                                    $a_TrespasserId[$a_Tmp_TrespasserTypeId[$i]] = $a_Tmp_TrespasserId[$i];
                                    $a_PEC[$a_Tmp_TrespasserTypeId[$i]] = $a_Tmp_PEC[$i];
                                    $a_TrespasserFullName[$a_Tmp_TrespasserTypeId[$i]] = $a_Tmp_TrespasserFullName[$i];
                                    $a_FineNotificationDate[$a_Tmp_TrespasserTypeId[$i]] = $a_Tmp_FineNotificationDate[$i];
                                }
                            }
                            
                            $str_Trespasser1 = $str_Trespasser2 = '';
                            $str_NotificationDate1 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                            $str_PEC = '<i class="fas fa-at tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="top" title="PEC assente" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
                            if (isset($a_TrespasserId[1]) || isset($a_TrespasserId[2]) || isset($a_TrespasserId[10])) {
                                if (isset($a_TrespasserId[1])) {
                                    $str_Trespasser1 = $a_TrespasserFullName[1];
                                    $n_AssingedIndex = 1;
                                    $maintrespid = $a_TrespasserId[1];
                                } else if (isset($a_TrespasserId[2])) {
                                    $str_Trespasser1 = $a_TrespasserFullName[2];
                                    $n_AssingedIndex = 2;
                                    $maintrespid = $a_TrespasserId[2];
                                } else {
                                    $str_Trespasser1 = $a_TrespasserFullName[10];
                                    $n_AssingedIndex = 10;
                                    $maintrespid = $a_TrespasserId[10];
                                }
                                
                                if ($a_FineNotificationDate[$n_AssingedIndex] != "")
                                    $str_NotificationDate1 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data ' . DateOutDB($a_FineNotificationDate[$n_AssingedIndex]) . '"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>';
                                    
                                    if($a_PEC[$n_AssingedIndex]!="")
                                        $str_PEC = '<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_PEC[$n_AssingedIndex].'" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
                                        
                                        $str_Trespasser1 = (strlen($str_Trespasser1) > 33) ? substr($str_Trespasser1, 0, 30) . '...' : $str_Trespasser1;
                                        
                                        $str_Trespasser1 = $str_NotificationDate1 . ' ' . $str_PEC . $str_Trespasser1;
                            }
                            $str_NotificationDate2 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                            $str_PEC = '<i class="fas fa-at tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="top" title="PEC assente" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
                            if (isset($a_TrespasserId[3]) || isset($a_TrespasserId[11])) {
                                if (isset($a_TrespasserId[3])) {
                                    $str_Trespasser2 = $a_TrespasserFullName[3];
                                    $n_AssingedIndex = 3;
                                } else {
                                    $str_Trespasser2 = $a_TrespasserFullName[11];
                                    $n_AssingedIndex = 11;
                                }
                                if ($a_FineNotificationDate[$n_AssingedIndex] != "")
                                    $str_NotificationDate2 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data ' . DateOutDB($a_FineNotificationDate[$n_AssingedIndex]) . '"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>';
                                    if($a_PEC[$n_AssingedIndex]!="")
                                        $str_PEC = '<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_PEC[$n_AssingedIndex].'" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
                                        
                                        
                                        $str_Trespasser2 = (strlen($str_Trespasser2) > 33) ? substr($str_Trespasser2, 0, 30) . '...' : $str_Trespasser2;
                                        
                                        $str_Trespasser2 = $str_NotificationDate2 .' '. $str_PEC . $str_Trespasser2;
                            }
                            
                            $str_VehicleType = '<i class="' . $aVehicleTypeId[$r_Fine['VehicleTypeId']] . '" style="color:#337AB7;"></i>';
                            
                            if($r_Fine['KindSendDate'] != ''){
                                $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
                            } else $FineIcon = $a_FineTypeId[$r_Fine['FineTypeId']];
                            
                            $str_FineData = $FineIcon . ' ' . DateOutDB($r_Fine['FineDate']) . ' - ' . TimeOutDB($r_Fine['FineTime']) .' <span style="position:absolute; right:0.5rem;">' . StringOutDB($r_Fine['VehiclePlate']) . ' ' . $str_VehicleType . '</span>';
                            $str_ArticleNumber = ($r_Fine['ArticleNumber'] > 1) ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : '';
                            
                            $str_Style = (isset($a_StatusTypeId[$r_Fine['StatusTypeId']])) ? 'color:' . $a_StatusTypeId[$r_Fine['StatusTypeId']] . ';' : '';
                            $str_CssEuro = (isset($a_Euro[$r_Fine['StatusTypeId']])) ? '#' . $a_Euro[$r_Fine['StatusTypeId']] : '#000';
                            $str_Euro = (isset($a_Euro[$r_Fine['StatusTypeId']])) ? $a_Euro[$r_Fine['StatusTypeId']] : '000';
                            
                            //*******IN QUESTO PUNTO VENGONO DEFINITE LE RIGHE*******
                            $str_out .= '
        <div class="tableRow">
            '.($b_ShowInfo ? '<div class="table_caption_H col-sm-1" style="' . $str_Style . 'width:7%;">' . $r_Fine['FineId'] . '</div>' : '').'
			<div class="table_caption_H col-sm-1" style="' . $str_Style . '">' . $r_Fine['ProtocolId'] . '/' . $r_Fine['ProtocolYear'] . ' ' . $str_PreviousId . $str_Archive . $str_ProtocolId . $str_Kind . '</div>
		    <div class="table_caption_H col-sm-1" style="' . $str_Style . '">' . $r_Fine['Code'] . '</div>
        	<div class="table_caption_H col-sm-2" style="' . $str_Style . '">' . $str_FineData . '</div>
			<div class="table_caption_H col-sm-2">' . StringOutDB($str_Trespasser1) . '</div>
			<div class="table_caption_H col-sm-2">' . StringOutDB($str_Trespasser2) . '</div>
			';
                            // <div class="table_caption_H col-sm-1"'.$str_Style.'>' . $ExternalProtocol .'</div>';
                            
                            $Status = '';
                            // if($r_Customer['ExternalRegistration']==1)
                            // $Status .= ($r_Fine['ExternalProtocol']>0) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale protocollato in data '. DateOutDB($r_FineHistoryTrespasser['ExternalDate']).'"><i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>' : '<i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i>';
                            
                            if ($r_Fine['StatusTypeId'] > 14) {
                                $Status .= (! is_null($notificationTypeId) && $notificationTypeId == 6 && ! is_null($flowDate)) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso creato in data ' . DateOutDB($flowDate) . '"><i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso non e stato creato"><i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                                $Status .= (! is_null($notificationTypeId) && $notificationTypeId == 6 && ! is_null($printDate)) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso stampato in data ' . DateOutDB($printDate) . '"><i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso non e stato stampato"><i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                            } else if ($r_Fine['FineTypeId'] == 2) {
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Preavviso"> <i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:3.4rem;font-size:1.3rem;"></i></span>';
                            } else if ($r_Fine['StatusTypeId'] == 3) {
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale"> <i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:3.4rem;font-size:1.3rem;"></i></span>';
                            } else {
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale"><i class="fa fa fa-file" style="margin-top:0.2rem;margin-left:3.4rem;font-size:1.3rem;"></i></span>';
                            }
                            
                            $Status .= (! is_null($notificationTypeId) && ($notificationTypeId == 6) && ! is_null($sendDate)) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale inviato in data ' . DateOutDB($sendDate) . '"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale non e stato inviato"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                            
                            if (! is_null($notificationTypeId) && $notificationTypeId == 6 && ! is_null($resultId)) {
                                $queryPictures = "
                               SELECT
                               f.Id,
                               fd.Documentation,
                               fd.DocumentationTypeId
                               from Fine f
                               LEFT JOIN
                               FineDocumentation fd
                               ON
                               f.Id = fd.FineId
                               WHERE
                               f.Id=".$r_Fine['FineId']." AND fd.DocumentationTypeId IN(10,11,12,82)";
                                
                                if (! is_null($deliveryDate)) {
                                    $rs_pictures = $rs->SelectQuery($queryPictures);
                                    $envelopeColor = "green";
                                    
                                    if(mysqli_num_rows($rs_pictures)==0)
                                        $envelopeColor = "blue";
                                        $Status .= '
                    <a href="mgmt_notification_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '">
                        <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale notificato in data ' . DateOutDB($deliveryDate) . '"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;color:'.$envelopeColor.';"></i></span>
                    </a>
                    ';
                                        $str_DeliveryStatus = '<a href="mgmt_notification_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                                } else { // TODO: stare attenti al caso senza delivery data o diverso da 6 che non tiene conto del tipo 30
                                    $Status .= '
                    <a href="mgmt_notification_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '">
                        <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="' . $a_Result[$resultId] . '"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;color:red;"></i></span>
                    </a>
                    ';
                                    $str_DeliveryStatus = '<a href="mgmt_notification_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                                }
                            } else {
                                if ($_SESSION['usertype'] > 50) {
                                    $Status .= '
                <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Importa notifica">
                    <a href="mgmt_notification_add.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '">
                        <i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.2"></i></a></span>
                ';
                                } else {
                                    $Status .= '<i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i>';
                                }
                                $str_DeliveryStatus = '&nbsp;';
                            }
                            
                            switch ($r_Fine['StatusTypeId']) {
                                case 27:
                                case 28:
                                case 30:
                                    
                                    if ($r_Fine['StatusTypeId'] == 27) {
                                        $spanTitle = 'Verbale non pagato';
                                        $i_id = '';
                                    } else {
                                        $spanTitle = 'Verbale pagato in data ' . DateOutDB($paymentDate);
                                        $i_id = 'id="' . $r_Fine['FineId'] . '"';
                                    }
                                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="' . $spanTitle . '">
                    <i ' . $i_id . ' class="fa fa-eur" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.3rem;color:' . $str_CssEuro . '" name="' . $str_Euro . '"></i>
                    </span>';
                                    break;
                                default:
                                    if ($paymentDate != "") {
                                        $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data ' . DateOutDB($paymentDate) . '"><i id="' . $paymentId . '" class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;color:' . $str_CssEuro . '" name="' . $str_Euro . '"></i></span>';
                                    } else {
                                        
                                        if ($_SESSION['usertype'] > 50) {
                                            $Status .= '
                                <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Cerca pagamento">
                                    <i class="fa fa-eur src_payment" fineid="' . $r_Fine['FineId'] . '" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.3rem;opacity:.2"></i>
                                </span>
                            ';
                                        } else {
                                            $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1;color:' . $str_CssEuro . '" name="' . $str_Euro . '"></i>';
                                        }
                                    }
                                    break;
                            }
                            
                            if (isset($a_paymentRate['FineId'])) {
                                if ($r_Fine['StatusTypeId'] == 30) {
                                    $spanTitle = 'Rateizzazione conclusa';
                                    $ratecolor = 'color:#3C763D;';
                                    $i_id = '';
                                } else {
                                    if($r_Fine['StatusRateId'] == 0)
                                        $spanTitle = 'Rateizzazione in corso';
                                    if($r_Fine['StatusRateId'] == 1)
                                        $spanTitle = 'Rateizzazione chiusa';
                                    $ratecolor = '';
                                    $i_id = '';
                                }
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="' . $spanTitle . '">
                    <i ' . $i_id . ' class="fa fa-credit-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;' . $ratecolor . '"></i>
                    </span>';
                            } else {
                                $Status .= '<i class="fa fa-credit-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:0.2;"></i>';
                            }
                            
                            if ($gradeTypeId != "") {
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="' . $a_GradeType[$gradeTypeId] . ' Grado - ' . $officeTitle . ' ' . $officeCity . ' Depositato in data ' . DateOutDB($r_FineHistoryTrespasser['DateFile']) . '"><i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;color:' . $a_DisputeStatusId[$r_FineHistoryTrespasser['DisputeStatusId']] . '"></i></span>';
                            } else
                                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Senza grado"><i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                                
                                if ($reducedPoint != "") {
                                    
                                    if ($reducedPoint > 0) {
                                        $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Punti decurtati in data ' . DateOutDB($reducedDate) . ' (' . $reducedPoint . ')"><i data-id="' . $fhtfineId . '" class="fa fa-sort-numeric-desc show-communication" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>';
                                    } else {
                                        $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Comunicazione presentata in data ' . DateOutDB($communicationDate) . '"><i data-id="' . $fhtfineId . '" class="fa fa-address-card show-communication" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;"></i></span>';
                                    }
                                } else
                                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Comunicazione non e stata presentata"><i class="fa fa-address-card show-communication" style="margin-top:0.2rem;margin-left:1rem;font-size:1.3rem;opacity:.1"></i></span>';
                                    
                                    $str_DocumentLink = "#";
                                    if ($documentation != "") {
                                        $str_DocumentFolder = ($r_Fine['VehicleCountryId'] == 'Z000') ? NATIONAL_FINE_HTML . "/" . $_SESSION['cityid'] . "/" . $r_Fine['FineId'] : FOREIGN_FINE_HTML . "/" . $_SESSION['cityid'] . "/" . $r_Fine['FineId'];
                                        
                                        $str_DocumentLink = $str_DocumentFolder . "/" . $documentation;
                                    }
                                    
                                    //Icona per indicare l'emissione del ruolo
                                    $Status .= $str_Injunction;
                                    
                                    // if($r_Fine['FineTypeId']==2)
                                    $upd_button = ChkButton($aUserButton, 'upd', '<a href="mgmt_fine_upd.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><span class="tooltip-r glyphicon glyphicon-pencil" title="Modifica" data-placement="top"  style="position:absolute;left:45px;top:5px;"></span></a>');
                                    // else if($r_Fine['FineTypeId']==3 || $r_Fine['FineTypeId']==4 || $r_Fine['FineTypeId']==5)
                                    // $upd_button = ChkButton($aUserButton, 'upd','<a href="mgmt_fine_upd.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:45px;top:5px;"></span></a>');
                                    // else $upd_button = '';
                                    $str_out .= '<div class="table_caption_H col-sm-3"'.($b_ShowInfo ? ' style="width:18%;"' : '').'>' . $Status . '</div>
			<div class="table_caption_button col-sm-1">
				' . ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="position:absolute;left:5px;top:5px;"></span></a>') . '
				&nbsp;
				' . ($str_Kind == "" ? ChkButton($aUserButton, 'prn', '<a href="' . $str_DocumentLink . '" target="_BLANK"><span class="tooltip-r fa fa-print" title="Visualizza stampa" data-placement="top" style="position:absolute;left:25px;top:5px;"></span></a>') : '') . '
				' . $upd_button . '
				' . ($str_Kind == "" ? ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_prn.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><span class="tooltip-r fa fa-paper-plane" data-placement="top" title="Stampa" style="position:absolute;left:65px;top:5px;"></span></a>') : '');
                                    
                                    //11/08/2022 Aggiunta condizione per poter archiviare i preavvisi pagati
                                    if ($r_Fine['StatusTypeId'] < 30 || ($r_Fine['StatusTypeId'] <= 30 && $r_Fine['FineTypeId'] == 2)) {
                                        $str_out .= ChkButton($aUserButton, 'del', '<a href="mgmt_fine_exp.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><span class="tooltip-r glyphicon glyphicon-remove-sign" data-placement="top" title="Archiviazione/rinotifica verbale" style="position:absolute;left:85px;top:5px;"></span></a>');
                                    } else {
                                        $str_out .= '<span style="position:absolute;left:85px;top:5px;"></span>';
                                    }
                                    
                                    if ($r_Fine['KindSendDate'] != '' && $r_Fine['StatusTypeId'] <= 32) {
                                        $str_out .= ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_exp_ag.php' . $str_GET_Parameter . '&Id=' . $r_Fine['FineId'] . '"><span class="tooltip-r fas fa-mail-bulk" data-placement="top" title="Crea verbale per invito AG" style="position:absolute;left:105px;top:5px;"></span></a>');
                                    }
                                    
                                    // eguagliamo il FineId del protocollo massimo selezionato con il FineId del record
                                    // if ($r_MaxFineId[0] == $r_Fine['FineId']) {
                                    // $str_out.= ChkButton($aUserButton, 'del','<a href="mgmt_fine_del_exe.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'&CountryId='.$r_Fine['CountryId'].'"><span class="tooltip-r glyphicon glyphicon-remove" data-placement="top" title="Cancella ultimo verbale inserito" style="position:absolute;left:105px;top:5px;"></span></a>');
                                    // } else {
                                    // $str_out .= '<span style="position:absolute;left:105px;top:5px;"></span>';
                                    // }
                                    
                                    // $str_out.='<a href="mgmt_fine_del_exe.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'"><span class="glyphicon glyphicon-remove" style="position:absolute;left:85px;top:5px;"></span></a>';
                                    $str_out .= '
			</div>
        </div>
			<div class="clean_row HSpace4"></div>';
                }
            }
            
            $str_FineHistoryLabel = '
 		<div style="position:absolute; top:5px;right:5rem; font-size:1.2rem;color:#fff;width:430px;text-align: left">
 			<div style="width:280px; position:relative; top:-5px;left:10px;">Avanzamento atto:</div>
 			<div style="width:140px;float:left; position:relative; top:-5px;">
                <div>
                    <i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Flusso creato
                </div>
                <div>
                    <i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale stampato
                </div>
            </div>
            <div style="width:140px;float:left; position:relative; top:-5px;">
                <div>
                    <i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale spedito
                </div>
                <div>
                   <i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale Notificato
                </div>
            </div>
            <div style="width:140px;float:left; position:relative; top:-5px;">
                <div>
                    <i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale pagato
                </div>
                <div>
                   <i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale contestato
                </div>
            </div>
		</div>
		';
            
            $str_out .= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage, $str_FineTypeLabel . $str_FineHistoryLabel);
}

$str_out .= '<div>
	</div>';

echo $str_out;

?>
<div class="overlay" id="overlay" style="display: none;"></div>

<div id="overlay_PaymentView">
	<div id="FormPaymentTrespasser"></div>
</div>

<div id="overlay_CommunicationView">
	<div id="FormCommunicationTrespasser"></div>
</div>



<script type="text/javascript">


	$(document).ready(function () {
        <?=$str_Search_ViolationDisabled;?>

        //TODO rimuovere quando verrà corretto il filtro
        $('#Search_PrefectCommunication').prop('disabled', true);

        $('#Search_HasKindSendDate, #Search_IsKindFine, #Search_Type').on('change', function() {
            $('#Search_HasKindSendDate, #Search_IsKindFine, #Search_Type').not(this).prop('checked', false);  
        });

        $('#f_Search').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_date:{
                    selector: '#f_Search .frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
                    }
                },
            }
        }).on('success.form.bv', function(event){
            return true;
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

    	$("#TypePlate").change(function(){
    	    if ($("#TypePlate").val()=='F'){
    	        $('#Search_Country').prop("disabled", false);

    	    }else{
    	        $('#Search_Country').prop("disabled", true);
    	    }
    	});

        <?=require ('inc/jquery/overlay_search_payment.php')?>
        <?=require ('inc/jquery/overlay_search_communication.php')?>

        $('.src_payment').on('click', function() {
            $('#DIV_SrcPayment').show();
            $('#FineId').val($(this).attr("fineid"));

        });
        $(".close_window").click(function () {
            $('#DIV_SrcPayment').hide();
        });
        $( function() {
            $( "#DIV_SrcPayment" ).draggable();
        } );


        $('.fa-search-plus').click(function () {
            $('.fa-search-plus').hide();


            var FineId = $('#FineId').val();
            var Search_Protocol = $('#Payment_Protocol').val();
            var Search_Name = $('#Payment_Name').val();
            var Search_Plate = $('#Payment_Plate').val();
            var Search_Code = $('#Payment_Code').val();

            $.ajax({
                url: 'ajax/ajx_src_finepayment.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {FineId:FineId, Search_Code: Search_Code, Search_Protocol: Search_Protocol, Search_Name: Search_Name, Search_Plate:Search_Plate},
                success: function (data) {
                    $('#payment_content').html(data.Payment);
                    $('.fa-search-plus').show();
                }
            });


        });
        $('.fa-archive').click(function () {
            var FineId = $(this).attr('id');
            if(FineId!=null){
                if(confirm("Si sta per validare la notifica. Continuare?")){
                    $.ajax({
                        url: 'ajax/ajx_validate_address.php?FineId='+FineId,
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        success: function (data) {
                            location.reload(true);
                        }
                    });
                }
            }

        });

        //doppia conferma su cancellazione

        $('.glyphicon-remove:not(.disabled)').on("click", function(){
            	if (confirm("Si sta per cancellare l'ultimo verbale inserito in maniera definitiva. Continuare?")) {
            		if (confirm("Si sta per cancellare l'ultimo verbale inserito in maniera definitiva. Sei veramente sicuro di voler continuare?")) {
            			return true;
            		} else return false;
            	} else return false;
        });


	});








</script>
<?php
include (INC . "/footer.php");