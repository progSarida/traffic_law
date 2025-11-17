<?php

$RuleTypeId = "";
$RuleTitle = "";
$FineDate = "";
$FineTime= "";
$FineCode= "";
$FinePrefix = "";
$FineBlock = "";
$FineAddress = "";
$Numero_blocco = "";
$Prefix = "";
$StartNumber = "";
$EndNumber = "";
$CountryId = "";
$FineCodeTemp = "";
$ControllerDate = "";
$ControllerTime= "";
$b_showControllerData = false;

$KindCreateDate = "";
$KindSendDate   = "";

if ($isPageUpdate) {

    $RuleTypeId = $r_FirstArticle['RuleTypeId'];
    
    if ($RuleTypeId != "") {
        $RuleTitle = mysqli_fetch_array($rs->SelectQuery("SELECT Title FROM ".MAIN_DB.".Rule WHERE Id=$RuleTypeId"))['Title'];
        $str_RuleType = '<span>'.$RuleTitle.'</span><input value="'.$RuleTypeId.'" type="hidden" id="RuleTypeId" name="RuleTypeId">';
    }
    
    $FineDate       = DateOutDB($r_Fine['FineDate']);
    $FineTime       = TimeOutDB($r_Fine['FineTime']);
    
    // Insertita Ago20
    $FineTypeId     = StringOutDB($r_Fine['FineTypeId']);
    $KindCreateDate = DateOutDB($r_Fine['KindCreateDate']);
    $KindSendDate   = DateOutDB($r_Fine['KindSendDate']);

    $ControllerDate = DateOutDB($r_Fine['ControllerDate']);
    $ControllerTime = isset($r_Fine['ControllerTime']) ? TimeOutDB($r_Fine['ControllerTime']) : null;
    
    if ($InsertionType == 3){
        $FineCodeTemp = $r_Fine['Code'];
    } else $FineCodeTemp = StringOutDB(substr($r_Fine['Code'],0,strlen($r_Fine['Code'])-5));
    
    //se il tipo di atto inserito è preinserimento (che vale 3) mostro i dati di validazione 
    if ($r_Fine['FineTypeId'] == 1)
       $b_showControllerData = true;
    
    /*$FineCodeElements = explode("/",$FineCodeTemp);
    if (count($FineCodeElements) == 1) {
        $FineCode = $FineCodeElements[0];
    } else if (count($FineCodeElements) == 2) {
        $FineCode = $FineCodeElements[0];
        if( is_numeric($FineCodeElements[1]))
            $FineBlock = $FineCodeElements[1];
        else 
            $FinePrefix;
    } else if (count($FineCodeElements) == 3) {
        $FineCode = $FineCodeElements[0];
        if( !is_numeric($FineCodeElements[1]))
            $FinePrefix = $FineCodeElements[1];
        $FineBlock =  $FineCodeElements[2];
    }*/
 
    $FineAddress = StringOutDB($r_Fine['Address']);
    
    $Numero_blocco = StringOutDB($r_Receipt['Numero_blocco']);
    $StartNumber = StringOutDB($r_Receipt['StartNumber']);
    $EndNumber = StringOutDB($r_Receipt['EndNumber']);
    $Prefix = StringOutDB($r_Receipt['Preffix']);
    
    $CountryId = $r_Fine['CountryId'];

} else if ($isLatestFine) {

    $FineDate = DateOutDB($r_PreviousFine['FineDate']);
    $FineTime = null; //TimeOutDB($r_PreviousFine['FineTime']);
    
    if (isset($_REQUEST['insertionType']) && ($_REQUEST['insertionType'] == 3 || $_REQUEST['insertionType'] == 4)){
        $FineCodeTemp = $r_PreviousFine['Code'];
    } else $FineCodeTemp = StringOutDB(substr($r_PreviousFine['Code'],0,strlen($r_PreviousFine['Code'])-5));
    //$FineCode = (ctype_digit($FineCode)) ? (int)$FineCode + 1 : $FineCode;
    
    if (isset($_REQUEST['insertionType']) && $_REQUEST['insertionType'] == 3)
        $b_showControllerData = true;
    
    /*$FineCodeElements = explode("/",$FineCodeTemp);
    if (count($FineCodeElements)>1)
        $FineCode = $FineCodeElements[0];
    else 
        $FineCode = $FineCodeTemp;*/
    
    $FineAddress = StringOutDB($r_PreviousFine['Address']);
    
    $Numero_blocco = StringOutDB($r_Receipt['Numero_blocco']);
    $StartNumber = StringOutDB($r_Receipt['StartNumber']);
    $EndNumber = StringOutDB($r_Receipt['EndNumber']);
    $Prefix = StringOutDB($r_Receipt['Preffix']);
    
    //$CountryId = $r_PreviousFine['CountryId'];
    $CountryId = "Z000"; //preseleziona sempre Nazione Italia
    
    $str_RuleType = '<span>'.$_SESSION['ruletypetitle'].'</span><input value="'.$_SESSION['ruletypeid'].'" type="hidden" id="RuleTypeId" name="RuleTypeId">';
} else {
    $CountryId = "Z000"; //preseleziona sempre Nazione Italia
    
    //se il tipo di atto inserito è preinserimento (che vale 3) mostro i dati di validazione
    if (isset($_REQUEST['insertionType']) && $_REQUEST['insertionType'] == 3)
        $b_showControllerData = true;
    
    $str_RuleType = '<span>'.$_SESSION['ruletypetitle'].'</span><input value="'.$_SESSION['ruletypeid'].'" type="hidden" id="RuleTypeId" name="RuleTypeId">';
}

$FineCodeElements = explode("/",$FineCodeTemp);
if (count($FineCodeElements) == 1) {
    $FineCode = $FineCodeElements[0];
} else if (count($FineCodeElements) == 2) {
    $FineCode = $FineCodeElements[0];
    if( is_numeric($FineCodeElements[1]))
        $FineBlock = $FineCodeElements[1];
    else
        $FinePrefix = $FineCodeElements[1];
} else if (count($FineCodeElements) == 3) {
    $FineCode = $FineCodeElements[0];
    if( !is_numeric($FineCodeElements[1]))
        $FinePrefix = $FineCodeElements[1];
        $FineBlock =  $FineCodeElements[2];
}

if ($_form_type_id ==1){
    $class = null;
}else{
    $class = 'frm_field_required';
}
$code = "";


$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);


if($n_Code>0){
    $str_Locality='
                   
                        <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="Locality" class="form-control">
                            <option></option>
               
    ';

    while($r_Row = mysqli_fetch_array($rs_Row)){
        $str_Locality.='<option value="'.$r_Row['Id'].'">'.$r_Row['Title'].'</option>';

    }

    $str_Locality.='        </select>
                        </div>
                    ';

}else{
    $str_Locality='    <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                        ' . $_SESSION['citytitle'] . '
                        </div>

    ';
}


$str_Toponym ='
        <div class="col-sm-2 BoxRowLabel">
            Toponimo
        </div>
        <div class="col-sm-3 BoxRowCaption">';

$str_Toponym .= CreateSelect(MAIN_DB.'.Toponym', '1=1', 'Id', 'Toponym', 'Title', 'Title', '', false);
$str_Toponym .='</div>';
$str_Toponym .='
        <div class="col-sm-4 BoxRowLabel">
            Seleziona Strada
        </div>
        <div class="col-sm-3 BoxRowCaption">';

$str_Toponym .= CreateSelect('StreetType', '1=1', 'Id', 'StreetType', 'Title', 'Title', '', false);
$str_Toponym .= '</div>';

/*
if (isset($fineId) && $fineId > 0){
    $getCode = $rs->SelectQuery("SELECT Code FROM Fine WHERE Id = ".$fineId);

    $DefaultCode = mysqli_fetch_array($getCode)['Code'];
    if ($DefaultCode != '/'.$_SESSION['year']) {
        $explodeCode = explode("/", $DefaultCode);
        if (count($explodeCode) == 2) {
            $code = $explodeCode[0];
            if (is_numeric($code)) {
                $code = (int)$code + 1;
            } else {
                $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $code);
                $prefix = $parts[0];
                $num = (int)$parts[1] + 1;
                $code = $prefix . $num;
            }
        } elseif (count($explodeCode) == 3) {
            if (is_numeric($explodeCode[0])) {
                $code = (int)$explodeCode[0] . "/" . (int)$explodeCode[1] + 1;
            } else {
                $prefix = $explodeCode[0];
                $num = (int)$explodeCode[1] + 1;
                $code = $prefix . "/" . $num;
            }
        }
    }
}
*/

$notifica = '<div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Genere
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.$str_RuleType.'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                <input type="hidden" id="VehicleCountry" name="VehicleCountry" value="Italia"/>
                    '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title",$CountryId,false,15,"frm_field_required") .'
                </div>
                <div class="clean_row HSpace4"></div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Riferimento
                </div>';

//TODO aggiornare quando la modifica dei nolleggi punterà a mgmt_report_upd.php
//Modifica la struttura del campo del riferimento se si stà modificando/inserendo un preinserimento o nolleggio
if (($isPageUpdate && $InsertionType == 3) || (isset($_REQUEST['insertionType']) && ($_REQUEST['insertionType'] == 3 || $_REQUEST['insertionType'] == 4))){
    $notifica .= '
                <div class="col-sm-6 BoxRowCaption">
                    <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" value="'.$FineCodeTemp.'" title="Progressivo bolletta">
                </div>';
} else {
    $notifica .= '
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" value="'.$FineCode.'" title="Progressivo bolletta">
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="InputPrefix" id="InputPrefix" type="text" class="form-control frm_field_string" value="'.$FinePrefix.'" title="Lettera blocco bollettario">
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="InputBlockNumber" id="InputBlockNumber" type="text" class="form-control frm_field_string" value="'.$FineBlock.'" title="Numero blocco bollettario">
                </div>';
}

$notifica .=   '<div class="col-sm-1 BoxRowCaption">
                  '
                 // . '<span id="Prefix"></span><span id="BlockNumber"></span>'
                .($isPageUpdate && $InsertionType == 3 ? '' : "/".$_SESSION['year']).'
                </div>
                <div id="span_code" class="BoxRowCaption col-sm-3 table_caption_error" style="display:none;color: white;"><small></small></div>
                <div id="Notification">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-2 BoxRowLabel">
                        Notifica
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select class="form-control frm_field_required" name="NotificationType" id="NotificationType">
                            <option></option>
                            <option value="2">Su strada</option>
                            <option value="1">Differita</option>
                        </select>
                    </div>
                    <div class="col-sm-8 BoxRowLabel"></div>
                </div>
                
                <div Id="Receipt" style="display:none;">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-2 BoxRowLabel">
                        N.Bollettario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <span id="ReceiptNumber">'.$Numero_blocco.'</span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lettera
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <span id="ReceiptPrefix">'.$Prefix.'</span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Dal numero
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <span id="ReceiptStart">'.$StartNumber.'</span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Al numero
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <span id="ReceiptEnd">'.$EndNumber.'</span>
                    </div>
                </div>
            </div>';

            $str_Locality_Data =  '
        	    <div class="clean_row HSpace4"></div>
        	            	
        	    '.$notifica.'
                <div class="clean_row HSpace4"></div>

        	    <div class="col-sm-12">

                    <div class="col-sm-2 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date fine_date txt-warning" value="'.$FineDate.'"  name="FineDate" id="FineDate">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time txt-warning" value="'.$FineTime.'" name="FineTime" id="FineTime">
                         <span id="span_time"></span>
                    </div>
                    '.$str_Locality.'
                </div>
                <div class="clean_row HSpace4"></div> 

                ';
// PM Ago 20                
if ($isPageUpdate && ($FineTypeId == '2' ||$FineTypeId == '1')) {

    $str_Locality_Data .= '

    <div class="col-sm-12">
    
    <div class="col-md-3 BoxRowLabel">
    Data creazione avviso bonario
    </div>
    <div class="col-md-3 BoxRowCaption">
        <input type="text" class="form-control frm_field_date" value="'.$KindCreateDate.'"  name="KindCreateDate" id="creaBonario" style="width:12rem; border: solid 3px #c49916;">
    <span id="span_date"></span>
    </div>
    
    <div class="col-md-3 BoxRowLabel">
    Data invio avviso bonario
    </div>
    <div class="col-md-3 BoxRowCaption">
        <input type="text" class="form-control frm_field_date kind_date " value="'.$KindSendDate.'"  name="KindSendDate" id="invioBonario" style="width:12rem; border: solid 3px #c49916;">
    <span id="span_date"></span>
    </div>
    '.'
    </div>
    <div class="clean_row HSpace4"></div> ';
    
}

if($b_showControllerData) {
    $str_Locality_Data .=  '
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Data validazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date" value="'.$ControllerDate.'"  name="ControllerDate" id="ControllerDate" style="width:12rem; border: solid 3px #c49916;">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-2 BoxRowLabel">
                        Ora validazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time" value="'.$ControllerTime.'" name="ControllerTime" id="ControllerTime" style="width:8rem; border: solid 3px #c49916;">
                         <span id="span_time"></span>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>';
}

$str_Locality_Data .=  '
                <div class="col-sm-12">
					'. $str_Toponym .'
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" value="'.htmlspecialchars($FineAddress).'" name="Address" id="Address">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
';
