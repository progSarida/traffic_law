<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//Usato per non perdere alcuni dati inseriti se c'è un errore
$a_POST = $_SESSION['POST'] ?? array();
unset($_SESSION['POST']);


$Id= CheckValue('Id','n');




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
        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserView($trespasser_row,"NOLEGGIO");
        
    }else{
        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];
        
        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserView($trespasser_row,"LOCATARIO");
        
    }
    
} else {
    $str_Table = "V_Violation";
    $table_row = mysqli_fetch_array($table_rows);
    $TrespasserId = (is_null($table_row['TrespasserId'])) ? 0 : $table_row['TrespasserId'];
    $whereTrespasser = "Id=".$TrespasserId;
    $trespasser_rows = $rs->Select('V_Trespasser',$whereTrespasser, "Id");
    $trespasser_row = mysqli_fetch_array($trespasser_rows);
    $strTrespasser = DivTrespasserView($trespasser_row, "TRASGRESSORE");
    
}

$str_ArticleDescription = $table_row['ArticleDescription' . LAN];

$str_ReasonDescription = $table_row['ReasonTitle' . LAN];


$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
if(strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0) $str_ArticleDescription = $r_FineOwner['ArticleDescription'.LAN];
if(strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) $str_ReasonDescription = $r_FineOwner['ReasonDescription'.LAN];



$article_rows = $rs->SelectQuery("
			SELECT
			FA.Fee,
			FA.MaxFee,
    
			ArT.ReducedPayment,
    
			FH.NotificationTypeId,
			FH.NotificationFee,
			FH.ResearchFee,
			FH.SendDate,
			FH.DeliveryDate,
			FH.ResultId
			FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
			JOIN FineHistory FH ON FA.FineId = FH.FineId
    
			WHERE FA.FineId=".$Id." AND NotificationTypeId=6");
$article_row = mysqli_fetch_array($article_rows);


$AdditionalFee = $article_row['NotificationFee'] +	$article_row['ResearchFee'];
$Fee = $article_row['Fee'];
$MaxFee = $article_row['MaxFee'];

$ReducedFee = "";
if($article_row['ReducedPayment']){
    $ReducedFee = ($article_row['Fee']*FINE_PARTIAL)+$AdditionalFee;
    $ReducedFee = NumberDisplay($ReducedFee);
}
$TotalFee = $article_row['Fee'] + $AdditionalFee;
$TotalMaxFee = $article_row['MaxFee'] + $AdditionalFee;

if($table_row['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$table_row['DetectorId']);
    $detector = mysqli_fetch_array($detectors);
    
    $DetectorTitle = $detector['Title'.LAN];
    
}



$str_Locality='    <div class="col-sm-1 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ' . $table_row['CityTitle'] . '
                    </div>
                        
';


$str_Folder = ($table_row['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation';
$str_WhereControllers = "CityId='".$_SESSION['cityid']."' AND ('".$table_row['FineDate']."' >= FromDate OR FromDate IS NULL) AND ('".$table_row['FineDate']."' <= ToDate OR ToDate IS NULL) AND Disabled=0";



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

if (isset($_SESSION['Fine_exp']['Error'])) {
    $str_out .= '<div class="alert alert-danger"> '.$_SESSION['Fine_exp']['Error'].'</div>';
}

if (isset($_SESSION['Fine_exp']['Success'])) {
    $str_out .= '<div class="alert alert-success"> '.$_SESSION['Fine_exp']['Success'].'</div>';
}

$str_out .='
<div class="row-fluid">
    <form name="f_violation" id="f_violation" method="post" action="mgmt_fine_exp_exe.php">
    <input type="hidden" name="Id" value="'.$Id.'">
    <input type="hidden" name="P" value="'.$str_BackPage.'">
    <input type="hidden" id="TrespasserId" name="TrespasserId" value="'.$TrespasserId.'">
    <input type="hidden" name="VehiclePlate" value="'.$table_row['VehiclePlate'].'">
    <input type="hidden" name="ProtocolId" value="'.$table_row['ProtocolId'].'">
    <input type="hidden" name="PreviousStatusTypeId" value="'.$table_row['StatusTypeId'].'">
    <input type="hidden" name="PreviousNote" value="'.$table_row['Note'].'">
        
        
    <div class="col-sm-6">
 	    <div class="col-sm-12" >
            <div class="col-sm-2 BoxRowLabel">
                Cronologico
            </div>
            <div class="col-sm-4 BoxRowCaption">
                ' . $table_row['ProtocolId'] . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-4 BoxRowCaption">
                ' . $table_row['Code'] . '
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Data
            </div>
            <div class="col-sm-4 BoxRowCaption">
                ' . DateOutDB($table_row['FineDate']) . '
            </div>
                    
            <div class="col-sm-2 BoxRowLabel">
                Ora
            </div>
            <div class="col-sm-4 BoxRowCaption">
                ' . TimeOutDB($table_row['FineTime']) . '
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
                '.($table_row['CountryId'] == "Z110" ? $table_row['DepartmentId'] : "").'
			</div>
  		</div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . $table_row['VehiclePlate'] .'
            </div>
            <div class="col-sm-3 BoxRowLabel">
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
            <div class="col-sm-5 BoxRowCaption">
                ' . $DetectorTitle . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Ora
                <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo.
                L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora. "><i class="glyphicon glyphicon-info-sign" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $a_TimeTypeId[$table_row['TimeTypeId']] .'
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
         ';


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

if($table_row['ControllerDate']!=""){
    $str_ControllerDate ='
            <div class="col-sm-3 BoxRowLabel">
                Data e ora accertamento
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.DateOutDB($table_row['ControllerDate']).' '. $table_row['ControllerTime'] .'
            </div>
            ';
}else{
    $str_ControllerDate ='
            <div class="col-sm-6 BoxRowCaption"></div>
            ';
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
        				    
        			<div class="col-sm-2 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $table_row['Letter'] . '
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
					<div class="col-sm-4 BoxRowCaption">
            			    
        				'.NumberDisplay($article_row['Fee']).' / ' . NumberDisplay($article_row['MaxFee']) . '
        				    
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Spese notifica
					</div>
					<div class="col-sm-2 BoxRowCaption">
				    	'.$AdditionalFee.'
					</div>
  				</div>
                <div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Importo totale
					</div>
					<div class="col-sm-8 BoxRowCaption">
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
        				<span id="span_ViolationTitle">' . $table_row['ViolationTitle'] . '</span>
					</div>
				</div>
				<div class="clean_row HSpace4"></div>
				<div class="col-sm-12">
					<div class="col-sm-2 BoxRowLabel">
        				Accertatore
					</div>
					<div class="col-sm-4 BoxRowCaption">
        				'.$table_row['ControllerCode'].' - '.StringOutDB($table_row['ControllerName']).'
					</div>
					'. $str_ControllerDate .'
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
                ' . StringOutDB($table_row['Note']) . '
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Data archiviazione
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date frm_field_required" name="ArchiveDate" id="ArchiveDate" value="'.($a_POST['ArchiveDate'] ?? '').'" style="width:12rem">
                        <span id="span_date" style="position:relative;top:-26px;left:140px;"></span>
                    </div>
                </div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Motivo archiviazione
					</div>
					<div class="col-sm-8 BoxRowCaption">
                        '. CreateSelect("Reason","ReasonTypeId=3 AND Disabled=0" ,"TitleIta","ReasonId","Id","TitleIta",$a_POST['ReasonId'] ?? '',true) .'
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel" style="height:6.4rem;">
        				Note archiviazione
					</div>
					<div class="col-sm-8 BoxRowCaption" style="height:6.4rem;">
                        <textarea class="form-control frm_field_string" name="Note" style="width:30rem;height:5.5rem">'.($a_POST['Note'] ?? '').'</textarea>
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				'. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN,$a_POST['ArchiveDate'] ?? $table_row['VehicleTypeId'],true) .'
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-10 BoxRowCaption">
                         <input class="form-control frm_field_string frm_field_required" type="text" name="VehiclePlate" value="'.($a_POST['VehiclePlate'] ?? $table_row['VehiclePlate']).'">
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
 				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-10 BoxRowCaption">
                         <input type="hidden" id="VehicleCountry" name="VehicleCountry" value="'.$table_row['VehicleCountry'].'"/>
                        '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title",$table_row['CountryId'],true) .'
					</div>
  				</div>
                <div class="clean_row HSpace4"></div>
  			    <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-10 BoxRowCaption">
			    <input type="text" name="VehicleBrand" id="VehicleBrand" class="find_list form-control" style="width:18rem" value="'.($a_POST['VehicleBrand'] ?? $table_row['VehicleBrand']).'" />	
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
 	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-10 BoxRowCaption">
			    <input type="text" name="VehicleModel" id="VehicleModel" class="find_list form-control" style="width:18rem" value="'.($a_POST['VehicleModel'] ?? $table_row['VehicleModel']).'" />	
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-10 BoxRowCaption">
                <input type="text" name="VehicleColor" id="VehicleColor" class="find_list form-control" style="width:18rem" value="'.($a_POST['VehicleColor'] ?? $table_row['VehicleColor']).'" />	
			</div>
		</div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">Verbalizzante</div>
        	<div class="col-sm-4 BoxRowCaption">
                '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WhereControllers ORDER BY CAST(Code AS UNSIGNED)","FineChiefControllerId","Id","Name",$a_POST['FineChiefControllerId'] ?? $table_row['FineChiefControllerId'],false) .'
					</div>
            <div class="col-sm-2 BoxRowLabel font_small">Verbalizzante stampa</div>
			<div class="col-sm-4 BoxRowCaption">
                '. CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WhereControllers ORDER BY CAST(Code AS UNSIGNED)","UIFineChiefControllerId","Id","Name",$a_POST['UIFineChiefControllerId'] ?? $table_row['UIFineChiefControllerId'],false) .'
  				</div>
			<div class="table_caption_H col-sm-12 alert-warning" style="height:auto;">
                <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;NOTA: nella stampa della rinotifica, se presente, "Verbalizzante" avrà priorità su "Verbalizzante stampa".
            </div>
        </div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12" >
        			<div class="col-sm-12 BoxRowLabel" style="height:19rem;">
        				<input type="radio" name="duplicate" value="0" CHECKED>Solo archivio <br />
        				<input type="radio" name="duplicate" value="1">Notifica ad altro trasgressore <br />
        				<input type="radio" name="duplicate" value="2">Rinotifica stesso trasgressore <br />
                        <!-- <input type="radio" name="duplicate" value="6">Rinotifica a trasgressore non firmatario <br /> -->
						<input type="radio" name="duplicate" value="3">Rinotifica per leasing/noleggio a lungo termine <br />
                        <input type="radio" name="duplicate" value="7">Rinotifica aggiungendo l\'obbligato in solido (noleggio a breve termine)<br />
						<input type="radio" name="duplicate" value="4">Rinotifica tramite messo <br /> 
						<input type="radio" name="duplicate" value="5" disabled>Notifica d\'ufficio <br />
					</div>
				</div>
  				<div class="col-sm-12">
			        <div class="col-sm-12 BoxRowCaption" style="height:4rem;">
					    <div id="exp_AddFee" class="desc" style="display: none;">
					        <input type="checkbox" value="1" name="AddNotificationFee" CHECKED>Aggiungi spese notifica
					        <input type="checkbox" value="1" name="AddResearchFee">Aggiungi spese ricerca
					    </div>
					</div>
  				</div>
  				<div class="col-sm-12">
				    <div class="col-sm-12 BoxRowCaption" style="height:38rem;">
                <div class="col-sm-12" style="margin-bottom:2rem;">
	                    <div id="exp_ReceiveDate" class="desc" style="display: none;">
                        <div class="col-sm-4 BoxRowLabel">Data identificazione dati soggetto</div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date frm_field_required" name="ReceiveDate" value="'.($a_POST['ReceiveDate'] ?? '').'">
                        </div>
    			    </div>
					    </div>
                        <div id="exp_Search" class="desc" style="display: none;">
                            
                       		<div class="col-sm-12 BoxRowLabel" style="text-align:center">
                                Assegnazione trasgressore:<br />
                                &nbsp;<span id="span_name"></span>
                                 Forza lingua
                                 <select name="LanguageId">
                                    <option value="0"></option>
                                    <option value="1">Italiano</option>
                                    <option value="2">Inglese</option>
                                    <option value="3">Tedesco</option>
                                    <option value="4">Spagnolo</option>
                                    <option value="5">Francese</option>
                                 </select>
					        </div>
                            <ul class="nav nav-tabs" id="mioTab">
                                <li class="active" id="tab_company"><a href="#company" data-toggle="tab">DITTA</a></li>
                                <li id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">PERSONA</a></li>
                            </ul>
                            
                            <div class="tab-content">
                                <div class="tab-pane active" id="company">
                                    <div class="row-fluid">
                                        <div class="col-sm-12 BoxRow">
                                            <div class="col-sm-4 BoxRowLabel">
                                                <input type="hidden" name="Genre" id="Genre" value="D">
                                                Ragione sociale
                                            </div>
                                            <div class="col-sm-5 BoxRowCaption">
                                                <input name="CompanyName_S" id="CompanyName_S" type="text" style="width:20rem">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="Trespasser">
                                    <div class="row-fluid">
                            
                                        <div class="col-sm-12 BoxRow">
                                            <div class="col-sm-2 BoxRowLabel">
                                                Cognome
                                            </div>
                                            <div class="col-sm-4 BoxRowCaption">
                                                <input type="text" name="Surname_S" id="Surname_S" style="width:12rem">
                                            </div>
                            
                                            <div class="col-sm-2 BoxRowLabel">
                                                Nome
                                            </div>
                                            <div class="col-sm-4 BoxRowCaption">
                                                <input type="text" name="Name_S" id="Name_S" style="width:12rem">
                                            </div>
                            
                                        </div>
                            
                                    </div>
                            
                                </div>
                                <div id="trespasser_content" class="col-sm-12" style="height:150px;overflow:auto"></div>
                            </div>
					</div>
  				</div>
  			</div>
  		</div>
  		<div class="col-sm-6">
                            
            '.$strTrespasser.'
                
            <div class="col-sm-12 BoxRow" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    DOCUMENTAZIONE
                </div>
            </div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:19rem;">
                <div class="example">
                    <div id="fileTreeDemo_1" class="BoxRowLabel" style="height:17rem;overflow:auto"></div>
                </div>
            </div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:40.2rem;">
                <div class="imgWrapper" style="height:40.2rem;overflow:auto">
                    <img id="preview" class="iZoom"  />
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default" id="update" value="Archivia">
                    <input type="button" class="btn btn-default" id="back" value="Indietro">
                 </div>
            </div>
        </div>
  	</div>
    </form>
</div>
';

echo $str_out;

unset($_SESSION['Fine_exp']['Error']);
unset($_SESSION['Fine_exp']['Success']);

?>
<script type="text/javascript">
    $('document').ready(function(){
        var del = false;
        //$('#preview').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});


        <?= $str_tree ?>

        <?= $str_Img ?>


        $('#back').click(function(e) {

            e.preventDefault();

            window.location="<?= $str_BackPage.$str_GET_Parameter ?>"
            return false;
        });

        $('#f_violation').bootstrapValidator({
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
            }
        }).on('success.form.bv', function(){
            var duplicate = $("input:radio[name='duplicate']:checked").val();

            if($('#FineChiefControllerId').val()=='' && $('#UIFineChiefControllerId').val()=='' && duplicate > 0){
                alert("Selezionare almeno un verbalizzante");
                return false;
            } else if(!confirm("Si sta per archiviare il verbale in maniera definitiva. Continuare?")){
            	return false;
        	}
        });

        $('#ArchiveDate').change(function() {
            var str = $('#ArchiveDate').val();
            if(str.length==8){
                $('#ArchiveDate').val( str.substr(0,2) + "/" + str.substr(2,2) + "/" + str.substr(4) );

            }
        });
        
        $('#CountryId').change(function(){

            $('#VehicleCountry').val($( "#CountryId option:selected" ).text());
        });

        $("input:radio[name='duplicate']").on('change', function() {
        	var duplicate = $(this).val();
            if(duplicate==0){
                $('.desc').hide();
            }
            if(duplicate==1){
                $('#exp_AddFee').show();
                $('#exp_Search').show();
                $('#exp_ReceiveDate').show();
                $('#exp_ChiefController').hide();
            }
            if(duplicate==2){
                $('#exp_AddFee, #exp_ReceiveDate').show();
                $('#exp_Search, #exp_ChiefController').hide();
            }
            if(duplicate==3){
                $('#exp_ReceiveDate').show();
                $('#exp_AddFee').show();
                $('#exp_Search').show();
                $('#exp_ChiefController').hide();
            }
            if(duplicate==4){
                $('#exp_AddFee').show();
                $('#exp_Search').hide();
                $('#exp_ReceiveDate').show();
                $('#exp_ChiefController').show();
            }
            if(duplicate==7){
                $('#exp_ReceiveDate, #exp_AddFee, #exp_Search').show();
                $('#exp_ChiefController').hide();
            }
        });

        var min_length = 2; // min caracters to display the autocomplete
        $('#CompanyName_S').keyup(function(){

            var CompanyName = $(this).val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();

            if (CompanyName.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {CompanyName:CompanyName, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);


                    }
                });
            } else {
                $('#trespasser_content').hide();

            }
        });

        $('#Name_S').keyup(function(){
            var Name = $(this).val();
            var Surname = $('#Surname_S').val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();

            if (Name.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);

                    }
                });
            } else {
                $('#trespasser_content').hide();

            }
        });

        $('#Surname_S').keyup(function(){
            var Name = $('#Name_S').val();
            var Surname = $(this).val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();

            if (Surname.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);

                    }
                });
            } else {
                $('#trespasser_content').hide();

            }
        });

        $('#tab_company').click(function(){
            $('#Genre').val('D');
            $('#Surname').val('');
            $('#Name').val('');

        });

        $('#tab_Trespasser').click(function(){
            $('#Genre').val('M');
            $('#CompanyName').val('');
        });

    });

</script>
<?php
include(INC."/footer.php");
/*
UFFICIO
Selezionando il tipo NOTIFICA D'UFFICIO, verra' inserita una nuova notifica senza le spese di notifica e ricerca ma con lo stato di stampa, la data di stampa e la data di notifica uguali a quelle della notifica esistente.


MANO
Selezionando il tipo RI-NOTIFICA A MANO, verra' inserita una nuova notifica da stampare in cui le spese di notifica precedenti saranno sommate a quelle della notifica a mani e le spese di ricerca saranno uguali a quelle della notifica precedente e il trasgressore/obbligato sarà lo stesso della notifica precedente che è stata ANNULLATA.



*/