<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');






$Id = CheckValue('Id','n');
//mgmt_violation.php
$Search_ViolationArticle = CheckValue ('Search_ViolationArticle', 'n');

$str_GET_Parameter .= "&Search_ViolationArticle=$Search_ViolationArticle";

$InsuranceDate = CheckValue('InsuranceDate','s');



$rs_Fine = $rs->Select('V_Violation',"Id=".$Id, "Id");


$r_Fine = mysqli_fetch_array($rs_Fine);
$rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$r_Fine['TrespasserId']);
$r_Trespasser = mysqli_fetch_array($rs_Trespasser);
$str_Trespasser = DivTrespasserView($r_Trespasser, "TRASGRESSORE");

$str_ArticleDescription = $r_Fine['ArticleDescription' . LAN];

if ($r_Fine['ExpirationDate'] != "") $str_ArticleDescription .= " (".DateOutDB($r_Fine['ExpirationDate']).")";

$str_ReasonDescription = $r_Fine['ReasonTitle' . LAN];


$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);

$rs_Article = $rs->SelectQuery("
    SELECT A.*,AT.Fee,AT.MaxFee
    FROM FineArticle FA
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ArticleTariff AT ON A.Id=AT.ArticleId AND AT.Year={$_SESSION['year']}
    WHERE FineId=$Id");
$r_Article = mysqli_fetch_array($rs_Article);

if(strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0) $str_ArticleDescription = $r_FineOwner['ArticleDescription'.LAN];
if(strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) $str_ReasonDescription = $r_FineOwner['ReasonDescription'.LAN];


$b_Insurance        = false;


if($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2") $b_Insurance = true;

$str_AdditionalArticle = '';
$AdditionalFee = 0;
if($r_Fine['ArticleNumber']>1){
    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=$Id", "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $AdditionalFee += $r_AdditionalArticle['Fee'];
        
        $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate'] != "") ? " (".DateOutDB($r_AdditionalArticle['ExpirationDate']).")" : "";
        $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.LAN]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.LAN] : $r_AdditionalArticle['ArticleDescription' . LAN].$str_ExpirationDate;
        
        
        
        if($r_AdditionalArticle['Article']==193 && $r_AdditionalArticle['Paragraph']=="2") $b_Insurance = true;
        
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
        				    
        			<div class="col-sm-2 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_AdditionalArticle['Letter'] . '
 					</div>
  				</div>
	        	<div class="col-sm-12" style="height:6rem;">
        			<div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
            			<span id="span_Article" style="font-size:1.1rem;">' . $str_AdditionalArticleDescription . '</span>
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

if($b_Insurance){
    $str_InsuranceDate = '
        <div class="col-sm-3 BoxRowLabel">
            Data assicurazione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="form-control frm_field_date" name="InsuranceDate" id="InsuranceDate" value="'.$InsuranceDate.'">
        </div>
    ';
    
}else{
    $str_InsuranceDate = '
        <div class="col-sm-5 BoxRowLabel"></div>
    ';
}


if($r_Customer['CityUnion']>1 || $_SESSION['usertype']==3 || $_SESSION['usertype']==2) {
    $str_ChiefController = '<div class="col-sm-6 BoxRowCaption"></div>';
}else{
    
    if($r_Customer['ChiefControllerList']){
        $str_ChiefController =
        '
                    <div class="col-sm-2 BoxRowLabel">
                            Verbalizzante
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        '. CreateSelectConcat("SELECT Id, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId='".$_SESSION['cityid']."' ORDER BY Name","ChiefControllerId","Id","Name","",false,null,"frm_field_required") .'
                    </div>
                    ';
    } else {
        $rs_ChiefController = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
        $str_OptionChiefController = '';
        while($r_ChiefController = mysqli_fetch_array($rs_ChiefController)){
            $str_OptionChiefController .= '<option value="'.$r_ChiefController['Id'].'">'. $r_ChiefController['Name'].' Matricola '.$r_ChiefController['Code'].'</option>';
        }
        
        $str_ChiefController =
        '
                    <div class="col-sm-2 BoxRowLabel">
                        Verbalizzante
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <select class="form-control" name="ChiefControllerId">
                            '.$str_OptionChiefController.'
                        </select>
                    </div>
                    ';
        
    }
}



$charge_rows = $rs->Select('CustomerCharge',"CreationType=5 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$str_Charge = ($r_Fine['CountryId']=='Z000') ? "National" : "Foreign";

$s_TypePlate        = ($r_Fine['CountryId']=='Z000') ? "N" : "F";




if($charge_row[$str_Charge.'TotalFee']>0){
    $TotalCharge = $charge_row[$str_Charge.'TotalFee'];
} else  {
    if($charge_row[$str_Charge.'NotificationFee']>0){
        $TotalCharge = $charge_row[$str_Charge.'NotificationFee'] + $charge_row[$str_Charge.'ResearchFee'];
    }else{
        $TotalCharge = $charge_row[$str_Charge.'ResearchFee'];
    }
    
}

$TotalFee = $TotalCharge  + $r_Fine['Fee'] + $AdditionalFee;




if($r_Fine['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
    $detector = mysqli_fetch_array($detectors);
    
    $DetectorTitle = $detector['Title'.LAN];
    
}



$str_Locality='    <div class="col-sm-1 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Fine['CityTitle'] . '
                    </div>
                        
';


$str_Folder = ($r_Fine['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation';



$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);






if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);
    
    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/' . $doc_row['Documentation'];
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
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
                
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
                    <div class="col-sm-2 BoxRowLabel">
        				Riferimento
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				' . $r_Fine['Code'] . '
					</div>
                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        ' . DateOutDB($r_Fine['FineDate']) . '
                    </div>
                            
                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . TimeOutDB($r_Fine['FineTime']) . '
                    </div>
  				</div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    '.$str_Locality.'
        			<div class="col-sm-1 BoxRowLabel">
        				Strada
					</div>
	        	    <div class="col-sm-1 BoxRowCaption">
        				'. $r_Fine['StreetTypeTitle'].'
					</div>
        			<div class="col-sm-1 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-5 BoxRowCaption">
        				' . $r_Fine['Address'] . '
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. $r_Fine['VehicleTitleIta'] .'
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
                        '. $r_Fine['VehicleCountry'] .'
					</div>
        			<div class="col-sm-1 BoxRowLabel">
        				Dip.
					</div>
					<div class="col-sm-1 BoxRowCaption">
                    ';

if($r_Fine['CountryId']=="Z110"){
    $str_out .= $r_Fine['DepartmentId'];
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
        				' . $r_Fine['VehiclePlate'] .'
					</div>
        			<div class="col-sm-3 BoxRowLabel">
        				Massa
					</div>
					<div class="col-sm-3 BoxRowCaption">
                        ' . $r_Fine['VehicleMass'] .'
					</div>
  				</div>
                            
   				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        ' . $r_Fine['VehicleColor'] . '
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    ' . $r_Fine['VehicleBrand'] . '
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        ' . $r_Fine['VehicleModel'] . '
					</div>
  				</div>
       			<div class="clean_row HSpace4"></div>
 				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Rilevatore
					</div>
					<div class="col-sm-5 BoxRowCaption">
        				' . $DetectorTitle . '
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Ora
        				<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo.
                        L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora. "><i class="glyphicon glyphicon-info-sign" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. $a_TimeTypeId[$r_Fine['TimeTypeId']] .'
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                 ';


if ($r_Fine['Speed'] > 0) {
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
					    ' . round($r_Fine['SpeedLimit']) . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($r_Fine['SpeedControl']) . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($r_Fine['Speed']) . '
 					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                ';
}
if ($r_Fine['TimeTLightFirst'] > 0) {
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
					     ' . $r_Fine['TimeTLightFirst'] . '
 					</div>
 					<div class="col-sm-4 BoxRowLabel">
        				Secondo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_Fine['TimeTLightSecond'] . '
 					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                ';
}

if($r_Fine['ControllerDate']!=""){
    $str_ControllerDate =
    '
        <div class="col-sm-3 BoxRowLabel">
            Data e ora accertamento
        </div>
        <div class="col-sm-3 BoxRowCaption">
            '.DateOutDB($r_Fine['ControllerDate']).' '. $r_Fine['ControllerTime'] .'
        </div>
        ';
}else{
    
    
    $str_ControllerDate =
    '
            <div class="col-sm-6 BoxRowCaption"></div>
            ';
    $str_ButtonController = '';
    
    
}
if($r_Fine['ControllerCode']!=""){
    $str_Controller = '
        <div class="col-sm-2 BoxRowLabel">
            Accertatore
		</div>
	    <div class="col-sm-4 BoxRowCaption">
	        '. $r_Fine['ControllerCode'].' - '.StringOutDB($r_Fine['ControllerName']) .'
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



$str_out .= '
	        	<div class="col-sm-12" >
        			<div class="col-sm-2 BoxRowLabel">
        				Articolo
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    ' . $r_Fine['Article'] . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Comma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_Fine['Paragraph'] . '
 					</div>
        				    
        			<div class="col-sm-2 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_Fine['Letter'] . '
 					</div>
  				</div>
	        	<div class="col-sm-12" style="height:6rem;">
        			<div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
            			<span id="span_Article" style="font-size:1.1rem;">' . $str_ArticleDescription . '</span>
        			</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Min/Max edittale
					</div>
					<div class="col-sm-8 BoxRowCaption">
            			    
        				'.NumberDisplay($r_Fine['Fee']).' / ' . NumberDisplay($r_Fine['MaxFee']) . '
        				    
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
					<div class="col-sm-10 BoxRowCaption">
        				<input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
        				<span id="span_ViolationTitle">' . $r_Fine['ViolationTitle'] . '</span>
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
                        ' . $r_Fine['Note'] . '
					</div>
  				</div>
                <div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Stato pratica
					</div>
					<div class="col-sm-9 BoxRowCaption">
                        '. $r_Fine['StatusTitle'] .'
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				' . $str_Trespasser . '
  				    
                </form>
                        
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
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
            </div>

            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12 BoxRowLabel table_caption_I text-center">
            	PARAMETRI DI ELABORAZIONE
        	</div>
                        
        	<div class="clean_row HSpace4"></div>

            <form name="f_print" id="f_print" action="frm_create_fine_exe.php?'.$str_Parameters.'" method="post">
                <input type="hidden" name="ultimate" id="ultimate" value=""/>
                <input type="hidden" name="P" id="P" value="mgmt_violation_act.php" />
                <input type="hidden" name="TypePlate" value="'. $s_TypePlate .'" />
                <input type="hidden" name="checkbox[]" value="' . $r_Fine['Id'] . '" />
                <input type="hidden" name="Id" value="' . $Id . '" />
                <input type="hidden" name="CreationType" value="5" />
                <input type="hidden" name="ProtocolIdAssigned" value="' . $r_Fine['ProtocolIdAssigned'] . '" />
        
                '. $str_InsuranceDate.'
                '. $str_ChiefController .'
    
                <div class="clean_row HSpace4"></div>';
        	
if($r_Article['Article'] == '193' && $r_Article['Paragraph'] == '2'){
    $str_out .= '
                <div class="col-sm-3 BoxRowLabel">
                	Articolo
            	</div>
                <div class="col-sm-6 BoxRowCaption">
                    '.CreateSelectQueryExtended("SELECT A.*,AT.Fee,AT.MaxFee,CONCAT_WS(' ',A.Article,A.Paragraph,A.Letter,' - Min:',AT.Fee,' Max:',AT.MaxFee) AS FullArticle FROM Article A JOIN ArticleTariff AT ON AT.ArticleId=A.Id AND AT.Year={$_SESSION['year']} WHERE Article=193 AND Paragraph=2 AND CityId='{$_SESSION['cityid']}' ORDER BY A.Article,A.Paragraph,A.Letter", 'ArticleSelect', 'ArticleSelect', 'Id', 'FullArticle', array('Fee','MaxFee','DescriptionIta'), $r_Article['Id'], true, null, 'frm_field_required').'
                </div>
                <div class="col-sm-3 BoxRowCaption">
            	</div>
                        
                <div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel">
                	Min/Max edittale
            	</div>
                <div id="ArticleFee" class="col-sm-9 BoxRowCaption">
            	</div>
                        
            	<div class="clean_row HSpace4"></div>
                        
                <div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
                	Descrizione Articolo
            	</div>
                <div id="ArticleDescription" class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
            	</div>';
}
$str_out .= '
            </form>
    	</div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <button class="btn btn-default" id="back" style="margin-top:1rem;">Indietro</button>
                    <button type="button" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;">Anteprima di stampa</button>
    		        <input type="checkbox" id="ultimatecheck" style="margin-left:5rem;">DEFINITIVA
                </div>
            </div>
        </div>
  	</div>
</div>';



echo $str_out;
?>
<script type="text/javascript">

	function fillArticleData(){
		var formatter = new Intl.NumberFormat('it-IT', {
			  minimumFractionDigits: 2,
			  maximumFractionDigits: 2,
			});
		var minFee = formatter.format($('#ArticleSelect').find(":selected").data('fee'));
		var maxFee = formatter.format($('#ArticleSelect').find(":selected").data('maxfee'));
		var description = $('#ArticleSelect').find(":selected").data('descriptionita');
		
		$('#ArticleFee').text(minFee + ' / ' + maxFee);
		$('#ArticleDescription').text(description);
	}
	
    $('document').ready(function(){
    
   		fillArticleData();
        
        //$('#preview_img').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= $str_tree ?>

        <?= $str_Img ?>

 		$('#ArticleSelect').on('change', fillArticleData);
 
        $('#back').click(function(){
            window.location="<?= 'mgmt_violation.php'.$str_GET_Parameter ?>"
            return false;
        });

        $('#ultimatecheck').click(function(){
            if($('#ultimatecheck').is(":checked")) {
                $('#sub_Button').html('Stampa defitiva');
                $('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
                $('#P').val( "mgmt_violation.php" );
				$('#ultimate').val('1');
            }else{
                $('#sub_Button').html('Anteprima di stampa');
                $('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
                $('#P').val( "mgmt_violation_act.php" );
                $('#ultimate').val('');
            }
        });



        $('#sub_Button').click(function() {
            if($('#ultimatecheck').is(":checked")) {
                var c = confirm("Si stanno per creare i verbali in maniera definitiva. Continuare?");
                if(c){
					$('#sub_Button').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
					$('#sub_Button').prop('disabled', true);
                    $('#f_print').submit();
                }

            }else{
                $('#f_print').submit();
            }
        });




    });

</script>
<?php
include(INC."/footer.php");