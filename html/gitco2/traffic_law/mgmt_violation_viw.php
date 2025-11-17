<?php
require_once ("mgmt_violation_cmn.php");
include(INC."/function_postalCharge.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//$a_LanF = unserialize(LANGUAGE);
$Id= CheckValue('Id','n');

$isValidation=CheckValue("Validation",'b');

$AnomalyBrandModelId = CheckValue ('AnomalyBrandModelId', 's');

if($str_PageCallBack=="mgmt_null.php"){
    $str_Where .= " AND StatusTypeId>=90 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

}else{
    $str_Where .= " AND StatusTypeId<11 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

}


$select_acertatore = $rs->SelectQuery("Select * from FineAdditionalController where FineId = $Id");
$many_accertatore = "";
if (mysqli_num_rows($select_acertatore) >0){
    $find_accertatore = $rs->SelectQuery("SELECT FineAdditionalController.*,Controller.Name,Controller.Code from FineAdditionalController 
    LEFT JOIN Controller on FineAdditionalController.ControllerId = Controller.Id WHERE FineAdditionalController.FineId=$Id");
   while ($row_acce = mysqli_fetch_array($find_accertatore)){
       $many_accertatore.= '
        <div class="col-sm-2 BoxRowLabel">
            Accertatore 
		</div>						
	    <div class="col-sm-6 BoxRowCaption">
	        '. $row_acce['Code'].' - '.StringOutDB($row_acce['Name']) .'
	    </div>
	     <div class="col-sm-4 BoxRowCaption"></div>
       ';
   }
}

$strOrder = "Id DESC";


$str_Next = "";
$str_Previous = "";
$str_Table = "";



$table_rows = $rs->Select('V_Violation',"Id=$Id", "Id");

$FindNumber = mysqli_num_rows($table_rows);
if($FindNumber==0){
    $str_Table = "V_ViolationRent";
    $table_rows = $rs->Select('V_ViolationRent',"Id=$Id", "Id");
    $table_row = mysqli_fetch_array($table_rows);

    $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=11 AND FineId=".$Id);
    $FindNumber = mysqli_num_rows($rs_TrespasserRent);
    if($FindNumber==0){

        $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=10 AND FineId=".$Id);
        $FindNumber = mysqli_num_rows($rs_TrespasserRent);
        if($FindNumber==0){
            $TrespasserId = 0;

        }else{
            $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
            $TrespasserId = $r_TrespasserRent['TrespasserId'];
        }

        $fnd = $rs->SelectQuery("SELECT FineNotificationDate,ReceiveDate from FineTrespasser WHERE FineId = $Id AND TrespasserId = $TrespasserId");
        $r_fnd = mysqli_fetch_array($fnd);

        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserView($trespasser_row,"NOLEGGIO",DateOutDB($r_fnd['FineNotificationDate']),DateOutDB($r_fnd['ReceiveDate']));

    }else{

        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];

        $fnd = $rs->SelectQuery("SELECT FineNotificationDate,ReceiveDate from FineTrespasser WHERE FineId = $Id AND TrespasserId = $TrespasserId");
        $r_fnd = mysqli_fetch_array($fnd);
        //print_r($r_fnd);

        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserView($trespasser_row,"LOCATARIO",DateOutDB($r_fnd['FineNotificationDate']),DateOutDB($r_fnd['ReceiveDate']));

    }

} else {
    $str_Table = "V_Violation";
    $table_row = mysqli_fetch_array($table_rows);
    $TrespasserId = (is_null($table_row['TrespasserId'])) ? 0 : $table_row['TrespasserId'];
    $whereTrespasser = "Id=".$TrespasserId;

    $fnd = $rs->SelectQuery("SELECT FineNotificationDate,ReceiveDate from FineTrespasser WHERE FineId = $Id AND TrespasserId = $TrespasserId");
    $r_fnd = mysqli_fetch_array($fnd);

    $trespasser_rows = $rs->Select('V_Trespasser',$whereTrespasser, "Id");
    $trespasser_row = mysqli_fetch_array($trespasser_rows);
    $strTrespasser = DivTrespasserView($trespasser_row, "TRASGRESSORE",DateOutDB($r_fnd['FineNotificationDate']),DateOutDB($r_fnd['ReceiveDate']));

}

$str_ArticleDescription = $table_row['ArticleDescription' . LAN];
if (@$table_row['ExpirationDate'] != "") $str_ArticleDescription .= " (".DateOutDB($table_row['ExpirationDate']).")";

$str_ReasonDescription = $table_row['Progressive'].' - '.$table_row['ReasonTitle' . LAN];

$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
if(strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0) $str_ArticleDescription = $r_FineOwner['ArticleDescription'.LAN];
if(strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) $str_ReasonDescription = $r_FineOwner['ReasonDescription'.LAN];

readparametersAndBuildWhere();
$rows=getRowsForNext(LIST_TABLE, LIST_ORDER, "FineId");
$nextId=getNextId($rows,$Id);
$previousId=getNextId($rows,$Id,false);
if (!is_null($previousId))
    $str_Previous = '<a href="' . $str_GET_FilterPage . '&Id=' . $previousId . '&Validation='.$isValidation.'"><i class="glyphicon glyphicon-arrow-left" style="font-size:2rem;color:#fff"></i></a>';
if (!is_null($nextId))
    $str_Next = '<a href="' . $str_GET_FilterPage . '&Id=' . $nextId . '&Validation='.$isValidation.'"><i class="glyphicon glyphicon-arrow-right" style="font-size:2rem;color:#fff"></i></a>';

$rs_FineTrespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id, "TrespasserTypeId");
$numT = mysqli_num_rows($rs_FineTrespasser);
$isYoung = false;

while($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
    
    $license = $r_FineTrespasser['LicenseDate'];
    $TrespasserTyepId = $r_FineTrespasser['TrespasserTypeId'];
    
    if ($TrespasserTyepId == 1 || $TrespasserTyepId == 11){
        if($license!=''){
            $d_LicenseDate1 = new DateTime($license);
            $d_LicenseDate2 = new DateTime($table_row['FineDate']);
            
            $d_DiffLicense = $d_LicenseDate2->diff($d_LicenseDate1);
            $d_DiffLicense->format('%R');
            $n_YoungAge =  $d_DiffLicense->y;
            
            $isYoung = ($n_YoungAge<3) ? true : false;
        }
    }
    
}

$str_AdditionalArticle = '';
$LicensePoint = 0;
$AdditionalFee = 0;
if($table_row['ArticleNumber']>1){
    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=$Id", "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $AdditionalFee += $r_AdditionalArticle['Fee'];
        
        $LicensePoint += ($isYoung) ? $r_AdditionalArticle['YoungLicensePoint'] : $r_AdditionalArticle['LicensePoint'];

        $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate'] != "") ? " (".DateOutDB($r_AdditionalArticle['ExpirationDate']).")" : "";
        $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.LAN]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.LAN] : $r_AdditionalArticle['ArticleDescription' . LAN].$str_ExpirationDate;

        $str_AdditionalArticle .= '
                <div class="clean_row HSpace4"></div> 
	        	<div class="col-sm-12" >
        			<div class="col-sm-2 BoxRowLabel">
        				Articolo
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    ' . $r_AdditionalArticle['Article'] . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Comma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_AdditionalArticle['Paragraph'] . '
 					</div>

        			<div class="col-sm-1 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-1 BoxRowCaption">
        				' . $r_AdditionalArticle['Letter'] . '
 					</div>
                    <div class="col-sm-1 BoxRowLabel">
        				Punti
					</div>
					<div class="col-sm-1 BoxRowCaption">
        				' . (($isYoung) ? $r_AdditionalArticle['YoungLicensePoint'] : $r_AdditionalArticle['LicensePoint']) . '
 					</div>
  				</div>    					
	        	<div class="col-sm-12" style="min-height:6rem;">
        			<div class="col-sm-12 BoxRowLabel" style="height:auto;min-height:6rem;background-color: rgb(40, 114, 150);">
            			<span id="span_Article" style="font-size:1.1rem;">' . utf8_encode($str_AdditionalArticleDescription) . '</span>
        			</div>
  				</div>   																																								
  				<div class="clean_row HSpace4"></div> 
	        	<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Min/Max edittale
					</div>
					<div class="col-sm-8 BoxRowCaption">
        				
        				'.NumberDisplay($r_AdditionalArticle['Fee']).' / ' . NumberDisplay($r_AdditionalArticle['MaxFee']) . '
        				
					</div>
  				</div>
  				';

    }

}

$LicensePoint = ($LicensePoint > 15) ? 15 : $LicensePoint;

$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$str_Charge = ($table_row['CountryId']=='Z000') ? "National" : "Foreign";

if($charge_row[$str_Charge.'TotalFee']>0){
    $TotalCharge = $charge_row[$str_Charge.'TotalFee'];
} else  {
    if($charge_row[$str_Charge.'NotificationFee']>0){
        $TotalCharge = $charge_row[$str_Charge.'NotificationFee'] + $charge_row[$str_Charge.'ResearchFee'];
    }else{
        $TotalCharge = $charge_row[$str_Charge.'ResearchFee'];

        if($TrespasserId>0){
            $postalcharge=getPostalCharge($_SESSION['cityid'],$table_row['FineDate']);
            $TotalCharge += $postalcharge['Zone'.$trespasser_row['ZoneId']];
        }
    }

}

$TotalFee = $TotalCharge  + $table_row['Fee'] + $AdditionalFee;

if($TrespasserId > 0){
    $a_LanF = unserialize(LANGUAGE);
    $rs_LanguageF = $rs->SelectQuery("SELECT LanguageId FROM Trespasser WHERE Id={$TrespasserId}");
    $LanF = $rs->getArrayLine($rs_LanguageF)['LanguageId'];
    if($LanF > 0)
        $LanF = $a_LanF[$LanF];
    else $LanF =$a_LanF[1];
} else {
    $LanF =$a_LanF[1];
}



if($table_row['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$table_row['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title'.$LanF].' (Matr. '.$detector['Number'].')';

}



$str_Locality='    <div class="col-sm-1 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ' . $table_row['CityTitle'] . '
                    </div>

';


$str_Folder = ($table_row['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation';



$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);






if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);

    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/' . $doc_row['Documentation'];
    if (strtolower(substr($doc_row['Documentation'], -3)) == "jpg") {
        $str_Img = ' 
            $("#preview").attr("src","' . $File . '");
            $("#preview_img").show();
        ';
    } else {
        //$str_Img = '$("#preview_img").html("<iframe style=\"width:100%; height:100%\" src=\"' . $File . '\"></iframe>");';
        $str_Img = '
            $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
            $("#preview_doc").show();
            ';


    }

    $str_tree = '
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
            
            var FileType = file.substr(file.length - 3);

            if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }            
        });
    ';

}



$str_out .= '

    	<div class="row-fluid">
        	<div class="col-sm-6">
  	            <div class="col-sm-12" >
  	              	<div class="col-sm-1 BoxRowCaption">
        				' . $str_Previous . '
					</div>
                    <div class="col-sm-2 BoxRowLabel">
        				Riferimento
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $table_row['Code'] . '
					</div>
                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        ' . DateOutDB($table_row['FineDate']) . '
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        ' . TimeOutDB($table_row['FineTime']) . '
                    </div>					
					<div class="col-sm-1 BoxRowCaption">
        				' . $str_Next . '
					</div>					
  				</div> 
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    '.$str_Locality.'
        			<div class="col-sm-1 BoxRowLabel">
        				Strada
					</div>
	        	    <div class="col-sm-1 BoxRowCaption">
        				'. $table_row['StreetTypeTitle'].'
					</div>
        			<div class="col-sm-1 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-5 BoxRowCaption">
        				' . $table_row['Address'] . '
					</div>
  				</div>  
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. $table_row['VehicleTitleIta'] .'
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
                        '. $table_row['VehicleCountry'] .'
					</div>
        			<div class="col-sm-1 BoxRowLabel">
        				Dip.
					</div>
					<div class="col-sm-1 BoxRowCaption">
                    ';

if($table_row['CountryId']=="Z110"){
    $str_out .= $table_row['DepartmentId'];
} else {
    $str_out .='';
}

$str_CssUserTypePlate = ($_SESSION['usertype']==2) ? 'style="font-weight: bold;font-size: 1.4rem;"' : '';




$str_out .= '
					</div>
  				</div>   
  				<div class="clean_row HSpace4"></div>
  	        	<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-3 BoxRowCaption" '.$str_CssUserTypePlate.'>
        				' . $table_row['VehiclePlate'] .'
					</div>			
        			<div class="col-sm-1 BoxRowLabel">
        				P
					</div>
					<div class="col-sm-1 BoxRowCaption">
        				<span class="'.($table_row['TemporaryPlate'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon-remove text-danger").'" style="line-height:1.6rem;"></span>
					</div>			
        			<div class="col-sm-1 BoxRowLabel">
        				Massa
					</div>
					<div class="col-sm-3 BoxRowCaption">
                        ' . $table_row['VehicleMass'] .' 	
					</div>
  				</div>  				
  				
   				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        ' . $table_row['VehicleColor'] . '	
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    ' . $table_row['VehicleBrand'] . '	
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        ' . $table_row['VehicleModel'] . '	
					</div>
  				</div>                            
       			<div class="clean_row HSpace4"></div>				
 				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Rilevatore
					</div>
					<div class="col-sm-6 BoxRowCaption font_small">
        				' . $DetectorTitle . '
					</div>
                	<div class="col-sm-2 BoxRowLabel">
                        Ora
                        <i data-toggle="tooltip" data-placement="top" data-container="body" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo. L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
               		</div>
					<div class="col-sm-2 BoxRowCaption">
        				'. $a_TimeTypeId[$table_row['TimeTypeId']] .'
					</div>					
  				</div>
  				<div class="clean_row HSpace4"></div>				
                 ';

$str_Control = "";
if ($table_row['Speed'] > 0) {
    $str_out .= '
  	            <div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				VELOCITA
					</div>
  				</div> 
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Limite
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    ' . round($table_row['SpeedLimit']) . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($table_row['SpeedControl']) . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($table_row['Speed']) . '
 					</div>
  				</div> 
  				<div class="clean_row HSpace4"></div>    
                ';
    $str_Control = '
        <div class="clean_row HSpace4"></div>  
        <div class="col-sm-4 BoxRowLabel">
            Velocità
        </div>
        <div class="col-sm-8 BoxRowCaption">
            ' . round($table_row['SpeedControl']) . '
        </div>
    ';
}
if ($table_row['TimeTLightFirst'] > 0) {
    $str_out .= '
  	            <div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				SEMAFORO
					</div>
  				</div>  
	        	<div class="col-sm-12" id="DIV_TLight">
        			<div class="col-sm-4 BoxRowLabel">
        				Primo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
					     ' . $table_row['TimeTLightFirst'] . '    				
 					</div>
 					<div class="col-sm-4 BoxRowLabel">
        				Secondo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $table_row['TimeTLightSecond'] . '
 					</div>
  				</div> 
  				<div class="clean_row HSpace4"></div>
                ';
}
$str_ButtonController ='';



if($table_row['ControllerDate']!=null && $table_row['ControllerDate']!=""){
    $str_ControllerDate =
        '
        <div class="col-sm-3 BoxRowLabel">
            Data e ora accertamento
        </div>						
        <div class="col-sm-3 BoxRowCaption">
            '.DateOutDB($table_row['ControllerDate']).' '. $table_row['ControllerTime'] .'
        </div>            
        ';
}else{
    if($_SESSION['usertype']==2 && $isValidation){
        $str_ControllerDate =
            '
            <div class="col-sm-3 BoxRowLabel">
                Data e ora accertamento
            </div>						
            <div class="col-sm-3 BoxRowCaption">
                <span id="span_ControllerDate"></span>
            </div>            
            ';
        $str_ButtonController = '<i id="Add_ControllerId" class="fa fa-cogs" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>';

    } else {

        $str_ControllerDate =
            '
            <div class="col-sm-6 BoxRowCaption"></div>
            ';
        $str_ButtonController = '';
    }

}
if($table_row['ControllerCode']!=""){
    $str_Controller = '
        <div class="col-sm-2 BoxRowLabel">
            Accertatore 
		</div>						
	    <div class="col-sm-4 BoxRowCaption">
	        '. $table_row['ControllerCode'].' - '.StringOutDB($table_row['ControllerName']) .'
	    </div>
    ';

} else {
    $str_Controller = '
        <div class="col-sm-2 BoxRowLabel">
            Accertatore '. $str_ButtonController .'
		</div>						
	    <div class="col-sm-4 BoxRowCaption"><span id="span_ControllerId"></span></div>
    ';
}
$str_Controller .= $str_ControllerDate;
$str_Controller .= $many_accertatore;

$rs_ArticleTariff = $rs->SelectQuery("SELECT LicensePoint,YoungLicensePoint FROM ArticleTariff WHERE ArticleId=".$table_row['ArticleId']);
$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

$LicensePoint += ($isYoung) ? ($r_ArticleTariff['YoungLicensePoint']) : $r_ArticleTariff['LicensePoint'];

$AdditionalDescriptionIta = $rs->Select('FineOwner', 'FineId='.$table_row['Id']);
$AdditionalDescriptionIta = mysqli_fetch_array($AdditionalDescriptionIta);
$AdditionalDescriptionIta = trim($AdditionalDescriptionIta['AdditionalDescriptionIta']);
if($AdditionalDescriptionIta == ''){
    $AdditionalSanction = $rs->SelectQuery('SELECT AdditionalSanction.TitleIta FROM ArticleTariff JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id WHERE ArticleTariff.ArticleId='.$table_row['ArticleId']." AND ArticleTariff.Year=".$_SESSION['year']);
    $AdditionalSanction = mysqli_fetch_array($AdditionalSanction);
    $AdditionalDescriptionIta = $AdditionalSanction['TitleIta'];
}


    $str_out .= '													
	        	<div class="col-sm-12" >
        			<div class="col-sm-2 BoxRowLabel">
        				Articolo
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    ' . $table_row['Article'] . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Comma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $table_row['Paragraph'] . '
 					</div>

        			<div class="col-sm-1 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-1 BoxRowCaption">
        				' . $table_row['Letter'] . '
 					</div>
        			<div class="col-sm-1 BoxRowLabel">
        				Punti
					</div>
					<div class="col-sm-1 BoxRowCaption">
        				' . (($isYoung) ? $r_ArticleTariff['YoungLicensePoint'] : $r_ArticleTariff['LicensePoint']) . '
 					</div>

  				</div>
  				
	        	<div class="col-sm-12" style="min-height:6rem;">
        			<div class="col-sm-12 BoxRowLabel" style="height:auto;min-height:6rem;background-color: rgb(40, 114, 150);">
            			<span id="span_Article" style="font-size:1.1rem;">' . StringOutDB($str_ArticleDescription) . '</span>
        			</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel font_small">
                        Sanzione accessoria
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height:auto;min-height:2.2rem;">
                        <span id="span_Article" style="font-size:1.1rem;">' . StringOutDB($AdditionalDescriptionIta ). '</span>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Min/Max edittale
					</div>
					<div class="col-sm-8 BoxRowCaption">
        				
        				'.NumberDisplay($table_row['Fee']).' / ' . NumberDisplay($table_row['MaxFee']) . '
        				
					</div>
  				</div>
  				'. $str_AdditionalArticle .'    	
                <div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Spese notifica
					</div>
					<div class="col-sm-2 BoxRowCaption">
				    	'.$TotalCharge.'
					</div>	        	
        			<div class="col-sm-4 BoxRowLabel">
        				Importo totale
					</div>
					<div class="col-sm-4 BoxRowCaption">
        				' . NumberDisplay($TotalFee) . '
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo infrazione
					</div>
					<div class="col-sm-4 BoxRowCaption">
        				<input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
        				<span id="span_ViolationTitle">' . StringOutDB($table_row['ViolationTitle']) . '</span>
					</div>
                    <div class="col-sm-3 BoxRowLabel">
                        Totale Punti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . $LicensePoint . '
                    </div>      
					'. $str_Controller .'
  				</div> 
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12" style="height:6rem;">
        			<div class="col-sm-3 BoxRowLabel" style="height:6rem;">
        				Mancata contestazione
					</div>
					<div class="col-sm-9 BoxRowCaption" style="height:6rem;">			    
        				<span id="span_ReasonDescription" style="height:6rem;width:40rem;font-size:1.1rem;">' . StringOutDB($str_ReasonDescription) . '</span>
					</div>
  				</div> 
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12" style="height:6.4rem;">
        			<div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
        				Note operatore
					</div>
					<div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                        ' . $table_row['Note'] . '
					</div>
  				</div>  
                <div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Stato pratica
					</div>
					<div class="col-sm-9 BoxRowCaption">
                        '. $table_row['StatusTitle'] .'
					</div>
  				</div>
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Testo dinamico collegato
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        ' . getFormDynamicTitle($table_row['FineTypeId'],$table_row['StatusTypeId'],$table_row['CityId'],$table_row['CountryId'],$table_row['TrespasserCountryId'],$table_row['ViolationTypeId']). '
                    </div>
                </div> 				
  				<div class="clean_row HSpace4"></div>
  				' . $strTrespasser . '
        	</div>             
  		</div>
        <div class="col-sm-6">
            <div class="col-sm-12" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    DOCUMENTAZIONE
                </div>
            </div> 
            <div class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
                <div class="example">
                    <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                                    <div id="Div_Controller_Window" style="width:20rem; position:absolute; top:0;right:0; border:2px solid yellow">
                    <div class="row-fluid">
                        <div class="col-sm-4 BoxRowLabel">
                            Tipo Veicolo
                        </div>
                        <div class="col-sm-8 BoxRowCaption">
                            '. $table_row['VehicleTitleIta'] .'
                        </div>
                        <div class="clean_row HSpace4"></div>
                           
                        <div class="col-sm-4 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-8 BoxRowCaption">
                            '. $table_row['VehicleCountry'] .'
                        </div>
                        <div class="clean_row HSpace4"></div>	
                        
                        <div class="col-sm-4 BoxRowLabel">
                            Targa
                        </div>
                        <div class="col-sm-8 BoxRowCaption" style="font-weight: bold; font-size: 1.6rem;">
                            '. $table_row['VehiclePlate'] .'
                        </div>
                        '. $str_Control .'
                    </div> 
                </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
            </div>	
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <button class="btn btn-default" id="back" style="margin-top:1rem;">Indietro</button>
                </div>    
            </div>
        </div>        
  	</div>
</div>';



echo $str_out;
?>
<script type="text/javascript">
    $('document').ready(function(){
        $( "#Div_Controller_Window" ).draggable();

        //$('#preview_img').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= $str_tree ?>

        <?= $str_Img ?>

        $('#back').click(function(){
           var brandmodel = "<?= $AnomalyBrandModelId ?>";
           if (brandmodel != ""){
        	   window.history.go(-1); return false;
           } else {
        	   window.location="<?= $str_BackPage.$str_GET_Parameter ?>";
           }
        });

        $(".fa-cogs").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });


        $("#Add_ControllerId").click(function () {

            var id=<?= $Id ?>;

            $.ajax({
                url: 'ajax/ajx_upd_controller_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id: id},
                success: function (data) {
                    if(data.Message!=null && data.Message!='')
                        alert(data.Message);
                    else{
                        $("#Add_ControllerId").hide();
                        $('#span_ControllerId').html(data.ControllerId);
                        $('#span_ControllerDate').html(data.ControllerDate);
                    }

                }
            });

        });




    });

</script>
<?php
include(INC."/footer.php");
