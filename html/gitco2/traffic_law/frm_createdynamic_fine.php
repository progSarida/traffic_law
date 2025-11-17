<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

// $DisplayMsg = CheckValue('DisplayMsg','n');

// if($DisplayMsg){
//     include(INC."/display.php");
//     DIE;
// }

//Preleva le checkbox degli atti selezionati dalla sessione, in modo da riselezionarli, utile per quando si fa anteprima di stampa
$a_SelectedCheckboxes = $_SESSION['Checkboxes']['frm_createdynamic_fine.php'] ?? array();
unset($_SESSION['Checkboxes']['frm_createdynamic_fine.php']);

$PageTitle = CheckValue('PageTitle','s');
$RecordLimit = CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$Search_CreationDate = CheckValue('Search_CreationDate','s') != '' ? CheckValue('Search_CreationDate','s') : DateOutDB(date('Y-m-d'));
$Search_HasPEC = CheckValue('Search_HasPEC', 'n') != '' ? CheckValue('Search_HasPEC', 'n') : ($r_Customer['ManagePEC'] == 1 ? 1 : 0);
$ChiefControllerId  = CheckValue('ChiefControllerId','n');
$PrintDestinationFold  = CheckValue('PrintDestinationFold','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinter'] : $r_Customer['ForeignPrinter']) : CheckValue('PrintDestinationFold','n');
$RegularPostalFine = CheckValue('RegularPostalFine','n');
$CountryId = CheckValue('CountryId','s');
$a_RegularPostalFine = array("","");

$FineIcon = '';

$CreationDate = date('d/m/Y');

//Controlla che vi siano presenti i parametri per i record di verbali bonari
$PaymentDaysColumn = $s_TypePlate == 'N' ? 'PaymentDaysNational' : 'PaymentDaysForeign';
$ElaborationDaysColumn = $s_TypePlate == 'N' ? 'ElaborationDaysNational' : 'ElaborationDaysForeign';
$rs_CustomerKindParams = $rs->SelectQuery("SELECT $PaymentDaysColumn, $ElaborationDaysColumn  FROM Customer WHERE CityId='".$_SESSION['cityid']."'");

$str_ErrorMsg = '';

$PaymentDays = 0;
$ElaborationDays = 0;
if (mysqli_num_rows($rs_CustomerKindParams) > 0){
    $r_CustomerKindParams =  mysqli_fetch_assoc($rs_CustomerKindParams);
    $PaymentDays = $r_CustomerKindParams[$PaymentDaysColumn];
    $ElaborationDays = $r_CustomerKindParams[$ElaborationDaysColumn];
    
    if (empty($PaymentDays) || $PaymentDays <= 0){
        $str_ErrorMsg = '"Giorni pagamento"';
    }
    
    if (empty($ElaborationDays) || $ElaborationDays <= 0){
        $str_ErrorMsg = '"Giorni elaborazione"';
    }
} else $str_ErrorMsg = '"Giorni elaborazione o pagamento"';

if (!empty($str_ErrorMsg)) {
    echo $str_out;
    
    echo '<div class="alert alert-danger">E\' necessario definire '.$str_ErrorMsg.' su Ente/Procedure Ente per poter utilizzare questa procedura per la nazionalità specificata.</div>';
    DIE;
}

//Controlla se è abilitato il flag "Attiva l’invio dei verbali esteri tramite avviso bonario da spedire tramite posta ordinaria"
$ForeignKindFlag = $r_Customer['EnableForeignKindSending'] == 0 ? false : true;


$str_PostalFineType = $s_TypePlate!='F' ? "Atto giudiziario" : "Raccomandata A/R";

$str_WhereCountry = str_replace("ViolationTypeId=", "",$str_Where);
$str_WHhereControllers = "CityId='".$_SESSION['cityid']."' AND ('".date('Y-m-d')."' >= FromDate OR FromDate IS NULL) AND ('".date('Y-m-d')."' <= ToDate OR ToDate IS NULL) AND Disabled=0 AND ChiefController=1";

$str_CountrySelect = CreateSelectQuery('SELECT DISTINCT CountryId, VehicleCountry FROM Fine WHERE '.$str_WhereCountry , 'CountryId', 'CountryId', 'VehicleCountry', $CountryId,false);

$str_RegularPostalFine = '
    <div class="col-sm-1 BoxRowLabel">
        Tipo Invio
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <input type="hidden" name="RegularPostalFine" id="RegularPostalFine" value="0">
        <span id="span_PostalFineType">'. $str_PostalFineType .'</span>
    </div>
    ';

if($r_Customer['RegularPostalFine']){
    $a_RegularPostalFine[$RegularPostalFine] = " SELECTED ";
    
    $str_RegularPostalFine = '
        <div class="col-sm-1 BoxRowLabel">
            Tipo invio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <select id="RegularPostalFine" name="RegularPostalFine" class="form-control">
            	<option value="0" '.$a_RegularPostalFine[0] .'>Atto giudiziario</option>
            	<option value="1"  '.$a_RegularPostalFine[1] .'>Posta ordinaria</option>
            </select>
        </div>
        ';
    
}

$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND RuleTypeId=".$_SESSION['ruletypeid']." AND ControllerId IS NOT NULL";

if ($s_TypePlate == 'N'){
    if($RegularPostalFine){
        $str_Where .= " AND StatusTypeId=7";
    }else {
        $str_Where .= " AND (StatusTypeId=10 OR StatusTypeId=14 OR StatusTypeId=9)";
    }
    
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['NationalPrinter'] > 0 ? "Id IN(1,7,{$r_Customer['NationalPrinter']})" : "Id IN(1,7)";
} else {
    if($RegularPostalFine){
        $str_Where .= " AND StatusTypeId=7";
    }else {
        $str_Where .= " AND (StatusTypeId=10 OR StatusTypeId=14".($ForeignKindFlag ? ' OR StatusTypeId=9' : '').")";
    }
    if($r_Customer['PagoPAPaymentForeign']==1){
        $str_Where .= " AND ((PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL) OR FineTypeId in (3,4)) ";
    }
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['ForeignPrinter'] > 0 ? "Id IN(1,7,{$r_Customer['ForeignPrinter']})" : "Id IN(1,7)";
}
if ($Search_Article != ""){
    $str_Where .= " AND ArticleId=".$Search_Article;
}
if ($Search_Detector > 0){
    $str_Where .= " AND DetectorId=$Search_Detector";
}

if ($Search_Plate != ""){
    $str_Where .= " AND VehiclePlate='$Search_Plate'";
}

switch ($Search_Special){
    case 1:
        //Solo avvisi bonari (Avvisi bonari non pagati)
        if(($ForeignKindFlag && $s_TypePlate=='F') || $s_TypePlate=='N'){
            $str_Where .= " AND Id IN
                (
                    SELECT KindFine.FineId FROM
                    (
                        SELECT FH30.FineId AS FineId, MIN(FP.PaymentDate)
                        FROM FineHistory FH30
                        JOIN Fine F30 ON FH30.FineId=F30.Id
                        LEFT JOIN FinePayment FP ON F30.Id=FP.FineId
                        WHERE FH30.NotificationTypeId = 30
                        AND F30.FineTypeId != 4
                        AND ((
                            F30.StatusTypeId=9 AND
                            FP.FineId IS NULL AND
                            '".DateInDB($Search_CreationDate)."' >= DATE_ADD(FH30.PrintDate, INTERVAL ".$PaymentDays." DAY)
                        )) GROUP BY FineId
                    ) AS KindFine
                )";
        }
        break;
    case 2:
        //Solo avvisi pagamento agevolato non creati entro termine (verbali per cui sono non stati emessi gli avvisi bonari entro i termini)
        if(($ForeignKindFlag && $s_TypePlate=='F') || $s_TypePlate=='N'){
            $str_Where .= " AND
            (
                StatusTypeId IN(10,14) AND
                Amicable = 1 AND
                PreviousId = 0 AND
                '".DateInDB($Search_CreationDate)."' > DATE_ADD(FineDate, INTERVAL ".$ElaborationDays." DAY)
            )";
        }
        break;
    default:
        //Se non si sceglie niente DEVONO ESSERE ESCLUSI gli atti che soddisfano i criteri per la creazione di avviso bonario
        //Di cui si occuperebbe quindi la pagina Moduli > Crea avviso bonario
        
        //esclude verbali e preinserimenti a pagamento agevolato per cui non sono passati i giorni elaborazione
        //esclude avvisi bonari inviati per cui non è ancora passato il tremine di pagamento anche se non pagati
        //esclude avvisi bonari inviati già pagati
        $str_Where .= " AND Id NOT IN
            (
                SELECT F.Id
                FROM Fine F
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN Article A ON A.Id=FA.ArticleId
                WHERE F.StatusTypeId IN(10,14) AND
                A.Amicable = 1 AND
                F.PreviousId = 0 AND
                '".DateInDB($Search_CreationDate)."' <= DATE_ADD(F.FineDate, INTERVAL $ElaborationDays DAY)
            ) 
            AND Id NOT IN
            (
                SELECT KindFine.FineId FROM
                (
                    SELECT FH30.FineId AS FineId, MIN(FP.PaymentDate)
                    FROM FineHistory FH30
                    JOIN Fine F30 ON FH30.FineId=F30.Id
                    LEFT JOIN FinePayment FP ON F30.Id=FP.FineId
                    WHERE FH30.NotificationTypeId = 30
                    AND F30.FineTypeId != 4
                    AND ((
                        F30.StatusTypeId=9 AND
                        FP.FineId IS NULL AND
                        '".DateInDB($Search_CreationDate)."' < DATE_ADD(FH30.PrintDate, INTERVAL ".$PaymentDays." DAY)
                    )) GROUP BY FineId
                ) AS KindFine
            )
            AND Id NOT IN
            (
                SELECT KindFine.FineId FROM
                (
                    SELECT FH30.FineId AS FineId, MIN(FP.PaymentDate)
                    FROM FineHistory FH30
                    JOIN Fine F30 ON FH30.FineId=F30.Id
                    JOIN FinePayment FP ON F30.Id=FP.FineId
                    WHERE FH30.NotificationTypeId = 30
                    AND ((
                        F30.StatusTypeId=9 AND
                        FP.FineId IS NOT NULL 
                    )) GROUP BY FineId
                ) AS KindFine
            )";
}

$str_out .= '
            <div class="col-sm-12">
                <div class="col-sm-12 alert alert-info" style="padding:5px; display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        <ul>
                            <li>Effetti filtri speciali:
                            <ul style="list-style-position: inside;">
                                <li>Solo avvisi bonari: include solo gli avvisi bonari creati e inviati, che non hanno alcun pagamento e la Data di creazione va oltre la data di violazione + i giorni di pagamento definiti in Ente > Procedure Ente nella scheda Avvisi Bonari.</li>
                                <li>Solo avvisi pagamento agevolato non creati entro termine: include solo gli che prevedevano il pagamento agevolato ma di cui è scaduto il termine di elaborazione, determinato da dai giorni di elaborazione definiti in Ente > Procedure Ente nella scheda Avvisi Bonari.</li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-12 alert alert-danger" style="padding:5px; display: flex;margin: 0px;align-items: center;">
                <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                <div class="col-sm-11" style="font-size: 1.2rem;">
                    <ul>
                        <li>Nota bene:
                            <ul style="list-style-position: inside;">
                                <li>
                                    Se i verbali prevedono pagamento tramite PagoPA con stampa di avviso di pagamento e l\'ente gestisce il PagoPA su conto corrente postale, è necessario abilitare il bollettino nazionale PagoPA definendo il TD in Ente > Procedure Ente > Postalizzazione, 
                                    altrimenti l\'avviso di pagamento PagoPA non avrà la sezione dedicata al pagamento in poste.
                                </li>
                            </ul>
                    	</li>
                    </ul>
                </div>
            </div>

            <div class="clean_row HSpace4"></div>';

if(!$ForeignKindFlag && $s_TypePlate=='F' && $Search_Special != ''){
    $str_out .= '
		<div class="table_caption_H col-sm-12 alert-warning">
            <i class="fas fa-fw fa-warning col-sm-1" style="margin-top: 0.5rem;"></i>
            &nbsp;&nbsp;&nbsp;Attenzione, la gestione degli avvisi bonari esteri non è abilitata per l\'ente in uso, pertanto "Filtri speciali" non avrà alcun effetto sulla ricerca.
        </div>';
}

$str_out .= '
<div class="row-fluid">
    <form id="f_search" action="frm_createdynamic_fine.php" method="post" autocomplete="off">
        <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.$_SESSION['ruletypetitle'].'
            </div>
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(5,25,50,100,200), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","Search_ControllerId","Id","ControllerName",$Search_ControllerId,false) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
            </div>
                    
            <div class="clean_row HSpace4"></div>

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
            <div id="DIV_Nation"'.($s_TypePlate=='F' ? '' : ' style="display:none;"').'>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $str_CountrySelect .'
                </div>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo contravventore
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="Search_Genre" id="Search_Genre">
                    <option value="">Entrambi</option>
                    <option value="D"'.($Search_Genre == "D" ? " selected" : "").'>Ditta</option>
                    <option value="P"'.($Search_Genre == "P" ? " selected" : "").'>Persona fisica</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.CreateSelectQuery("SELECT Id,CONCAT(progressive, ' - ', COALESCE(NULLIF(Ratification, ''), TitleIta)) AS TitleIta FROM Detector WHERE CityId='{$_SESSION['cityid']}' ORDER BY progressive", 'Search_Detector', 'Id', 'TitleIta', $Search_Detector, false).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Articolo
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateSelectQuery("SELECT Id,CONCAT_WS(' ',Article,Paragraph,Letter)Article FROM Article WHERE CityId='".$_SESSION['cityid']."' ORDER BY Article ASC", 'Search_Article', 'Id', 'Article', $Search_Article, false).'
            </div>
            <div class="col-sm-3 BoxRowLabel" id="DIV_Empty"'.($s_TypePlate!='F' ? '' : ' style="display:none;"').'>
            </div>
                
            <div class="clean_row HSpace4"></div>
                
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control " type="text" value="'.$Search_Plate.'" name="Search_Plate" id="Search_Plate">
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Data di creazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_CreationDate.'" name="Search_CreationDate" id="Search_CreationDate">
            </div>'
                    . $str_RegularPostalFine .'
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("ViolationType","RuleTypeId={$_SESSION['ruletypeid']}","Id","Search_Violation","Id","Title",$Search_Violation,false) .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-1 BoxRowLabel">
                Escludi PEC
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name="Search_HasPEC">
            	<input name="Search_HasPEC" id="Search_HasPEC" type="checkbox" value="1" '. ChkCheckButton($Search_HasPEC) .'/>
            </div>
            <div class="col-sm-2 BoxRowLabel font_small">
                Solo rinotifiche di invii PEC falliti
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input'.($r_Customer['ManagePEC'] != 1 ? ' disabled' : '').' name="Search_PECRenotification" id="Search_PECRenotification" type="checkbox" value="1" '. ChkCheckButton($Search_PECRenotification) .'/>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo inviti in AG
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="1" id="Search_HasKindSendDate" name="Search_HasKindSendDate" ' . ChkCheckButton($Search_HasKindSendDate > 0 ? 1 : 0) . '>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Filtri speciali
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.CreateArraySelect(array(1 => 'Solo avvisi bonari', 2 => 'Solo avvisi pagamento agevolato non creati entro termine'), true, 'Search_Special', 'Search_Special', $Search_Special).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align:center;">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:9.1rem;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';
                    
                    
                    
$str_out .='
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_createdynamic_fine_exe.php'.$str_GET_Parameter.'" method="post">
        	<input type="hidden" name="P" value="frm_createdynamic_fine.php" />
        	<input type="hidden" name="RegularPostalFine" value="'. $RegularPostalFine .'" />
        	    
        	<div class="col-sm-12">
                <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked /></div>
        	    <div class="table_label_H col-sm-1">Info</div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-2">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-1">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
        	    
				<div class="clean_row HSpace4"></div>';

if($s_TypePlate==""){
    $str_out.= '
        <div class="table_caption_H col-sm-12 text-center">
			Scegliere nazionalità targa
		</div>';
} else {
    //$str_Where .= " OR ((Article=193 AND Paragraph='2' AND KindSendDate IS NOT NULL) AND (Article=80 AND Paragraph='14' AND KindSendDate IS NOT NULL))";
    
    if ($Search_HasPEC != ''){
        $str_Where .= $Search_HasPEC == 1 ? " AND Id NOT IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
    }
    
    //nota i dati di FlowPecMails sono quelli presi per il previuos cioè per l'atto originale rinotificato mentre la PEC è presa per l'atto corrente
    if ($Search_PECRenotification != 0){
        $str_Where .= $Search_PECRenotification == 1 ? " AND PreviousId > 0 AND length(COALESCE(PEC,''))>0 AND PreviousPECTrespasserId is not null AND (PreviousAnomaly = 'S' OR COALESCE(PreviousSendError,'') != '')" : "";
    }
    
    if($Search_ControllerId > 0){
        $str_Where .= " AND ControllerId=$Search_ControllerId ";
    }
    
    if($Search_Violation > 0){
        $str_Where .= " AND ViolationTypeId=$Search_Violation ";
    }
    
    if($Search_Genre != ""){
        if ($Search_Genre == "D"){
            $str_Where .= " AND Genre='D' ";
        } else if ($Search_Genre == "P") {
            $str_Where .= " AND Genre!='D' ";
        }
    }
    
    if($CountryId!="") $str_Where .= " AND CountryID='". $CountryId ."'";
    
    //COndizionare secondo flag nuova scheda
    $str_KindArticle = '';
    $str_KindArticle = " OR (" .$str_Where;
    
    $str_Where .= " AND (KindSendDate IS NULL AND((Article!=193 AND Paragraph!='2') OR (Article!=80 AND Paragraph!='14')) ";
    
    $str_KindArticle .= " AND KindSendDate IS NOT NULL AND((Article=193 AND Paragraph='2') OR (Article=80 AND Paragraph='14'))))";
    
    //NOTA: Questo filtro va lasciato dopo $str_KindArticle
    if ($Search_HasKindSendDate > 0) {
        $str_Where .= " AND KindSendDate IS NOT NULL";
    }
    
    $strOrder = "FineDate ASC, FineTime ASC, Id ASC";
    
    if($RecordLimit>0){
        $strOrder .= " LIMIT $RecordLimit";
    }
    
    $table_rows = $rs->Select('V_ViolationAll',$str_Where.$str_KindArticle, $strOrder);
    //echo $str_Where.$str_KindArticle;
    $RowNumber = mysqli_num_rows($table_rows);
    $str_out.= '<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';
    
    if ($RowNumber == 0) {
        $str_out.= '
            <div class="table_caption_H col-sm-12 text-center">
			    Nessun record presente
		    </div>';
    } else {
        $n_Row = 1;
        $n_FineId = 0;
        while ($table_row = mysqli_fetch_array($table_rows)) {
                
            if($table_row['PreviousId'] > 0){
                $rs_PreviousFine=$rs->Select('Fine', "Id={$table_row['PreviousId']}");
                $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);
            } else $r_PreviousFine = null;
            $rs_Trespasser = $rs->Select('Trespasser', 'Id='.$table_row['TrespasserId']);
            $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
            
            $str_Status = '';
            if($r_PreviousFine){
                if($r_PreviousFine['StatusTypeId'] == 34 && $r_PreviousFine['KindSendDate'] != '')
                    $str_Status .= '<i class="fas fa-mail-bulk tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Creazione verbale da invito in AG" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;';
            }
            if($r_Trespasser){
                $str_Status .= '<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'])).'" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;';
            }
            if(!empty($table_row['PEC'])){
                $str_Status .= '<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.StringOutDB($table_row['PEC']).'" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
            }
            if(empty($table_row['ControllerId']) || $table_row['ControllerId'] == 0){
                $str_Status .= '<i class="fas fa-info-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Accertatore mancante" style="margin-top:0.2rem;font-size:1.8rem; color:red"></i>';
            }
                
            $str_CssController = "";
                
            if($n_FineId!=$table_row['Id']){
                $str_Check ='
                    <input '.(in_array($table_row['Id'], $a_SelectedCheckboxes) || empty($a_SelectedCheckboxes) ? 'checked ' : '').'type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '"/>';
                $n_FineId = $table_row['Id'];
            } else $str_Check = '';
            
            if($table_row['ControllerId']=="") $str_CssController = ' style="background-color:rgba(107,155,29,0.76)"';
                
            if($table_row['KindSendDate'] != ''){
                $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
            } else $FineIcon = $a_FineTypeId[$table_row['FineTypeId']];
                
            
            $str_out .= '<div class="tableRow">';
                
            $str_out .= '
    			<div class="col-sm-1" style="text-align:center;padding:0">
        			<div class="table_caption_button col-sm-6" style="text-align:center;">
                        '. $str_Check .'
    				</div>
        			<div class="table_caption_H col-sm-6" style="text-align:center;">
        				'. $n_Row++ .'
    				</div>
    			</div>
                <div class="table_caption_H col-sm-1 text-center"'.$str_CssController.'>' . $str_Status .'</div>
    			<div class="table_caption_H col-sm-1">' . $FineIcon . $table_row['Id'] .'</div>
    			<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>
    			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
    			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>
    			<div class="table_caption_H col-sm-1">' . $table_row['Article'] .' '.$table_row['Paragraph'].' '.$table_row['Letter'].'</div>
    			<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
    			<div class="table_caption_H col-sm-1">' . $table_row['VehicleCountry'].'</div>
    			    
    			<div class="table_caption_button col-sm-1">
    			'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>') .'
    			&nbsp;
    			'. ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id']."&#38;TypePlate=".$s_TypePlate."&ControllerId=".$Search_ControllerId."&RecordLimit=".$RecordLimit.'"><span class="glyphicon glyphicon-pencil"></span></a>') .'
    			&nbsp;
    			</div>
    			<div class="clean_row HSpace4"></div>';
                
            $str_out .= '</div>';
        }
        
        if ($RowNumber > 0){
            if($_SESSION['usertype']==3 || $_SESSION['usertype']==2) {
				$str_ChiefController = '<div class="col-sm-6 BoxRowCaption" style="height:6rem;"></div>';
            } else {
                
                // 		        if($r_Customer['ChiefControllerList']){
                // 		            $str_ChiefController =
                // 		            '
                //                     <div class="col-sm-3 BoxRowLabel">
                //                             Verbalizzante
                //                     </div>
                //                     <div class="col-sm-3 BoxRowCaption">
                //                         '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WHhereControllers ORDER BY CAST(Code AS UNSIGNED)","ChiefControllerId","Id","Name",$ChiefControllerId,false,"","frm_field_required") .'
                //                     </div>
                //                     ';
                // 		        } else {
                // 		            $rs_ChiefController = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
                // 		            $str_OptionChiefController = '';
                // 		            while($r_ChiefController = mysqli_fetch_array($rs_ChiefController)){
                // 		                $str_OptionChiefController .= '<option value="'.$r_ChiefController['Id'].'"';
                // 		                if($ChiefControllerId==$r_ChiefController['Id'])  $str_OptionChiefController .= " SELECTED ";
                // 		                $str_OptionChiefController .= '>'. $r_ChiefController['Name'].' Matricola '.$r_ChiefController['Code'].'</option>';
                // 		            }
                
                // 		            $str_ChiefController =
                // 		            '
                //                     <div class="col-sm-3 BoxRowLabel">
                //                         Verbalizzante
                //                     </div>
                //                     <div class="col-sm-3 BoxRowCaption">
                //                         <select class="form-control" name="ChiefControllerId">
                //                             '.$str_OptionChiefController.'
                //                         </select>
                //                     </div>
                //                     ';
                
                // 		        }
                
                $str_ChiefController = '
                    <div class="col-sm-3 BoxRowLabel" style="height:6rem;">
                            Verbalizzante/Firmatario
                    </div>
                    <div class="col-sm-3 BoxRowCaption" style="height:6rem;">
                        '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WHhereControllers ORDER BY CAST(Code AS UNSIGNED)","ChiefControllerId","Id","Name",$ChiefControllerId,false,"","frm_field_required") .'
                    </div>';
            }
            
            $str_out .= '
                <div class="col-sm-12 BoxRowFilterButton" style="height:auto;padding:0;">
                <div class="col-sm-3 BoxRowLabel" style="height:5rem;">
                    Data verbalizzazione
                </div>
                <div class="col-sm-3 BoxRowCaption" style="height:5rem;">
                    <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="'.$CreationDate.'">
                </div>
    			<div class="table_caption_H col-sm-6 alert-warning pull-right" style="height:auto;min-height:5rem;line-height:1.5rem;">
                    <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>
                    <ul style="margin:0;padding-left:3.5rem;">
                        <li>La scelta della data di verbalizzazione determina quali verbalizzanti sono disponibili in base al loro periodo di incarico.</li>
                    </ul>
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
                        <li>In base a quanto scelto tra \'Ufficio del comune\' e stampatore specifico verranno incluse nelle stampe, se valorizzate, le informazioni sulla restituzione del piego come definite nelle configurazioni. Lo stampatore specifico può essere scelto e modificato nella pagina Ente/Gestione Ente nella scheda \'Posta\'. Le informazioni del piego possono essere inserite/modificate in Strumenti/Destinazioni di stampa.</li>
                    </ul>
                </div>

                <div class="clean_row HSpace4"></div>

                '. $str_ChiefController .'

    			<div class="table_caption_H col-sm-6 alert-warning pull-right" style="height:auto;min-height:6rem;line-height:1.5rem;">
                    <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>
                    <ul style="margin:0;padding-left:3.5rem;">
                        <li>In caso di prima stampa: se il dato del verbalizzante è già registrato nel verbale, quest\'ultimo avrà priorità sulla tendina.</li>
                        <li>In caso di rinotifica: il verbalizzante mostrato nella stampa sarà quello specificato nella tendina.
                    </ul>
                </div>
            </div>
                
            <div class="clean_row HSpace4"></div>
                
    	    <div class="table_label_H HSpace4" style="height:8rem;">
    	    	<div style="padding-top:2rem;">
        	    	'.ChkButton($aUserButton, 'act','<button type="submit" id="sub_Button" class="btn btn-success" style="width:16rem;">Anteprima di stampa</button>').'
					'.ChkButton($aUserButton, 'act','<input value=1 type="checkbox" name="ultimate" id="ultimate" style="margin-left:5rem;"> Definitiva').'
                    <img src="'.IMG.'/progress.gif" style="display: none;" id="Progress"/>
    	    	</div>
            </div>
		</form>
	</div>';
        } else {
            $str_out.= '
                <div class="table_caption_H col-sm-12 text-center">
    			    Nessun record presente
    		    </div>';
        }
    }
}
                    
echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {
	
		$('#TypePlate').change(function() {
			$('#f_search').submit();
		});

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_print").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_print").trigger( "check" );
        });

        $("#f_print").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#sub_Button').prop('disabled', false);
        	else
        		$('#sub_Button').prop('disabled', true);
        });
        
        $('#Search_HasPEC, #Search_PECRenotification').on('change', function() {
            $('#Search_HasPEC, #Search_PECRenotification').not(this).prop('checked', false);  
        });
        
    	$('#Search_HasKindSendDate, #Search_IsKindFine').on('change', function() {
            $('#Search_HasKindSendDate, #Search_IsKindFine').not(this).prop('checked', false);  
        });
		
		$('#ultimate').click(function(){
			if($(this).is(":checked")) {
				$('#sub_Button').html('Stampa defitiva');
				$('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
			}else{
				$('#sub_Button').html('Anteprima di stampa');
				$('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
			}
		});

		$('#CreationDate').on('change', function(){
			var date = $(this).val();
			
			$.ajax({
		        url: 'ajax/ajx_getControllersByValidityDate.php',
		        type: 'POST',
		        dataType: 'json',
		        data: {Date: date},
		        ContentType: "application/json; charset=UTF-8",
		        success: function (data) {
		        	$('#ChiefControllerId').html('');
		        	$('#ChiefControllerId').append('<option></option>');
		        	$.each(data.Result, function(i, value) {
			        	var name = [value.code, value.qualification, value.name];
		        		$('#ChiefControllerId').append($('<option>', {
		        		    value: value.id,
		        		    text: name.join(' ')
		        		}));
		        	});
		        },
		        error: function (data) {
		            console.log(data);
		            alert("error: " + data.responseText);
		        }
		    });
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
            }
        }).on('success.form.bv', function(e){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si stanno per creare i verbali in maniera definitiva. Continuare?")){
					$('#sub_Button').hide();
					$('#Progress').show();
					$('#ultimate').hide();
				} else {
                	e.preventDefault();
                	return false;
				}
			}
        });

        
	});
</script>
<?php
include(INC."/footer.php");

