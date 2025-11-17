<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//Preleva le checkbox degli atti selezionati dalla sessione, in modo da riselezionarli, utile per quando si fa anteprima di stampa
$a_SelectedCheckboxes = $_SESSION['Checkboxes']['frm_create_fine.php'] ?? array();
unset($_SESSION['Checkboxes']['frm_create_fine.php']);

$DisplayMsg = CheckValue('DisplayMsg','n');

$PageTitle = CheckValue('PageTitle','s');
$Search_HasPEC = CheckValue('Search_HasPEC', 'n') != '' ? CheckValue('Search_HasPEC', 'n') : ($r_Customer['ManagePEC'] == 1 ? 1 : 0);
$ChiefControllerId  = CheckValue('ChiefControllerId','n');
$PrintDestinationFold  = CheckValue('PrintDestinationFold','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinter'] : $r_Customer['ForeignPrinter']) : CheckValue('PrintDestinationFold','n');
$RegularPostalFine = CheckValue('RegularPostalFine','n');
$CountryId = CheckValue('CountryId','s');
$RecordLimit = CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$a_RegularPostalFine = array("","");

$FineIcon = '';

$CreationDate = date('d/m/Y');

if($DisplayMsg){
	include(INC."/display.php");
	DIE;
}

$str_PostalFineType = $s_TypePlate!='F' ? "Atto giudiziario" : "Raccomandata A/R";
$str_WhereCountry = str_replace("ViolationTypeId=", "",$str_Where);
$str_WHhereControllers = "CityId='".$_SESSION['cityid']."' AND ('".date('Y-m-d')."' >= FromDate OR FromDate IS NULL) AND ('".date('Y-m-d')."' <= ToDate OR ToDate IS NULL) AND Disabled=0 AND ChiefController=1";

$str_CountrySelect = CreateSelectQuery('SELECT DISTINCT CountryId, VehicleCountry FROM Fine WHERE '.$str_WhereCountry , 'CountryId', 'CountryId', 'VehicleCountry', $CountryId,false);

$str_RegularPostalFine = '
    <div class="col-sm-1 BoxRowLabel">
        Tipo Invio
    </div>
    <div class="col-sm-1 BoxRowCaption">
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
        <div class="col-sm-1 BoxRowCaption">
            <select id="RegularPostalFine" name="RegularPostalFine" class="form-control">
                <option value="0" '.$a_RegularPostalFine[0] .'>Atto giudiziario</option>
                <option value="1"  '.$a_RegularPostalFine[1] .'>Posta ordinaria</option>
            </select>
        </div>
        ';

}


$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND RuleTypeId = ".$_SESSION['ruletypeid'];

if($RegularPostalFine){
    $str_Where .= " AND StatusTypeId=7 AND ControllerId IS NOT NULL";
}else {
    $str_Where .= " AND (StatusTypeId=10 OR StatusTypeId=14) AND ControllerId IS NOT NULL";
}

if ($Search_Article != ""){
    $str_Where .= " AND Article=".$Search_Article;
}
if ($Search_Paragraph != ""){
    $str_Where .= " AND Paragraph='".$Search_Paragraph."'";
}
if ($Search_Letter != ""){
    $str_Where .= " AND Letter='".$Search_Letter."'";
}

if ($Search_Plate != ""){
    $str_Where .= " AND VehiclePlate='$Search_Plate'";
}

if ($s_TypePlate == 'N'){
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['NationalPrinter'] > 0 ? "Id IN(1,7,{$r_Customer['NationalPrinter']})" : "Id IN(1,7)";
} else {
    //1 = Ufficio del comune, 7 = Ufficio SARIDA
    $str_WherePrinter = $r_Customer['ForeignPrinter'] > 0 ? "Id IN(1,7,{$r_Customer['ForeignPrinter']})" : "Id IN(1,7)";
}


$str_out .= '
<div class="row-fluid">
    <form id="f_search" action="frm_create_fine.php" method="post" autocomplete="off">
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
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1 AND RuleTypeId = {$_SESSION['ruletypeid']}","Id","Search_Violation","Id","Title",$Search_Violation,false) .'
            </div>
            <div id="DIV_Nation"'.($s_TypePlate=='F' ? '' : ' style="display:none;"').'>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-1 BoxRowCaption">
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
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control " type="text" value="'.$Search_Plate.'" name="Search_Plate" id="Search_Plate">
            </div>'
            . $str_RegularPostalFine .'
            <div class="col-sm-2 BoxRowLabel" id="DIV_Empty"'.($s_TypePlate!='F' ? '' : ' style="display:none;"').'>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-4" style="padding:0;">
                <div class="col-sm-2 BoxRowLabel">
                    Art.
                </div>    
                <div class="col-sm-2 BoxRowCaption">
                    '.CreateSelectQuery("SELECT DISTINCT Article FROM Article WHERE CityId='".$_SESSION['cityid']."' ORDER BY Article ASC", 'Search_Article', 'Article', 'Article', $Search_Article, false).'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Comma
                </div>                        
                <div class="col-sm-2 BoxRowCaption">
                     '.CreateSelectQuery("SELECT DISTINCT Paragraph FROM Article WHERE CityId='".$_SESSION['cityid']."' ORDER BY Paragraph ASC", 'Search_Paragraph', 'Paragraph', 'Paragraph', $Search_Paragraph, false).'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Lettera
                </div>     
                <div class="col-sm-2 BoxRowCaption">
                    '.CreateSelectQuery("SELECT DISTINCT Letter FROM Article WHERE CityId='".$_SESSION['cityid']."' ORDER BY Letter ASC", 'Search_Letter', 'Letter', 'Letter', $Search_Letter, false).'
                </div>
            </div>
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
            </div>  
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align:center;">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:6.8rem;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_create_fine_exe.php'.$str_GET_Parameter.'" method="post">
        	<input type="hidden" name="P" value="frm_create_fine.php" />
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
	$str_out.=
		'<div class="table_caption_H col-sm-12 text-center">
			Scegliere nazionalità targa
		</div>';
} else {


    if ($Search_HasPEC != ''){
        $str_Where .= $Search_HasPEC == 1 ? " AND Id NOT IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
    }
    
    if ($Search_PECRenotification != 0){
        $str_Where .= $Search_PECRenotification == 1 ? //" AND Id IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL AND PreviousId >0) GROUP BY Id) AND (PreviousAnomaly = 'S' OR COALESCE(PreviousSendError,'') != '')" : "";
        " AND PreviousId > 0 AND length(COALESCE(PEC,''))>0 AND PreviousPECTrespasserId is not null AND (PreviousAnomaly = 'S' OR COALESCE(PreviousSendError,'') != '')" : "";
    }

    //$str_Where .= " OR ((Article=193 AND Paragraph='2' AND KindSendDate IS NOT NULL) AND (Article=80 AND Paragraph='14' AND KindSendDate IS NOT NULL))";


    if($s_TypePlate == 'N'){
        if($r_Customer['PagoPAPayment']==1){
            $str_Where .= " AND ((PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL) OR FineTypeId in (3,4)) ";
        }      
    } else {
        if($r_Customer['PagoPAPaymentForeign']==1){
            $str_Where .= " AND ((PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL) OR FineTypeId in (3,4)) ";
        }
    }

	//$str_Where .= ($s_TypePlate=='N') ? " AND (PEC IS NULL OR PEC='')" : "";


    //  U480
    //$str_Where .= ($s_TypePlate=='N' && $_SESSION['cityid']=='U480') ? " AND TaxCode != ''" : "";

    //$str_Where .= ($s_TypePlate=='N' && $_SESSION['cityid']=='U480') ? " AND VehiclePlate!='FC843JM' AND VehiclePlate!='ET183PE'" : "";
    //$str_Where .= ($s_TypePlate=='N' && $_SESSION['cityid']=='U480') ? " AND VehiclePlate!='DZ037RA'" : "";   //Martinez Perez Andres Enrique


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

    $str_KindArticle = " OR (" .$str_Where;

    $str_Where .= " AND (
    KindSendDate IS NULL AND((Article!=193 AND Paragraph!='2') OR (Article!=80 AND Paragraph!='14')) ";





    $str_KindArticle .= " AND  KindSendDate IS NOT NULL AND((Article=193 AND Paragraph='2') OR (Article=80 AND Paragraph='14'))))";

    if ($Search_HasKindSendDate > 0) {
        $str_Where .= " AND KindSendDate IS NOT NULL and StatusTypeId <> 9";
    }

//	$strOrder = "VehiclePlate LIMIT 10,2";
	$strOrder = "FineDate ASC, FineTime ASC, Id ASC";


	if($RecordLimit>0){
		$strOrder .= " LIMIT $RecordLimit";

	}
	$table_rows = $rs->Select('V_ViolationAll',$str_Where.$str_KindArticle, $strOrder);
	//echo $str_Where.$str_KindArticle;

	$RowNumber = mysqli_num_rows($table_rows);
    $n_ContRow = 0;
	$str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12 text-center">
			    Nessun record presente
		    </div>';
	} else {
	    $n_FineId = 0;
	    $n_Row = 1;
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
				    <input '.(in_array($table_row['Id'], $a_SelectedCheckboxes) || empty($a_SelectedCheckboxes) ? 'checked ' : '').'type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '"/>
			        ';
                $n_FineId = $table_row['Id'];
            } else $str_Check = '';


            if($table_row['ControllerId']=="") $str_CssController = ' style="background-color:rgba(107,155,29,0.76)"';

            if($table_row['KindSendDate'] != ''){
                $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
            } else $FineIcon = $a_FineTypeId[$table_row['FineTypeId']];
            
			$str_out.= '
            <div class="tableRow">
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
            </div>
			<div class="clean_row HSpace4"></div>';
		}


		if($_SESSION['usertype']==3 || $_SESSION['usertype']==2) {
		    $str_ChiefController = '<div class="col-sm-7 BoxRowCaption"></div>';
		}else{
		    //TODO non è chiaro cosa va messo nel caso questo flag sia spento, il codice presente in entrambi i casi mette gli stessi dati
		    if($r_Customer['ChiefControllerList']){
		        $str_ChiefController =
		        '
                    <div class="col-sm-3 BoxRowLabel" style="height:6rem;">
                            Verbalizzante
                    </div>
                    <div class="col-sm-3 BoxRowCaption" style="height:6rem;">
                        '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WHhereControllers ORDER BY CAST(Code AS UNSIGNED)","ChiefControllerId","Id","Name",$ChiefControllerId,false,"","frm_field_required") .'
                    </div>
                    ';
		    } else {
		        $rs_ChiefController = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
		        $str_OptionChiefController = '';
		        while($r_ChiefController = mysqli_fetch_array($rs_ChiefController)){
		            $str_OptionChiefController .= '<option value="'.$r_ChiefController['Id'].'"';
		            if($ChiefControllerId==$r_ChiefController['Id'])  $str_OptionChiefController .= " SELECTED ";
		            $str_OptionChiefController .= '>'. $r_ChiefController['Name'].' Matricola '.$r_ChiefController['Code'].'</option>';
		        }
		        
		        $str_ChiefController =
		        '
                    <div class="col-sm-3 BoxRowLabel" style="height:6rem;">
                        Verbalizzante
                    </div>
                    <div class="col-sm-3 BoxRowCaption" style="height:6rem;">
                        <select class="form-control" name="ChiefControllerId">
                            '.$str_OptionChiefController.'
                        </select>
                    </div>
                    ';
		        
		    }

		}

        $str_out.= '
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
        
        $('#Search_HasPEC, #Search_PECRenotification').on('change', function() {
            $('#Search_HasPEC, #Search_PECRenotification').not(this).prop('checked', false);  
        });

        $("#f_print").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#sub_Button').prop('disabled', false);
        	else
        		$('#sub_Button').prop('disabled', true);
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
