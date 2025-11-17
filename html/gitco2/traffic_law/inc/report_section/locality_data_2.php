<?php

$FineDate = "";
$FineTime= "";
$FineCode= "";
$FineAddress = "";
$Numero_blocco = "";
$Prefix = "";
$StartNumber = "";
$EndNumber = "";
$CountryId = "";

if ($isPageUpdate){
    $FineDate = DateOutDB($r_Fine['FineDate']);
    $FineTime = TimeOutDB($r_Fine['FineTime']);
    
    $FineCodeTemp = StringOutDB(substr($r_Fine['Code'],0,strlen($r_Fine['Code'])-5));
    
    $FineCodeElements = explode("/",$FineCodeTemp);
    if (count($FineCodeElements)>1)
        $FineCode = $FineCodeElements[0];
    else
        $FineCode = $FineCodeTemp;
        
    $FineAddress = StringOutDB($r_Fine['Address']);
    
    $Numero_blocco = StringOutDB($r_Receipt['Numero_blocco']);
    $StartNumber = StringOutDB($r_Receipt['StartNumber']);
    $EndNumber = StringOutDB($r_Receipt['EndNumber']);
    $Prefix = StringOutDB($r_Receipt['Preffix']);
    
    $CountryId = $r_Fine['CountryId'];
} else if ($isLatestFine) {
    $FineDate = DateOutDB($r_PreviousFine['FineDate']);
    $FineTime = TimeOutDB($r_PreviousFine['FineTime']);
    $FineCodeTemp = StringOutDB(substr($r_PreviousFine['Code'],0,strlen($r_PreviousFine['Code'])-5));
    //$FineCode = (ctype_digit($FineCode)) ? (int)$FineCode + 1 : $FineCode;
    
    $FineCodeElements = explode("/",$FineCodeTemp);
    if (count($FineCodeElements)>1)
        $FineCode = $FineCodeElements[0];
    else 
        $FineCode = $FineCodeTemp;
    
    $FineAddress = StringOutDB($r_PreviousFine['Address']);
    
    $Numero_blocco = StringOutDB($r_Receipt['Numero_blocco']);
    $StartNumber = StringOutDB($r_Receipt['StartNumber']);
    $EndNumber = StringOutDB($r_Receipt['EndNumber']);
    $Prefix = StringOutDB($r_Receipt['Preffix']);
    
    //$CountryId = $r_PreviousFine['CountryId'];
    $CountryId = "Z000"; //preseleziona sempre Nazione Italia
} else {
    $CountryId = "Z000"; //preseleziona sempre Nazione Italia
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
                            <select name="Locality" class="form-control" style="width:10rem;">
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
                    '. CreateSelect("RuleType","CityId='".$_SESSION['cityid']."'","Id","RuleTypeId","Id","Title","",true,10, "frm_field_required") .'
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
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" value="'.$FineCode.'" style="width:12rem;" >
                    <input type="hidden" name="InputPrefix" id="InputPrefix" value="">
                    <input type="hidden" name="InputBlockNumber" id="InputBlockNumber" value="">
                    <span id="span_code"></span>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                  '
                  . '<span id="Prefix"></span><span id="BlockNumber"></span>'
                  ."/".$_SESSION['year'].'
                </div>
                <div id="Notification">
                    <div class="col-sm-1 BoxRowLabel" style="border-left:1px solid white;">
                        Notifica
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select class="form-control frm_field_required" name="NotificationType" id="NotificationType" style="width:10rem;">
                            <option></option>
                            <option value="2">Su strada</option>
                            <option value="1">Differita</option>
                        </select>
                    </div>
                </div>
                
                <div Id="Receipt" style="display:none;">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-2 BoxRowLabel">
                        N.Bollettario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <span id="ReceiptNumber" style="display:none;">'.$Numero_blocco.'</span>
                        <select class="form-control" id="ReceiptSelect"></select>
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
                        <input type="text" class="form-control frm_field_date fine_date" value="'.$FineDate.'"  name="FineDate" id="FineDate" style="width:12rem; border: solid 3px #c49916;">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time" value="'.$FineTime.'" name="FineTime" id="FineTime" style="width:8rem; border: solid 3px #c49916;">
                         <span id="span_time"></span>
                    </div>
                    '.$str_Locality.'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
					'. $str_Toponym .'
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" value="'.$FineAddress.'" name="Address" id="Address" style="width:40rem;">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
';

