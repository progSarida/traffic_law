<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
include(INC . "/function_postalCharge.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Id= CheckValue('Id','n');
//mgmt_violation.php
$Search_ViolationArticle = CheckValue ('Search_ViolationArticle', 'n');

$str_GET_Parameter .= "&Search_ViolationArticle=$Search_ViolationArticle";






$str_Where = "StatusTypeId<11 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];




$strOrder = "Id DESC";



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


$str_AdditionalArticle = '';
$AdditionalFee = 0;
if($table_row['ArticleNumber']>1){
    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=$Id", "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $AdditionalFee += $r_AdditionalArticle['Fee'];
        $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.LAN]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];

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

if($table_row['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$table_row['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title'.LAN];
}

$str_Folder = ($table_row['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation' ;



$str_tree = "";
$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);

$str_Locality='    <div class="col-sm-1 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ' . $table_row['CityTitle'] . '
                    </div>

';


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
        <form name="f_violation" id="f_violation" method="post" action="mgmt_violation_del_exe.php">
        <input type="hidden" name="Id" value="'.$Id.'">
        <input type="hidden" name="P" value="'.$str_BackPage.'">
        <input type="hidden" name="CountryId" value="'.$table_row['CountryId'].'">

    	<div class="row-fluid">
        	<div class="col-sm-6">
  	            <div class="col-sm-12" >
                   <div class="col-sm-2 BoxRowLabel">
        				Riferimento
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				' . $table_row['Code'] . '
					</div>
                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . DateOutDB($table_row['FineDate']) . '
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
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
        				'. $aVehicleTypeId[$table_row['VehicleTypeId']] .'
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

$str_out .= '
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
	        	<div class="col-sm-12" id="DIV_Speed" style="display:none;">
        			<div class="col-sm-2 BoxRowLabel">
        				Limite
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
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
	        	<div class="col-sm-12" id="DIV_TLight" style="display:none;">
        			<div class="col-sm-4 BoxRowLabel">
        				Primo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
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
        $str_ControllerDate =
            '
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
					<div class="col-sm-10 BoxRowCaption">
        				<input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
        				<span id="span_ViolationTitle">' . $table_row['ViolationTitle'] . '</span>
					</div>
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
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    
                    <button class="btn btn-default" id="delete">Cancella</button>
                    <button class="btn btn-default" id="back">Indietro</button>
                </div>    
            </div>
        </div>        

  	</div>
    </form>
</div>';



echo $str_out;
?>
<script type="text/javascript">
    $('document').ready(function(){
        var del = false;
        //$('#preview').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= $str_tree ?>

        $('#back').click(function(){
            window.location="<?= $str_BackPage.$str_GET_Parameter ?>"
            return false;
        });
        $('#delete').click(function(){
            del = true;
        });

        $('#f_violation').submit(function() {
            if(del){
                var c = confirm("Si sta per CANCELLARE il verbale in maniera definitiva. Continuare?");
                return c;
            }
        });


    });

</script>
<?php
include(INC."/footer.php");
